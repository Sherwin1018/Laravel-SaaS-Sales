# SaaS Event Router — lead.created Branch (Step-by-Step Guide)

This guide walks you through building the **lead.created** branch of your n8n **SaaS Event Router** workflow: configuring the Switch rule, connecting Edit Fields and Send an Email, and responding to the webhook. It uses the exact payload shape Laravel sends (see [automation-n8n-implementation.md](automation-n8n-implementation.md)) and works whether n8n exposes the data in `body` or at root.

---

## 1. Prerequisites

- **SaaS Event Router** workflow already created in n8n with:
  - A **Webhook** trigger node (path = your router path, e.g. `saas-events` or `events`, matching `N8N_ROUTER_PATH` in Laravel).
  - A **Switch** node connected after the Webhook.
- **Laravel** is sending events to this webhook (queue worker running, `N8N_USE_ROUTER=true`, `N8N_ROUTER_PATH` set).
- At least **one successful execution** triggered by a **lead.created** event (create a lead in Laravel CRM → Leads → Create, then check n8n Executions). You will use it to confirm where the data lives (body vs root).

---

## 2. Confirm Where the Data Lives (body vs root)

The Webhook node’s output structure determines which expressions you use in later nodes.

1. In n8n, open your **SaaS Event Router** workflow.
2. In the **left sidebar**, open **Executions** (or the execution list for this workflow).
3. Click a **successful** execution that was triggered by **lead.created** (e.g. after creating a lead in Laravel).
4. In the execution view, click the **Webhook** node.
5. Open the **OUTPUT** tab (or the panel that shows the node’s output data).
6. Look at the **first level** of the JSON:

**Pattern A — Data inside `body`**  
You see an object with a key **`body`**, and inside `body` you see `event`, `event_id`, `tenant_id`, `lead`, `lead_id`, etc.

- **Use in this guide:** expressions like `{{ $json.body.event }}`, `{{ $json.body.lead.email }}`, `{{ $json.body.lead.name }}`.

**Pattern B — Data at root**  
You see `event`, `event_id`, `tenant_id`, `lead`, etc. **directly** at the top level (no `body` wrapper).

- **Use in this guide:** expressions like `{{ $json.event }}`, `{{ $json.lead.email }}`, `{{ $json.lead.name }}`.

**Write down which pattern you have** (e.g. in a sticky note in the workflow or in your team docs). Use that pattern consistently in the Switch, Edit Fields, and Send an Email nodes below.

### Laravel real payload (lead.created)

Laravel sends this JSON in the POST body (from `AutomationWebhookService::buildLeadCreatedPayload()`). All fields are always present so you can use them in n8n.

**Payload Laravel POSTs (what ends up in n8n `body`):**

```json
{
  "event": "lead.created",
  "event_id": "550e8400-e29b-41d4-a716-446655440000",
  "tenant_id": 1,
  "lead_id": 14,
  "lead": {
    "id": 14,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "phone": "09171234567",
    "status": "new",
    "assigned_to": "5",
    "source_campaign": "Direct",
    "created_by": {
      "id": 3,
      "email": "creator@example.com",
      "name": "CRM User"
    }
  },
  "assigned_agent": {
    "id": 5,
    "email": "agent@example.com",
    "name": "Sales Agent"
  },
  "metadata": {},
  "steps": []
}
```

**How it appears in n8n Webhook OUTPUT (Pattern A — data in `body`):**

The Webhook node wraps the request; the Laravel payload above is in the `body` key. So in n8n you get one item like:

```json
{
  "headers": { "host": "localhost:5678", "content-type": "application/json", ... },
  "params": {},
  "query": {},
  "body": {
    "event": "lead.created",
    "event_id": "550e8400-e29b-41d4-a716-446655440000",
    "tenant_id": 1,
    "lead_id": 14,
    "lead": {
      "id": 14,
      "name": "Jane Doe",
      "email": "jane@example.com",
      "phone": "09171234567",
      "status": "new",
      "assigned_to": "5",
      "source_campaign": "Direct",
      "created_by": {
        "id": 3,
        "email": "creator@example.com",
        "name": "CRM User"
      }
    },
    "assigned_agent": {
      "id": 5,
      "email": "agent@example.com",
      "name": "Sales Agent"
    },
    "metadata": {},
    "steps": []
  },
  "webhookUrl": "http://localhost:5678/webhook/saas-events"
}
```

**n8n expressions to use (data in `body`):**

