<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                // Compared against `company_id`, never against the
                // session-derived active company: a root admin can point the
                // switcher at a child, which would otherwise unlock
                // deleting their own tenant root.
                ->visible(fn (): bool => (bool) auth()->user()?->isSuperAdmin()),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();

        // The quota and the hierarchy link are super-admin only. The fields are
        // hidden from the form for everyone else, but hidden is a UI hint and
        // a crafted request can still carry them.
        if ($user && !$user->isSuperAdmin()) {
            unset($data['max_sub_companies'], $data['parent_id']);
        }

        return $data;
    }
}
