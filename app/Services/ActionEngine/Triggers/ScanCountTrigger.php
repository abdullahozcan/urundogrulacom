<?php

namespace App\Services\ActionEngine\Triggers;

use App\Models\ActionRule;
use App\Services\ActionEngine\Contracts\ITrigger;
use App\Services\ActionEngine\ScanContext;

class ScanCountTrigger implements ITrigger
{
    public function matches(ActionRule $rule, ScanContext $context): bool
    {
        $config = $rule->trigger_config ?? [];
        $target = (int) ($config['count'] ?? 0);

        if ($target < 1) {
            return false;
        }

        $scope = $config['scope'] ?? 'item';

        return $context->scanCountFor($scope) === $target;
    }
}
