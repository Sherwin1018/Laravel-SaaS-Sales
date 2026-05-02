<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformPayout extends Model
{
    protected $fillable = [
        'tenant_id',
        'amount',
        'destination_type',
        'masked_destination',
        'destination_value_snapshot',
        'account_name_snapshot',
        'payment_reference',
        'status',
        'paid_at',
        'paid_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'destination_value_snapshot' => 'encrypted',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function paidByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function resolvedDestination(): ?string
    {
        $destination = trim((string) ($this->destination_value_snapshot ?? ''));
        if ($destination !== '') {
            return $destination;
        }

        $masked = trim((string) ($this->masked_destination ?? ''));

        return $masked !== '' ? $masked : null;
    }
}
