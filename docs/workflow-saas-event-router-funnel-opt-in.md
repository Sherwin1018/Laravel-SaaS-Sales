# SaaS Event Router — `funnel.opt_in` Branch (Step-by-Step Guide)

This guide shows how to build the **`funnel.opt_in`** branch in your n8n **SaaS Event Router** workflow. It assumes:

- Laravel is already sending automation events to a **single router webhook** (e.g. `/webhook/saas-events`).
- The `lead.created` branch in this router is already working.
- Your Webhook node exposes the Laravel payload under `body` (Pattern A), as in your actual OUTPUT.

---

## 1. Payload reference for `funnel.opt_in`

Laravel uses `AutomationWebhookService::buildFunnelOptInPayload()` + `buildPayload()` to build this payload. In Laravel, the JSON **request body** for `funnel.opt_in` looks like:

```json
{
  "event": "funnel.opt_in",
  "event_id": "UUID-HERE",
  "tenant_id": 1,
  "lead": {
    "id": 14,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "phone": "09171234567",
    "status": "new",
    "assigned_to": "5",
    "source_campaign": "Direct"
  },
  "metadata": {
    "funnel_id": 3,
    "funnel_name": "Main Opt-in Funnel"
  },
  "steps": []
}
```

In your SaaS Event Router workflow, the Webhook node wraps this under a `body` key, so the Webhook OUTPUT item in n8n is:

```json
{
  "headers": { "host": "localhost:5678", "content-type": "application/json", "...": "..." },
  "params": {},
  "query": {},
  "body": {
    "event": "funnel.opt_in",
    "event_id": "UUID-HERE",
    "tenant_id": 1,
    "lead": {
      "id": 14,
      "name": "Jane Doe",
      "email": "jane@example.com",
      "phone": "09171234567",
      "status": "new",
      "assigned_to": "5",
      "source_campaign": "Direct"
    },
    "metadata": {
      "funnel_id": 3,
      "funnel_name": "Main Opt-in Funnel"
    },
    "steps": []
  },
  "webhookUrl": "http://localhost:5678/webhook/saas-events"
}
```

Because the data is inside `body`, you will use expressions like:

- `{{ $json.body.event }}`
- `{{ $json.body.lead.email }}`
- `{{ $json.body.lead.name }}`
- `{{ $json.body.metadata.funnel_name }}`

---

## 2. Trigger a real `funnel.opt_in` execution (once)

Before wiring the branch, generate at least one real execution:

1. **Laravel:**
   - Ensure the queue worker is running (`php artisan queue:work`).
   - Open a **published funnel** with an opt-in step.
   - Submit the **opt-in form** (valid email, phone, etc.).
2. **n8n:**
   - Open the **SaaS Event Router** workflow.
   - Go to the **Executions** tab.
   - Open the new execution triggered by your opt-in.
   - Click the **Webhook** node → **OUTPUT** and confirm:
     - `body.event` is `"funnel.opt_in"`.
     - `body.lead.email` has the email you entered.
     - `body.metadata.funnel_id` / `body.metadata.funnel_name` are present once everything is wired.

This confirms you can safely use `$json.body.*` expressions.

---

## 3. Add the `funnel.opt_in` rule to the Switch

Your router Switch node already has a rule for `lead.created`. Add another rule for `funnel.opt_in`:

1. Click the **Switch** node.
2. In **Rules**, add a new rule:
   - **Value 1** (or “Value”):

     ```n8n
     {{ $json.body.event }}
     ```

   - **Operation:** `equals` (or “is equal to”).
   - **Value 2:**

     ```text
     funnel.opt_in
     ```

3. Optionally set the **output name** for this rule to `funnel.opt_in` for clarity.
4. On the canvas, a new output appears for this rule. This output is the start of your `funnel.opt_in` branch.

---

## 4. (Optional) Edit Fields node for funnel.opt_in

You can either pass data straight through or create nicer field names for the email node.

### Option A — Pass-through (simplest)

- Add an **Edit Fields** (or **Set**) node.
- Connect **Switch (funnel.opt_in output)** → **Edit Fields**.
- Do **not** change any fields; leave it as pass-through so `$json.body.*` stays intact.
- Later, connect **Edit Fields** → **Send Email**.

### Option B — Define explicit fields

- Add an **Edit Fields** node.
- In **Add field**, create:
  - `email_to` = `{{ $json.body.lead.email }}`
  - `recipient_name` = `{{ $json.body.lead.name }}`
  - `funnel_name` = `{{ $json.body.metadata.funnel_name }}`
  - (Optional) `funnel_id` = `{{ $json.body.metadata.funnel_id }}`
