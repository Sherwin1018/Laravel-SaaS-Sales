<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    public const ACTIVATION_STATES = [
        'invited',
        'pending_activation',
        'email_sent',
        'email_verified',
        'password_set',
        'active',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function setupTokens(): HasMany
    {
        return $this->hasMany(SetupToken::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(self::class, 'invited_by');
    }

    public function hasRole(string $roleSlug): bool
    {
        return $this->roles->contains('slug', $roleSlug);
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'role',
        'status',
        'suspension_reason',
        'phone',
        'secondary_phone',
        'profile_photo_path',
        'last_login_at',
        'activation_state',
        'invited_by',
        'invited_at',
        'activation_completed_at',
        'google_id',
        'must_change_password',
        'is_customer_portal_user',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'invited_at' => 'datetime',
        'activation_completed_at' => 'datetime',
        'must_change_password' => 'boolean',
        'is_customer_portal_user' => 'boolean',
    ];
}
