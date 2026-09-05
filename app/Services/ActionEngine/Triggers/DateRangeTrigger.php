<?php

namespace App\Services\ActionEngine\Triggers;

use App\Models\ActionRule;
use App\Services\ActionEngine\Contracts\ITrigger;
use App\Services\ActionEngine\ScanContext;

class DateRangeTrigger implements ITrigger
{
    public function matches(ActionRule $rule, ScanContext $context): bool
    {
        return true;
    }
}
