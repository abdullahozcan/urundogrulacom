<?php

namespace App\Services\ActionEngine\Actions;

use App\Jobs\SendNotifyAdminAction;
use App\Models\ActionRule;
use App\Services\ActionEngine\Contracts\IAction;
use App\Services\ActionEngine\ScanContext;
use InvalidArgumentException;

class NotifyAdminAction implements IAction
{
    public function execute(ActionRule $rule, ScanContext $context): array
    {
        $email = $rule->action_payload['email'] ?? null;

        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid notify email for action rule [{$rule->id}]");
        }

        $product = $context->product;

        dispatch(new SendNotifyAdminAction(
            $email,
            $rule->action_payload['subject'] ?? __('Ürün doğrulama bildirimi'),
            $rule->action_payload['message'] ?? (
                '"' . ($product?->name ?? '-') . '" ürünü doğrulandı.' . PHP_EOL .
                'Kural: ' . $rule->name . PHP_EOL .
                'Tarama: #' . $context->scan->id
            )
        ));

        return ['notify_email' => $email];
    }
}
