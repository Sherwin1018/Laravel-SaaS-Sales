<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InAppNotification extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'source',
        'event_name',
        'level',
        'idempotency_key',
        'title',
        'message',
        'action_url',
        'payload',
        'occurred_at',
        'read_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function getActionUrlAttribute($value): ?string
    {
        $url = trim((string) $value);
        if ($url === '') {
            return null;
        }

        $parsedUrl = parse_url($url);
        $appUrl = trim((string) config('app.url'));
        $parsedAppUrl = $appUrl !== '' ? parse_url($appUrl) : false;

        if (! is_array($parsedUrl) || ! is_array($parsedAppUrl)) {
            return $url;
        }

        $host = strtolower((string) ($parsedUrl['host'] ?? ''));
        $port = isset($parsedUrl['port']) ? (int) $parsedUrl['port'] : null;
        if ($host !== 'localhost' || ! in_array($port, [null, 80, 443], true)) {
            return $url;
        }

        $scheme = (string) ($parsedAppUrl['scheme'] ?? 'http');
        $appHost = trim((string) ($parsedAppUrl['host'] ?? ''));
        if ($appHost === '') {
            return $url;
        }

        $normalized = $scheme . '://' . $appHost;
        if (isset($parsedAppUrl['port'])) {
            $normalized .= ':' . (int) $parsedAppUrl['port'];
        }

        $normalized .= (string) ($parsedUrl['path'] ?? '');

        if (isset($parsedUrl['query']) && $parsedUrl['query'] !== '') {
            $normalized .= '?' . $parsedUrl['query'];
        }

        if (isset($parsedUrl['fragment']) && $parsedUrl['fragment'] !== '') {
            $normalized .= '#' . $parsedUrl['fragment'];
        }

        return $normalized;
    }
}
