@extends('automation.layout')

@section('title', 'Sequence Builder')

@section('automation_content')
<div class="sequence-builder">
    {{-- Header --}}
    <header class="sequence-builder-header">
        <a href="{{ route('automation.sequences.index') }}" class="btn-back"><i class="fas fa-arrow-left"></i> Back</a>
        <input type="text" id="sequenceName" class="sequence-name-input" placeholder="Sequence name" value="{{ old('name', optional($sequence)->name ?? '') }}" maxlength="150">
        <div class="header-actions">
            <button type="button" class="btn-secondary" id="sequenceSaveBtn"><i class="fas fa-save"></i> Save</button>
            <label class="toggle-activate">
                <input type="checkbox" id="sequenceActive" name="is_active" value="1" {{ old('is_active', optional($sequence)->is_active ?? true) ? 'checked' : '' }}>
                <span class="toggle-label" id="activateLabel">Activate</span>
            </label>
        </div>
    </header>

    <form id="sequenceBuilderForm" method="POST" action="{{ $sequence ? route('automation.sequences.update', $sequence) : route('automation.sequences.store') }}" style="display: none;">
        @csrf
        @if($sequence) @method('PUT') @endif
        <input type="hidden" name="name" id="formName">
        <input type="hidden" name="is_active" id="formIsActive">
        <input type="hidden" name="steps" id="formSteps">
    </form>

    <div class="sequence-builder-columns">
        {{-- LEFT: Sequence Steps --}}
        <div class="builder-column builder-column-steps">
            <div class="column-header">
                <h3>Sequence Steps</h3>
                <div class="add-step-wrap">
                    <select id="addStepType">
                        <option value="email">Send Email</option>
                        <option value="sms">Send SMS</option>
                        <option value="delay">Wait (delay)</option>
                    </select>
                    <button type="button" class="btn-add-step" id="addStepBtn" title="Add step"><i class="fas fa-plus"></i></button>
                </div>
            </div>
            <div class="steps-list" id="stepsList">
                {{-- Steps rendered by JS; sample placeholder for empty state --}}
                <div class="steps-empty" id="stepsEmpty">No steps yet. Click + to add one.</div>
            </div>
        </div>

        {{-- MIDDLE: Step Editor --}}
        <div class="builder-column builder-column-editor">
            <div class="column-header">
                <h3 id="editorTitle">Edit Step</h3>
            </div>
            <div class="step-editor-card card" id="stepEditorCard">
                <div class="step-editor-empty" id="stepEditorEmpty">Select a step to edit its settings.</div>
                <div class="step-editor-panels" id="stepEditorPanels" style="display: none;">
                    {{-- Email step panel --}}
                    <div class="editor-panel" id="panelEmail" data-step-type="email">
                        <label>Email Subject</label>
                        <input type="text" id="stepEmailSubject" placeholder="e.g. Welcome (use lead name in n8n)" maxlength="255">
                        <label>Email Body</label>
                        <textarea id="stepEmailBody" rows="4" placeholder="e.g. Hi, thanks for joining. Use merge tags in n8n for lead name."></textarea>
                        <label>Recipient</label>
                        <select id="stepEmailRecipient">
                            <option value="lead.email">Lead email</option>
                            <option value="assigned_agent.email">Assigned agent email</option>
                        </select>
                    </div>
                    {{-- SMS step panel --}}
                    <div class="editor-panel" id="panelSms" data-step-type="sms" style="display: none;">
                        <label>SMS Message</label>
                        <textarea id="stepSmsBody" rows="4" placeholder="e.g. Short text message to the lead."></textarea>
                        <label>Recipient</label>
                        <select id="stepSmsRecipient">
                            <option value="lead.phone">Lead phone</option>
                        </select>
                        <p class="field-hint">SMS will be sent by your n8n workflow when this step runs.</p>
                    </div>
                    {{-- Delay step panel --}}
                    <div class="editor-panel" id="panelDelay" data-step-type="delay" style="display: none;">
                        <label>Wait Duration</label>
                        <div class="duration-row">
                            <input type="number" id="stepDelayDuration" min="1" value="2">
                            <select id="stepDelayUnit">
                                <option value="minutes">Minutes</option>
                                <option value="hours">Hours</option>
                                <option value="days" selected>Days</option>
                            </select>
                        </div>
                        <p class="field-hint">Wait this amount of time before proceeding to the next step.</p>
                    </div>
                                    </div>
            </div>
        </div>

        {{-- RIGHT: Sequence Settings --}}
        <div class="builder-column builder-column-settings">
            <div class="column-header">
                <h3>Sequence Settings</h3>
            </div>
            <div class="card settings-card">
                <p class="field-hint" style="margin: 0 0 12px 0;">This sequence runs when a workflow starts it. Configure triggers in Workflows.</p>
                @if(!empty($sequence) && isset($sequenceWorkflows) && count($sequenceWorkflows) > 0)
                    @php
                        $activeCount = collect($sequenceWorkflows)->where('is_active', true)->count();
                    @endphp
                    <div class="sequence-usage-banner" data-active-workflow-count="{{ $activeCount }}">
                        <strong>Used in workflows:</strong>
                        <ul>
                            @foreach($sequenceWorkflows as $wf)
                                <li>
                                    {{ $wf->name }}
                                    @if($wf->is_active)
                                        <span class="usage-badge usage-badge-active">Active</span>
                                    @else
                                        <span class="usage-badge usage-badge-paused">Paused</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        @if($activeCount > 0)
                            <p class="field-hint" style="margin-top: 6px;">At least one active workflow starts this sequence. Pausing the sequence will stop new contacts from entering it.</p>
                        @endif
                    </div>
                @endif
                <label class="setting-toggle">
                    <input type="checkbox" id="sequenceActiveSetting" checked>
                    <span>Active (sequence runs when trigger fires)</span>
                </label>
                <div class="sequence-stats" id="sequenceStats">
                    <h4>Statistics</h4>
                    <p>Enrolled: <strong id="statEnrolled">0</strong></p>
                    <p>Completed: <strong id="statCompleted">0</strong></p>
                    <p>Active: <strong id="statActive">0</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.sequence-builder { padding: 0 0 24px 0; }
