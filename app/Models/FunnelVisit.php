<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FunnelVisit extends Model
{
    protected $fillable = [
        'tenant_id',
        'funnel_id',
        'funnel_step_id',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'utm_id',
        'referrer',
        'visited_at',
    ];

    public $timestamps = true;
}
