@extends('layouts.admin')

@section('title', 'Edit Lead')

@section('content')
    <div class="top-header">
        <h1>Edit Lead: {{ $lead->name }}</h1>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="card">
            <h3>Lead Details</h3>
            <form action="{{ route('leads.update', $lead->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div style="margin-bottom: 20px;">
                    <label for="name" style="display: block; margin-bottom: 8px; font-weight: bold;">Name</label>
                    <input type="text" name="name" id="name" required
                        style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;"
                        value="{{ old('name', $lead->name) }}">
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="email" style="display: block; margin-bottom: 8px; font-weight: bold;">Email</label>
                    <input type="email" name="email" id="email" required
                        style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;"
                        value="{{ old('email', $lead->email) }}">
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="phone" style="display: block; margin-bottom: 8px; font-weight: bold;">Phone</label>
                    <input type="text" name="phone" id="phone" required pattern="^09\d{9}$" maxlength="11" minlength="11" inputmode="numeric"
                        style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;"
                        value="{{ old('phone', $lead->phone) }}">
                    @error('phone')
                        <span style="color: red; font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="source_campaign" style="display: block; margin-bottom: 8px; font-weight: bold;">Source / Campaign</label>
                    <select name="source_campaign" id="source_campaign" required
                        style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                        @foreach(['Direct', 'Facebook Ads', 'Google Ads', 'Referral', 'Email Campaign', 'Webinar', 'Organic Search'] as $source)
                            <option value="{{ $source }}" {{ old('source_campaign', $lead->source_campaign ?? 'Direct') === $source ? 'selected' : '' }}>{{ $source }}</option>
                        @endforeach
                    </select>
                    @error('source_campaign')
                        <span style="color: red; font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="status" style="display: block; margin-bottom: 8px; font-weight: bold;">Pipeline Stage</label>
                    <select name="status" id="status" required
                        style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $lead->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                @if(auth()->user()->hasRole('account-owner') || auth()->user()->hasRole('marketing-manager'))
                    <div style="margin-bottom: 20px;">
                        <label for="assigned_to" style="display: block; margin-bottom: 8px; font-weight: bold;">Assign to Sales Agent</label>
                        <select name="assigned_to" id="assigned_to" required
                            style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                            @foreach($assignableAgents as $agent)
                                <option value="{{ $agent->id }}" {{ (string) old('assigned_to', $lead->assigned_to) === (string) $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="assigned_to" value="{{ $lead->assigned_to }}">
                @endif

                <div style="margin-bottom: 20px;">
                    <label for="score" style="display: block; margin-bottom: 8px; font-weight: bold;">Score</label>
                    <input type="number" name="score" id="score"
                        style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;"
                        value="{{ old('score', $lead->score) }}">
                </div>

                @if($canEditTags ?? false)
                    <div style="margin-bottom: 20px;">
                        <label for="tags" style="display: block; margin-bottom: 8px; font-weight: bold;">Tags</label>
                        <input type="text" name="tags" id="tags"
                            style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;"
                            value="{{ old('tags', implode(', ', is_array($lead->tags) ? $lead->tags : [])) }}"
                            placeholder="e.g. webinar, warm-lead, q1-campaign">
                        <p style="margin-top: 6px; color: var(--theme-muted, #6B7280); font-size: 12px; font-weight: 600;">
                            Comma-separated tags. Sales agents can view but not edit.
                        </p>
                    </div>
                @else
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Tags</label>
                        @php($leadTags = is_array($lead->tags) ? $lead->tags : [])
                        @if(count($leadTags))
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                @foreach($leadTags as $tag)
                                    <span style="padding: 4px 10px; border-radius: 999px; background: #E7D8F0; color: #240E35; font-size: 12px; font-weight: 700;">
                                        {{ $tag }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p style="margin:0;color:var(--theme-muted, #6B7280);font-size:12px;">No tags</p>
                        @endif
                    </div>
                @endif

                <div style="display: flex; gap: 10px;">
                    <button type="submit"
                        style="padding: 10px 20px; background-color: var(--theme-primary, #240E35); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        Update Lead
                    </button>
                    <a href="{{ route('leads.index') }}"
                        style="padding: 10px 20px; background-color: var(--theme-primary-dark, #2E1244); color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <div class="card">
            <h3>Activity Log</h3>

            <div style="margin-bottom: 20px; max-height: 220px; overflow-y: auto;">
                @forelse($lead->activities as $activity)
                    <div style="border-left: 2px solid var(--theme-primary, #240E35); padding-left: 10px; margin-bottom: 15px;">
                        <p style="font-size: 12px; color: #6B7280; margin-bottom: 4px;">
                            {{ $activity->created_at->format('M d, H:i') }} - <strong>{{ $activity->activity_type }}</strong>
                        </p>
                        <p style="font-size: 14px; color: #1F2937;">{{ $activity->notes }}</p>
                    </div>
                @empty
                    <p style="color: #6B7280; font-style: italic;">No activities recorded yet.</p>
                @endforelse
            </div>

            <hr style="border: 0; border-top: 1px solid var(--theme-border, #E6E1EF); margin: 15px 0;">

            <h4>Add Note</h4>
            <form action="{{ route('leads.activities.store', $lead->id) }}" method="POST" style="margin-bottom: 16px;">
                @csrf
                <input type="hidden" name="activity_type" value="Note">
                <textarea name="notes" rows="2" required
                    style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px; margin-bottom: 10px;"
                    placeholder="Enter activity details..."></textarea>
                <button type="submit"
                    style="padding: 8px 16px; background-color: #10B981; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    Add Note
                </button>
            </form>

            @if(auth()->user()->hasRole('account-owner') || auth()->user()->hasRole('marketing-manager'))
                <h4>Log Email Activity</h4>
                <form action="{{ route('leads.log-email', $lead->id) }}" method="POST" style="margin-bottom: 16px;">
                    @csrf
                    <input type="text" name="subject" placeholder="Email subject" required
                        style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px; margin-bottom: 10px;">
                    <textarea name="message" rows="2" required placeholder="Message summary"
                        style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px; margin-bottom: 10px;"></textarea>
                    <button type="submit"
                        style="padding: 8px 16px; background-color: var(--theme-accent, #6B4A7A); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        Log Email
                    </button>
                </form>
            @endif

            <h4>Scoring Events</h4>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <form action="{{ route('leads.score-event', $lead->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="event" value="email_opened">
                    <button type="submit" style="padding: 6px 10px; border: 1px solid var(--theme-border, #E6E1EF); background: var(--theme-surface-soft, #F3EEF7); border-radius: 6px; cursor: pointer; font-weight: 600;">
                        +5 Email Opened
                    </button>
                </form>
                <form action="{{ route('leads.score-event', $lead->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="event" value="link_clicked">
                    <button type="submit" style="padding: 6px 10px; border: 1px solid #FDE68A; background: #FFFBEB; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        +10 Link Clicked
                    </button>
                </form>
                <form action="{{ route('leads.score-event', $lead->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="event" value="form_submitted">
                    <button type="submit" style="padding: 6px 10px; border: 1px solid #BBF7D0; background: #F0FDF4; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        +20 Form Submitted
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
