<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LeadLinkClick extends Model
{
    protected $fillable = [
        'tenant_id',
        'lead_id',
        'clicked_at',
    ];
}