- Connect **Switch (funnel.opt_in)** → **Edit Fields** → **Send Email**.
- In the email node you can now use:
  - `{{ $json.email_to }}`, `{{ $json.recipient_name }}`, `{{ $json.funnel_name }}`.

If you are just starting, use **Option A** and reference `$json.body.*` directly.

---

## 5. Send an Email node for funnel.opt_in

This node sends a **“Thanks for opting in”** email to the lead.

1. Add a **Send Email** (or Gmail / SMTP / SendGrid) node.
2. Connect:
   - **Switch (funnel.opt_in output)** → **[optional Edit Fields]** → **Send Email**.
3. Configure the email node.

### If you did NOT add extra fields in Edit Fields

- **To:**

  ```n8n
  {{ $json.body.lead.email }}
  ```

- **Subject:** for example:

  ```n8n
  "Thanks for joining our funnel"
  ```

  or, using the funnel name when available:

  ```n8n
  {{ $json.body.metadata.funnel_name ? "Thanks for joining " + $json.body.metadata.funnel_name : "Thanks for joining our funnel" }}
  ```

- **Body / Message:** for example:

  ```n8n
  Hi {{ $json.body.lead.name || "there" }},

  Thanks for opting in through our funnel{{ $json.body.metadata.funnel_name ? " \"" + $json.body.metadata.funnel_name + "\"" : "" }}.

  We'll follow up with more details soon.

  — Your Company
  ```

### If you DID add fields in Edit Fields (Option B)

- **To:** `{{ $json.email_to }}`
- **Subject:** e.g.

  ```n8n
  {{ $json.funnel_name ? "Thanks for joining " + $json.funnel_name : "Thanks for joining our funnel" }}
  ```

- **Body:** e.g.

  ```n8n
  Hi {{ $json.recipient_name || "there" }},

  Thanks for opting in through our funnel{{ $json.funnel_name ? " \"" + $json.funnel_name + "\"" : "" }}.

  We'll follow up with more details soon.

  — Your Company
  ```

Make sure your email provider (SMTP / Gmail / etc.) is configured and working (you can reuse the setup from the `lead.created` branch).

---

## 6. Connect to Respond to Webhook

Just like the `lead.created` branch, your Laravel request must receive an HTTP response.

1. Connect the **Send Email** node’s output to **Respond to Webhook**.
2. The default 200 OK response is fine; no extra configuration is required unless your team prefers a specific body.

Final `funnel.opt_in` path:

```text
Webhook → Switch (event)
  → (rule: event == "funnel.opt_in")
    → [Edit Fields (optional)]
      → Send Email
        → Respond to Webhook
```

---

## 7. Optional: Add Wait and IF logic for funnel.opt_in

Use this when you want to **wait** a bit after the opt-in, then **only send the email** when a condition is met (e.g. lead status is `new`, or a specific campaign). Otherwise the flow still responds to the webhook without sending the email.

### 7.1 Disconnect the current branch

1. On the canvas, find the connection from the **Switch** (funnel.opt_in output) to the next node (Edit Fields or Send an Email).
2. **Click** that connection line and delete it (or drag it off).
3. Leave the **Edit Fields** and **Send an Email** nodes in place; you will reconnect them after Wait and IF.

### 7.2 Add the Wait node

1. **Double‑click** on empty canvas (or click the **+** between nodes) and add a **Wait** node.
2. **Connect:** **Switch** (funnel.opt_in output) → **Wait**.
3. Configure the Wait node:
   - **Wait Type:** “Time interval” (or “For a specific time”, depending on your n8n version).
   - **Duration:** e.g. **1 minute** for testing, or **10 minutes** for production (so the welcome email goes out after a short delay).
4. **Save** the workflow.

### 7.3 Add the IF node

1. Add an **IF** node (search for “IF” in the node list).
2. **Connect:** **Wait** → **IF**.
3. Configure the IF node so that you only send the email when a condition is true. Two common options:

**Option A — Only when lead status is `new`**

- **Value 1:** `{{ $json.body.lead.status }}`
- **Operation:** equals
- **Value 2:** `new`

So: if the lead’s status is `new`, the flow goes to the **true** output (Send Email); otherwise to **false** (skip email).

**Option B — Only for a specific campaign (e.g. “Free Guide”)**

- **Value 1:** `{{ $json.body.lead.source_campaign }}`
- **Operation:** equals
- **Value 2:** `Free Guide` (or whatever campaign name you use)

