<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TenantCustomField extends Model
{
    use HasFactory;

    public const FIELD_TYPES = [
        'text' => 'Text',
        'textarea' => 'Textarea',
        'number' => 'Number',
        'date' => 'Date',
        'select' => 'Select',
        'checkbox' => 'Checkbox',
    ];

    protected $fillable = [
        'tenant_id',
        'label',
        'key',
        'field_type',
        'options',
        'is_required',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $field) {
            $field->key = self::normalizeKey($field->key ?: $field->label);
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(LeadCustomFieldValue::class);
    }

    public static function normalizeKey(string $value): string
    {
        return Str::of($value)->lower()->snake()->replaceMatches('/[^a-z0-9_]/', '')->limit(50, '')->toString();
    }
}
