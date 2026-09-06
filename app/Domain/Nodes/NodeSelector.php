<?php
namespace App\Domain\Nodes;

use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

final class NodeSelector
{
    public function select(?string $locationId = null): Node
    {
        $query = Node::query()->where('sales_enabled', true)->where('maintenance_mode', false)->where('status', 'online');
        if ($locationId) $query->where('location_id', $locationId);
        /** @var Collection<int,Node> $nodes */
        $nodes = $query->get();
        if ($nodes->isEmpty()) throw new RuntimeException('No healthy node is available');

        return $nodes->sortBy(function (Node $node) {
            $m = $node->metadata ?? [];
            $load = (float)($m['load_percent'] ?? 0);
            $users = (float)($m['user_load_percent'] ?? 0);
            $traffic = (float)($m['traffic_load_percent'] ?? 0);
            $weightPenalty = max(0, 100 - (int)$node->weight);
            return ($load * .30) + ($users * .35) + ($traffic * .25) + ($weightPenalty * .10);
        })->first();
    }
}
