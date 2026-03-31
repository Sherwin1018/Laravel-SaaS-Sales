<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadCustomFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'tenant_custom_field_id',
        'value',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customField(): BelongsTo
    {
        return $this->belongsTo(TenantCustomField::class, 'tenant_custom_field_id');
    }
}
