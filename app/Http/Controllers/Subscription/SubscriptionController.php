<?php
namespace App\Http\Controllers\Subscription;

use App\Domain\Services\Service;
use App\Domain\Subscription\SubscriptionToken;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SubscriptionController
{
    public function __invoke(Request $request, string $token): Response
    {
        $hash = SubscriptionToken::hash($token);
        $service = Service::query()->where('subscription_token_hash', $hash)->with('endpoints')->firstOrFail();

        abort_unless($service->status === 'active', 403, 'Service is not active');
        if ($service->expires_at && $service->expires_at->isPast()) abort(403, 'Service expired');
        if ($service->traffic_limit_bytes && $service->traffic_used_bytes >= $service->traffic_limit_bytes) abort(403, 'Traffic limit reached');

        $links = $service->endpoints->where('status', 'active')->pluck('metadata.uri')->filter()->values();
        $payload = base64_encode($links->implode("\n"));

        return response($payload, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store, private',
            'Subscription-Userinfo' => sprintf('upload=0; download=%d; total=%d; expire=%d',
                $service->traffic_used_bytes,
                $service->traffic_limit_bytes ?? 0,
                $service->expires_at?->timestamp ?? 0
            ),
        ]);
    }
}
