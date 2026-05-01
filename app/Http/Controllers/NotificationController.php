<?php

namespace App\Http\Controllers;

use App\Models\InAppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $status = trim((string) $request->query('status', 'all'));

        $notifications = InAppNotification::query()
            ->where('user_id', $user->id)
            ->when($status === 'unread', function ($query) {
                $query->whereNull('read_at');
            })
            ->when($status === 'read', function ($query) {
                $query->whereNotNull('read_at');
            })
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            'statusFilter' => in_array($status, ['all', 'read', 'unread'], true) ? $status : 'all',
            'summary' => [
                'total' => InAppNotification::query()->where('user_id', $user->id)->count(),
                'unread' => InAppNotification::query()->where('user_id', $user->id)->whereNull('read_at')->count(),
                'read' => InAppNotification::query()->where('user_id', $user->id)->whereNotNull('read_at')->count(),
            ],
        ]);
    }

    public function markRead(Request $request, InAppNotification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'notification_id' => $notification->id,
                'unread_count' => $this->unreadCountForUser($request->user()->id),
            ]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(Request $request)
    {
        InAppNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    public function feed(Request $request)
    {
        $user = $request->user();
        $notifications = InAppNotification::query()
            ->where('user_id', $user->id)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        return response()->json([
            'unread_count' => InAppNotification::query()
                ->where('user_id', $user->id)
                ->whereNull('read_at')
                ->count(),
            'latest_id' => (int) ($notifications->max('id') ?? 0),
            'notifications' => $notifications
                ->sortBy('id')
                ->values()
                ->map(function (InAppNotification $notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'level' => $notification->level,
                        'event_name' => $notification->event_name,
                        'action_url' => $notification->action_url ?: route('notifications.index'),
                        'read_url' => route('notifications.read', $notification),
                        'read_at' => optional($notification->read_at)?->toIso8601String(),
                        'occurred_at_human' => optional($notification->occurred_at)?->diffForHumans(),
                    ];
                })
                ->all(),
        ]);
    }

    private function unreadCountForUser(int $userId): int
    {
        return InAppNotification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }
}
