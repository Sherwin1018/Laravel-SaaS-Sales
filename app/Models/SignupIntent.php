<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignupIntent extends Model
{
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
}
