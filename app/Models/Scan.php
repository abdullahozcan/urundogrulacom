<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scan extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'ip_address',
        'lat',
        'lng',
        'browser',
        'device_type',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
