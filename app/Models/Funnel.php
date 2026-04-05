<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Funnel extends Model
{
    public const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
    ];

    public const PURPOSES = [
        'service' => 'Service / Lead',
        'digital_product' => 'Digital Product',
        'physical_product' => 'Physical Product',
        'hybrid' => 'Hybrid',
    ];

    protected $fillable = [
        'tenant_id',
        'created_by',
        'name',
        'slug',
        'description',
        'purpose',
        'default_tags',
        'status',
    ];

    protected $casts = [
        'default_tags' => 'array',
    ];

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = self::normalizeStatus($value);
    }

    public function setPurposeAttribute($value): void
    {
        $this->attributes['purpose'] = self::normalizePurpose($value);
    }

    public static function normalizeStatus(mixed $value): string
    {
        $normalized = mb_strtolower(trim((string) $value));

        return array_key_exists($normalized, self::STATUSES) ? $normalized : $normalized;
    }

    public static function normalizePurpose(mixed $value): string
    {
        $normalized = mb_strtolower(trim((string) $value));

        return array_key_exists($normalized, self::PURPOSES) ? $normalized : 'service';
    }

    public function purposeLabel(): string
    {
        return self::PURPOSES[$this->purpose] ?? self::PURPOSES['service'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(FunnelStep::class)->orderBy('position');
    }

    public function events(): HasMany
    {
        return $this->hasMany(FunnelEvent::class);
    }
}
