<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignupIntent extends Model
{
    public const STATUSES = [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'completed' => 'Completed',
        'failed' => 'Failed',
    ];

    protected $fillable = [
        'full_name',
        'company_name',
        'email',
        'password_encrypted',
        'plan_code',
        'plan_name',
        'amount',
        'status',
        'provider',
        'provider_reference',
        'payment_method',
        'paid_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'completed_at' => 'datetime',
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
}
