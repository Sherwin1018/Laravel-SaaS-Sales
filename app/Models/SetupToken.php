<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SetupToken extends Model
{
    protected $fillable = [
        'user_id',
        'purpose',
        'token_hash',
        'expires_at',
        'used_at',
        'meta',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isUsable(): bool
    {
        return $this->used_at === null && now()->lt($this->expires_at);
    }
}
