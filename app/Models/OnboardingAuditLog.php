<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingAuditLog extends Model
{
    protected $fillable = [
        'event_type',
        'status',
        'message',
        'user_id',
        'signup_intent_id',
        'context',
        'occurred_at',
    ];

    protected $casts = [
        'context' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function signupIntent(): BelongsTo
    {
        return $this->belongsTo(SignupIntent::class);
    }
}

