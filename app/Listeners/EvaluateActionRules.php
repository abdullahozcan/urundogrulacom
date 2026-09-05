<?php

namespace App\Listeners;

use App\Events\ScanRecorded;
use App\Helpers\SettingHelper;
use App\Services\ActionEngine\ActionEngine;
use Illuminate\Support\Facades\Log;
use Throwable;

class EvaluateActionRules
{
    public function handle(ScanRecorded $event): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        try {
            app(ActionEngine::class)->evaluate($event->scan);
        } catch (Throwable $e) {
            Log::error('Action engine evaluation failed', [
                'scan_id' => $event->scan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function isEnabled(): bool
    {
        $flag = SettingHelper::get('action_engine_enabled', '0');

        if (is_array($flag)) {
            $flag = $flag[config('languages.default', 'tr')] ?? reset($flag);
        }

        return filter_var((string) $flag, FILTER_VALIDATE_BOOLEAN);
    }
}
