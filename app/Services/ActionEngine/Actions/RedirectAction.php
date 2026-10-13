<?php

namespace App\Services\ActionEngine\Actions;

use App\Models\ActionRule;
use App\Services\ActionEngine\Contracts\IAction;
use App\Services\ActionEngine\ScanContext;
use App\Support\SafeUrl;
use InvalidArgumentException;

class RedirectAction implements IAction
{
    public function execute(ActionRule $rule, ScanContext $context): array
    {
        $url = $rule->action_payload['url'] ?? null;

        // Same gate as webhooks: `javascript:`, `data:` and internal hosts
        // must never reach a public 302.
        if (! SafeUrl::isSafe($url)) {
            throw new InvalidArgumentException(
                "Unsafe redirect URL for action rule [{$rule->id}]: " . (SafeUrl::violation($url) ?? 'invalid')
            );
        }

        return ['redirect_url' => $url];
    }
}
