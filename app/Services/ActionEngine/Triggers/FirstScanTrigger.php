<?php

namespace App\Services\ActionEngine\Triggers;

use App\Models\ActionRule;
use App\Services\ActionEngine\Contracts\ITrigger;
use App\Services\ActionEngine\ScanContext;

class FirstScanTrigger implements ITrigger
{
    public function matches(ActionRule $rule, ScanContext $context): bool
    {
        return $context->scanCountFor('item') === 1;
    }
}
