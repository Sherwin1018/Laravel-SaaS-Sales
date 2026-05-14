@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
    <div class="top-header">
        <h1>Notifications</h1>
    </div>

    <div class="card notifications-summary">
        <div class="notifications-summary-grid">
            <div class="notification-stat">
                <div class="notification-stat__label">Total</div>
                <div class="notification-stat__value">{{ number_format((int) data_get($summary, 'total', 0)) }}</div>
            </div>
            <div class="notification-stat notification-stat--unread">
                <div class="notification-stat__label">Unread</div>
                <div class="notification-stat__value">{{ number_format((int) data_get($summary, 'unread', 0)) }}</div>
            </div>
            <div class="notification-stat notification-stat--read">
                <div class="notification-stat__label">Read</div>
                <div class="notification-stat__value">{{ number_format((int) data_get($summary, 'read', 0)) }}</div>
            </div>
        </div>
    </div>

    <div class="card notifications-panel">
        <div class="notifications-toolbar">
            <div class="notifications-filter-group">
                @foreach(['all' => 'All', 'unread' => 'Unread', 'read' => 'Read'] as $filterKey => $label)
                    <a href="{{ route('notifications.index', ['status' => $filterKey]) }}"
                        class="notifications-filter-pill {{ $statusFilter === $filterKey ? 'is-active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                @csrf
                <button type="submit" class="notifications-toolbar__action">
                    Mark All Read
                </button>
            </form>
        </div>

        <div class="notifications-list">
            @forelse($notifications as $notification)
                @php
                    $levelTone = match ($notification->level) {
                        'error' => ['bg' => 'rgba(220, 38, 38, 0.08)', 'border' => 'rgba(220, 38, 38, 0.2)', 'text' => '#B91C1C'],
                        'warning' => ['bg' => 'rgba(245, 158, 11, 0.08)', 'border' => 'rgba(245, 158, 11, 0.2)', 'text' => '#92400E'],
                        'success' => ['bg' => 'rgba(22, 163, 74, 0.08)', 'border' => 'rgba(22, 163, 74, 0.2)', 'text' => '#166534'],
                        default => ['bg' => 'rgba(59, 130, 246, 0.08)', 'border' => 'rgba(59, 130, 246, 0.2)', 'text' => '#1D4ED8'],
                    };
                @endphp
                <article class="notification-record {{ $notification->read_at ? 'is-read' : 'is-unread' }}"
                    style="--notification-tone-bg: {{ $levelTone['bg'] }}; --notification-tone-border: {{ $levelTone['border'] }}; --notification-tone-text: {{ $levelTone['text'] }}; --notification-surface: {{ $notification->read_at ? '#fff' : $levelTone['bg'] }};">
                    <div class="notification-record__content">
                        <div class="notification-record__meta">
                            <span class="notification-record__badge notification-record__badge--level">
                                    {{ $notification->level }}
                            </span>
                            <span class="notification-record__badge notification-record__badge--event">
                                    {{ $notification->event_name }}
                            </span>
                            <span class="notification-record__time">{{ optional($notification->occurred_at)->format('Y-m-d H:i:s') ?? $emptyDash }}</span>
                        </div>
                        <h3 class="notification-record__title">{{ $notification->title }}</h3>
                        <p class="notification-record__message">{{ $notification->message }}</p>
                    </div>

                    <div class="notification-record__actions">
                        @if($notification->action_url)
                            <a href="{{ $notification->action_url }}" class="notification-record__button">
                                Open
                            </a>
                        @endif

                        @if(!$notification->read_at)
                            <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                @csrf
                                <button type="submit" class="notification-record__button notification-record__button--secondary">
                                    Mark Read
                                </button>
                            </form>
                        @else
                            <span class="notification-record__status">Read</span>
                        @endif
                    </div>
                </article>
            @empty
                <div class="notifications-empty">
                    No notifications found for this filter yet.
                </div>
            @endforelse
        </div>

        <div class="notifications-pagination">
            {{ $notifications->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection
