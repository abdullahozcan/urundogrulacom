<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
        'parent_id',
        'max_sub_companies',
    ];

    protected $casts = [
        'max_sub_companies' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Company::class, 'parent_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function accessibleCompanyIds(): array
    {
        return array_values(array_map('intval', array_merge(
            [$this->id],
            $this->children()->pluck('id')->toArray()
        )));
    }

    public function canCreateSubCompany(): bool
    {
        if (!is_null($this->parent_id)) {
            return false;
        }

        if ($this->max_sub_companies === null) {
            return true;
        }

        return $this->children()->count() < $this->max_sub_companies;
    }

    /**
     * Hiyerarşi yalnızca bir katman derinliğinde tutulur: bir üst firma
     * alt firma, alt firma torun firma oluşturamaz.
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }
}
