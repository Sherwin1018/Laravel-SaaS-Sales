@extends('layouts.admin')

@section('title', 'Create automation')

@section('content')
<div class="auto-page auto-page-create">
    <header class="auto-page-header">
        <div class="auto-page-header-text">
            <span class="auto-step-badge" aria-hidden="true">Step 1 of 2</span>
            <h1 class="auto-page-title">Create automation</h1>
            <p class="auto-page-subtitle">Set a name and when it runs. You'll add steps (emails/SMS) on the next page.</p>
        </div>
        <a href="{{ route('automation.index') }}" class="auto-link-back"><i class="fas fa-arrow-left" aria-hidden="true"></i> Back to Automation</a>
    </header>

    <div class="auto-card auto-card-form auto-create-form-card">
        <form method="POST" action="{{ route('automation.store') }}" class="auto-create-form">
            @csrf
            <input type="hidden" name="type" value="{{ ($view ?? 'workflow') === 'sequences' ? 'sequence' : 'workflow' }}">

            <div class="auto-create-form-grid">
                <section class="auto-create-block auto-create-block-details">
                    <div class="auto-create-block-header">
                        <i class="fas fa-sliders-h auto-card-icon" aria-hidden="true"></i>
                        <div>
                            <h2 class="auto-section-heading">Details</h2>
                            <p class="auto-card-desc">Name and status. Draft automations don't run until you activate them.</p>
                        </div>
                    </div>
                    <div class="auto-form-group">
                        <label for="name" class="auto-form-label">Name</label>
                        <input id="name" name="name" type="text" required value="{{ old('name') }}" class="auto-input" placeholder="e.g. Welcome new leads">
                        @error('name')<span class="auto-form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="auto-form-group">
                        <label for="status" class="auto-form-label">Status</label>
                        <select id="status" name="status" class="auto-select">
                            @foreach(\App\Models\AutomationWorkflow::STATUSES as $value => $label)
                                <option value="{{ $value }}" {{ old('status', 'draft') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="auto-form-hint">Draft = not running yet.</span>
                        @error('status')<span class="auto-form-error">{{ $message }}</span>@enderror
                    </div>
                </section>

                <section class="auto-create-block auto-create-block-trigger">
                    <div class="auto-create-block-header">
                        <i class="fas fa-bolt auto-card-icon" aria-hidden="true"></i>
                        <div>
                            <h2 class="auto-section-heading">When should it run?</h2>
                            <p class="auto-card-desc">Choose an event. The path is set automatically. Optional: limit to one funnel.</p>
                        </div>
                    </div>
                    <div class="auto-form-group">
                        <label for="trigger_event" class="auto-form-label">When it runs</label>
                        <select id="trigger_event" name="trigger_event" class="auto-select">
                            <option value="">— Don't trigger from this app —</option>
                            @foreach($triggerEvents as $value => $label)
                                <option value="{{ $value }}" {{ old('trigger_event') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="auto-form-hint">e.g. Lead created = when a new lead is added.</span>
                    </div>
                    <div class="auto-form-group auto-create-funnel-wrap" id="trigger_funnel_wrap" style="display: none;">
                        <label for="trigger_funnel_id" class="auto-form-label">Only for this funnel (optional)</label>
                        <select id="trigger_funnel_id" name="trigger_funnel_id" class="auto-select">
                            <option value="">Any funnel</option>
                            @foreach($funnels as $funnel)
                                <option value="{{ $funnel->id }}" {{ (string) old('trigger_funnel_id') === (string) $funnel->id ? 'selected' : '' }}>{{ $funnel->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </section>
            </div>

            <div class="auto-create-form-footer">
                <p class="auto-form-hint">Next: you'll add steps (emails/SMS) on the next page.</p>
                <div class="auto-create-form-actions">
                    <button type="submit" class="auto-btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> Create automation</button>
                    <a href="{{ route('automation.index') }}" class="auto-link-cancel">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/automation.css') }}">
@endsection

@section('scripts')
<script>
(function() {
    var triggerSelect = document.getElementById('trigger_event');
    var funnelWrap = document.getElementById('trigger_funnel_wrap');
    if (triggerSelect && funnelWrap) {
        function toggleFunnel() {
            funnelWrap.style.display = triggerSelect.value ? 'block' : 'none';
        }
        triggerSelect.addEventListener('change', toggleFunnel);
        toggleFunnel();
    }
})();
</script>
@endsection
