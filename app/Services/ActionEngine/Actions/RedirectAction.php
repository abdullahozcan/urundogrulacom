<?php

namespace App\Services\ActionEngine\Actions;

use App\Models\ActionRule;
use App\Services\ActionEngine\Contracts\IAction;
use App\Services\ActionEngine\ScanContext;
use InvalidArgumentException;

class RedirectAction implements IAction
{
    public function execute(ActionRule $rule, ScanContext $context): array
    {
        $url = $rule->action_payload['url'] ?? null;

        if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Invalid redirect URL for action rule [{$rule->id}]");
        }

        return ['redirect_url' => $url];
    }
}
