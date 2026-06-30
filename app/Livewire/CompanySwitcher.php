<?php

namespace App\Livewire;

use Livewire\Component;

class CompanySwitcher extends Component
{
    public array $companies = [];

    public ?int $activeCompanyId = null;

    public function mount(): void
    {
        $user = auth()->user();

        if (!$user || $user->isSuperAdmin() || !$user->company_id) {
            return;
        }

        $this->activeCompanyId = $user->getActiveCompanyId();
        $this->companies = $user->company?->children()
            ->get(['id', 'name'])
            ->prepend(['id' => $user->company_id, 'name' => $user->company->name . ' (Ana Firma)'])
            ->toArray() ?? [];
    }

    public function switchCompany(int $companyId): void
    {
        $user = auth()->user();

        if (!$user || !$user->canAccessCompany(\App\Models\Company::find($companyId))) {
            return;
        }

        session(['active_company_id' => $companyId]);
        $this->activeCompanyId = $companyId;

        $this->redirect(request()->header('Referer', '/admin'));
    }

    public function render()
    {
        return view('livewire.company-switcher');
    }
}
