<?php

namespace App\Services\ActionEngine\Triggers;

use App\Models\ActionRule;
use App\Services\ActionEngine\Contracts\ITrigger;
use App\Services\ActionEngine\ScanContext;

class ItemRevisitTrigger implements ITrigger
{
    public function matches(ActionRule $rule, ScanContext $context): bool
    {
        $config = $rule->trigger_config ?? [];
        $afterDays = max(1, (int) ($config['after_days'] ?? 1));

        $previous = $context->previousScanForItem();

        if ($previous === null) {
            return false;
        }

        $previousAt = $previous->scanned_at ?? $previous->created_at;
        $currentAt = $context->scan->scanned_at ?? $context->scan->created_at;

        return $currentAt->gte($previousAt->copy()->addDays($afterDays));
    }
}
