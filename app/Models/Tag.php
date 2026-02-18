<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'color',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class, 'lead_tag');
    }

    public static function makeSlug(string $name, int $tenantId): string
    {
        $base = Str::slug($name);
        $slug = $base ?: 'tag';
        $n = 0;
        while (static::where('tenant_id', $tenantId)->where('slug', $slug)->exists()) {
            $slug = ($base ?: 'tag') . '-' . (++$n);
        }
        return $slug;
    }
}
