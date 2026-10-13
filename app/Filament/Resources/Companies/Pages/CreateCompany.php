<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Models\Company;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        if (!$user->isSuperAdmin()) {
            // `parent_id` must never be trusted from form state: it is always
            // the admin's own root company. Using the session-derived active
            // company here would allow a third hierarchy level (a grandchild)
            // whenever the switcher points at a child company.
            $parent = Company::find($user->company_id);

            if (!$parent || !$parent->isRoot()) {
                throw ValidationException::withMessages([
                    'name' => 'Yalnızca ana firma yeni alt firma oluşturabilir.',
                ]);
            }

            // Enforce the sub-company quota server side. The relation manager
            // only hides the button; the limit must hold on the create page.
            if (!$parent->canCreateSubCompany()) {
                throw ValidationException::withMessages([
                    'name' => 'Alt firma limitinize ulaşıldı (maksimum: '
                        .($parent->max_sub_companies ?? 'sınırsız') . ').',
                ]);
            }

            $data['parent_id'] = $parent->id;
        } elseif (array_key_exists('parent_id', $data)) {
            // Only one hierarchy level deep: a parent must itself be a root.
            $data['parent_id'] = $this->validatedRootParentId($data['parent_id']);
        }

        if (blank($data['company_id'] ?? null)) {
            $data['company_id'] = $user->getActiveCompanyId();
        }

        // A non super admin must never be able to raise the quota.
        if (!$user->isSuperAdmin()) {
            unset($data['max_sub_companies']);
        }

        return $data;
    }

    protected function validatedRootParentId(mixed $parentId): ?int
    {
        if (blank($parentId)) {
            return null;
        }

        $parent = Company::find($parentId);

        if (!$parent || !$parent->isRoot()) {
            throw ValidationException::withMessages([
                'parent_id' => 'Üst firma yalnızca ana (kök) firma olabilir.',
            ]);
        }

        if (!$parent->canCreateSubCompany()) {
            throw ValidationException::withMessages([
                'parent_id' => 'Bu firmanın alt firma limiti dolmuş.',
            ]);
        }

        return $parent->id;
    }
}