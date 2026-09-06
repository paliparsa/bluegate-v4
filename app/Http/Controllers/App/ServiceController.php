<?php
namespace App\Http\Controllers\App;

use App\Domain\Services\Service;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::where('user_id', request()->user()->id)->latest()->paginate(15);
        return view('app.services.index', compact('services'));
    }

    public function show(string $id)
    {
        $s = Service::where('id', $id)->where('user_id', request()->user()->id)->firstOrFail();
        $product = DB::table('products')->where('id', $s->product_id)->first();
        $plan = $s->plan_id ? DB::table('plans')->where('id', $s->plan_id)->first() : null;
        $location = $s->current_location_id ? DB::table('locations')->where('id', $s->current_location_id)->first() : null;
        $subscriptionUrl = $s->subscription_token_plain ? url('/s/'.$s->subscription_token_plain) : null;
        $hiddifyDeepLink = $subscriptionUrl ? 'hiddify://import/'.$subscriptionUrl.'#BlueGate' : null;
        $locations = DB::table('locations')->where('active', true)->orderBy('sort_order')->get();
        $trafficPrice = (float) config('bluegate.operations.traffic_price_per_gb', 5000);
        $locationPrice = (float) config('bluegate.operations.location_change_price', 0);

        return view('app.services.show', compact(
            's', 'product', 'plan', 'location', 'subscriptionUrl', 'hiddifyDeepLink',
            'locations', 'trafficPrice', 'locationPrice'
        ));
    }

    public function setup(Request $request)
    {
        $services = Service::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->latest()
            ->get();

        $selected = null;
        $requestedService = $request->query('service');

        if ($requestedService) {
            $selected = $services->firstWhere('id', $requestedService);
            abort_unless($selected, 404);
        } else {
            $selected = $services->first();
        }

        $subscriptionUrl = $selected?->subscription_token_plain
            ? url('/s/'.$selected->subscription_token_plain)
            : null;

        $hiddifyDeepLink = $subscriptionUrl
            ? 'hiddify://import/'.$subscriptionUrl.'#BlueGate'
            : null;

        $selectedProduct = $selected
            ? DB::table('products')->where('id', $selected->product_id)->first()
            : null;

        return view('app.setup', compact(
            'services', 'selected', 'selectedProduct', 'subscriptionUrl', 'hiddifyDeepLink'
        ));
    }
}
