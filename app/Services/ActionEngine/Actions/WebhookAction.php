<?php

namespace App\Services\ActionEngine\Actions;

use App\Jobs\ExecuteWebhookAction;
use App\Models\ActionRule;
use App\Services\ActionEngine\Contracts\IAction;
use App\Services\ActionEngine\ScanContext;
use App\Support\SafeUrl;
use InvalidArgumentException;

class WebhookAction implements IAction
{
    public function execute(ActionRule $rule, ScanContext $context): array
    {
        $url = $rule->action_payload['url'] ?? null;

        // `FILTER_VALIDATE_URL` alone accepts cloud metadata endpoints and
        // RFC1918 hosts, which would make this an SSRF primitive.
        if (! SafeUrl::isSafe($url)) {
            throw new InvalidArgumentException(
                "Unsafe webhook URL for action rule [{$rule->id}]: " . (SafeUrl::violation($url) ?? 'invalid')
            );
        }

        $payload = array_merge($context->summary(), [
            'rule' => [
                'id' => $rule->id,
                'name' => $rule->name,
                'trigger_type' => $rule->trigger_type,
            ],
            'event' => 'scan.action',
            'timestamp' => now()->toIso8601String(),
        ]);

        dispatch(new ExecuteWebhookAction(
            $url,
            $rule->action_payload['secret'] ?? null,
            $payload
        ));

        return ['webhook_url' => $url];
    }
}
