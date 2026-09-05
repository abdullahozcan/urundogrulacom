<?php

namespace App\Filament\Resources\ActionRules\Pages;

use App\Filament\Resources\ActionRules\ActionRuleResource;
use App\Helpers\SettingHelper;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListActionRules extends ListRecords
{
    protected static string $resource = ActionRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->engineToggleAction(),
            CreateAction::make(),
        ];
    }

    private function engineToggleAction(): Action
    {
        $enabled = filter_var((string) SettingHelper::get('action_engine_enabled', '0'), FILTER_VALIDATE_BOOLEAN);

        return Action::make('toggleEngine')
            ->label($enabled ? 'Aksiyon Motorunu Kapat' : 'Aksiyon Motorunu Aç')
            ->icon($enabled ? 'heroicon-o-pause' : 'heroicon-o-play')
            ->color($enabled ? 'danger' : 'success')
            ->visible(fn () => (bool) auth()->user()?->isSuperAdmin())
            ->requiresConfirmation()
            ->modalDescription($enabled
                ? 'Motor kapatıldığında kurallar değerlendirilmez ve sistem mevcut akışıyla çalışır.'
                : 'Motor açıldığında aktif kurallar taramalarda değerlendirilir.')
            ->action(function () use ($enabled) {
                Setting::updateOrCreate(
                    ['key' => 'action_engine_enabled'],
                    ['value' => ! $enabled]
                );

                SettingHelper::forgetCache();
            });
    }
}
