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
                        style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;"
                        value="{{ old('name', $lead->name) }}">
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="email" style="display: block; margin-bottom: 8px; font-weight: bold;">Email</label>
                    <input type="email" name="email" id="email" required
                        style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;"
                        value="{{ old('email', $lead->email) }}">
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="phone" style="display: block; margin-bottom: 8px; font-weight: bold;">Phone</label>
                    <input type="text" name="phone" id="phone" required pattern="^09\d{9}$" maxlength="11" minlength="11" inputmode="numeric"
                        style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;"
                        value="{{ old('phone', $lead->phone) }}">
                    @error('phone')
                        <span style="color: red; font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="status" style="display: block; margin-bottom: 8px; font-weight: bold;">Pipeline Stage</label>
                    <select name="status" id="status" required
                        style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $lead->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                @if(auth()->user()->hasRole('account-owner') || auth()->user()->hasRole('marketing-manager'))
                    <div style="margin-bottom: 20px;">
                        <label for="assigned_to" style="display: block; margin-bottom: 8px; font-weight: bold;">Assign to Sales Agent</label>
                        <select name="assigned_to" id="assigned_to" required
                            style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                            @foreach($assignableAgents as $agent)
                                <option value="{{ $agent->id }}" {{ (string) old('assigned_to', $lead->assigned_to) === (string) $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div style="margin-bottom: 20px;">
                    <label for="score" style="display: block; margin-bottom: 8px; font-weight: bold;">Score</label>
                    <input type="number" name="score" id="score"
                        style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;"
                        value="{{ old('score', $lead->score) }}">
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit"
                        style="padding: 10px 20px; background-color: #2563EB; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        Update Lead
                    </button>
                    <a href="{{ route('leads.index') }}"
                        style="padding: 10px 20px; background-color: #1E40AF; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <div class="card">
            <h3>Activity Log</h3>

            <div style="margin-bottom: 20px; max-height: 220px; overflow-y: auto;">
                @forelse($lead->activities as $activity)
                    <div style="border-left: 2px solid #2563EB; padding-left: 10px; margin-bottom: 15px;">
                        <p style="font-size: 12px; color: #6B7280; margin-bottom: 4px;">
                            {{ $activity->created_at->format('M d, H:i') }} - <strong>{{ $activity->activity_type }}</strong>
                        </p>
                        <p style="font-size: 14px; color: #1F2937;">{{ $activity->notes }}</p>
                    </div>
                @empty
                    <p style="color: #6B7280; font-style: italic;">No activities recorded yet.</p>
                @endforelse
            </div>

            <hr style="border: 0; border-top: 1px solid #DBEAFE; margin: 15px 0;">

            <h4>Add Note</h4>
            <form action="{{ route('leads.activities.store', $lead->id) }}" method="POST" style="margin-bottom: 16px;">
                @csrf
                <input type="hidden" name="activity_type" value="Note">
                <textarea name="notes" rows="2" required
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px; margin-bottom: 10px;"
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
                        style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px; margin-bottom: 10px;">
                    <textarea name="message" rows="2" required placeholder="Message summary"
                        style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px; margin-bottom: 10px;"></textarea>
                    <button type="submit"
                        style="padding: 8px 16px; background-color: #0EA5E9; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        Log Email
                    </button>
                </form>
            @endif

            <h4>Scoring Events</h4>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <form action="{{ route('leads.score-event', $lead->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="event" value="email_opened">
                    <button type="submit" style="padding: 6px 10px; border: 1px solid #BFDBFE; background: #EFF6FF; border-radius: 6px; cursor: pointer; font-weight: 600;">
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
