@extends('automation.layout')

@section('title', 'Create Workflow')

@section('automation_content')
    <div class="automation-page-header workflow-form-header">
        <div>
            <h2 class="workflow-form-title">Create Workflow</h2>
            <p class="workflow-form-subtitle">Configure when this workflow runs and what action to take. One trigger, one action per workflow.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('automation.workflows.store') }}" id="workflowForm" class="workflow-form">
        @csrf

        @if($errors->any())
            <div class="workflow-form-errors" role="alert">
                <strong>Please fix the following:</strong>
                <ul>
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="workflow-steps">
            <div class="workflow-step card" data-step="1">
                <div class="workflow-step-header">
                    <span class="workflow-step-num">1</span>
                    <h3>Name &amp; status</h3>
                </div>
                <p class="workflow-step-desc">Name this workflow and set its status.</p>
                <label class="workflow-label">Workflow name <span class="required">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="150" placeholder="e.g. Opt-in welcome email" class="workflow-input">
                <p class="workflow-hint">Shown in the workflow list. Max 150 characters.</p>
                <label class="workflow-checkbox-label">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <span>Active (run when trigger fires)</span>
                </label>
            </div>

            <div class="workflow-step card" data-step="2">
                <div class="workflow-step-header">
                    <span class="workflow-step-num">2</span>
                    <h3>Trigger</h3>
                </div>
                <p class="workflow-step-desc">Choose the event that starts this workflow.</p>
                <label class="workflow-label">Event <span class="required">*</span></label>
                <select name="trigger" required class="workflow-select">
                    <option value="lead.created" {{ old('trigger') === 'lead.created' ? 'selected' : '' }}>Lead created</option>
                    <option value="funnel.opt_in" {{ old('trigger') === 'funnel.opt_in' ? 'selected' : '' }}>Funnel opt-in</option>
                    <option value="lead.status_changed" {{ old('trigger') === 'lead.status_changed' ? 'selected' : '' }}>Lead status changed</option>
                    <option value="payment.paid" {{ old('trigger') === 'payment.paid' ? 'selected' : '' }}>Payment paid</option>
                    <option value="payment.failed" {{ old('trigger') === 'payment.failed' ? 'selected' : '' }}>Payment failed</option>
                </select>
            </div>

            <div class="workflow-step card" data-step="3">
                <div class="workflow-step-header">
                    <span class="workflow-step-num">3</span>
                    <h3>Conditions (optional)</h3>
                </div>
                <p class="workflow-step-desc">Optional filters for when this should run.</p>
                <label class="workflow-label">Funnel (optional, Funnel opt-in only)</label>
                <select name="funnel_id" class="workflow-select" id="workflowFunnelSelect">
                    <option value="">Any funnel</option>
                    @foreach($funnels ?? [] as $funnel)
                        <option value="{{ $funnel->id }}" {{ (string) old('funnel_id') === (string) $funnel->id ? 'selected' : '' }}>
                            {{ $funnel->name }}
                        </option>
                    @endforeach
                </select>
                <p class="workflow-hint">If set, this workflow only runs when someone opts in on this funnel. Ignored for other triggers.</p>
                <label class="workflow-label">Conditions note</label>
                <input type="text" name="conditions_note" value="{{ old('conditions_note') }}" maxlength="500" placeholder="e.g. Only when status is Closed Won" class="workflow-input">
                <p class="workflow-hint">Max 500 characters.</p>
            </div>

            <div class="workflow-step card" data-step="4">
                <div class="workflow-step-header">
                    <span class="workflow-step-num">4</span>
                    <h3>Action</h3>
                </div>
                <p class="workflow-step-desc">Choose the action to run.</p>
                <label class="workflow-label">Action type <span class="required">*</span></label>
                <select name="action_type" id="workflowActionType" class="workflow-select">
                    <option value="send_email" {{ old('action_type', 'send_email') === 'send_email' ? 'selected' : '' }}>Send Email</option>
                    <option value="start_sequence" {{ old('action_type') === 'start_sequence' ? 'selected' : '' }}>Start Sequence</option>
                    <option value="notify_sales" {{ old('action_type') === 'notify_sales' ? 'selected' : '' }}>Notify Sales Agent</option>
                </select>

                <div id="actionEmailFields" class="workflow-action-fields" style="display: {{ old('action_type', 'send_email') === 'send_email' ? 'block' : 'none' }};">
                    <label class="workflow-label">Recipient</label>
                    <select name="recipient" class="workflow-select">
                        <option value="lead.email" {{ old('recipient', 'lead.email') === 'lead.email' ? 'selected' : '' }}>Lead email</option>
                        <option value="assigned_agent.email" {{ old('recipient') === 'assigned_agent.email' ? 'selected' : '' }}>Assigned agent email</option>
                    </select>
                    <label class="workflow-label">Email subject</label>
                    <input type="text" name="email_subject" value="{{ old('email_subject') }}" maxlength="255" placeholder="e.g. Welcome – use merge tags in n8n for lead name" class="workflow-input">
                    <label class="workflow-label">Email body</label>
                    <textarea name="email_body" rows="4" maxlength="5000" placeholder="e.g. Hi, thanks for opting in. Use merge tags in n8n for personalization." class="workflow-input workflow-textarea">{{ old('email_body') }}</textarea>
                    <p class="workflow-hint">Subject max 255 chars; body max 5000. Merge tags applied in n8n.</p>
                </div>

                <div id="actionSequenceFields" class="workflow-action-fields" style="display: {{ old('action_type') === 'start_sequence' ? 'block' : 'none' }};">
                    <label class="workflow-label">Sequence <span class="required">*</span></label>
                    <select name="sequence_id" class="workflow-select">
                        <option value="">— Select sequence —</option>
                        @foreach($sequences ?? [] as $seq)
                            <option value="{{ $seq->id }}" {{ (string) old('sequence_id') === (string) $seq->id ? 'selected' : '' }}>{{ $seq->name }}</option>
                        @endforeach
                    </select>
                    <p class="workflow-hint">Create sequences under Automation → Sequences.</p>
                </div>

                <div id="actionNotifyFields" class="workflow-action-fields" style="display: {{ old('action_type') === 'notify_sales' ? 'block' : 'none' }};">
                    <p class="workflow-notify-hint">Sends an internal notification to the assigned sales agent. No extra configuration needed.</p>
                </div>
            </div>
        </div>

        <div class="workflow-form-actions">
            <a href="{{ route('automation.workflows.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-create"><i class="fas fa-save"></i> Save Workflow</button>
        </div>
    </form>

    @push('styles')
    <style>
        .workflow-form-header { margin-bottom: 18px; }
        .workflow-form-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 4px 0;
            color: var(--theme-sidebar-text, #1E40AF);
        }
        .workflow-form-subtitle {
            font-size: 11px;
            color: #64748B;
            margin: 0;
        }
        .workflow-form-errors {
            background: #FEF2F2;
            color: #B91C1C;
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 14px;
            font-size: 12px;
        }
        .workflow-form-errors ul {
            margin: 4px 0 0 14px;
            padding: 0;
        }
        .workflow-steps {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .workflow-step {
            padding: 14px;
        }
        .workflow-step-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 2px;
        }
        .workflow-step-num {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--theme-primary, #2563EB);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 11px;
            flex-shrink: 0;
        }
        .workflow-step h3 {
            font-size: 12px !important;
            margin: 0;
            color: var(--theme-sidebar-text, #1E40AF);
            font-weight: 500;
        }
        .workflow-step-desc {
            font-size: 11px !important;
            color: #64748B;
            margin: 0 0 6px 0 !important;
            font-weight: 400;
            line-height: 1.35;
        }
        .workflow-label {
            display: block;
            font-size: 11px !important;
            font-weight: 600;
            margin-bottom: 4px;
            color: var(--theme-sidebar-text, #1E40AF);
        }
        .workflow-label .required {
            color: #DC2626;
        }
        .workflow-input,
        .workflow-select {
            width: 100%;
            padding: 7px 9px;
            border: 1px solid #DBEAFE;
            border-radius: 6px;
            font-size: 12px !important;
            margin-bottom: 6px;
        }
        .workflow-textarea {
            min-height: 80px;
            resize: vertical;
        }
        .workflow-hint {
            font-size: 10px !important;
            color: #94A3B8;
            margin: 2px 0 6px 0;
            line-height: 1.3;
        }
        .workflow-action-fields {
            margin-top: 10px;
        }
        .workflow-notify-hint {
            font-size: 11px;
            color: #64748B;
            margin: 0;
            padding: 8px 10px;
            background: #F8FAFC;
            border-radius: 6px;
        }
        .workflow-checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px !important;
            color: var(--theme-sidebar-text, #1E40AF);
            cursor: pointer;
            margin-top: 6px;
        }
        .workflow-form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .btn-secondary {
            padding: 9px 14px;
            border: 1px solid #DBEAFE;
            border-radius: 6px;
            background: #fff;
            color: var(--theme-sidebar-text);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
    </style>
    @endpush
    @push('scripts')
    <script>
        document.getElementById('workflowActionType').addEventListener('change', function() {
            var v = this.value;
            document.getElementById('actionEmailFields').style.display = v === 'send_email' ? 'block' : 'none';
            document.getElementById('actionSequenceFields').style.display = v === 'start_sequence' ? 'block' : 'none';
            document.getElementById('actionNotifyFields').style.display = v === 'notify_sales' ? 'block' : 'none';
        });
    </script>
    @endpush
@endsection
