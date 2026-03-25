<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Funnel extends Model
{
    protected $fillable = [
        'tenant_id',
        'created_by',
        'name',
        'slug',
        'description',
        'default_tags',
        'status',
        'require_double_opt_in',
    ];

    protected $casts = [
        'default_tags' => 'array',
        'require_double_opt_in' => 'boolean',
    ];

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
}
