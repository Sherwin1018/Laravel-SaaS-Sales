# n8n Send Email & Notify Branch — Step-by-Step Beginner Guide

This guide shows how to handle **single-email** and **notify_sales** actions coming from Laravel in your existing **SaaS Event Router** workflow in n8n.

You already built the **start_sequence** branch in  
`docs/workflow-n8n-start-sequence-branch-guide.md`.  
This guide focuses on the **other action types**:

- `send_email` — “Send Email” workflows.
- `notify_sales` — “Notify Sales Agent” workflows (simple notification).

We will re-use the same **Split Actions** node and add a **parallel branch** for these actions.

---

## PART 1 — What We Already Have

Your router workflow already has:

1. **Webhook** node  
   - Receives the event from Laravel.  
   - Important fields are inside `body` (e.g. `body.event`, `body.tenant_id`, `body.lead.email`, `body.assigned_agent.email`, `body.from_email`).

2. **Event Router (Switch)** node  
   - Routes by `{{ $json.body.event }}` (e.g. `lead.created`, `funnel.opt_in`, `lead.status_changed`, `payment.paid`, `payment.failed`).

3. **Get Tenant Actions (HTTP Request)** node  
   - **Method:** POST  
   - **URL:** your Laravel app + `/api/automation/tenant/run`  
   - **Body (JSON):**
     ```json
     {
       "event": "{{ $json.body.event }}",
       "tenant_id": {{ $json.body.tenant_id }},
       "payload": {{ JSON.stringify($json.body) }}
     }
     ```
   - **Output:** Laravel returns something like:
     ```json
     {
       "actions": [
         { "type": "send_email", "to": "lead.email", "subject": "...", "body": "..." },
         { "type": "start_sequence", "sequence_id": 3, "steps": [ ... ] }
       ],
       "from_email": "accountowner@tenant.com"
     }
     ```

4. **Split Actions** + **Is Start Sequence?** + **start_sequence branch**  
   - You already use **Split Actions** to get one item per action and an **IF** node (“Is Start Sequence?”) to route `type === "start_sequence"` into the sequence branch.

We will now add a **separate branch** that handles `send_email` and `notify_sales` using the same Split Actions output.

---

## PART 2 — What We Are Adding

We will add:

1. A **Switch** that routes by `type` for non-sequence actions:
   - `send_email` → **Workflow – Send Email**.
   - `notify_sales` → **Workflow – Notify Sales Agent**.
2. A **Send Email** node that:
   - Uses the **To** email from the Webhook (lead or assigned agent).
   - Uses `subject` and `body` directly from the `send_email` action.
   - Uses `from_email` from Webhook / Get Tenant Actions.
3. (Optional) A **Notify Sales** node — for now this can simply send an email to the assigned agent or a fixed internal address.

We will **not** change the Webhook, Event Router, or Get Tenant Actions request.

---

## PART 3 — Exact Nodes to Create

Starting from **Split Actions**, add these nodes:

| # | Node type | Suggested name                 | Purpose |
|---|-----------|--------------------------------|---------|
| 1 | Switch    | Route Non-Sequence Actions     | Route `send_email` and `notify_sales` by `type` |
| 2 | Send Email| Workflow – Send Email          | Send one email for `send_email` actions |
| 3 | Send Email (or other) | Workflow – Notify Sales Agent | Notify assigned agent / internal inbox for `notify_sales` actions |

> You will **reuse** the existing **Split Actions** node. Do **not** add another Split Actions.

Connection overview:

- `Get Tenant Actions` → `Split Actions` (already).
- `Split Actions` → `Is Start Sequence?` (already).
- **New:** `Split Actions` → `Route Non-Sequence Actions` →  
  - Output “send_email” → `Workflow – Send Email`.  
  - Output “notify_sales” → `Workflow – Notify Sales Agent`.

The same `Split Actions` output now feeds **two** branches:

- **Sequence branch** (for `start_sequence`).
- **Single-email / notify branch** (for `send_email` and `notify_sales`).

---

## PART 4 — Configure “Route Non-Sequence Actions” (Switch)

### Step 1: Create the Switch node

1. On the canvas, click the **+** icon on the connection coming out of **Split Actions**.
2. Add a **Switch** node.
3. Name it **Route Non-Sequence Actions**.

### Step 2: Configure rules

