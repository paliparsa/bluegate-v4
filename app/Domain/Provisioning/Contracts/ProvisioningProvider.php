<?php
namespace App\Domain\Provisioning\Contracts;

use App\Domain\Nodes\Node;

interface ProvisioningProvider
{
    public function healthCheck(Node $node): array;
    public function listInbounds(Node $node): array;
    public function createClient(Node $node, array $payload): array;
    public function updateClient(Node $node, string $clientId, array $payload): array;
    public function deleteClient(Node $node, string $clientId): bool;
    public function getUsage(Node $node, string $clientId): array;
}
