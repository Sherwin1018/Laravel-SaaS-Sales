<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
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

    /**
     * User belongs to a tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * User can have multiple roles
     */
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

    /**
     * Check if user has a role by slug
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles->contains('slug', $roleSlug);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Laravel hashes automatically
        'last_login_at' => 'datetime',
        'invited_at' => 'datetime',
        'activation_completed_at' => 'datetime',
        'must_change_password' => 'boolean',
        'is_customer_portal_user' => 'boolean',
    ];
}
