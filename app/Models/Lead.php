<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Lead extends Model
{
    use HasFactory;

    public const PIPELINE_STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'proposal_sent' => 'Proposal Sent',
        'closed_won' => 'Closed Won',
        'closed_lost' => 'Closed Lost',
    ];

    public const STATUS_ALIASES = [
        'new' => 'new',
        'contacted' => 'contacted',
        'proposal_sent' => 'proposal_sent',
        'proposal sent' => 'proposal_sent',
        'closed_won' => 'closed_won',
        'closed won' => 'closed_won',
        'closed_lost' => 'closed_lost',
        'closed lost' => 'closed_lost',
    ];

    protected $fillable = [
        'tenant_id',
        'assigned_to',
        'name',
        'email',
        'phone',
        'source_campaign',
        'tags',
        'status',
        'score',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function ($query) {
            if (app()->runningInConsole()) {
                return;
            }

            if (auth()->check() && auth()->user()) {
                $query->where('tenant_id', auth()->user()->tenant_id);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function customFieldValues(): HasMany
    {
        return $this->hasMany(LeadCustomFieldValue::class);
    }

    public function stageHistories(): HasMany
    {
        return $this->hasMany(LeadStageHistory::class)->latest('created_at');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(FunnelReview::class);
    }

    public function customFieldValueMap(): Collection
    {
        return $this->customFieldValues
            ->mapWithKeys(fn (LeadCustomFieldValue $value) => [$value->tenant_custom_field_id => $value->value]);
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = self::normalizeStatus($value);
    }

    public static function normalizeStatus(mixed $value): string
    {
        $normalized = mb_strtolower(trim(str_replace('-', '_', (string) $value)));
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

        return self::STATUS_ALIASES[$normalized] ?? $normalized;
    }

    public static function wonStatusValues(): array
    {
        return ['closed_won'];
    }

    public static function lostStatusValues(): array
    {
        return ['closed_lost'];
    }

    public static function closedStatusValues(): array
    {
        return array_values(array_unique([
            ...self::wonStatusValues(),
            ...self::lostStatusValues(),
        ]));
    }
}
