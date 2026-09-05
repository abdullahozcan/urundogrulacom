<?php

namespace App\Filament\Resources\ActionRules\Pages;

use App\Filament\Resources\ActionRules\ActionRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditActionRule extends EditRecord
{
    protected static string $resource = ActionRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['config'] = $this->record->trigger_config ?? [];
        $data['payload'] = $this->record->action_payload ?? [];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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
