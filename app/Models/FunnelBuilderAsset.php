<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FunnelBuilderAsset extends Model
{
    protected $fillable = [
        'tenant_id',
        'funnel_id',
        'user_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'kind',
        'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function funnel(): BelongsTo
    {
        return $this->belongsTo(Funnel::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
