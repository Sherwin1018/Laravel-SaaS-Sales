<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FunnelTemplate extends Model
{
    public const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
    ];

    protected $fillable = [
        'created_by',
        'name',
        'slug',
        'description',
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

    public static function normalizeStatus(mixed $value): string
    {
        $normalized = mb_strtolower(trim((string) $value));

        return array_key_exists($normalized, self::STATUSES) ? $normalized : 'draft';
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
