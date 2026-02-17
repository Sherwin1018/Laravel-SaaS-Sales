<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FunnelPage extends Model
{
    public const TYPE_LANDING = 'landing';
    public const TYPE_OPT_IN = 'opt-in';
    public const TYPE_SALES = 'sales';
    public const TYPE_CHECKOUT = 'checkout';

    public const TYPES = [
        self::TYPE_LANDING => 'Landing Page',
        self::TYPE_OPT_IN => 'Opt-in Form',
        self::TYPE_SALES => 'Sales Page',
        self::TYPE_CHECKOUT => 'Checkout Page',
    ];

    protected $fillable = [
        'funnel_id',
        'type',
        'title',
        'slug',
        'content',
        'form_fields',
        'sort_order',
    ];

    protected $casts = [
        'form_fields' => 'array',
        'sort_order' => 'integer',
    ];

    public function funnel(): BelongsTo
    {
        return $this->belongsTo(Funnel::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'source_funnel_page_id');
    }

    public static function makeSlug(string $title, int $funnelId): string
    {
        $base = Str::slug($title);
        $slug = $base ?: 'page';
        $n = 0;
        while (static::where('funnel_id', $funnelId)->where('slug', $slug)->exists()) {
            $slug = ($base ?: 'page') . '-' . (++$n);
        }
        return $slug;
    }
}