.sequence-builder-header {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid #DBEAFE;
}
.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    color: var(--theme-sidebar-text, #1E40AF);
    text-decoration: none;
    font-weight: 600;
    border-radius: 6px;
}
.btn-back:hover { background: #EFF6FF; color: var(--theme-primary, #2563EB); }
.sequence-name-input {
    flex: 1;
    min-width: 200px;
    padding: 10px 14px;
    border: 1px solid #DBEAFE;
    border-radius: 6px;
    font-size: 16px;
    color: var(--theme-sidebar-text, #1E40AF);
}
.header-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.toggle-activate { display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600; color: var(--theme-sidebar-text, #1E40AF); }
.btn-secondary { padding: 10px 16px; border: 1px solid #DBEAFE; border-radius: 6px; background: #fff; color: var(--theme-sidebar-text); font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
.btn-secondary:hover { background: #EFF6FF; }

.sequence-builder-columns {
    display: grid;
    grid-template-columns: 1fr 1.2fr 1fr;
    gap: 24px;
    align-items: start;
}
@media (max-width: 1200px) {
    .sequence-builder-columns { grid-template-columns: 1fr 1fr; }
    .builder-column-settings { grid-column: 1 / -1; }
}
@media (max-width: 768px) {
    .sequence-builder-columns { grid-template-columns: 1fr; }
    .sequence-builder-header { flex-direction: column; align-items: stretch; }
}

.builder-column { min-width: 0; }
.column-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.column-header h3 { margin: 0; font-size: 16px; color: var(--theme-sidebar-text, #1E40AF); }
.btn-add-step {
    width: 36px; height: 36px;
    border-radius: 50%;
    border: 1px solid #DBEAFE;
    background: #fff;
    color: var(--theme-primary, #2563EB);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.btn-add-step:hover { background: #EFF6FF; }
.add-step-wrap { display: flex; align-items: center; gap: 8px; }
.add-step-wrap select {
    padding: 6px 10px;
    border: 1px solid #DBEAFE;
    border-radius: 6px;
    font-size: 13px;
    color: var(--theme-sidebar-text, #1E40AF);
}

.steps-list { display: flex; flex-direction: column; gap: 10px; }
.steps-empty {
    padding: 24px;
    text-align: center;
    color: #64748B;
    font-size: 14px;
    border: 1px dashed #CBD5E1;
    border-radius: 8px;
    background: #F8FAFC;
}
.step-card {
    padding: 12px 14px;
    border: 2px solid #E2E8F0;
    border-radius: 8px;
    background: #fff;
    cursor: pointer;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.step-card:hover { border-color: #DBEAFE; }
.step-card.selected { border-color: var(--theme-primary, #2563EB); box-shadow: 0 0 0 1px var(--theme-primary, #2563EB); }
.step-card-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}
.step-card-drag { color: #94A3B8; cursor: grab; font-size: 12px; }
.step-card-title { font-weight: 600; font-size: 14px; color: var(--theme-sidebar-text, #1E40AF); }
.step-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    background: #EFF6FF;
    color: var(--theme-primary, #2563EB);
}
.step-badge.delay { background: #FEF3C7; color: #92400E; }
.step-badge.condition { background: #E0E7FF; color: #3730A3; }
.step-card-desc { font-size: 13px; color: #64748B; margin: 0; }
.step-card-actions { margin-top: 8px; display: flex; justify-content: flex-end; }
.step-delete-btn { background: none; border: none; color: #64748B; cursor: pointer; padding: 4px; font-size: 12px; }
.step-delete-btn:hover { color: #DC2626; }

.step-editor-card { padding: 20px; }
.step-editor-empty { color: #64748B; font-size: 14px; }
.editor-panel label { display: block; margin: 12px 0 4px 0; font-size: 13px; font-weight: 600; color: var(--theme-sidebar-text, #1E40AF); }
.editor-panel label:first-child { margin-top: 0; }
.editor-panel input[type="text"],
.editor-panel input[type="number"],
.editor-panel select,
.editor-panel textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #DBEAFE;
    border-radius: 6px;
    font-size: 14px;
}
.duration-row { display: flex; gap: 8px; }
.duration-row input { flex: 0 0 80px; }
.duration-row select { flex: 1; }
.field-hint { font-size: 12px; color: #64748B; margin: 8px 0 0 0; }

.settings-card label { display: block; margin: 12px 0 4px 0; font-size: 13px; font-weight: 600; color: var(--theme-sidebar-text, #1E40AF); }
.settings-card label:first-child { margin-top: 0; }
.settings-card select,
.settings-card input[type="text"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #DBEAFE;
    border-radius: 6px;
    font-size: 14px;
}
.setting-toggle { display: flex; align-items: center; gap: 8px; cursor: pointer; margin-top: 12px; }
.sequence-stats { margin-top: 20px; padding-top: 16px; border-top: 1px solid #E2E8F0; }
.sequence-stats h4 { margin: 0 0 8px 0; font-size: 14px; color: var(--theme-sidebar-text, #1E40AF); }
.sequence-stats p { margin: 4px 0; font-size: 13px; color: #64748B; }
.sequence-usage-banner {
    margin-top: 10px;
    padding: 10px 12px;
    border-radius: 6px;
    background: #EFF6FF;
    border: 1px solid #DBEAFE;
    font-size: 13px;
    color: #1E40AF;
}
.sequence-usage-banner ul {
    margin: 6px 0 0 18px;
    padding: 0;
}
.sequence-usage-banner li {
    margin: 0 0 2px 0;
}
.usage-badge {
    display: inline-block;
    margin-left: 6px;
    padding: 1px 6px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
}
.usage-badge-active {
    background: #DCFCE7;
    color: #166534;
}
.usage-badge-paused {
    background: #E5E7EB;
    color: #374151;
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    var steps = [];
    var selectedIndex = -1;

    var stepsList = document.getElementById('stepsList');
    var stepsEmpty = document.getElementById('stepsEmpty');
    var addStepBtn = document.getElementById('addStepBtn');
    var stepEditorEmpty = document.getElementById('stepEditorEmpty');
    var stepEditorPanels = document.getElementById('stepEditorPanels');
    var editorTitle = document.getElementById('editorTitle');
    var panelEmail = document.getElementById('panelEmail');
    var panelSms = document.getElementById('panelSms');
    var panelDelay = document.getElementById('panelDelay');

    var sequenceActiveSetting = document.getElementById('sequenceActiveSetting');
    var sequenceName = document.getElementById('sequenceName');
    var sequenceActive = document.getElementById('sequenceActive');
    var activateLabel = document.getElementById('activateLabel');
    var sequenceSaveBtn = document.getElementById('sequenceSaveBtn');
    var form = document.getElementById('sequenceBuilderForm');
    var formName = document.getElementById('formName');
    var formIsActive = document.getElementById('formIsActive');
    var formSteps = document.getElementById('formSteps');
    var usageBanner = document.querySelector('.sequence-usage-banner');
    var activeUsageCount = 0;
    if (usageBanner && usageBanner.dataset.activeWorkflowCount) {
        activeUsageCount = parseInt(usageBanner.dataset.activeWorkflowCount, 10) || 0;
    }

    var initialSteps = @json($steps ?? []);

    function stepTypeLabel(type) {
        if (type === 'email') return 'Email';
        if (type === 'sms') return 'SMS';
        if (type === 'delay') return 'Delay';
        return type;
    }

    function stepDescription(step) {
        if (step.type === 'email') return (step.config && step.config.subject) ? step.config.subject : 'Send email';
        if (step.type === 'sms') {
            var body = (step.config && step.config.body) ? step.config.body : '';
            if (!body) return 'Send SMS';
            if (body.length > 40) {
                return 'SMS: ' + body.substring(0, 37) + '...';
            }
            return 'SMS: ' + body;
        }
        if (step.type === 'delay') {
            var d = (step.config && step.config.duration) ? step.config.duration : 1;
            var u = (step.config && step.config.unit) ? step.config.unit : 'days';
            return 'Wait ' + d + ' ' + u;
        }
        return 'Step';
    }

    function renderSteps() {
        stepsEmpty.style.display = steps.length ? 'none' : 'block';
        stepsList.querySelectorAll('.step-card').forEach(function(el) { el.remove(); });
        steps.forEach(function(step, i) {
            var card = document.createElement('div');
            card.className = 'step-card' + (selectedIndex === i ? ' selected' : '');
            card.setAttribute('data-index', i);
            card.innerHTML =
                '<div class="step-card-header">' +
                '<span class="step-card-drag" title="Drag to reorder"><i class="fas fa-grip-vertical"></i></span>' +
                '<span class="step-card-title">Step ' + (i + 1) + '</span>' +
                '<span class="step-badge ' + (step.type || 'email') + '">' + stepTypeLabel(step.type || 'email') + '</span>' +
                '</div>' +
                '<p class="step-card-desc">' + (step.description || stepDescription(step)) + '</p>' +
                '<div class="step-card-actions"><button type="button" class="step-delete-btn" data-index="' + i + '" title="Delete"><i class="fas fa-trash-alt"></i></button></div>';
            stepsList.appendChild(card);

            card.addEventListener('click', function(e) {
                if (e.target.closest('.step-delete-btn')) return;
                selectStep(parseInt(card.getAttribute('data-index'), 10));
            });
            card.querySelector('.step-delete-btn').addEventListener('click', function(e) {
                e.stopPropagation();
                deleteStep(parseInt(this.getAttribute('data-index'), 10));
            });
        });
    }

    function selectStep(index) {
        selectedIndex = index;
        renderSteps();
        if (index < 0 || index >= steps.length) {
            stepEditorEmpty.style.display = 'block';
            stepEditorPanels.style.display = 'none';
            return;
        }
        stepEditorEmpty.style.display = 'none';
        stepEditorPanels.style.display = 'block';
        var step = steps[index];
        editorTitle.textContent = 'Edit Step ' + (index + 1);
        panelEmail.style.display = step.type === 'email' ? 'block' : 'none';
        panelSms.style.display = step.type === 'sms' ? 'block' : 'none';
        panelDelay.style.display = step.type === 'delay' ? 'block' : 'none';

        if (step.type === 'email') {
            document.getElementById('stepEmailSubject').value = (step.config && step.config.subject) || '';
            document.getElementById('stepEmailBody').value = (step.config && step.config.body) || '';
            document.getElementById('stepEmailRecipient').value = (step.config && step.config.recipient) || 'lead.email';
        } else if (step.type === 'sms') {
            document.getElementById('stepSmsBody').value = (step.config && step.config.body) || '';
            document.getElementById('stepSmsRecipient').value = (step.config && step.config.recipient) || 'lead.phone';
        } else if (step.type === 'delay') {
            document.getElementById('stepDelayDuration').value = (step.config && step.config.duration) || 2;
            document.getElementById('stepDelayUnit').value = (step.config && step.config.unit) || 'days';
        }
    }

    function syncEditorToStep() {
        if (selectedIndex < 0 || selectedIndex >= steps.length) return;
        var step = steps[selectedIndex];
        if (!step.config) step.config = {};
        if (step.type === 'email') {
            step.config.subject = document.getElementById('stepEmailSubject').value;
            step.config.body = document.getElementById('stepEmailBody').value;
            step.config.recipient = document.getElementById('stepEmailRecipient').value;
        } else if (step.type === 'sms') {
            step.config.body = document.getElementById('stepSmsBody').value;
            step.config.recipient = document.getElementById('stepSmsRecipient').value;
        } else if (step.type === 'delay') {
            step.config.duration = parseInt(document.getElementById('stepDelayDuration').value, 10) || 1;
            step.config.unit = document.getElementById('stepDelayUnit').value;
        }
        step.description = stepDescription(step);
    }

    function deleteStep(index) {
        steps.splice(index, 1);
        if (selectedIndex >= steps.length) selectedIndex = steps.length - 1;
        if (selectedIndex < 0) selectedIndex = -1;
        renderSteps();
        selectStep(selectedIndex);
    }

    addStepBtn.addEventListener('click', function() {
        var type = document.getElementById('addStepType').value;
        var config = {};
        if (type === 'delay') {
            config = { duration: 2, unit: 'days' };
        } else if (type === 'sms') {
            config = { body: '', recipient: 'lead.phone' };
        }
        steps.push({ type: type, config: config, description: '' });
        selectedIndex = steps.length - 1;
        renderSteps();
        selectStep(selectedIndex);
    });

    panelEmail.querySelectorAll('input, select, textarea').forEach(function(el) {
        el.addEventListener('change', syncEditorToStep);
        el.addEventListener('blur', syncEditorToStep);
    });
    panelSms.querySelectorAll('input, select, textarea').forEach(function(el) {
        el.addEventListener('change', syncEditorToStep);
        el.addEventListener('blur', syncEditorToStep);
    });
    panelDelay.querySelectorAll('input, select').forEach(function(el) {
        el.addEventListener('change', syncEditorToStep);
    });

    function syncActiveToggles() {
        var on = sequenceActiveSetting.checked;
        sequenceActive.checked = on;
        activateLabel.textContent = on ? 'Activate' : 'Pause';
    }
    sequenceActive.addEventListener('change', function() {
        sequenceActiveSetting.checked = this.checked;
        activateLabel.textContent = this.checked ? 'Activate' : 'Pause';
    });
    sequenceActiveSetting.addEventListener('change', syncActiveToggles);
    syncActiveToggles();

    sequenceSaveBtn.addEventListener('click', function() {
        syncEditorToStep();
        var name = (sequenceName.value || '').trim();
        if (!name) {
            alert('Please enter a sequence name.');
            sequenceName.focus();
            return;
        }
        if (!sequenceActiveSetting.checked && activeUsageCount > 0) {
            var confirmPause = confirm('This sequence is used by ' + activeUsageCount + ' active workflow(s). Pausing it will stop new contacts from entering those automations. Continue?');
            if (!confirmPause) {
                return;
            }
        }
        formName.value = name;
        formIsActive.value = sequenceActiveSetting.checked ? '1' : '0';
        formSteps.value = JSON.stringify(steps.map(function(s, i) {
            return {
                step_order: i + 1,
                type: s.type,
                config: s.config || {}
            };
        }));
        form.submit();
    });

    if (initialSteps && initialSteps.length > 0) {
        steps = initialSteps.map(function(s) {
            return {
                type: s.type || 'email',
                config: s.config || {},
                description: s.description || ''
            };
        });
        renderSteps();
    }
})();
</script>
@endpush
@endsection