| Use in n8n | Expression |
|------------|------------|
| Event type (Switch) | `{{ $json.body.event }}` |
| Lead email (Send an Email → To) | `{{ $json.body.lead.email }}` |
| Lead name | `{{ $json.body.lead.name }}` |
| Lead phone | `{{ $json.body.lead.phone }}` |
| Lead status | `{{ $json.body.lead.status }}` |
| Tenant ID | `{{ $json.body.tenant_id }}` |
| Lead ID | `{{ $json.body.lead_id }}` or `{{ $json.body.lead.id }}` |
| Source campaign | `{{ $json.body.lead.source_campaign }}` |
| Assigned agent email | `{{ $json.body.assigned_agent.email }}` (null if no agent assigned) |
| Assigned agent name | `{{ $json.body.assigned_agent.name }}` |
| Created by (lead.created only) | `{{ $json.body.lead.created_by.id }}`, `{{ $json.body.lead.created_by.email }}`, `{{ $json.body.lead.created_by.name }}` (user who created the lead in CRM; null if not applicable) |
| Event ID (idempotency) | `{{ $json.body.event_id }}` |

To see the real payload in n8n: create a lead in Laravel (Leads → Create) with the queue worker running, then open Executions → that run → Webhook node → OUTPUT; the `body` object will match the structure above.

---

## 3. Switch Node — Rule for lead.created

Configure the Switch so that when the event is **lead.created**, the flow goes to the lead.created branch.

1. Click the **Switch** node. The right panel opens with **Rules**.
2. Add or edit the **first rule** so it matches **lead.created**:
   - **Value 1** (or the field that receives the incoming value):  
     - If your data is in **body:** `{{ $json.body.event }}`  
     - If your data is at **root:** `{{ $json.event }}`
   - **Operation:** **equals** (or “is equal to”).
   - **Value 2:** `lead.created` (type exactly, no extra spaces).
3. Optionally **name the output** for this rule (e.g. “lead.created”) so the branch is easy to recognize.
4. Connect the **output** of this rule (the green line for “lead.created”) to the next node — either **Edit Fields** or **Send an Email** (see below).

**Result:** Only executions where `event === "lead.created"` will follow this branch.

---

## 4. Edit Fields Node (Optional)

You can add an **Edit Fields** node between the Switch and Send an Email to pass data through or to prepare explicit fields for the email.

**Option A — Pass through (simplest)**  
- Add an **Edit Fields** node.
- Connect: Switch (lead.created output) → Edit Fields.
- Do **not** add or change any fields; leave the node as pass-through so the same `$json` (with `body` or root) is available to the next node.
- Connect Edit Fields → Send an Email.

**Option B — Add fields for the email node**  
- Add an **Edit Fields** node.
- In **Add field** (or “Set”):
  - Add `email_to` = `{{ $json.body.lead.email }}` (or `{{ $json.lead.email }}` if at root).
  - Add `recipient_name` = `{{ $json.body.lead.name }}` (or `{{ $json.lead.name }}` if at root).
- Connect: Switch (lead.created output) → Edit Fields → Send an Email.
- In the **Send an Email** node you can then use `{{ $json.email_to }}` and `{{ $json.recipient_name }}` instead of `lead.email` and `lead.name`.

**Recommendation:** Start with **Option A** and use the expressions from the table in section 9 directly in the email node. Use Option B if you prefer a single place to define “email recipient” and “name” for reuse.

---

## 5. Send an Email Node

This node sends the **welcome email** to the lead when the event is **lead.created**.

1. Add a **Send an Email** (or Gmail / SMTP / SendGrid) node and connect it after the Switch’s lead.created output (or after Edit Fields if you use it).
2. Configure:

| Field   | Value (data in **body**)                    | Value (data at **root**)           |
|--------|----------------------------------------------|------------------------------------|
| **To** | `{{ $json.body.lead.email }}`               | `{{ $json.lead.email }}`           |
| **Subject** | e.g. `Welcome, {{ $json.body.lead.name }}!` or a fixed “Welcome – we got your request” | Same with `$json.lead.name` or fixed text |
| **Body / Message** | e.g. `Hi {{ $json.body.lead.name }}, thanks for reaching out. We'll be in touch soon.` Use `{{ $json.body.lead.email }}`, `{{ $json.body.lead.phone }}` as needed. | Same with `$json.lead.name`, `$json.lead.email`, `$json.lead.phone` |

3. If you used **Edit Fields Option B**, you can set **To** to `{{ $json.email_to }}` and use `{{ $json.recipient_name }}` in the body.
4. **From / credentials:** Configure your email provider (Gmail, SMTP, etc.). For testing, use a sandbox (e.g. Mailtrap) or a test account.

**Note:** Laravel sends the full `lead` object and `event`, `event_id`, etc. It does **not** send pre-built `email_subject` or `email_body` in the router payload, so the subject and body are built in n8n using the expressions above.

