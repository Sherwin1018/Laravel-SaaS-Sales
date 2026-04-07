<?php

namespace App\Http\Controllers;

use App\Models\Funnel;
use App\Models\FunnelReview;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FunnelReviewController extends Controller
{
    public function index(Request $request, Funnel $funnel): View
    {
        abort_unless((int) $funnel->tenant_id === (int) auth()->user()->tenant_id, 403);
        $modalMode = $request->boolean('modal');

        $status = strtolower(trim((string) $request->query('status', 'pending')));
        if (! array_key_exists($status, FunnelReview::STATUSES) && $status !== 'all') {
            $status = 'pending';
        }

        $reviewsQuery = FunnelReview::query()
            ->with(['step', 'payment'])
            ->where('tenant_id', $funnel->tenant_id)
            ->where('funnel_id', $funnel->id)
            ->latest('created_at');

        if ($status !== 'all') {
            $reviewsQuery->where('status', $status);
        }

        $viewData = [
            'funnel' => $funnel,
            'reviews' => $reviewsQuery->paginate(20)->withQueryString(),
            'activeStatus' => $status,
            'statusOptions' => ['all' => 'All'] + FunnelReview::STATUSES,
            'modalMode' => $modalMode,
        ];

        if ($modalMode && $request->ajax()) {
            return view('funnels.reviews._content', $viewData);
        }

        return view('funnels.reviews.index', $viewData);
    }

    public function updateStatus(Request $request, Funnel $funnel, FunnelReview $review)
    {
        abort_unless((int) $funnel->tenant_id === (int) auth()->user()->tenant_id, 403);
        abort_unless((int) $review->tenant_id === (int) $funnel->tenant_id && (int) $review->funnel_id === (int) $funnel->id, 404);

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'is_public' => 'nullable|boolean',
        ]);

        $status = (string) $validated['status'];
        $review->status = $status;
        $review->is_public = (bool) ($validated['is_public'] ?? ($status === 'approved'));
        if ($status === 'approved') {
            $review->approved_at = now();
            $review->approved_by = auth()->id();
        } else {
            $review->approved_at = null;
            if ($status !== 'pending') {
                $review->approved_by = auth()->id();
            }
        }
        $review->save();

        if ($request->boolean('modal') && $request->ajax()) {
            $statusFilter = strtolower(trim((string) $request->input('status_filter', 'pending')));
            if (! array_key_exists($statusFilter, FunnelReview::STATUSES) && $statusFilter !== 'all') {
                $statusFilter = 'pending';
            }

            $reviewsQuery = FunnelReview::query()
                ->with(['step', 'payment'])
                ->where('tenant_id', $funnel->tenant_id)
                ->where('funnel_id', $funnel->id)
                ->latest('created_at');

            if ($statusFilter !== 'all') {
                $reviewsQuery->where('status', $statusFilter);
            }

            return response()->view('funnels.reviews._content', [
                'funnel' => $funnel,
                'reviews' => $reviewsQuery->paginate(20)->withQueryString(),
                'activeStatus' => $statusFilter,
                'statusOptions' => ['all' => 'All'] + FunnelReview::STATUSES,
                'modalMode' => true,
            ]);
        }

        $routeParams = ['funnel' => $funnel];
        if ($request->boolean('modal')) {
            $routeParams['modal'] = 1;
        }
        if ($request->filled('status_filter')) {
            $routeParams['status'] = (string) $request->input('status_filter');
        }

        return redirect()
            ->route('funnels.reviews.index', $routeParams)
            ->with('status', 'Review updated.');
    }
}
