<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'company_name',
        'logo_path',
        'subscription_plan',
        'status',
        'theme_primary_color',
        'theme_accent_color',
        'theme_sidebar_bg',
        'theme_sidebar_text',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function funnels()
    {
        return $this->hasMany(Funnel::class);
    }

}
