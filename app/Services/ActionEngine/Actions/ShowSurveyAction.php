<?php

namespace App\Services\ActionEngine\Actions;

use App\Models\ActionRule;
use App\Models\Survey;
use App\Services\ActionEngine\Contracts\IAction;
use App\Services\ActionEngine\ScanContext;
use InvalidArgumentException;

class ShowSurveyAction implements IAction
{
    public function execute(ActionRule $rule, ScanContext $context): array
    {
        $surveyId = $rule->action_payload['survey_id'] ?? null;

        $survey = $surveyId
            ? Survey::query()
                ->where('company_id', $rule->company_id)
                ->where('is_active', true)
                ->find($surveyId)
            : null;

        if (! $survey) {
            throw new InvalidArgumentException("Survey [{$surveyId}] not found for action rule [{$rule->id}]");
        }

        return ['survey_id' => $survey->id];
    }
}
