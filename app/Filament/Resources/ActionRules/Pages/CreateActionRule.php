<?php

namespace App\Filament\Resources\ActionRules\Pages;

use App\Filament\Resources\ActionRules\ActionRuleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateActionRule extends CreateRecord
{
    protected static string $resource = ActionRuleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if ($user && !$user->isSuperAdmin()) {
            $data['company_id'] = $user->company_id;
        }

        $data['trigger_config'] = $data['config'] ?? null;
        $data['action_payload'] = $data['payload'] ?? null;
        unset($data['config'], $data['payload']);

        return $data;
    }
}
