# SaaS Event Router — `lead.status_changed` Branch (Step-by-Step Guide)

This guide shows how to build the **`lead.status_changed`** branch in your n8n **SaaS Event Router** workflow. It assumes:

- Laravel is already sending automation events to a **single router webhook** (e.g. `/webhook/saas-events`).
- Your Webhook node exposes the Laravel payload under `body` (Pattern A).

**Why a separate branch:** `lead.status_changed` is **not** the same as `lead.created`. `lead.created` fires once when a lead is first created; `lead.status_changed` fires every time the Pipeline Stage is changed in the CRM (e.g. New → Contacted, Proposal Sent → Closed Won). You need both branches in the first Switch so you can run different automations for “new lead” vs “status changed to X”.

Laravel dispatches `lead.status_changed` when a lead’s **pipeline status** is updated in the CRM. You can use it to send different emails or run different automations based on the new status (e.g. “Contacted”, “Closed Won”).

---

## 1. Payload reference for `lead.status_changed`

Laravel uses `AutomationWebhookService::buildLeadStatusChangedPayload()` + `buildPayload()`. The JSON **request body** looks like:

```json
{
  "event": "lead.status_changed",
  "event_id": "UUID-HERE",
  "tenant_id": 1,
  "lead": {
    "id": 14,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "phone": "09171234567",
    "status": "contacted",
    "assigned_to": "5",
    "source_campaign": "Direct"
  },
  "metadata": {
    "old_status": "new",
    "new_status": "contacted"
  },
  "steps": []
}
```

**Important:** `lead.status` in the payload is the **new** status (after the update). The **previous** status is in `metadata.old_status`; the new one is also in `metadata.new_status` (same as `lead.status`).

In n8n the Webhook node wraps this under `body`, so use:

- `{{ $json.body.event }}`
- `{{ $json.body.lead.email }}`, `{{ $json.body.lead.name }}`, `{{ $json.body.lead.status }}`
- `{{ $json.body.metadata.old_status }}`, `{{ $json.body.metadata.new_status }}`

---

## 2. Trigger a real `lead.status_changed` execution (once)

1. **Laravel:** Queue worker running (`php artisan queue:work`). In the CRM go to **Leads**, edit a lead, **change the status** (e.g. New → Contacted), and save.
2. **n8n:** Open **SaaS Event Router** → **Executions**. Open the new run, click **Webhook** → **OUTPUT**. Confirm `body.event` is `"lead.status_changed"` and `body.metadata.old_status` / `body.metadata.new_status` are present.

---

## 3. Add the `lead.status_changed` rule to the Switch

1. Click the **Switch** node.
2. Add a new rule:
   - **Value 1:** `{{ $json.body.event }}`
   - **Operation:** equals
   - **Value 2:** `lead.status_changed`
3. Optionally name the output (e.g. `lead.status_changed`).
4. The new output is the start of your lead.status_changed branch.

---

## 4. (Optional) Edit Fields node

- **Option A — Pass-through:** Add an Edit Fields node, connect Switch (lead.status_changed) → Edit Fields. Leave it empty so `$json.body.*` is unchanged.
- **Option B — Add fields:** Set e.g. `email_to` = `{{ $json.body.lead.email }}`, `old_status` = `{{ $json.body.metadata.old_status }}`, `new_status` = `{{ $json.body.metadata.new_status }}` for use in later nodes.

---

## 5. Pipeline stage values (UI vs payload)

In the Laravel CRM, the **Pipeline Stage** dropdown shows labels like **New**, **Contacted**, **Proposal Sent**, **Closed Won**, **Closed Lost**. The webhook payload uses **internal keys** (snake_case), not those labels. In n8n’s IF node you must use the **value** from the table below.

| Pipeline Stage (UI) | Value to use in n8n (Value 2) |
|---------------------|-------------------------------|
| New                 | `new`                         |
| Contacted           | `contacted`                  |
| Proposal Sent       | `proposal_sent`              |
| Closed Won          | `closed_won`                 |
| Closed Lost         | `closed_lost`                |

`body.metadata.new_status` and `body.lead.status` always contain one of these five values.

---

## 6. Send an Email (or other action)

Common use: send an email when the lead’s status changes; optionally **only when** the new status is a specific stage (e.g. only for “Closed Won”).

### 6.1 Send on every status change

1. Add **Send an Email**. Connect: **Switch (lead.status_changed)** → [Edit Fields] → **Send an Email**.
2. **To:** `{{ $json.body.lead.email }}`
3. **Subject:** e.g. `Status update: {{ $json.body.metadata.old_status }} → {{ $json.body.metadata.new_status }}`
4. **Body:** e.g. `Hi {{ $json.body.lead.name }}, your lead status was updated to {{ $json.body.metadata.new_status }}.`
5. Connect Send an Email → **Respond to Webhook**.