1. Set **Mode** = “Rules”.
2. Add rules:

   - **Rule 1 – Send Email**
     - **Value 1:** `{{ $json.type }}`
     - **Operation:** equals
     - **Value 2:** `send_email`
     - **Output name:** `email` (optional, for clarity)

   - **Rule 2 – Notify Sales**
     - **Value 1:** `{{ $json.type }}`
     - **Operation:** equals
     - **Value 2:** `notify_sales`
     - **Output name:** `notify`

3. Leave the default output as‑is (you can ignore it or connect it to a debug node if you want to inspect unexpected types).

**What you should see after a test run:**

- If Laravel returns one action with `type: "send_email"`, the **email** output of this Switch has **1 item**.
- If it returns both `send_email` and `start_sequence`, the **send_email** goes to this branch; `start_sequence` is still handled by your existing **Is Start Sequence?** logic.

---

## PART 5 — Configure “Workflow – Send Email” (Send Email node)

This node sends **one email** for each `send_email` action, independent of sequences.

### Step 1: Create the node

1. Click the **email** output of **Route Non-Sequence Actions**.
2. Add a **Send Email** node.
3. Name it **Workflow – Send Email**.

### Step 2: Understand the data shape

For a `send_email` action, Laravel returns something like:

```json
{
  "workflow_id": 10,
  "type": "send_email",
  "to": "lead.email",
  "subject": "Welcome user",
  "body": "Thanks for joining..."
}
```

In this node, `$json` is **that action object**. It does **not** contain the lead; the lead is still on the Webhook node.

- `To` logic:
  - `$json.to` tells you whether to send to `lead.email` or `assigned_agent.email`.
  - The actual email address comes from the Webhook output.
- `Subject` and `body` are taken directly from `$json.subject` and `$json.body`.

### Step 3: Configure the Send Email node

Assuming your Webhook node is named **Webhook**:

- **Operation:** `Send`
- **From Email:**

  ```text
  {{ $('Webhook').first().json.body.from_email || $('Webhook').first().json.body.assigned_agent.email || 'noreply@yourdomain.com' }}
  ```

- **To Email:**

  Use an expression that looks at `$json.to` and picks the right address:

  ```text
  {{ $json.to === 'assigned_agent.email'
      && $('Webhook').first().json.body.assigned_agent
      && $('Webhook').first().json.body.assigned_agent.email
      ? $('Webhook').first().json.body.assigned_agent.email
      : $('Webhook').first().json.body.lead.email }}
  ```

  If your n8n version prefers `$node["Webhook"]` syntax:

  ```text
  {{ $json.to === 'assigned_agent.email'
      && $node["Webhook"].json.body.assigned_agent
      && $node["Webhook"].json.body.assigned_agent.email
      ? $node["Webhook"].json.body.assigned_agent.email
      : $node["Webhook"].json.body.lead.email }}
  ```

- **Subject:**

  ```text
  {{ $json.subject }}
  ```

- **Email Format:** `HTML` (recommended)

- **HTML (or Message) field:**

  ```text
  {{ $json.body }}
  ```

If you want to support merge tags like `{{lead.name}}` in the workflow’s email body:

```text
{{ ($json.body || '').replace(/\{\{lead\.name\}\}/g, $('Webhook').first().json.body.lead.name || '') }}
```

### Step 4: Test

1. In Laravel, create a **Workflow**:
   - Trigger: Funnel opt‑in (or any event).
   - Action: **Send Email**.
   - Recipient: Lead email / Assigned agent email.
   - Subject & body: set a simple test message.
2. Trigger the event (e.g. submit an opt‑in).
3. In n8n:
   - Check **Get Tenant Actions** OUTPUT — it should contain an action with `type: "send_email"`.
   - Check **Split Actions** OUTPUT — one item per action.
   - Check **Route Non-Sequence Actions** OUTPUT → **email** tab — it should have 1 item.
   - The **Workflow – Send Email** node should send exactly one email to the expected address.

---

## PART 6 — Configure “Workflow – Notify Sales Agent”

This is intentionally simple. You can start with an **email notification** and later switch to Slack or another internal channel.

### Step 1: Decide who to notify

Basic options:

1. **Assigned agent email** (recommended):
   - If `assigned_agent.email` exists in the Webhook payload.
2. **Fixed internal inbox**:
   - e.g. `sales@yourcompany.com`, if there is no assigned agent.

For now we’ll implement:

```text
assigned_agent.email || 'sales@yourcompany.com'
```

