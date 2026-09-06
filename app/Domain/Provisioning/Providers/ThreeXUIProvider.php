<?php
namespace App\Domain\Provisioning\Providers;

use App\Domain\Nodes\Node;
use App\Domain\Provisioning\Contracts\ProvisioningProvider;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class ThreeXUIProvider implements ProvisioningProvider
{
    private function client(Node $node): PendingRequest
    {
        return Http::timeout((int) config('bluegate.node_health_timeout', 8))
            ->acceptJson()
            ->baseUrl(rtrim($node->panel_url, '/'));
    }

    private function authenticated(Node $node): PendingRequest
    {
        $creds = $node->credentials ?? [];
        $login = $this->client($node)->asForm()->post('/login', [
            'username' => $creds['username'] ?? '',
            'password' => $creds['password'] ?? '',
        ]);
        if (!$login->successful()) throw new RuntimeException('3x-ui authentication failed');
        $cookie = $login->header('Set-Cookie');
        return $this->client($node)->withHeaders(['Cookie' => $cookie]);
    }

    public function healthCheck(Node $node): array
    {
        try {
            $response = $this->authenticated($node)->get('/panel/api/inbounds/list');
            return ['ok' => $response->successful(), 'status' => $response->status()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function listInbounds(Node $node): array
    {
        return $this->authenticated($node)->get('/panel/api/inbounds/list')->throw()->json('obj', []);
    }

    public function createClient(Node $node, array $payload): array
    {
        $inboundId = $payload['inbound_id'] ?? throw new RuntimeException('inbound_id is required');
        $body = ['id' => $inboundId, 'settings' => json_encode(['clients' => [$payload['client']]], JSON_UNESCAPED_SLASHES)];
        return $this->authenticated($node)->asForm()->post('/panel/api/inbounds/addClient', $body)->throw()->json();
    }

    public function updateClient(Node $node, string $clientId, array $payload): array
    {
        return $this->authenticated($node)->asForm()->post('/panel/api/inbounds/updateClient/'.$clientId, $payload)->throw()->json();
    }

    public function deleteClient(Node $node, string $clientId): bool
    {
        $inboundId = $node->metadata['default_inbound_id'] ?? null;
        if (!$inboundId) throw new RuntimeException('default_inbound_id missing');
        return $this->authenticated($node)->post("/panel/api/inbounds/{$inboundId}/delClient/{$clientId}")->successful();
    }

    public function updateClientOnInbound(Node $node, string $inboundId, string $clientUuid, array $client): array
    {
        $body = [
            'id' => $inboundId,
            'settings' => json_encode(['clients' => [$client]], JSON_UNESCAPED_SLASHES),
        ];
        return $this->authenticated($node)->asForm()
            ->post('/panel/api/inbounds/updateClient/'.$clientUuid, $body)->throw()->json();
    }

    public function deleteClientFromInbound(Node $node, string $inboundId, string $clientUuid): bool
    {
        return $this->authenticated($node)
            ->post("/panel/api/inbounds/{$inboundId}/delClient/{$clientUuid}")
            ->successful();
    }

    public function getUsage(Node $node, string $clientId): array
    {
        return $this->authenticated($node)->get('/panel/api/inbounds/getClientTraffics/'.$clientId)->throw()->json('obj', []);
    }
}
