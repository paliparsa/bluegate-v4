<?php
namespace App\Domain\Nodes;

use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

final class NodeSelector
{
    public function select(?string $locationId = null, array $excludeNodeIds = []): Node
    {
        $query = Node::query()
            ->where('sales_enabled', true)
            ->where('maintenance_mode', false)
            ->where('status', 'online');

        if ($locationId) $query->where('location_id', $locationId);
        if ($excludeNodeIds) $query->whereNotIn('id', $excludeNodeIds);

        /** @var Collection<int,Node> $nodes */
        $nodes = $query->get();
        if ($nodes->isEmpty()) throw new RuntimeException('No healthy node is available');

        return $nodes->sortBy(function (Node $node) {
            $m = $node->metadata ?? [];
            $load = (float)($m['load_percent'] ?? 0);
            $users = (float)($m['user_load_percent'] ?? 0);
            $traffic = (float)($m['traffic_load_percent'] ?? 0);
            $weightPenalty = max(0, 100 - (int)$node->weight);

            $capacityPenalty = 0;
            if ($node->max_users && isset($m['active_users'])) {
                $ratio = min(1.5, ((float)$m['active_users'] / max(1, (float)$node->max_users)));
                $capacityPenalty += $ratio * 20;
            }
            if ($node->max_traffic_bytes && isset($m['traffic_bytes'])) {
                $ratio = min(1.5, ((float)$m['traffic_bytes'] / max(1, (float)$node->max_traffic_bytes)));
                $capacityPenalty += $ratio * 20;
            }

            return ($load * .25) + ($users * .30) + ($traffic * .20) + ($weightPenalty * .10) + $capacityPenalty;
        })->first();
    }
}
