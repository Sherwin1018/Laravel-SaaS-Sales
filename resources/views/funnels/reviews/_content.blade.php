<div class="reviews-toolbar">
    <div>
        <div style="font-size:20px;font-weight:900;color:#240E35;">{{ $funnel->name }}</div>
        <div style="font-size:13px;color:#64748B;">Approve customer reviews before they appear in your funnel review component.</div>
    </div>
    @if(!($modalMode ?? false))
        <a href="{{ route('funnels.edit', $funnel) }}" class="btn-create" style="text-decoration:none;">Back To Builder</a>
    @endif
</div>

@if(session('status'))
    <div class="card" style="margin-bottom:14px;color:#047857;font-weight:700;">
        {{ session('status') }}
    </div>
@endif

<div class="card">
    <form method="GET" action="{{ route('funnels.reviews.index', $funnel) }}" class="reviews-filter">
        @if(($modalMode ?? false))
            <input type="hidden" name="modal" value="1">
        @endif
        <label for="status" style="font-weight:700;color:#240E35;">Status</label>
        <select id="status" name="status" @if(!($modalMode ?? false)) onchange="this.form.submit()" @endif>
            @foreach($statusOptions as $value => $label)
                <option value="{{ $value }}" @selected($activeStatus === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="reviews-list" style="margin-top:16px;">
    @forelse($reviews as $review)
        <div class="review-card">
            <div class="review-head">
                <div>
                    <div class="review-name">{{ $review->customer_name }}</div>
                    <div class="review-meta">
                        @if($review->customer_email)
                            {{ $review->customer_email }}
                        @else
                            No email provided
                        @endif
                        • {{ optional($review->created_at)->format('M d, Y h:i A') }}
                        @if($review->step)
                            • {{ $review->step->title ?: ucfirst(str_replace('_', ' ', $review->step->type)) }}
                        @endif
                    </div>
                    <div class="review-stars">{{ str_repeat('★', max(1, (int) $review->rating)) }}{{ str_repeat('☆', max(0, 5 - (int) $review->rating)) }}</div>
                </div>
                <span class="review-status {{ $review->status }}">{{ ucfirst($review->status) }}</span>
            </div>
            <div class="review-body">{{ $review->review_text }}</div>
            <div class="review-actions">
                @if($review->status !== 'approved')
                    <form method="POST" action="{{ route('funnels.reviews.update', [$funnel, $review]) }}">
                        @csrf
                        @method('PATCH')
                        @if(($modalMode ?? false))
                            <input type="hidden" name="modal" value="1">
                        @endif
                        <input type="hidden" name="status_filter" value="{{ $activeStatus }}">
                        <input type="hidden" name="status" value="approved">
                        <input type="hidden" name="is_public" value="1">
                        <button type="submit" class="review-btn approve">Approve</button>
                    </form>
                @endif
                @if($review->status !== 'pending')
                    <form method="POST" action="{{ route('funnels.reviews.update', [$funnel, $review]) }}">
                        @csrf
                        @method('PATCH')
                        @if(($modalMode ?? false))
                            <input type="hidden" name="modal" value="1">
                        @endif
                        <input type="hidden" name="status_filter" value="{{ $activeStatus }}">
                        <input type="hidden" name="status" value="pending">
                        <input type="hidden" name="is_public" value="0">
                        <button type="submit" class="review-btn">Move To Pending</button>
                    </form>
                @endif
                @if($review->status !== 'rejected')
                    <form method="POST" action="{{ route('funnels.reviews.update', [$funnel, $review]) }}">
                        @csrf
                        @method('PATCH')
                        @if(($modalMode ?? false))
                            <input type="hidden" name="modal" value="1">
                        @endif
                        <input type="hidden" name="status_filter" value="{{ $activeStatus }}">
                        <input type="hidden" name="status" value="rejected">
                        <input type="hidden" name="is_public" value="0">
                        <button type="submit" class="review-btn reject">Reject</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="card">
            No reviews found for this filter yet.
        </div>
    @endforelse
</div>

<div style="margin-top:18px;">
    {{ $reviews->links('pagination::bootstrap-4') }}
</div>
