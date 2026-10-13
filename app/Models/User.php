<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'company_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isCompanyScoped(): bool
    {
        return !$this->isSuperAdmin();
    }

    /**
     * Session'daki aktif şirketi döndürür.
     *
     * Session değeri asla güvenilmez kabul edilmez: kullanıcı başka bir
     * firmaya taşınmış, firması pasifleştirilmiş ya da hiyerarşi
     * değişmiş olabilir. Değer erişilebilir şirketler kümesinde yoksa
     * düşürülür ve kullanıcının kendi firmasına geri dönerülür.
     */
    public function getActiveCompanyId(): ?int
    {
        if ($this->isSuperAdmin()) {
            return null;
        }

        $accessible = $this->getAccessibleCompanyIds();
        $accessible = array_values(array_filter(
            $accessible,
            static fn ($id): bool => $id !== null
        ));

        $active = session('active_company_id');

        if ($active !== null && in_array((int) $active, $accessible, true)) {
            return (int) $active;
        }

        return $this->company_id;
    }

    public function getAccessibleCompanyIds(): array
    {
        if ($this->isSuperAdmin()) {
            return Company::pluck('id')->map(fn ($id) => (int) $id)->toArray();
        }

        if (!$this->company_id) {
            return [];
        }

        return array_values(array_map(
            'intval',
            $this->company?->accessibleCompanyIds() ?? [$this->company_id]
        ));
    }

    public function canAccessCompany(Company $company): bool
    {
        return in_array((int) $company->id, $this->getAccessibleCompanyIds(), true);
    }
}