### 6.2 Send only when new status is a specific value

To send the email **only** when the new pipeline stage is, for example, **Closed Won** (or Contacted, etc.):

1. Add an **IF** node. Connect: **Switch (lead.status_changed)** → [Edit Fields] → **IF**.
2. In the IF node set:
   - **Value 1:** `{{ $json.body.metadata.new_status }}`
   - **Operation:** equals
   - **Value 2:** the exact value from the table in section 5, e.g. `closed_won` for “Closed Won”, or `contacted` for “Contacted”.
3. **IF (true)** → **Send an Email** → **Respond to Webhook**.
4. **IF (false)** → **Respond to Webhook** (no email).

**Examples for Value 2:**

- Only when lead is set to **Closed Won:** `closed_won`
- Only when lead is set to **Contacted:** `contacted`
- Only when lead is set to **New:** `new`
- Only when lead is set to **Proposal Sent:** `proposal_sent`
- Only when lead is set to **Closed Lost:** `closed_lost`

**Multiple statuses (e.g. send for Contacted OR Closed Won):** In the IF node add two conditions and set the mode to **Any (OR)**:
- Condition 1: `{{ $json.body.metadata.new_status }}` equals `contacted`
- Condition 2: `{{ $json.body.metadata.new_status }}` equals `closed_won`

---

## 6.3 Under Main Switch index 2 (lead.status_changed): Second Switch “Status Router”

When your **first Switch** routes by `{{ $json.body.event }}` and has five outputs (e.g. index 0 = lead.created, 1 = funnel.opt_in, **2 = lead.status_changed**, 3 = payment.paid, 4 = payment.failed), add a **second Switch** only on the **lead.status_changed** branch (index 2). This keeps one email (or path) per pipeline stage without stacking many IF nodes.

### Step 1 — First Switch (event router)

Your main Switch is already set up:

- **Value to check:** `{{ $json.body.event }}`
- **Rules (String equals):** `lead.created`, `funnel.opt_in`, `lead.status_changed`, `payment.paid`, `payment.failed`
- **lead.status_changed** is the third rule → that output is **index 2** (0-based).

Do not change this. The next steps are only for what comes **after** the lead.status_changed output.

### Step 2 — Add the second Switch (Status Router) under index 2

1. Add a **Switch** node. Name it **Status Router**.
2. **Disconnect** the current link from **Main Switch (lead.status_changed / index 2)** to **Edit Fields2** (or whatever is there).
3. **Connect:** **Main Switch** (output for `lead.status_changed`, i.e. index 2) → **Status Router**.

### Step 3 — Configure Status Router

In the Status Router node:

- **Mode:** Rules.
- **Value to check:** `{{ $json.body.metadata.new_status }}`  
  (This is the new pipeline stage Laravel sends; use `body.metadata.new_status` because the payload is under `body`.)
- **Add rules (String equals)** for each status you want to handle:
  - `contacted`
  - `proposal_sent`
  - `closed_won` (optional)
  - `closed_lost` (optional)
- **Enable “Fallback” / “Default” output** so any other value (e.g. `new`) goes there and does not error.

Use the **exact values** from the table in section 5 (`contacted`, `proposal_sent`, `closed_won`, `closed_lost`), not the UI labels (“Contacted”, “Proposal Sent”, etc.).

### Step 4 — Connect each Status Router output to Respond (and optional emails)

Wire each output of the Status Router (and the fallback) as follows. Every path that sends an email must eventually connect to **Respond to Webhook** so the webhook gets a response.

| Status Router output | What to connect |
|----------------------|------------------|
| **contacted**        | Optional: Edit Fields → **Send an Email** (e.g. “We reached out”) → **Respond to Webhook**. To: `{{ $json.body.lead.email }}`, Subject e.g. “We reached out, {{ $json.body.lead.name \|\| 'there' }}”. |
| **proposal_sent**    | Optional: Edit Fields → **Send an Email** (e.g. “Your proposal is ready”) → **Respond to Webhook**. To: `{{ $json.body.lead.email }}`, Subject e.g. “Your proposal is ready, {{ $json.body.lead.name \|\| 'there' }}”. |
| **closed_won**       | Optional: **Send an Email** (e.g. “Congrats”) → **Respond to Webhook**. Often skipped if you already email on `payment.paid`. |
| **closed_lost**      | Optional: **Wait** (e.g. 7 days) → **Send an Email** (e.g. “Re-engage”) → **Respond to Webhook**. |
| **Fallback**         | **Respond to Webhook** only (no email). Use this for `new` or any unknown status. |

