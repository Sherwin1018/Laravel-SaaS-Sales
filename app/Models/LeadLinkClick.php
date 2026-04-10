<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LeadLinkClick extends Model
{
    protected $fillable = [
        'tenant_id',
        'lead_id',
        'workflow_id',
        'sequence_id',
        'sequence_step_order',
        'link_name',
        'destination_url',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'utm_id',
        'click_number',
        'clicked_at',
    ];
}
