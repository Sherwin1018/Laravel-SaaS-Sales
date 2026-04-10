<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FunnelTemplate extends Model
{
    public const TEMPLATE_TYPE_UNCATEGORIZED = 'uncategorized';

    public const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
    ];

    public const TEMPLATE_TYPES = [
        self::TEMPLATE_TYPE_UNCATEGORIZED => 'Needs Purpose',
        'service' => 'Service Funnel',
        'digital_product' => 'Digital Product Funnel',
        'physical_product' => 'Physical Product Funnel',
        'hybrid' => 'Hybrid Funnel',
    ];

    public static function selectableTemplateTypes(): array
    {
        return [
            'service' => self::TEMPLATE_TYPES['service'],
            'physical_product' => self::TEMPLATE_TYPES['physical_product'],
        ];
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
    ];

    protected $casts = [
        'template_tags' => 'array',
        'published_at' => 'datetime',
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
}