You can reuse or replace your existing **Edit Fields2** and **Send an Email2** on one of these branches (e.g. for `contacted` or `proposal_sent`). All branches that do not send email (e.g. fallback) must still go to **Respond to Webhook**.

### Resulting flow (index 2 branch only)

```text
Webhook → Main Switch (event)
  → index 2: lead.status_changed
    → Status Router (metadata.new_status)
      → contacted     → [Edit Fields] → Send Email (contacted)  → Respond to Webhook
      → proposal_sent → [Edit Fields] → Send Email (proposal)   → Respond to Webhook
      → closed_won    → Send Email (congrats)                   → Respond to Webhook  (optional)
      → closed_lost   → Wait (e.g. 7 days) → Send Email (re-engage) → Respond to Webhook  (optional)
      → fallback      → Respond to Webhook
```

---

## 7. Connect to Respond to Webhook

Connect the last node of the branch (Send an Email or IF false output) to **Respond to Webhook**. Default 200 is fine.

Flow:

```text
Webhook → Switch (event == "lead.status_changed")
  → [Edit Fields] → [IF new_status == "closed_won" (optional)]
    → true:  Send an Email → Respond to Webhook
    → false: Respond to Webhook
```

---

## 8. Optional: Wait and IF (e.g. only for “Closed Won”)

If you want a delay and/or to act only on certain statuses:

1. After the Switch (lead.status_changed), add **Wait** (e.g. 1 minute). Connect Switch → Wait.
2. Add **IF**. Connect Wait → IF.
3. Configure IF: **Value 1** = `{{ $json.body.metadata.new_status }}`, **Operation** = equals, **Value 2** = `closed_won` (or `contacted`, etc.).
4. **IF (true)** → Send an Email → Respond to Webhook. **IF (false)** → Respond to Webhook.

---

## 9. Testing

1. In Laravel CRM, **edit a lead** and **change its status** (e.g. New → Contacted). Save.
2. In n8n **Executions**, open the new run. Confirm Webhook OUTPUT has `body.event: "lead.status_changed"`, `body.metadata.old_status`, `body.metadata.new_status`, and the path goes through your branch to Respond to Webhook.
3. If you added Send an Email (e.g. only for closed_won), change a lead to that status and check the inbox.

---

## 10. Reference: Expressions for `lead.status_changed` (data in `body`)

| Field | Expression |
|-------|------------|
| Event | `{{ $json.body.event }}` |
| Event ID | `{{ $json.body.event_id }}` |
| Tenant ID | `{{ $json.body.tenant_id }}` |
| Lead ID | `{{ $json.body.lead.id }}` |
| Lead email | `{{ $json.body.lead.email }}` |
| Lead name | `{{ $json.body.lead.name }}` |
| Lead phone | `{{ $json.body.lead.phone }}` |
| Lead status (current/new) | `{{ $json.body.lead.status }}` |
| Old status | `{{ $json.body.metadata.old_status }}` |
| New status | `{{ $json.body.metadata.new_status }}` |

Valid pipeline status values (from Laravel) are: `new`, `contacted`, `proposal_sent`, `closed_won`, `closed_lost`.

---

## 11. Manual test with curl

Trigger the branch without using the Laravel UI:

**PowerShell (Windows):**

```powershell
curl -X POST http://localhost:5678/webhook/saas-events -H "Content-Type: application/json" -d "{\"event\":\"lead.status_changed\",\"event_id\":\"status-1\",\"tenant_id\":1,\"lead\":{\"id\":14,\"name\":\"Jane Doe\",\"email\":\"jane@example.com\",\"phone\":\"09171234567\",\"status\":\"contacted\",\"assigned_to\":\"5\",\"source_campaign\":\"Direct\"},\"metadata\":{\"old_status\":\"new\",\"new_status\":\"contacted\"},\"steps\":[]}"
```

**Bash / Linux / macOS:**

```bash
curl -X POST http://localhost:5678/webhook/saas-events \
  -H "Content-Type: application/json" \
  -d '{"event":"lead.status_changed","event_id":"status-1","tenant_id":1,"lead":{"id":14,"name":"Jane Doe","email":"jane@example.com","phone":"09171234567","status":"contacted","assigned_to":"5","source_campaign":"Direct"},"metadata":{"old_status":"new","new_status":"contacted"},"steps":[]}'
```

Then check n8n **Executions**; Webhook OUTPUT should show `body.event: "lead.status_changed"` and `body.metadata.old_status` / `new_status`.

---

## Related docs

- [workflow-saas-event-router-lead-created.md](workflow-saas-event-router-lead-created.md) — lead.created branch.
- [workflow-saas-event-router-funnel-opt-in.md](workflow-saas-event-router-funnel-opt-in.md) — funnel.opt_in branch.
- [automation-n8n-implementation.md](automation-n8n-implementation.md) — payload format and all events.
