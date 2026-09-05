<?php

namespace App\Services\ActionEngine;

use App\Events\ActionExecuted;
use App\Models\ActionLog;
use App\Models\ActionRule;
use App\Models\Scan;
use App\Services\ActionEngine\Actions\NotifyAdminAction;
use App\Services\ActionEngine\Actions\RedirectAction;
use App\Services\ActionEngine\Actions\ShowContentAction;
use App\Services\ActionEngine\Actions\ShowSurveyAction;
use App\Services\ActionEngine\Actions\WebhookAction;
use App\Services\ActionEngine\Contracts\IAction;
use App\Services\ActionEngine\Contracts\ITrigger;
use App\Services\ActionEngine\Triggers\DateRangeTrigger;
use App\Services\ActionEngine\Triggers\FirstScanTrigger;
use App\Services\ActionEngine\Triggers\ItemRevisitTrigger;
use App\Services\ActionEngine\Triggers\ScanCountTrigger;
use RuntimeException;
use Throwable;

class ActionEngine
{
    public function evaluate(Scan $scan): array
    {
        $context = ScanContext::fromScan($scan);

        if (! $context->product) {
            return [];
        }

        $rules = ActionRule::query()
            ->where('company_id', $context->product->company_id)
            ->where(function ($query) use ($context) {
                $query->whereNull('product_id')
                    ->orWhere('product_id', $context->product->id);
            })
            ->active()
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        $executed = [];

        foreach ($rules as $rule) {
            $result = $this->processRule($rule, $scan, $context);

            if ($result !== null) {
                $executed[] = $result;
            }
        }

        return $executed;
    }

    private function processRule(ActionRule $rule, Scan $scan, ScanContext $context): ?array
    {
        $trigger = $this->triggerFor($rule->trigger_type);

        if ($trigger === null || ! $trigger->matches($rule, $context)) {
            return null;
        }

        if ($rule->hasReachedLimit() || ! $rule->isWithinWindow()) {
            $this->log(
                $rule,
                $scan,
                ActionLog::RESULT_SKIPPED,
                $rule->hasReachedLimit() ? 'max_executions reached' : 'outside time window'
            );

            return null;
        }

        if ($this->alreadyLogged($rule, $scan)) {
            return null;
        }

        try {
            $action = $this->actionFor($rule->action_type);

            if ($action === null) {
                throw new RuntimeException("No executor registered for action type [{$rule->action_type}]");
            }

            $payload = $action->execute($rule, $context);

            $this->log($rule, $scan, ActionLog::RESULT_SUCCESS);
            $rule->increment('times_executed');

            ActionExecuted::dispatch($rule, $scan, $payload);

            return [
                'rule_id' => $rule->id,
                'action_type' => $rule->action_type,
                'result' => ActionLog::RESULT_SUCCESS,
                'payload' => $payload,
            ];
        } catch (Throwable $e) {
            $this->log($rule, $scan, ActionLog::RESULT_FAILED, $e->getMessage());

            return [
                'rule_id' => $rule->id,
                'action_type' => $rule->action_type,
                'result' => ActionLog::RESULT_FAILED,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function alreadyLogged(ActionRule $rule, Scan $scan): bool
    {
        return ActionLog::query()
            ->where('action_rule_id', $rule->id)
            ->where('scan_id', $scan->id)
            ->exists();
    }

    private function log(ActionRule $rule, Scan $scan, string $result, ?string $error = null): void
    {
        ActionLog::firstOrCreate(
            [
                'action_rule_id' => $rule->id,
                'scan_id' => $scan->id,
            ],
            [
                'item_id' => $scan->item_id,
                'result' => $result,
                'error_message' => $error,
            ]
        );
    }

    private function triggerFor(string $type): ?ITrigger
    {
        return match ($type) {
            ActionRule::TRIGGER_SCAN_COUNT => new ScanCountTrigger(),
            ActionRule::TRIGGER_FIRST_SCAN => new FirstScanTrigger(),
            ActionRule::TRIGGER_DATE_RANGE => new DateRangeTrigger(),
            ActionRule::TRIGGER_ITEM_REVISIT => new ItemRevisitTrigger(),
            default => null,
        };
    }

    private function actionFor(string $type): ?IAction
    {
        return match ($type) {
            ActionRule::ACTION_SHOW_CONTENT => new ShowContentAction(),
            ActionRule::ACTION_REDIRECT => new RedirectAction(),
            ActionRule::ACTION_WEBHOOK => new WebhookAction(),
            ActionRule::ACTION_NOTIFY_ADMIN => new NotifyAdminAction(),
            ActionRule::ACTION_SHOW_SURVEY => new ShowSurveyAction(),
            default => null,
        };
    }
}
