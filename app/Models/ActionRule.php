<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActionRule extends Model
{
    use HasFactory;

    public const TRIGGER_SCAN_COUNT = 'scan_count';
    public const TRIGGER_FIRST_SCAN = 'first_scan';
    public const TRIGGER_DATE_RANGE = 'date_range';
    public const TRIGGER_ITEM_REVISIT = 'item_revisit';

    public const ACTION_SHOW_CONTENT = 'show_content';
    public const ACTION_REDIRECT = 'redirect';
    public const ACTION_WEBHOOK = 'webhook';
    public const ACTION_NOTIFY_ADMIN = 'notify_admin';

    protected $fillable = [
        'company_id',
        'product_id',
        'name',
        'trigger_type',
        'trigger_config',
        'action_type',
        'action_payload',
        'is_active',
        'priority',
        'starts_at',
        'ends_at',
        'max_executions',
        'times_executed',
    ];

    protected $casts = [
        'trigger_config' => 'array',
        'action_payload' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'max_executions' => 'integer',
        'times_executed' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ActionLog::class, 'action_rule_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isWithinWindow(): bool
    {
        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    public function hasReachedLimit(): bool
    {
        return $this->max_executions !== null && $this->times_executed >= $this->max_executions;
    }
}
