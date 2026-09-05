<?php

namespace App\Services\ActionEngine\Contracts;

use App\Models\ActionRule;
use App\Services\ActionEngine\ScanContext;

interface ITrigger
{
    public function matches(ActionRule $rule, ScanContext $context): bool;
}
