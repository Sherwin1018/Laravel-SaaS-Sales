<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignupIntent extends Model
{
    public const STATE_SIGNUP_INTENT_CREATED = 'signup_intent_created';
    public const STATE_PAYMENT_PENDING = 'payment_pending';
    public const STATE_PAYMENT_PAID = 'payment_paid';
    public const STATE_ACCOUNT_CREATED_PENDING_ACTIVATION = 'account_created_pending_activation';
    public const STATE_EMAIL_SENT = 'email_sent';
    public const STATE_EMAIL_VERIFIED = 'email_verified';
    public const STATE_PASSWORD_SET = 'password_set';
    public const STATE_ACTIVE = 'active';

    public const STATUSES = [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'completed' => 'Completed',
        'failed' => 'Failed',
    ];

    public const LIFECYCLE_STATES = [
        self::STATE_SIGNUP_INTENT_CREATED,
        self::STATE_PAYMENT_PENDING,
        self::STATE_PAYMENT_PAID,
        self::STATE_ACCOUNT_CREATED_PENDING_ACTIVATION,
        self::STATE_EMAIL_SENT,
        self::STATE_EMAIL_VERIFIED,
        self::STATE_PASSWORD_SET,
        self::STATE_ACTIVE,
    ];

    public const LIFECYCLE_TRANSITIONS = [
        self::STATE_SIGNUP_INTENT_CREATED => [self::STATE_PAYMENT_PENDING],
        self::STATE_PAYMENT_PENDING => [self::STATE_PAYMENT_PAID],
        self::STATE_PAYMENT_PAID => [self::STATE_ACCOUNT_CREATED_PENDING_ACTIVATION],
        self::STATE_ACCOUNT_CREATED_PENDING_ACTIVATION => [self::STATE_EMAIL_SENT],
        self::STATE_EMAIL_SENT => [self::STATE_EMAIL_VERIFIED],
        self::STATE_EMAIL_VERIFIED => [self::STATE_PASSWORD_SET],
        self::STATE_PASSWORD_SET => [self::STATE_ACTIVE],
        self::STATE_ACTIVE => [],
    ];

    protected $fillable = [
        'full_name',
        'company_name',
        'email',
        'mobile',
        'password_encrypted',
        'plan_code',
        'plan_name',
        'amount',
        'status',
        'lifecycle_state',
        'provider',
        'provider_reference',
        'payment_method',
        'paid_at',
        'email_sent_at',
        'email_delivery_status',
        'email_delivery_attempts',
        'email_last_attempt_at',
        'email_last_error',
        'completed_at',
        'activated_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'email_sent_at' => 'datetime',
        'email_last_attempt_at' => 'datetime',
        'completed_at' => 'datetime',
        'activated_at' => 'datetime',
    ];

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = self::normalizeStatus($value);
    }

    public static function normalizeStatus(mixed $value): string
    {
        $normalized = mb_strtolower(trim((string) $value));

        return array_key_exists($normalized, self::STATUSES) ? $normalized : $normalized;
    }

    public static function canTransition(string $from, string $to): bool
    {
        if ($from === $to) {
            return true;
        }

        if (! array_key_exists($from, self::LIFECYCLE_TRANSITIONS)) {
            return false;
        }

        return in_array($to, self::LIFECYCLE_TRANSITIONS[$from], true);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function transitionTo(string $nextState, array $attributes = []): bool
    {
        $current = (string) $this->lifecycle_state;
        if (! self::canTransition($current, $nextState)) {
            return false;
        }

        if ($current === $nextState && $attributes === []) {
            return true;
        }

        $this->fill(array_merge($attributes, [
            'lifecycle_state' => $nextState,
        ]));

        return $this->save();
    }
}
