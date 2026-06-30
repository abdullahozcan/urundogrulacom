<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Batch;
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

        if (($data['digital_content_type'] ?? null) !== 'video') {
            $data['youtube_url'] = null;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record->qr_mode !== 'general') {
            return;
        }

        Batch::create([
            'product_id' => $this->record->id,
            'batch_number' => strtoupper(substr($this->record->name, 0, 3)) . '-' . now()->format('YmdHis'),
            'quantity' => 1,
        ]);
    }
}
