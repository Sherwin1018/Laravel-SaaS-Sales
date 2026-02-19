<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'company_name',
        'subscription_plan',
        'status',
        'logo_path',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

}
