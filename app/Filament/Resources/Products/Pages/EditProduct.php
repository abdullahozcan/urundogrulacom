<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Html;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function getRelationManagersContentComponent(): Component
    {
        $original = parent::getRelationManagersContentComponent();

        return Group::make([
            Html::make('<div wire:loading wire:target="activeRelationManager" class="absolute inset-0 z-20 flex items-center justify-center bg-white/80 dark:bg-gray-900/80 rounded-xl transition duration-200">
                <div class="flex flex-col items-center gap-2 p-6">
                    <svg class="animate-spin h-8 w-8 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">Yükleniyor...</span>
                </div>
            </div>'),
            $original,
        ])->extraAttributes(['class' => 'relative']);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();

        if ($user && !$user->isSuperAdmin()) {
            $data['company_id'] = $user->company_id;
        }

        if (($data['qr_mode'] ?? 'unique') !== 'general') {
            $data['general_qr_code'] = null;
        }

        if (!empty($data['category_id']) && !empty($data['company_id'])) {
            $categoryExists = Category::query()
                ->whereKey($data['category_id'])
                ->where('company_id', $data['company_id'])
                ->exists();

            if (!$categoryExists) {
                $data['category_id'] = null;
            }
        }

        if (($data['digital_content_type'] ?? null) !== 'video') {
            $data['youtube_url'] = null;
        }

        return $data;
    }
}