**Note:** If you test with the curl payload from section 10, it does not include `lead.status` or `lead.source_campaign`. For that test, either add those fields to the JSON (e.g. `"status": "new"` inside `lead`) or temporarily use a condition that is always true (e.g. leave IF as-is and send a payload that includes `"status": "new"` in `lead`).

### 7.4 Connect the IF outputs

1. **IF (true)** → connect to your **Edit Fields** node (if you have one) or directly to **Send an Email**.
   - If you have Edit Fields: **IF (true)** → **Edit Fields** → **Send an Email**.
   - If you don’t: **IF (true)** → **Send an Email**.
2. **IF (false)** → connect directly to **Respond to Webhook** (so when the condition fails, you still respond to the webhook and don’t leave the request hanging).
3. **Send an Email** → connect to **Respond to Webhook** (same as before).

### 7.5 Resulting flow

```text
Webhook → Switch (event == "funnel.opt_in")
  → Wait (e.g. 1 min or 10 min)
    → IF (e.g. lead.status == "new")
      → true:  [Edit Fields] → Send an Email → Respond to Webhook
      → false: Respond to Webhook
```

After saving, run a test (curl or Laravel form). In **Executions**, confirm the path goes **Webhook → Switch → Wait → IF**, then either through the true branch (Send Email → Respond) or the false branch (Respond only).

---

## 8. Testing the funnel.opt_in branch

1. Ensure the SaaS Event Router workflow is **active/published**.
2. Verify Laravel is configured to use the router:
   - `N8N_WEBHOOK_BASE_URL=http://localhost:5678`
   - `N8N_USE_ROUTER=true`
   - `N8N_ROUTER_PATH=saas-events` (or your chosen path).
3. In Laravel, open your funnel and submit the **opt-in form** again.
4. In n8n:
   - Go to **Executions** for the SaaS Event Router workflow.
   - Open the new execution.
   - Confirm:
     - Webhook OUTPUT → `body.event` is `"funnel.opt_in"`.
     - The execution path goes: **Webhook → Switch → funnel.opt_in branch → Send Email → Respond to Webhook**.
5. Check your test inbox (or Mailtrap) for the **funnel opt-in email**.

If you see errors, look at the failing node (often an expression typo or missing email credentials).

---

## 9. Reference: Expressions for `funnel.opt_in` (data in `body`)

Use this table when building Switch conditions or email content.

| Field               | Expression                          |
|---------------------|-------------------------------------|
| Event               | `{{ $json.body.event }}`           |
| Event ID            | `{{ $json.body.event_id }}`        |
| Tenant ID           | `{{ $json.body.tenant_id }}`       |
| Lead ID             | `{{ $json.body.lead.id }}`         |
| Lead email          | `{{ $json.body.lead.email }}`      |
| Lead name           | `{{ $json.body.lead.name }}`       |
| Lead phone          | `{{ $json.body.lead.phone }}`      |
| Lead status         | `{{ $json.body.lead.status }}`     |
| Lead source         | `{{ $json.body.lead.source_campaign }}` |
| Funnel ID (metadata)| `{{ $json.body.metadata.funnel_id }}`   |
| Funnel name         | `{{ $json.body.metadata.funnel_name }}` |

---

## 10. Manual test with curl

You can trigger the `funnel.opt_in` branch without using the Laravel form by POSTing directly to the router webhook.

**PowerShell (Windows):**

```powershell
curl -X POST http://localhost:5678/webhook/saas-events -H "Content-Type: application/json" -d "{\"event\":\"funnel.opt_in\",\"event_id\":\"optin-1\",\"tenant_id\":1,\"lead\":{\"email\":\"test@example.com\",\"name\":\"Angel\"},\"metadata\":{\"funnel_id\":10,\"funnel_name\":\"Free Guide Opt-in\"},\"steps\":[]}"
```

**Bash / Linux / macOS:**

```bash
curl -X POST http://localhost:5678/webhook/saas-events \
  -H "Content-Type: application/json" \
  -d '{"event":"funnel.opt_in","event_id":"optin-1","tenant_id":1,"lead":{"email":"test@example.com","name":"Angel"},"metadata":{"funnel_id":10,"funnel_name":"Free Guide Opt-in"},"steps":[]}'
```

After running, check n8n **Executions** for a new run; Webhook OUTPUT should show `body.event: "funnel.opt_in"` and the flow should go through the funnel.opt_in branch.

---

## Related docs

- `workflow-saas-event-router-lead-created.md` — SaaS Event Router: `lead.created` branch.
- `automation-n8n-implementation.md` — Laravel payload format, router config, and all automation events.

