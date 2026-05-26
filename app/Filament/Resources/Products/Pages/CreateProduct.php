<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Category;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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

        return $data;
    }
}
