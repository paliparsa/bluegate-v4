<?php
namespace App\Http\Controllers\App;

use App\Domain\Growth\CouponService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShopController extends Controller
{
    public function index()
    {
        $products = DB::table('products')
            ->where('active', true)
            ->orderBy('sort_order')
            ->get()
            ->filter(function ($product) {
                $haystack = strtolower(($product->name ?? '').' '.($product->slug ?? ''));
                return str_contains($haystack, 'standard')
                    || (str_contains($haystack, 'pro') && !str_contains($haystack, 'boost'));
            })
            ->values();

        $plans = DB::table('plans')
            ->where('active', true)
            ->whereIn('product_id', $products->pluck('id'))
            ->orderBy('sort_order')
            ->orderBy('base_price')
            ->get()
            ->groupBy('product_id');

        // Only show locations that currently have at least one sellable, healthy node.
        $locations = DB::table('locations as l')
            ->where('l.active', true)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('nodes as n')
                    ->whereColumn('n.location_id', 'l.id')
                    ->where('n.sales_enabled', true)
                    ->where('n.maintenance_mode', false)
                    ->where('n.status', 'online');
            })
            ->select('l.id', 'l.name', 'l.city', 'l.country_code', 'l.flag', 'l.service_type')
            ->orderBy('l.sort_order')
            ->get();

        return view('app.buy', compact('products', 'plans', 'locations'));
    }

    public function order(Request $r, CouponService $coupons)
    {
        $data = $r->validate([
            'plan_id' => 'required|uuid',
            'location_id' => 'required|uuid',
            'coupon_code' => 'nullable|string|max:60',
        ]);

        try {
            $orderId = DB::transaction(function () use ($r, $data, $coupons) {
                $plan = DB::table('plans')->where('id', $data['plan_id'])->where('active', true)->lockForUpdate()->firstOrFail();
                $product = DB::table('products')->where('id', $plan->product_id)->where('active', true)->firstOrFail();

                $publicName = strtolower(($product->name ?? '').' '.($product->slug ?? ''));
                $isPublicBluePing = str_contains($publicName, 'standard')
                    || (str_contains($publicName, 'pro') && !str_contains($publicName, 'boost'));
                if (!$isPublicBluePing) {
                    throw ValidationException::withMessages(['plan_id' => 'این سرویس در فروشگاه عمومی BluePing قابل خرید نیست.']);
                }

                $location = DB::table('locations as l')
                    ->where('l.id', $data['location_id'])
                    ->where('l.active', true)
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('nodes as n')
                            ->whereColumn('n.location_id', 'l.id')
                            ->where('n.sales_enabled', true)
                            ->where('n.maintenance_mode', false)
                            ->where('n.status', 'online');
                    })
                    ->select('l.id', 'l.name', 'l.city', 'l.country_code', 'l.flag')
                    ->first();

                if (!$location) {
                    throw ValidationException::withMessages(['location_id' => 'این لوکیشن در حال حاضر برای فروش در دسترس نیست.']);
                }

                $couponResult = $coupons->calculate(
                    $data['coupon_code'] ?? null,
                    $r->user()->id,
                    $plan,
                    (float) $plan->base_price
                );
                $discount = (float) $couponResult['discount'];
                $payable = max(0, (float) $plan->base_price - $discount);

                $orderId = (string) Str::uuid();
                $itemId = (string) Str::uuid();
                $configuration = [
                    'location_id' => $location->id,
                    'location_name' => trim(($location->flag ? $location->flag.' ' : '').$location->name),
                    'location_city' => $location->city,
                    'builder' => [
                        'traffic_gb' => $plan->unlimited_traffic ? null : $plan->traffic_gb,
                        'duration_days' => $plan->unlimited_duration ? null : $plan->duration_days,
                        'device_limit' => $plan->device_limit,
                    ],
                ];

                DB::table('orders')->insert([
                    'id' => $orderId,
                    'order_number' => 'BG'.now()->format('ymd').strtoupper(Str::random(6)),
                    'user_id' => $r->user()->id,
                    'status' => 'pending_payment',
                    'subtotal' => $plan->base_price,
                    'discount' => $discount,
                    'wallet_used' => 0,
                    'payable' => $payable,
                    'currency' => $plan->currency,
                    'metadata' => json_encode([
                        'coupon_code' => $couponResult['coupon']->code ?? null,
                        'purchase_builder' => true,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('order_items')->insert([
                    'id' => $itemId,
                    'order_id' => $orderId,
                    'product_id' => $product->id,
                    'plan_id' => $plan->id,
                    'quantity' => 1,
                    'unit_price' => $plan->base_price,
                    'total_price' => $plan->base_price,
                    'configuration' => json_encode($configuration),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($couponResult['coupon']) {
                    $coupons->redeem($couponResult['coupon'], $r->user()->id, $orderId, $discount);
                }

                return $orderId;
            }, 3);

            return redirect()->route('app.orders')->with('success', 'سفارش ساخته شد؛ روش پرداخت را انتخاب کن.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
