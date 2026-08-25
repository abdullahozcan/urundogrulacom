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
        'redirect_url',
        'expiration_date',
        'serial_number',
        'youtube_url',
        'website_url',
        'instagram_url',
        'facebook_url',
        'twitter_url',
        'tiktok_url',
        'linkedin_url',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'qr_mode' => 'string',
        'expiration_date' => 'date',
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

    public function documents(): HasMany
    {
        return $this->hasMany(ProductDocument::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('is_approved', true);
    }

    public function getName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $names = $this->getAttributeValue('name') ?? [];

        return $names[$locale] ?? $names[config('languages.fallback', 'en')] ?? '';
    }

    public function getDescription(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $descriptions = $this->getAttributeValue('description') ?? [];

        return $descriptions[$locale] ?? $descriptions[config('languages.fallback', 'en')] ?? '';
    }

    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        if (!$this->youtube_url) {
            return null;
        }

        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $this->youtube_url, $matches);

        return isset($matches[1]) ? "https://www.youtube.com/embed/{$matches[1]}" : null;
    }
}
