<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['parent_id'] ?? null) && request()->has('parent_id')) {
            $data['parent_id'] = request()->query('parent_id');
        }

        if (blank($data['company_id'] ?? null)) {
            $data['company_id'] = auth()->user()->getActiveCompanyId();
        }

        return $data;
    }
}
