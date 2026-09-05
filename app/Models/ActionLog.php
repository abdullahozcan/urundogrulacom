<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActionLog extends Model
{
    use HasFactory;

    public const RESULT_SUCCESS = 'success';
    public const RESULT_FAILED = 'failed';
    public const RESULT_SKIPPED = 'skipped';

    protected $fillable = [
        'action_rule_id',
        'scan_id',
        'item_id',
        'result',
        'error_message',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(ActionRule::class, 'action_rule_id');
    }

    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
