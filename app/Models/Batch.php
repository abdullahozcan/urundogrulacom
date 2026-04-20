<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_number',
        'quantity',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    protected static function booted()
    {
        static::created(function ($batch) {
            $items = [];
            $now = now();
            for ($i = 0; $i < $batch->quantity; $i++) {
                $items[] = [
                    'batch_id' => $batch->id,
                    'uuid' => (string) Str::uuid(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            // Insert in chunks to avoid memory issues if quantity is very large
            foreach (array_chunk($items, 1000) as $chunk) {
                Item::insert($chunk);
            }
        });
    }
}
