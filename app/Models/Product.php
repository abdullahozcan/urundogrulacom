<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'category_id',
        'name',
        'description',
        'image',
        'type',
        'digital_content_type',
        'digital_file_path',
        'qr_mode',
        'general_qr_code',
    ];

    protected $casts = [
        'qr_mode' => 'string',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            if ($product->qr_mode !== 'general') {
                $product->general_qr_code = null;
            }

            if (!$product->category_id) {
                return;
            }

            $category = Category::query()->find($product->category_id);

            if (!$category || (int) $category->company_id !== (int) $product->company_id) {
                throw ValidationException::withMessages([
                    'category_id' => 'Secilen kategori bu urunun kurulusuna ait degil.',
                ]);
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
