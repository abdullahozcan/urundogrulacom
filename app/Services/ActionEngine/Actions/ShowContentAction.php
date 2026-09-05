<?php

namespace App\Services\ActionEngine\Actions;

use App\Models\ActionRule;
use App\Services\ActionEngine\Contracts\IAction;
use App\Services\ActionEngine\ScanContext;

class ShowContentAction implements IAction
{
    public function execute(ActionRule $rule, ScanContext $context): array
    {
        $payload = $rule->action_payload ?? [];

        return [
            'content' => [
                'title' => $payload['title'] ?? null,
                'body' => $payload['body'] ?? null,
                'style' => in_array($payload['style'] ?? 'info', ['info', 'warning'], true)
                    ? ($payload['style'] ?? 'info')
                    : 'info',
            ],
        ];
    }
}