**Internal notification to the assigned agent (e.g. "Email - Internal: New Lead Added"):** Use **To** = `{{ $json.body.assigned_agent.email }}`. Laravel includes `assigned_agent` (id, email, name) when the lead has an assigned user; if none is assigned, `assigned_agent` is null and the expression is undefined, which causes "No recipients defined" in n8n. To avoid that, use a fallback: e.g. **To** = `{{ $json.body.assigned_agent?.email ?? 'sales@yourdomain.com' }}` (or your n8n equivalent, e.g. a fixed team inbox) so unassigned leads still notify a default address.

**Send to assigned agent only when creator ≠ assigned agent:** Add an **IF** node before the "Send Email to assigned_agent" node. Condition: `{{ $json.body.lead.created_by.id }}` is not equal to `{{ $json.body.assigned_agent.id }}`. Use **IF (true)** → Send an Email (To: `{{ $json.body.assigned_agent.email }}`) → Respond to Webhook. Use **IF (false)** → Respond to Webhook (no email, so the person who created the lead does not get a self-notification). Laravel sends `lead.created_by` (id, email, name) for leads created in the CRM; it is null for other sources (e.g. funnel opt-in). When either `created_by` or `assigned_agent` is null, the comparison may need a fallback in n8n (e.g. treat as "send" when assigned_agent exists).

---

## 6. Respond to Webhook

The webhook request from Laravel must receive an HTTP response.

1. Connect the **last node** of the lead.created branch (Send an Email or Edit Fields if you skip email) to the **Respond to Webhook** node.
2. No special configuration is required; the default 200 response is sufficient unless your team needs a specific body or status.

**Result:** Flow for lead.created is: **Webhook → Switch (lead.created) → [Edit Fields] → Send an Email → Respond to Webhook**.

---

## 7. Optional: Wait and IF (Only When status = new)

If you want to send the welcome email **only when** the lead’s status is **new**:

1. After the Switch (lead.created output), add a **Wait** node (e.g. 1 minute) and connect Switch → Wait.
2. Add an **IF** node. Connect Wait → IF.
3. Configure the IF node:
   - **Value 1:** `{{ $json.body.lead.status }}` (or `{{ $json.lead.status }}` if at root).
   - **Operation:** equals.
   - **Value 2:** `new`
4. Connect **IF (true)** → Send an Email.
5. Connect **IF (false)** → Respond to Webhook (or to Edit Fields if you use it before Respond to Webhook).
6. Connect Send an Email → Respond to Webhook.

So: **Switch → Wait → IF → (true: Send Email → Respond to Webhook; false: Respond to Webhook)**.

---

## 8. Testing

1. **Laravel:** Ensure the queue worker is running (`php artisan queue:work`) and that `N8N_USE_ROUTER=true` and `N8N_ROUTER_PATH` match your Webhook path (e.g. `saas-events` or `events`).
2. **Laravel:** Create a new lead (CRM → Leads → Create). Fill name, email, phone, source campaign, status (e.g. New), assignee, and save.
3. **n8n:** Open **Executions** for the SaaS Event Router workflow. You should see a new run (e.g. “Succeeded”).
4. Open that execution. Confirm:
   - **Webhook** OUTPUT contains `event: "lead.created"` and a **lead** object with `email`, `name`, `phone`, `status`, etc.
   - The flow went through **Switch** → the lead.created branch → **Send an Email** → **Respond to Webhook**.
5. **Inbox (or Mailtrap):** Confirm that the lead’s email address received the welcome email.

If the execution fails, check the node that errors (e.g. wrong expression for body vs root, or missing credentials on the email node).

---

## 9. Reference: Expressions for lead.created

Use this table to copy expressions into the Switch, Edit Fields, or Send an Email nodes. Choose the column that matches your data location (section 2).

| Field        | Expression (data in **body**)   | Expression (data at **root**) |
|-------------|----------------------------------|---------------------------------|
| event       | `{{ $json.body.event }}`        | `{{ $json.event }}`             |
| event_id    | `{{ $json.body.event_id }}`     | `{{ $json.event_id }}`         |
| tenant_id   | `{{ $json.body.tenant_id }}`    | `{{ $json.tenant_id }}`        |
| lead_id     | `{{ $json.body.lead_id }}`      | `{{ $json.lead_id }}`          |
| lead.email  | `{{ $json.body.lead.email }}`   | `{{ $json.lead.email }}`       |
| lead.name   | `{{ $json.body.lead.name }}`    | `{{ $json.lead.name }}`        |
| lead.phone  | `{{ $json.body.lead.phone }}`   | `{{ $json.lead.phone }}`       |
| lead.status | `{{ $json.body.lead.status }}`   | `{{ $json.lead.status }}`      |

---

## Related docs

- [automation-n8n-implementation.md](automation-n8n-implementation.md) — Payload format, config, and all automation events.
- [workflow-lead-created-automation-guide.md](workflow-lead-created-automation-guide.md) — Standalone “Lead Created” workflow (separate webhook path).
