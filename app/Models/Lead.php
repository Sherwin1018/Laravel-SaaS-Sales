<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    protected $fillable = [
        'tenant_id',
        'source_funnel_id',
        'source_funnel_page_id',
        'assigned_to',
        'name',
        'email',
        'phone',
        'status',
        'score',
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

    public function sourceFunnel(): BelongsTo
    {
        return $this->belongsTo(Funnel::class, 'source_funnel_id');
    }

    public function sourceFunnelPage(): BelongsTo
    {
        return $this->belongsTo(FunnelPage::class, 'source_funnel_page_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'lead_tag');
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
}
