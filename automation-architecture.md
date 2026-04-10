# Automation Module — Architecture Overview

## How it works (end-to-end)

1. **Laravel fires an event** — When a lead is created, someone opts in to a funnel, a lead's pipeline status changes, or a payment is recorded, the relevant controller calls `AutomationWebhookService` to build a standard payload and queue a webhook via `SendN8nWebhookJob`.
2. **n8n receives the webhook** — A single n8n "router" workflow receives all events on one endpoint and uses a Switch node to branch by event type.
3. **n8n calls back to Laravel** — An HTTP Request node calls `POST /api/automation/tenant/run` with the `tenant_id` and `event`. Laravel looks up active tenant workflows for that event, builds an `actions` array (Send Email, Start Sequence, Notify Sales Agent), and returns it along with the tenant's preferred `from_email`.
4. **n8n executes the actions** — A Split Out node iterates over the actions array. A Switch routes each action to the correct branch:
   - **Start Sequence** — Steps (Email / Delay / SMS) are processed sequentially using Split In Batches (batch size 1) so delays take effect between emails/SMS.
   - **Send Email** — A single email is sent immediately using the workflow's configured subject/body.
   - **Notify Sales Agent** — An internal notification email is sent to the lead's assigned agent.

## Supported events

| Event | Fired from | Description |
|-------|-----------|-------------|
| `lead.created` | `LeadController` | A new lead is added to the CRM |
| `funnel.opt_in` | `FunnelPortalController` / `LeadVerificationController` | Someone opts in on a funnel. If the funnel has **Require double opt-in** enabled, this fires when they *click the verification link*, not on form submit. |
| `lead.status_changed` | `LeadController` | A lead's pipeline status is updated |
| `payment.paid` | `PaymentController` | A payment is marked as paid |
| `payment.failed` | `PaymentController` | A payment fails |

## Workflow actions

| Action type | What it does |
|-------------|-------------|
| `send_email` | Sends a single email with configured subject/body |
| `start_sequence` | Runs a multi-step sequence (Email → Delay → SMS → …) |
| `notify_sales` | Sends a notification email to the lead's assigned sales agent |

## Key files

| Area | Path |
|------|------|
| Webhook service | `app/Services/AutomationWebhookService.php` |
| Queued webhook job | `app/Jobs/SendN8nWebhookJob.php` |
| n8n config | `config/n8n.php` |
| Internal API controller | `app/Http/Controllers/Api/TenantAutomationRunController.php` |
| Automation UI controller | `app/Http/Controllers/AutomationController.php` |
| Models | `app/Models/AutomationWorkflow.php`, `AutomationSequence.php`, `AutomationSequenceStep.php`, `AutomationLog.php`, `AutomationEventOutbox.php` |
| Blade views | `resources/views/automation/` (overview, workflows, sequences, logs) |
| API route | `routes/api.php` — `POST /api/automation/tenant/run` |
| Web routes | `routes/web.php` — `/automation/*` |

## Database tables

| Table | Purpose |
|-------|---------|
| `automation_workflows` | Tenant-configured workflows (trigger, action type, config, filters) |
| `automation_sequences` | Named multi-step sequences (Email / Delay / SMS) |
| `automation_sequence_steps` | Ordered steps belonging to a sequence |
| `automation_logs` | Execution log entries |
| `automation_event_outbox` | Idempotency tracking for dispatched webhooks |

## Tenant UI pages

| Page | Route | Description |
|------|-------|-------------|
| Overview | `/automation` | Summary cards + recent activity |
| Workflows | `/automation/workflows` | List / Create (modal) / Edit / Pause / Delete |
| Sequences | `/automation/sequences` | List / Create / Edit (visual builder) / Pause / Delete |
| Sequence Builder | `/automation/sequences/{id}/builder` | 3-column drag-and-drop step editor |
| Logs | `/automation/logs` | Filterable execution log |

## Sequence step types

| Type | Fields | Description |
|------|--------|-------------|
| `email` | `subject`, `body`, `recipient` | Sends an email to the lead |
| `delay` | `duration`, `unit` (seconds/minutes/hours/days) | Pauses execution before the next step |
| `sms` | `body`, `recipient` | Sends an SMS to the lead (provider TBD) |

## Access control

- **Account Owner** and **Marketing Manager** can access the Automation UI.
- Only **Account Owner** can set the tenant "Automation From Email" (Profile page).
- The internal API is secured with an `X-Automation-Token` header.

## Related documentation (in `docs/`)

| Guide | Covers |
|-------|--------|
| `workflow-n8n-start-sequence-branch-guide.md` | Building the Start Sequence branch with Split In Batches loop, Email/Delay/SMS step handling |
| `workflow-n8n-send-email-and-notify-guide.md` | Building the Send Email and Notify Sales Agent branches |
| `automation-n8n-implementation.md` | Full technical implementation reference |
| `automation-opt-in-and-sequences-overview.md` | High-level team overview of the automation flow |
