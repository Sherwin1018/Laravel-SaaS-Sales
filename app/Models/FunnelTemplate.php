<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FunnelTemplate extends Model
{
    public const TEMPLATE_TYPE_UNCATEGORIZED = 'uncategorized';
    public const TEMPLATE_TYPE_STEP_BY_STEP = 'step_by_step';
    public const PURPOSE_TAG_PREFIX = '__funnel_purpose:';

    public const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
    ];

    public const TEMPLATE_TYPES = [
        self::TEMPLATE_TYPE_UNCATEGORIZED => 'Needs Purpose',
        'service' => 'Service Funnel',
        'single_page' => 'Single Page Funnel',
        'step_by_step' => 'Step-by-Step Funnel',
        'digital_product' => 'Digital Product Funnel',
        'physical_product' => 'Physical Product Funnel',
        'hybrid' => 'Hybrid Funnel',
    ];

    public const FUNNEL_PURPOSE_OPTIONS = [
        'service' => 'Services',
        'physical_product' => 'Physical Product',
    ];

    public static function selectableTemplateTypes(): array
    {
        return [
            self::TEMPLATE_TYPE_STEP_BY_STEP => self::TEMPLATE_TYPES[self::TEMPLATE_TYPE_STEP_BY_STEP],
        ];
    }

    public static function defaultTemplateType(): string
    {
        return self::TEMPLATE_TYPE_STEP_BY_STEP;
    }

    public static function normalizeFunnelPurpose(mixed $value): string
    {
        $normalized = mb_strtolower(trim((string) $value));

        return array_key_exists($normalized, self::FUNNEL_PURPOSE_OPTIONS) ? $normalized : 'service';
    }

    public function resolvedFunnelPurpose(): string
    {
        $tags = collect($this->template_tags ?? [])
            ->map(fn ($tag) => mb_strtolower(trim((string) $tag)))
            ->filter();

        $prefix = self::PURPOSE_TAG_PREFIX;
        $tagPurpose = $tags
            ->first(function (string $tag) use ($prefix) {
                return str_starts_with($tag, $prefix);
            });

        if (is_string($tagPurpose) && $tagPurpose !== '') {
            return self::normalizeFunnelPurpose(mb_substr($tagPurpose, mb_strlen($prefix)));
        }

        if (self::normalizeTemplateType($this->template_type) === 'physical_product') {
            return 'physical_product';
        }

        return 'service';
    }

    protected $fillable = [
        'created_by',
        'name',
        'slug',
        'description',
        'template_type',
        'template_tags',
        'status',
        'preview_image',
        'published_at',
        'royalty_rate',
    ];

    protected $casts = [
        'template_tags' => 'array',
        'published_at' => 'datetime',
        'royalty_rate' => 'decimal:2',
    ];

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = self::normalizeStatus($value);
    }

    public function setTemplateTypeAttribute($value): void
    {
        $this->attributes['template_type'] = self::normalizeTemplateType($value);
    }

    public static function normalizeStatus(mixed $value): string
    {
        $normalized = mb_strtolower(trim((string) $value));

        return array_key_exists($normalized, self::STATUSES) ? $normalized : 'draft';
    }

    public static function normalizeTemplateType(mixed $value): string
    {
        $normalized = mb_strtolower(trim((string) $value));

        return array_key_exists($normalized, self::TEMPLATE_TYPES) ? $normalized : self::TEMPLATE_TYPE_UNCATEGORIZED;
    }

    public function templateTypeLabel(): string
    {
        return self::TEMPLATE_TYPES[$this->template_type] ?? self::TEMPLATE_TYPES[self::TEMPLATE_TYPE_UNCATEGORIZED];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(FunnelTemplateStep::class)->orderBy('position');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(FunnelTemplateAsset::class)->orderByDesc('created_at');
    }

    public function funnels(): HasMany
    {
        return $this->hasMany(Funnel::class, 'source_template_id');
    }
}