### Step 2: Create the node

1. Click the **notify** output of **Route Non-Sequence Actions**.
2. Add a **Send Email** node.
3. Name it **Workflow – Notify Sales Agent**.

### Step 3: Configure fields

Assuming the Webhook node is named **Webhook**:

- **From Email:**

  ```text
  {{ $('Webhook').first().json.body.from_email || 'noreply@yourdomain.com' }}
  ```

- **To Email:**

  ```text
  {{ $('Webhook').first().json.body.assigned_agent && $('Webhook').first().json.body.assigned_agent.email
      ? $('Webhook').first().json.body.assigned_agent.email
      : 'sales@yourcompany.com' }}
  ```

- **Subject:**

  ```text
  {{ 'Lead activity: ' + ($('Webhook').first().json.body.lead.name || $('Webhook').first().json.body.lead.email || 'Unknown lead') }}
  ```

- **HTML (or Message) body:**

  Basic example:

  ```text
  {{ 'A workflow with action type \"notify_sales\" was triggered for lead '
      + ($('Webhook').first().json.body.lead.name || $('Webhook').first().json.body.lead.email || 'Unknown lead')
      + '. Event: ' + ($('Webhook').first().json.body.event || 'N/A') }}
  ```

You can later enrich this with more details (pipeline status, funnel name, etc.) by reading additional fields from the Webhook payload.

### Step 4: Test

1. In Laravel, create a **Workflow**:
   - Trigger: e.g. Lead status changed.
   - Action: **Notify Sales Agent**.
2. Change a lead’s status so the workflow fires.
3. In n8n:
   - Confirm **Get Tenant Actions** returns an action with `type: "notify_sales"`.
   - Confirm **Route Non-Sequence Actions** OUTPUT → **notify** tab has 1 item.
   - Confirm **Workflow – Notify Sales Agent** sends one email to the assigned agent (or fallback).

---

## Troubleshooting: Split Actions shows “No output data” / empty `actions[]`

If **Get Tenant Actions** returns `{ "actions": [], "from_email": "..." }`, the **Split Actions** node has nothing to split, so it outputs no items and the workflow stops there.

**Common causes:**

1. **Laravel does not return an action for this workflow type**  
   The internal API must build an action for **Notify Sales Agent** workflows. Laravel’s `TenantAutomationRunController::buildActions()` includes `notify_sales`: it adds `{ "type": "notify_sales", "workflow_id": ... }` to the `actions` array. If you still see empty `actions`, ensure your Laravel code is up to date and that the workflow’s `action_type` is saved as `notify_sales`.

2. **Trigger and event do not match**  
   The workflow runs only when the **event** sent to the API matches the workflow’s **Trigger**.  
   - If the workflow is **“Lead status changed”**, the event must be `lead.status_changed` (e.g. you change a lead’s pipeline status in the app).  
   - If the workflow is **“Funnel opt-in”**, the event must be `funnel.opt_in` (e.g. someone submits the funnel form).  
   - If you trigger an opt-in but your only active workflow is “Lead status changed”, Laravel returns no actions for that event.

3. **Workflow is paused or wrong tenant**  
   Check in **Laravel → Automation → Workflows** that the workflow is **Active** and belongs to the same tenant as the lead/event.

After fixing, run the test again. **Get Tenant Actions** OUTPUT should show `actions: [ { type: "notify_sales", workflow_id: ... } ]`. Then Split Actions will output 1 item and **Route Non-Sequence Actions** will route it to the notify branch.

---

## PART 7 — How this fits with Start Sequence

After you add this branch, your SaaS Event Router has a clear structure:

- **Split Actions**:
  - Feeds **Is Start Sequence?** → **start_sequence branch** (email / delay / sms steps).
  - Feeds **Route Non-Sequence Actions** → **Workflow – Send Email / Notify Sales Agent**.

This matches how tenants configure **Workflows** in Laravel:

- **Send Email** → `type: "send_email"` → Workflow – Send Email node in n8n.
- **Start Sequence** → `type: "start_sequence"` → start_sequence branch (see the other guide).
- **Notify Sales Agent** → `type: "notify_sales"` → Workflow – Notify Sales Agent node.

With this in place, you have a complete and understandable automation flow:

- **Laravel** defines *when* and *what* using Workflows and Sequences.
- **n8n** implements *how* (SMTP emails, waits, SMS, notifications) using a small set of reusable branches.

