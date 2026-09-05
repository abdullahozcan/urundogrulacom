<?php

namespace App\Services\ActionEngine\Actions;

use App\Jobs\ExecuteWebhookAction;
use App\Models\ActionRule;
use App\Services\ActionEngine\Contracts\IAction;
use App\Services\ActionEngine\ScanContext;
use InvalidArgumentException;

class WebhookAction implements IAction
{
    public function execute(ActionRule $rule, ScanContext $context): array
    {
        $url = $rule->action_payload['url'] ?? null;

        if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Invalid webhook URL for action rule [{$rule->id}]");
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
