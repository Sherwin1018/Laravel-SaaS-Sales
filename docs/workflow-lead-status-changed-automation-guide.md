# Lead Status Changed — n8n Workflow Guide

This guide helps you build an **n8n workflow** for the **Lead status changed** trigger. When a lead’s pipeline status is updated in Laravel (e.g. from "new" to "qualified" or "won"), Laravel will send a webhook to n8n. You can then send an email (e.g. congratulations when status = won), an SMS, or run other automation steps.

**Prerequisite:** Laravel must be wired to dispatch the `lead.status_changed` webhook when the lead’s status changes (see [automation-feature-blueprint.md](automation-feature-blueprint.md)). Until then, you can build and test the n8n workflow using a manual test POST (see Section 5).

---

## 1. n8n screen (quick reference)

| Area | What it's for |
|------|----------------|
| **Canvas** | Your workflow: nodes (Webhook, IF, Send Email, etc.) and connections. |
| **Right panel** | Opens when you click a node — Parameters, Settings, OUTPUT. |
| **Webhook node** | Receives the POST from Laravel. |
| **IF node** | Branch by condition (e.g. "only when new status is 'won'"). |

To add a node: double-click on empty canvas or click **+** between nodes.

---

## 2. Webhook URL and path

Laravel sends the request to:

- **Full URL:** `{N8N_WEBHOOK_BASE_URL}/webhook/{path}`
- **Path for Lead status changed:** `lead-status-changed` (default) or the value of `N8N_WEBHOOK_LEAD_STATUS_CHANGED` in your Laravel `.env`.

Example: `http://localhost:5678/webhook/lead-status-changed`

In n8n: add a **Webhook** node, Method POST, Path `lead-status-changed`. Use **Production** URL when Laravel is wired; **Test** URL for manual testing.

---

## 3. Payload from Laravel (what n8n receives)

Once Laravel is wired, the POST body will look like this (or with fields inside `body`):

**Pattern A — Fields inside `body`:**

```json
{
  "body": {
    "event": "lead.status_changed",
    "tenant_id": 1,
    "lead_id": 14,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "phone": "09171234567",
    "status": "qualified",
    "previous_status": "new"
  }
}
```

**Pattern B — Fields at root:** same keys at the top level (`$json.status`, `$json.previous_status`, etc.).

Optional: `steps`, `email_subject`, `email_body`, `email_sender_name` if Laravel sends automation step content for this trigger.

---

## 4. n8n expressions (use in nodes)

**If your data is inside `body` (Pattern A):**

| Data | Expression |
|------|------------|
| Lead email | `{{ $json.body.email }}` |
| Lead name | `{{ $json.body.name }}` |
| Lead phone | `{{ $json.body.phone }}` |
| **New status** | `{{ $json.body.status }}` |
| **Previous status** | `{{ $json.body.previous_status }}` |
| Tenant ID | `{{ $json.body.tenant_id }}` |
| Lead ID | `{{ $json.body.lead_id }}` |
| Email subject (if sent) | `{{ $json.body.email_subject }}` |
| Email body (if sent) | `{{ $json.body.email_body }}` |

**If your data is at root (Pattern B):** use `$json.status`, `$json.previous_status`, `$json.email`, etc., without `.body`.

---

## 5. Minimal workflow (Webhook → Respond → Send Email)

1. **Webhook** — Add Webhook node. Method: POST. Path: `lead-status-changed`. Save and note the Production URL.
2. **Respond to Webhook** — Connect from Webhook so Laravel gets a 200 response quickly.
3. **Send Email** — Add Email node. To: `{{ $json.body.email }}`, Subject/Body from payload or fixed text (e.g. "Your lead status was updated").
4. **Save** and **Activate**.

Result: every time a lead’s status changes in Laravel, the webhook fires and the lead gets the email (or you can add an IF to send only for certain statuses).

---

## 6. Optional: Send email only for certain statuses (IF or second Switch)

Sometimes you only want to email when the new status is a particular pipeline stage (e.g. Contacted, Proposal Sent, or Closed Won). You can do this with a single **IF**, or for multiple statuses, a **second Switch** (Status Router) which is cleaner than stacking many IF nodes.

### 6.1 Single IF (one status)

1. After **Respond to Webhook** (or after the Webhook if you prefer), add an **IF** node.
2. **Condition:** e.g. `{{ $json.body.status }}` equals `closed_won` (or `qualified`, etc.).
3. Connect **true** branch to **Send Email** (e.g. "Congratulations, you’re marked as won!"). Connect **false** branch to nothing (or to a different action).
4. This way only leads that move to your chosen status get the email.

### 6.2 Second Switch (Status Router) for multiple statuses

If you want **different behavior for several statuses** (e.g. one email for Contacted, another for Proposal Sent), add a **second Switch** after the Webhook (or after Respond):

1. Add a **Switch** node and name it **Status Router**.
2. Connect: **Webhook** (or Respond) → **Status Router**.
3. In **Status Router**:
   - **Mode:** Rules.
   - **Value to check:** `{{ $json.body.status }}` (or `{{ $json.status }}` if your data is at root).
   - Add rules (String equals) for each status you care about, using the **internal values**:
     - `contacted`
     - `proposal_sent`
     - `closed_won` (optional)
     - `closed_lost` (optional)
   - Enable **Fallback Output** for “anything else”.
4. On the canvas, you now have one output per status, plus a fallback.

Connect each status to its own mini-flow, for example:

- **contacted**: Status Router (contacted) → Send Email (\"We reached out\") → Respond.\n
- **proposal_sent**: Status Router (proposal_sent) → Send Email (\"Your proposal is ready\") → Respond.\n
- **closed_won** (optional): Status Router (closed_won) → Send Email (\"Congrats\") → Respond.\n
- **closed_lost** (optional, delayed): Status Router (closed_lost) → Wait (7 days) → Send Email (\"Re-engage\") → Respond.\n
- **fallback**: Status Router (fallback) → Respond (no email).\n

This keeps your workflow tidy even when you handle many statuses differently.

---

## 7. Optional: Wait then SMS

Add **Wait** (e.g. 10 minutes) after the IF or Send Email, then add **SMS** (e.g. Twilio) using `{{ $json.body.phone }}` and a message that references the status change.

---

## 8. Test without Laravel (manual POST)

Until Laravel dispatches the webhook, test with PowerShell (use the **Test** URL from the Webhook node):

```powershell
$body = @{
  event = "lead.status_changed"
  tenant_id = 1
  lead_id = 14
  name = "Jane Doe"
  email = "jane@example.com"
  phone = "09171234567"
  status = "qualified"
  previous_status = "new"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:5678/webhook-test/lead-status-changed" -Method Post -Body $body -ContentType "application/json"
```

Check **Executions** in n8n to see the payload and verify your expressions.

---

## 9. Reference

- **Laravel payload and wiring:** [automation-feature-blueprint.md](automation-feature-blueprint.md)
- **Lead created workflow:** [workflow-lead-created-automation-guide.md](workflow-lead-created-automation-guide.md)
- **Funnel opt-in workflow:** [workflow-funnel-opt-in-automation-guide.md](workflow-funnel-opt-in-automation-guide.md)
