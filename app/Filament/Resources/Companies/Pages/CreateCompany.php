<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin()) {
            $data['parent_id'] = $user->getActiveCompanyId();
        }

        if (blank($data['company_id'] ?? null)) {
            $data['company_id'] = $user->getActiveCompanyId();
        }

        return $data;
    }
}
