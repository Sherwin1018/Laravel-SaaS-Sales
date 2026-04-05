<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FunnelTemplateAsset extends Model
{
    protected $fillable = [
        'funnel_template_id',
        'created_by',
        'disk',
        'path',
        'kind',
        'original_name',
        'size',
    ];

    public function funnelTemplate(): BelongsTo
    {
        return $this->belongsTo(FunnelTemplate::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
