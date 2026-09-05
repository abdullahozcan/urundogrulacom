<?php

namespace App\Services\ActionEngine\Contracts;

use App\Models\ActionRule;
use App\Services\ActionEngine\ScanContext;

interface IAction
{
    public function execute(ActionRule $rule, ScanContext $context): array;
}
