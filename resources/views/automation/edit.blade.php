@extends('layouts.admin')

@section('title', 'Edit ' . $workflow->name)

@section('content')
@php
    $firstTrigger = $workflow->triggers->first();
    $triggerLabel = $firstTrigger ? (\App\Models\AutomationTrigger::EVENTS[$firstTrigger->event] ?? $firstTrigger->event) : '—';
    $triggerTag = $workflow->trigger_tag ?? '';
    $settingsLabel = 'Settings';
    $stepsLabel = 'Steps';
@endphp
<div class="auto-page auto-page-edit">
    {{-- Top bar: Back | Name | Save | Activate --}}
    <header class="auto-edit-topbar">
        <a href="{{ route('automation.index') }}" class="auto-edit-topbar-back"><i class="fas fa-arrow-left" aria-hidden="true"></i> Back</a>
        <h1 class="auto-edit-topbar-title">{{ $workflow->name }}</h1>
        <div class="auto-edit-topbar-actions">
            <button type="button" class="auto-btn-save" data-auto-sticky-save aria-label="Save"><i class="fas fa-save" aria-hidden="true"></i> Save</button>
            @if($workflow->display_status !== 'active')
                <form method="POST" action="{{ route('automation.update', $workflow) }}" class="auto-edit-topbar-activate-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="name" value="{{ $workflow->name }}">
                    <input type="hidden" name="type" value="{{ $workflow->type }}">
                    <input type="hidden" name="status" value="active">
                    <button type="submit" class="auto-btn-primary"><i class="fas fa-play" aria-hidden="true"></i> Activate</button>
                </form>
            @else
                <span class="auto-badge auto-badge-active">Active</span>
            @endif
        </div>
    </header>

    {{-- Mobile sticky bar (Back, Save, Activate) --}}
    <div class="auto-edit-sticky-bar" aria-label="Quick actions">
        <a href="{{ route('automation.index') }}" class="auto-btn-secondary auto-btn-icon-text"><i class="fas fa-arrow-left" aria-hidden="true"></i> Back</a>
        <button type="button" class="auto-btn-primary auto-btn-sm" data-auto-sticky-save aria-label="Save">Save</button>
        @if($workflow->display_status !== 'active')
            <form method="POST" action="{{ route('automation.update', $workflow) }}" class="auto-edit-sticky-activate-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="name" value="{{ $workflow->name }}">
                <input type="hidden" name="type" value="{{ $workflow->type }}">
                <input type="hidden" name="status" value="active">
                <button type="submit" class="auto-btn-primary auto-btn-sm">Activate</button>
            </form>
        @else
            <span class="auto-badge auto-badge-active auto-badge-sm">Active</span>
        @endif
    </div>

    @if(session('success'))
        <div class="auto-toast" role="status">{{ session('success') }}</div>
    @endif

    <div class="auto-edit-layout">
        {{-- Left: Steps --}}
        <aside class="auto-edit-col auto-edit-steps-col">
            <div class="auto-card auto-card-steps">
                <div class="auto-card-steps-header">
                    <h2 class="auto-section-heading">{{ $stepsLabel }}</h2>
                    <a href="{{ route('automation.edit', $workflow) }}" class="auto-btn-add-step" title="Add step"><i class="fas fa-plus" aria-hidden="true"></i></a>
                </div>
                @if($workflow->sequenceSteps->isEmpty())
                    <p class="auto-card-desc auto-card-desc-muted auto-steps-empty">No steps yet. Click + to add one.</p>
                @else
                    <ul class="auto-step-cards" aria-label="Sequence steps">
                        @foreach($workflow->sequenceSteps as $step)
                            <li class="auto-step-card {{ $selectedStep && $selectedStep->id === $step->id ? 'is-selected' : '' }}">
                                <span class="auto-step-grip" aria-hidden="true"><i class="fas fa-grip-vertical"></i></span>
                                <a href="{{ route('automation.edit', ['workflow' => $workflow, 'step' => $step->id]) }}" class="auto-step-card-link">
                                    <span class="auto-step-card-label">Step {{ $step->position }}</span>
                                    <span class="auto-step-chip auto-step-chip-{{ $step->channel }}">{{ $step->channel === 'email' ? 'Email' : 'SMS' }}</span>
                                    <span class="auto-step-card-subject">{{ Str::limit($step->subject ?? 'Step ' . $step->position, 40) }}</span>
                                </a>
                                <form method="POST" action="{{ route('automation.sequences.destroy', [$workflow, $step]) }}" class="auto-step-card-delete" onsubmit="return confirm('Remove this step?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="auto-btn-icon auto-btn-icon-sm" title="Remove step" aria-label="Remove step"><i class="fas fa-trash"></i></button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </aside>

        {{-- Center: Edit Step N / Add step --}}
        <main class="auto-edit-col auto-edit-editor-col">
            <div class="auto-card auto-card-form auto-editor-card">
                @if($selectedStep)
                    <h2 class="auto-section-heading">Edit Step {{ $selectedStep->position }}</h2>
                    <p class="auto-card-desc auto-editor-desc">Update content and settings. Changes save to the step on the left.</p>
                    <form method="POST" action="{{ route('automation.sequences.update', [$workflow, $selectedStep]) }}" class="auto-form-step" data-auto-save-form>
                        @csrf
                        @method('PUT')
                        <div class="auto-form-step-section">
                            <h3 class="auto-form-step-section-title">Content</h3>
                            <div class="auto-form-group">
                                <label for="sender_name" class="auto-form-label">Sender Name</label>
                                <input id="sender_name" name="sender_name" type="text" value="{{ old('sender_name', $selectedStep->sender_name) }}" class="auto-input" placeholder="e.g. SaaS Platform">
                            </div>
                            <div class="auto-form-group">
                                <label for="subject" class="auto-form-label">Subject</label>
                                <input id="subject" name="subject" type="text" value="{{ old('subject', $selectedStep->subject) }}" class="auto-input" placeholder="e.g. Welcome to our platform!">
                            </div>
                            <div class="auto-form-group">
                                <label for="body" class="auto-form-label">Body <span class="auto-required">Required</span></label>
                                <textarea id="body" name="body" rows="8" required class="auto-textarea auto-textarea-lg" placeholder="Hi @{{ name }}, thanks for signing up." data-placeholder-target>{{ old('body', $selectedStep->body) }}</textarea>
                                <span class="auto-form-hint">Click a placeholder to insert:</span>
                                <div class="auto-placeholder-pills" data-placeholder-target="body">
                                    <button type="button" class="auto-placeholder-pill" data-insert="@{{ name }}">@{{ name }}</button>
                                    <button type="button" class="auto-placeholder-pill" data-insert="@{{ email }}">@{{ email }}</button>
                                    <button type="button" class="auto-placeholder-pill" data-insert="@{{ phone }}">@{{ phone }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="auto-form-step-section">
                            <h3 class="auto-form-step-section-title">Settings</h3>
                            <div class="auto-form-group">
                                <label for="channel" class="auto-form-label">Channel</label>
                                <select id="channel" name="channel" class="auto-select">
                                    <option value="email" {{ old('channel', $selectedStep->channel) === 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="sms" {{ old('channel', $selectedStep->channel) === 'sms' ? 'selected' : '' }}>SMS</option>
                                </select>
                            </div>
                            <div class="auto-form-group">
                                <label for="delay_minutes" class="auto-form-label">Delay after previous step (minutes)</label>
                                <input id="delay_minutes" name="delay_minutes" type="number" min="0" value="{{ old('delay_minutes', $selectedStep->delay_minutes) }}" class="auto-input" style="width: 100px;">
                            </div>
                        </div>
                        <button type="submit" class="auto-btn-primary" data-auto-submit>Save step</button>
                    </form>
                @else
                    <h2 class="auto-section-heading">Add step</h2>
                    <p class="auto-card-desc auto-editor-desc">Add an email or SMS step. It will appear in the list on the left.</p>
                    <form method="POST" action="{{ route('automation.sequences.store', $workflow) }}" class="auto-form-step" data-auto-save-form>
                        @csrf
                        <div class="auto-form-step-section">
                            <h3 class="auto-form-step-section-title">Content</h3>
                            <div class="auto-form-group">
                                <label for="sender_name_new" class="auto-form-label">Sender Name</label>
                                <input id="sender_name_new" name="sender_name" type="text" value="{{ old('sender_name') }}" class="auto-input" placeholder="e.g. SaaS Platform">
                            </div>
                            <div class="auto-form-group">
                                <label for="subject_new" class="auto-form-label">Subject</label>
                                <input id="subject_new" name="subject" type="text" value="{{ old('subject') }}" class="auto-input" placeholder="e.g. Welcome!">
                            </div>
                            <div class="auto-form-group">
                                <label for="body_new" class="auto-form-label">Body <span class="auto-required">Required</span></label>
                                <textarea id="body_new" name="body" rows="8" required class="auto-textarea auto-textarea-lg" placeholder="Hi @{{ name }}, thanks for signing up." data-placeholder-target>{{ old('body') }}</textarea>
                                <span class="auto-form-hint">Click a placeholder to insert:</span>
                                <div class="auto-placeholder-pills" data-placeholder-target="body_new">
                                    <button type="button" class="auto-placeholder-pill" data-insert="@{{ name }}">@{{ name }}</button>
                                    <button type="button" class="auto-placeholder-pill" data-insert="@{{ email }}">@{{ email }}</button>
                                    <button type="button" class="auto-placeholder-pill" data-insert="@{{ phone }}">@{{ phone }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="auto-form-step-section">
                            <h3 class="auto-form-step-section-title">Settings</h3>
                            <div class="auto-form-group">
                                <label for="channel_new" class="auto-form-label">Channel</label>
                                <select id="channel_new" name="channel" class="auto-select">
                                    <option value="email">Email</option>
                                    <option value="sms">SMS</option>
                                </select>
                            </div>
                            <div class="auto-form-group">
                                <label for="delay_minutes_new" class="auto-form-label">Delay after previous step (minutes)</label>
                                <input id="delay_minutes_new" name="delay_minutes" type="number" min="0" value="{{ old('delay_minutes', 0) }}" class="auto-input" style="width: 100px;">
                            </div>
                        </div>
                        <button type="submit" class="auto-btn-primary" data-auto-submit><i class="fas fa-plus"></i> Add step</button>
                    </form>
                @endif
            </div>
        </main>

        {{-- Right: Settings + Statistics (two separate cards like reference) --}}
        <aside class="auto-edit-col auto-edit-settings-col">
            <div class="auto-card auto-card-settings">
                <h2 class="auto-section-heading">{{ $settingsLabel }}</h2>
                <form method="POST" action="{{ route('automation.update', $workflow) }}">
                    @csrf
                    @method('PUT')
                    <div class="auto-form-group">
                        <label for="name" class="auto-form-label">Name</label>
                        <input id="name" name="name" type="text" required value="{{ old('name', $workflow->name) }}" class="auto-input">
                    </div>
                    <div class="auto-form-group">
                        <label for="status" class="auto-form-label">Status</label>
                        <select id="status" name="status" class="auto-select">
                            @foreach(\App\Models\AutomationWorkflow::STATUSES as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $workflow->display_status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="auto-form-group">
                        <label for="trigger_event" class="auto-form-label">When it runs</label>
                        <select id="trigger_event" name="trigger_event" class="auto-select">
                            <option value="">— Select —</option>
                            @foreach($triggerEvents as $value => $label)
                                <option value="{{ $value }}" {{ $firstTrigger && $firstTrigger->event === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(\Illuminate\Support\Facades\Schema::hasColumn('automation_workflows', 'trigger_tag'))
                    <div class="auto-form-group">
                        <label for="trigger_tag" class="auto-form-label">Trigger Tag</label>
                        <input id="trigger_tag" name="trigger_tag" type="text" value="{{ old('trigger_tag', $triggerTag) }}" class="auto-input" placeholder="e.g. new-lead">
                    </div>
                    @endif
                    <button type="submit" class="auto-btn-primary auto-btn-sm">Save settings</button>
                </form>
            </div>
            <div class="auto-card auto-card-stats">
                <h3 class="auto-section-heading auto-section-heading-sm">Statistics</h3>
                <p class="auto-stats-empty-hint">No data yet. Stats will appear when the automation runs.</p>
                <dl class="auto-stats-dl">
                    <div class="auto-stats-row">
                        <dt>Enrolled</dt>
                        <dd>—</dd>
                    </div>
                    <div class="auto-stats-row">
                        <dt>Completed</dt>
                        <dd>—</dd>
                    </div>
                    <div class="auto-stats-row">
                        <dt>Active</dt>
                        <dd>—</dd>
                    </div>
                </dl>
            </div>
        </aside>
    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/automation.css') }}">
@endsection

@section('scripts')
<script>
(function() {
    var forms = document.querySelectorAll('[data-auto-save-form]');
    forms.forEach(function(form) {
        form.addEventListener('submit', function() {
            var btn = form.querySelector('[data-auto-submit]');
            if (btn) {
                btn.disabled = true;
                btn.textContent = btn.dataset.savingText || 'Saving…';
            }
        });
    });

    var stickySave = document.querySelectorAll('[data-auto-sticky-save]');
    stickySave.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var form = document.querySelector('[data-auto-save-form]');
            if (form) {
                var submitBtn = form.querySelector('[data-auto-submit]');
                if (submitBtn) submitBtn.click();
            }
        });
    });

    document.querySelectorAll('.auto-placeholder-pills').forEach(function(container) {
        var targetId = container.getAttribute('data-placeholder-target');
        var textarea = document.getElementById(targetId);
        if (!textarea) return;
        container.querySelectorAll('.auto-placeholder-pill').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var insert = this.getAttribute('data-insert') || '';
                var start = textarea.selectionStart, end = textarea.selectionEnd;
                var before = textarea.value.substring(0, start), after = textarea.value.substring(end);
                textarea.value = before + insert + after;
                textarea.selectionStart = textarea.selectionEnd = start + insert.length;
                textarea.focus();
            });
        });
    });
})();
</script>
@endsection
