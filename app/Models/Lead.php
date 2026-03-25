<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Lead extends Model
{
    use HasFactory, Notifiable;

    public function routeNotificationForMail(): string
    {
        return (string) $this->email;
    }

    public const PIPELINE_STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'proposal_sent' => 'Proposal Sent',
        'closed_won' => 'Closed Won',
        'closed_lost' => 'Closed Lost',
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
        'email_verified_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'email_verified_at' => 'datetime',
    ];

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function getEmailForVerification(): string
    {
        return (string) $this->email;
    }

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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function linkClicks(): HasMany
    {
        return $this->hasMany(LeadLinkClick::class, 'lead_id');
    }
}
