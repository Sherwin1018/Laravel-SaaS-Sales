<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\InAppNotification;
use Illuminate\Support\Str;

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

    public function createdCoupons(): HasMany
    {
        return $this->hasMany(Coupon::class, 'created_by');
    }

    public function uploadedReceipts(): HasMany
    {
        return $this->hasMany(PaymentReceipt::class, 'uploaded_by');
    }

    public function reviewedReceipts(): HasMany
    {
        return $this->hasMany(PaymentReceipt::class, 'reviewed_by');
    }

    public function commissionEntries(): HasMany
    {
        return $this->hasMany(CommissionEntry::class);
    }

    public function referredLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'referrer_user_id');
    }

    public function referredPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'referrer_user_id');
    }

    public function inAppNotifications(): HasMany
    {
        return $this->hasMany(InAppNotification::class);
    }

    /**
     * Check if user has a role by slug
     */
    public function hasRole(string $roleSlug): bool
    {
        $normalize = static fn (?string $value): string => str_replace('_', '-', strtolower(trim((string) $value)));

        $needle = $normalize($roleSlug);
        if ($needle === '') {
            return false;
        }

        $roleColumn = $normalize($this->role ?? '');
        if ($roleColumn !== '' && $roleColumn === $needle) {
            return true;
        }

        return $this->roles->contains(function ($role) use ($normalize, $needle) {
            return $normalize($role->slug ?? '') === $needle;
        });
    }

    /**
     * Check if user has at least one role from a list of slugs.
     *
     * @param  array<int, string>  $roleSlugs
     */
    public function hasAnyRole(array $roleSlugs): bool
    {
        foreach ($roleSlugs as $roleSlug) {
            if ($this->hasRole($roleSlug)) {
                return true;
            }
        }

        return false;
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
        'referral_code',
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

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if (trim((string) $user->referral_code) === '') {
                $user->referral_code = self::generateUniqueReferralCode((string) ($user->name ?? 'User'));
            }
        });
    }

    public static function generateUniqueReferralCode(string $seed = 'User'): string
    {
        $base = Str::upper(Str::slug($seed, ''));
        $base = $base !== '' ? Str::substr($base, 0, 10) : 'USER';
        $code = $base . '-' . strtoupper(Str::random(6));

        while (self::query()->where('referral_code', $code)->exists()) {
            $code = $base . '-' . strtoupper(Str::random(6));
        }

        return $code;
    }
}
