<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SurveyQuestion extends Model
{
    use HasFactory;

    public const TYPE_SINGLE = 'single';

    public const TYPE_MULTIPLE = 'multiple';

    public const TYPE_TEXT = 'text';

    protected $fillable = [
        'survey_id',
        'type',
        'label',
        'options',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'label' => 'array',
        'options' => 'array',
        'is_required' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function getLabel(): string
    {
        $label = $this->label;

        if (is_array($label)) {
            return $label[app()->getLocale()]
                ?? $label[config('languages.default', 'tr')]
                ?? (string) reset($label);
        }

        return (string) $label;
    }

    public function getOptions(): array
    {
        return array_map(
            fn ($option) => Str::limit(strip_tags(trim((string) $option)), 200),
            $this->options ?? []
        );
    }
}
