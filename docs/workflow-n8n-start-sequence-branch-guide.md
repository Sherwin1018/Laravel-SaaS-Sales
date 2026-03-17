# n8n Start Sequence Branch — Step-by-Step Beginner Guide

This guide shows how to add the **start_sequence** branch to your **existing** n8n SaaS Event Router workflow. You will build on what you already have (Webhook → Event Router → Get Tenant Actions) and add nodes that run sequence steps (email and delay) in order.

---

## PART 1 — What We Already Have

Your workflow already has:

1. **Webhook** node  
   - Receives the event from Laravel.  
   - The data you need is inside **body** (e.g. `body.event`, `body.lead.email`, `body.tenant_id`).

2. **Event Router (Switch)** node  
   - Routes by `{{ $json.body.event }}`.  
   - You have outputs for `lead.created`, `funnel.opt_in`, `lead.status_changed`, etc.

3. **Get Tenant Actions (HTTP Request)** node  
   - **Method:** POST  
   - **URL:** Your Laravel app + `/api/automation/tenant/run`  
   - **Body (JSON):** something like:
     ```json
     {
       "event": "{{ $json.body.event }}",
       "tenant_id": {{ $json.body.tenant_id }},
       "payload": {{ JSON.stringify($json.body) }}
     }
     ```
   - **Output:** Laravel returns `{ "actions": [ ... ] }`.

4. **Existing branch for direct emails**  
   - You already handle actions where `type` is **send_email** (e.g. IF “lead has email” then Send Email using subject/body from the action).

So at this point you have:

- **Webhook** → **Event Router** → (e.g. funnel.opt_in) → **Get Tenant Actions** → … (send_email handling).

We will add a **parallel** path that handles actions where **type** is **start_sequence**, and run their **steps** one by one (email and delay).

---

## PART 2 — What We Are Adding

We will add:

1. A way to **see each action** from `actions[]` one by one.
2. A **filter** so we only process actions where `type === "start_sequence"`.
3. For each **start_sequence** action, a way to run its **steps[]** in order.
4. For each step:
   - **email** → Send one email (to lead or assigned agent, using subject/body from the step).
   - **delay** → Wait for the step’s duration and unit (e.g. 2 days), then continue to the next step.
5. Use the **original webhook payload** for the lead/agent email (from `body.lead.email` or `body.assigned_agent.email`).

We are **not** changing the Webhook, the Event Router, or the Get Tenant Actions request. We are only adding nodes **after** “Get Tenant Actions” to handle **start_sequence** actions and their steps.

---

## PART 3 — Exact Nodes to Create

Add these nodes in order (you can do it in one branch that comes from “Get Tenant Actions”):

| # | Node type        | Suggested name              | Purpose |
|---|------------------|-----------------------------|--------|
| 1 | Split Out        | Split Actions               | One item per action from `actions[]` |
| 2 | IF               | Is Start Sequence?          | Keep only items where `type === "start_sequence"` |
| 3 | Split Out        | Split Sequence Steps        | One item per step from `steps[]` (keeps order) |
| 4 | Switch           | Step Type                   | Route by step type: email vs delay |
| 5 | Set (or Edit Fields) | Resolve Email To (optional) | Build the “To” email address for email steps |
| 6 | Send Email       | Sequence – Send Email       | Send one email for an “email” step |
| 7 | Wait             | Sequence – Wait             | Wait for “duration” + “unit” for a “delay” step |
| 8 | Respond to Webhook (if needed) | — | Only if you must respond to the Webhook; otherwise you can merge back into your existing “Respond to Webhook” or end the branch |

You will connect them like this (see Part 5 for the exact flow).

---

## PART 4 — How to Configure Each Node

### Step 1: Split Actions (Split Out node)

- **Where it is:** Right after **Get Tenant Actions (Laravel)**.  
  Connect: **Get Tenant Actions** → **Split Actions**.

- **What it does:**  
  Get Tenant Actions returns one JSON object like `{ "actions": [ action1, action2, ... ] }`. **Split Actions** takes that **`actions` array** and outputs **one item per action**. So if Laravel returns 2 actions, you get 2 items; each item is one action (e.g. `type`, `steps`, etc.). The next node (Is Start Sequence?) then sees each action as a separate item and can route it.  
  **If Laravel returns `"actions": []` (empty array), Split Actions has nothing to split, so it outputs no items and the run effectively stops there.** So “stops at Split Actions” almost always means: Laravel returned no actions for this event.

- **How to add the node:**
  1. In the n8n canvas, click the **+** on the connection coming out of **Get Tenant Actions (Laravel)**.
  2. Search for **Split Out** or **Item Lists**.
  3. Add the node and name it **Split Actions** (double‑click the title).

- **Configuration (click the node to open settings):**

  - Find the setting **“Fields To Split Out”** or **“Field to Split Out”** (name may vary by n8n version).
  - **You must point to the array, not the whole object.**

  **Correct — use one of these (depending on your input shape):**
  - If **Get Tenant Actions** returns `{ "actions": [ ... ] }` at the top level (one item with `actions` array), use this **expression**:
    ```text
    {{ $json.actions }}
    ```
  - If your n8n only accepts a field name (no expression), enter: **`actions`**.
  - If the response is wrapped (e.g. you see `body.actions` in the OUTPUT), use:
    ```text
    {{ $json.body.actions }}
    ```

  **Wrong — do not use:**
  - **`{{ $json }}`** — This points to the whole input item. The node would try to split the object itself, not the `actions` array, so you will not get one item per action and may see “No output data returned” or only one odd item.

  **Include (important for From Email):**  
  If there is an **“Include”** dropdown (e.g. “No Other Fields” / “All Other Fields”), “No Other Fields” drops root-level fields like `from_email`; use **All Fields** (or Include Other Fields) so each output item will be one action and still include `from_email` (with `workflow_id`, `type`, `steps`, etc.). You don’t need to keep the rest of the input object for this branch.

  **If you see “No output data returned”:**  
  That is normal when the input is `{ "actions": [] }` (empty array). There are no actions to split, so the node outputs nothing. Once Laravel returns at least one action in `actions`, the node will output one item per action.

- **What you should see after running:**
  - **Input:** 1 item with `actions: [ action1, action2, ... ]`.
  - **Output:** Several items (one per action). Each item has the action as its content, e.g. `workflow_id`, `type`, `sequence_id`, `sequence_name`, `steps` (array).

- **Quick check:**
  - Run the workflow once (test execution).
  - Open **Split Actions** → **OUTPUT**. You should see multiple items.
  - Click one item and confirm it has `type` (e.g. `send_email` or `start_sequence`), and if `start_sequence`, a `steps` array.

- **Output:**  
  Each item is one action object, e.g.:
  - `workflow_id`, `type`, `sequence_id`, `sequence_name`, `steps` (array).

- **Important:**  
  After this node, **each item** is one action. So in the next nodes you use `$json.type`, `$json.steps`, etc.

---

### Step 2: Is Start Sequence? (IF node)

- **Where it is:** After **Split Actions**.  
  Connect: **Split Actions** → **Is Start Sequence?**.

- **What it does:** Keeps only items that are **start_sequence** actions. Other actions (e.g. send_email) are ignored on this branch.

- **Configuration:**
  - **Condition:**
    - **Value 1:** `{{ $json.type }}`
    - **Operation:** equals (or “is equal to”)
    - **Value 2:** `start_sequence`

  **If the condition still goes to False** even though the input shows `type: "start_sequence"`: the item may have **`actions`** at root as a single **object** (not array), with `type` inside it. Then **Value 1** must be an expression: `{{ $json.actions && $json.actions.type ? $json.actions.type : $json.type }}`. If your item has **`actions` as an array**, use `$json.actions[0].type` instead of `$json.actions.type`.

- **Output:**
  - **True:** items where `type === "start_sequence"` → connect this to the next node (Split Sequence Steps).
  - **False:** items where `type` is something else (e.g. **`send_email`**). Connect this to your **existing send_email handling** (e.g. a node that sends one email using `$json.to`, `$json.subject`, `$json.body` and the Webhook’s lead email) or to “Respond to Webhook” / end.

- **Why it goes to False when you have only a send_email workflow:**  
  If Laravel returns an action like `{ "type": "send_email", "to": "lead.email", "subject": "welcome user", "body": "..." }`, the IF condition `$json.type === "start_sequence"` is **false**, so the item correctly goes to the **False** branch. That is expected. The **True** branch only runs when Laravel returns an action with **`type: "start_sequence"`**. To get that, in Laravel create (or edit) a workflow with **Trigger** = e.g. “Funnel opt-in” and **Action** = **“Start Sequence”** (not “Send Email”), and choose a sequence. Then the API will return an action with `type: "start_sequence"` and that item will go to the True branch.

- **“I have a workflow and a sequence but Is Start Sequence? still gives False”**  
  The workflow must use **Action = “Start Sequence”**, not “Send Email”. In **Laravel → Automation → Workflows**, open your **Funnel opt-in** workflow. In the list, the **Action** column should show **“Start Sequence (YourSequenceName)”**, not “Send Email (Lead email)”. If it shows Send Email, edit that workflow, change **Action type** to **“Start Sequence”**, select your sequence, save, and run the opt-in again. One workflow = one action: that workflow will then produce a `start_sequence` action and the IF will go to True.

---

### Step 3: Split Sequence Steps (Split Out node)

- **Where it is:** After **Is Start Sequence?** (True output).  
  Connect: **Is Start Sequence?** (True) → **Split Sequence Steps**.

- **What it does:** Turns each action’s **steps** array into **one item per step**. Order is preserved (step 1, then 2, then 3…).

- **Configuration:**
  - **Field to Split Out:** Use `steps` if each item has `steps` at root. If the item has **`actions`** as a single object (with `actions.steps` inside it), you must point to that array:
    - **Option A:** In **expression mode (fx)**, set the field to: `{{ $json.actions.steps }}` so the node splits that array and outputs one item per step.
    - **Option B:** If your n8n node has a plain "Field" path, try **`actions.steps`** (dot notation). If it only accepts a single name, the node may not support nested paths — use Option A or add an **Edit Fields** node before this one to copy `actions.steps` to a root-level `steps` field, then split on `steps`.

- **If it stops here ("No output" or execution doesn’t continue):** The node is not finding the array. Your item has steps at **`actions.steps`**, not at root `steps`. Use **expression mode** and **`{{ $json.actions.steps }}`** for the field to split. After splitting, each output item is one step (with `step_order`, `type`, `subject`, `body`, etc. at root).

- **Output:**  
  Each item is **one step** object, e.g.:
  - `step_order`, `type`, and for email: `recipient`, `subject`, `body`; for delay: `duration`, `unit`.

- **Important:**  
  The **original webhook** data is not in this item. To get the lead email we will use the **Webhook** node by name in expressions (see below).

---

### Step 4: Step Type (Switch node)

- **Where it is:** After **Split Sequence Steps**.  
  Connect: **Split Sequence Steps** → **Step Type**.

- **What it does:** Sends each step to the right path: **email** → Send Email, **delay** → Wait. Optionally **sms** → Send SMS when you add that step type later.

- **Configuration:**
  - **Rules (required for current Laravel response):**
    1. **Value 1:** `{{ $json.type }}`  
       **Operation:** equals  
       **Value 2:** `email`  
       → Output 1: “email”.
    2. **Value 1:** `{{ $json.type }}`  
       **Operation:** equals  
       **Value 2:** `delay`  
       → Output 2: “delay”.
    3. **Value 1:** `{{ $json.type }}`  
       **Operation:** equals  
       **Value 2:** `sms`  
       → Output 3: “sms”.

  - You can name the outputs “email” and “delay” so the canvas is clear.

  - **SMS steps (implemented):**  
    Laravel now returns **email**, **delay**, and **sms** sequence steps. The third rule above sends `type === "sms"` items to a separate **sms** output. Connect that output to an SMS node (e.g. Twilio or your SMS provider) and use:
    - **To phone:** `{{ $('Webhook').first().json.body.lead.phone }}` (or your Webhook node name).
    - **Message:** `{{ $json.body }}` (the SMS body from the Sequence Builder).  
    The SMS branch will only receive items for steps where the tenant chose **Send SMS** in the Laravel Sequence Builder.

- **If Step Type shows "No output data" (all branches empty):** The node is receiving items that don’t have **`type`** at the root (e.g. one wrapper item with a `steps` array). That means **Split Sequence Steps** didn’t split: it’s still outputting 1 item. Fix the node *before* Split (the one that sets `steps`): make sure the **value** for `steps` is set in **expression mode (fx)** to `{{ $json.actions.steps }}` so it’s the actual array, not the literal text. Then Split will output one item per step, each with `type` at root, and Step Type will route correctly.

- **Output:**
  - **email** → connect to **Sequence – Send Email**.
  - **delay** → connect to **Sequence – Wait**.
  - **sms** (optional) → connect to your SMS node when you add SMS steps in the app.

---

### Step 5: Sequence – Send Email (Send Email node)

- **Where it is:** After **Step Type** (email output).

- **What it does:** Sends one email for this step. The **recipient** comes from the **original Webhook** output (`body.lead.email` or `body.assigned_agent.email`). **Subject** and **body** come from the **current step** (`$json` here is the step from Laravel’s `actions[].steps[]`).

- **Where the data lives (match your Webhook output):**  
  Your Webhook node returns data with **`body`** at the top level (e.g. `body.event`, `body.lead.email`, `body.lead.name`, `body.tenant_id`, `body.assigned_agent`). So we always read from **`body`** when pulling lead/agent data. The **current item** in this node is one **step** (with `subject`, `body`, `recipient`).

- **Configuration:**

  - **To Email (required):**  
    Use the **real email** from the Webhook. Your Webhook node is named **“Webhook”** and the payload is in `json.body`, so use one of these:

    - **Lead only (simplest):**
      ```text
      {{ $('Webhook').first().json.body.lead.email }}
      ```
      If your n8n version uses the other syntax:
      ```text
      {{ $node["Webhook"].json.body.lead.email }}
      ```

    - **Lead or assigned agent (when step has `recipient`):**
      ```text
      {{ $json.recipient === 'assigned_agent.email' && $('Webhook').first().json.body.assigned_agent && $('Webhook').first().json.body.assigned_agent.email ? $('Webhook').first().json.body.assigned_agent.email : $('Webhook').first().json.body.lead.email }}
      ```
      With `$node["Webhook"]`:
      ```text
      {{ $json.recipient === 'assigned_agent.email' && $node["Webhook"].json.body.assigned_agent && $node["Webhook"].json.body.assigned_agent.email ? $node["Webhook"].json.body.assigned_agent.email : $node["Webhook"].json.body.lead.email }}
      ```

    If your Webhook node has a different name (e.g. “Webhook 1”), replace `Webhook` in the expression with that exact name.

  - **Subject:**  
    From the current step (Laravel sends this in the sequence step):
    ```text
    {{ $json.subject }}
    ```
    If the subject contains merge tags like `{{lead.name}}` and you want to replace them with the real name from the webhook, you can use:
    ```text
    {{ $json.subject.replace('{{lead.name}}', $('Webhook').first().json.body.lead.name || '') }}
    ```
    (Use `$node["Webhook"]` if that’s what works in your n8n.)

  - **Email body (or Message):**  
    From the **tenant's Sequence** (the step body they set in Laravel). Use **expression mode** (fx) for this field, then:
    ```text
    {{ $json.body }}
    ```
    To replace `{{lead.name}}` in the body with the webhook’s lead name:
    ```text
    {{ $json.body.replace(/\{\{lead\.name\}\}/g, $('Webhook').first().json.body.lead.name || '') }}
    ```

  - **From Email:** Your usual “from” address (e.g. `noreply@yourdomain.com`).

- **From Email (tenant preference):** Laravel can return `from_email` in the Get Tenant Actions response when the account owner sets it in **Profile → Automation From Email**. In n8n use: `{{ $('Get Tenant Actions').first().json.from_email || 'noreply@yourdomain.com' }}` so each tenant’s preferred sender is used, with a fallback if not set.

- **How to access from_email in the email node:** In **Sequence – Send Email**, the current item (`$json`) is only one **step** (subject, body, recipient). It does **not** contain `from_email`. Split Actions and Split Sequence Steps do not add it to each item. So you must reference another node by name. Use one of these (replace the node name with the **exact** name as shown in your workflow):
  - **From Webhook (recommended — most reliable):** Laravel sends `body.from_email` in every webhook. Use this so you don't depend on the Get Tenant Actions node name: `{{ $('Webhook').first().json.body.from_email || $('Webhook').first().json.body.assigned_agent.email || 'noreply@yourdomain.com' }}`. Replace `Webhook` with your webhook node's **exact** name.
  - **From Get Tenant Actions:** Use the node's **exact** name as shown on the canvas (e.g. "Get Tenant Actions (Laravel)" with parentheses and plural). Example: `{{ $('Get Tenant Actions (Laravel)').first().json.from_email || 'noreply@yourdomain.com' }}`. If your HTTP node puts the response in `body`, try: `{{ $('Get Tenant Actions (Laravel)').first().json.body.from_email || 'noreply@yourdomain.com' }}`.
  - **If you still get noreply:** (1) Confirm the node name has no typo (e.g. "Action" vs "Actions", with or without "(Laravel)"). (2) Prefer the Webhook expression above. (3) In the same run, open the **Webhook** node OUTPUT and check that `body.from_email` exists; if not, ensure Laravel has sent a new event after we added `from_email` to the payload.
  - **If `from_email` appears in Split Actions INPUT but not in the email node:** The **Split Actions** node is likely set to **Include = "No Other Fields"**. That setting drops all root-level fields (including `from_email`) and only outputs the split `actions` content. **Fix:** Open **Split Actions** → Parameters → **Include**, and change to **"All Fields"** (or "Include Other Fields" / "All Other Fields", depending on n8n version), or use **"Selected Fields"** and add `from_email`. Then each output item will carry `from_email`, and you can use `$json.from_email` in the email node or keep using the Webhook expression above.

- **HTML body not showing:** Use `{{ $json.body }}` in the **HTML** (or **Message**) field of the Send Email node, not in a plain-text field. Set **Email Format** to **HTML**. If the step body is plain text, wrap it so it renders as HTML, e.g. `{{ '<p>' + ($json.body || '').replace(/\\n/g, '</p><p>') + '</p>' }}` (syntax may vary by n8n version). Ensure the sequence step in Laravel has body content.

- **Subject and HTML body show "undefined":** (1) Make sure **Subject** and **HTML** use **expression mode (fx)** — click the **fx** button next to each field so the value is an expression, then enter `{{ $json.subject }}` and `{{ $json.body }}`. (2) The node must receive **one item per email step** (each item = one step with `subject` and `body` at root). If it receives one wrapper item with a **`steps`** array, `$json.subject` is undefined. Fix the flow so **Split Sequence Steps** really outputs one item per step and **Step Type** passes those to Send Email. If you must support a single wrapper item, use a fallback: Subject = `{{ $json.subject || ($json.steps && $json.steps[0] ? $json.steps[0].subject : '') }}`, Body = `{{ $json.body || ($json.steps && $json.steps[0] ? $json.steps[0].body : '') }}` (only the first step would be used per run).

- **Quick check:**  
  In the Send Email node, open the **To** field and use the expression panel (fx). You should see a preview: it should resolve to the same address as in your Webhook output (`body.lead.email`), e.g. `angelrosebasillilote2@gmail.com`.

- **Output:**  
  One email sent per “email” step. Data then continues to whatever you connect after (e.g. merge back or Respond to Webhook).

---

### Step 6: Sequence – Wait (Wait node)

- **Where it is:** After **Step Type** (delay output).

- **What it does:** Waits for the time defined in the step (`duration` + `unit`), then continues. So step order is preserved: email → delay (2 days) → next email.

- **Configuration:**
  - **Resume:** “After time interval” (or “Wait for time”).
  - **Amount:** `{{ $json.duration }}`  
    (So: use the number from the step, e.g. 2.)
  - **Unit:** `{{ $json.unit }}`  
    (So: use the unit from the step: `minutes`, `hours`, or `days`.)

  If your Wait node has separate “Duration” and “Unit” fields, set:
  - **Duration:** `{{ $json.duration }}`
  - **Unit:** `{{ $json.unit }}`

- **Output:**  
  After the wait, the flow continues to the next node (e.g. merge with the email branch and then Respond to Webhook).

---

### Step 7: Run steps one after another (order)

By default, n8n may run **all items in parallel**. So if you have step 1 (email), step 2 (delay), step 3 (email), the **Send Email** node often receives **both email steps at once** and sends both emails at the **same time** — the delay never runs “between” them. That’s why you see both “welcome user” and “this after delay” with the same timestamp.

To get **step 1 → delay → step 2** (e.g. welcome email → wait 1 minute → second email), steps must run **one after another**, not in parallel.

- **Option A (recommended):** On the **Split Sequence Steps** node (or the node that outputs one item per step), enable **“Run Once for Each Item”** or **“Execute once for each item”** if your n8n version has it. That way each step is processed in its own run, and a **Wait** node between runs will actually delay the next step.

- **Option B:** Use a **Loop Over Items** (or “Loop”) node that processes the steps array **one item at a time**. Put **Step Type → Send Email / Wait** inside the loop. So: loop iteration 1 → email; iteration 2 → wait (duration from step); iteration 3 → email. The Wait runs in the middle, so the second email goes out after the delay.

- **Option C:** If your version doesn’t support that, you may need to restructure so the **Wait** is in the main path: e.g. one branch that does “first email → Wait → second email” with a single execution, using **Wait** between two **Send Email** nodes and feeding each Send Email from a different source (e.g. first step vs second step). That’s more manual but guarantees order.

So: after **Split Sequence Steps**, ensure execution is **once per item** or use a **Loop** so that step 1 runs, then step 2 (delay), then step 3 — not all at once.

---

### Step 8: Respond to Webhook

- If your **funnel.opt_in** branch already ends with **Respond to Webhook**, you can:
  - Merge the **start_sequence** branch (after Send Email and Wait) into the same **Respond to Webhook**, or
  - Add a second **Respond to Webhook** at the end of the start_sequence branch.

- **Respond to Webhook** must receive the **original webhook request** so it can reply. So either:
  - The branch that does start_sequence still has access to the same execution (and the Webhook node output), and you connect that branch to **Respond to Webhook**, or
  - You pass the webhook response reference through the flow (advanced). For a beginner, the simplest is: one **Respond to Webhook** at the end of the funnel.opt_in branch, and the start_sequence branch eventually connects back to that same path so the response is sent once.

---

## PART 5 — How the Data Flows Step by Step

1. **Webhook** receives Laravel payload.  
   - You use: `$json.body` (e.g. `body.event`, `body.lead.email`, `body.tenant_id`, `body.assigned_agent`).

2. **Event Router** routes by `$json.body.event` (e.g. `funnel.opt_in`).

3. **Get Tenant Actions** sends `event` + `tenant_id` + `payload` to Laravel.  
   - Laravel returns `{ "actions": [ ... ] }`.  
   - So after this node, each item has an `actions` array (and possibly still `body` from the webhook, depending on how you built the request).

4. **Split Actions** turns `actions` into one item per action.  
   - So now each item is one object: `workflow_id`, `type`, `sequence_id`, `sequence_name`, `steps`.

5. **Is Start Sequence?** keeps only items with `type === "start_sequence"`.  
   - True output = only start_sequence actions.

6. **Split Sequence Steps** turns `steps` into one item per step.  
   - Each item = one step: `step_order`, `type`, and either (recipient, subject, body) or (duration, unit).  
   - Order is the same as in the array (step 1, 2, 3…).

7. **Step Type** routes each step:
   - `type === "email"` → **Sequence – Send Email** (using `$json.subject`, `$json.body`, and “To” from Webhook).
   - `type === "delay"` → **Sequence – Wait** (using `$json.duration` and `$json.unit`).

8. **Sequence – Send Email** uses:
   - **To:** from `$('Webhook').first().json.body.lead.email` or `assigned_agent.email` (see expressions above).
   - **Subject:** `{{ $json.subject }}`
   - **Body:** `{{ $json.body }}`

9. **Sequence – Wait** uses:
   - **Duration:** `{{ $json.duration }}`
   - **Unit:** `{{ $json.unit }}`

10. After all steps for that branch, the run continues to **Respond to Webhook** (or your existing end node).

---

## PART 5.5 — Troubleshooting: “Execution stops at Split Actions” / “No output data returned”

If the run **stops at Split Actions** and never reaches **“is start_sequence?”**, and the Split Actions node shows **“No output data returned”**, the reason is:

**The `actions` array from Laravel is empty.**  
Split Actions splits that array. If it’s empty, it produces **zero items**, so downstream nodes (IF, Switch, etc.) get no input and don’t run. The opt-in user will not receive the sequence email until Laravel returns at least one action.

---

**Step 1 — Confirm Laravel is returning empty actions**

1. In the same execution, click **Get Tenant Actions (Laravel)**.
2. Open the **OUTPUT** tab.
3. Look at the JSON. If you see **`"actions": []`** (empty array), that’s why Split Actions has no output.

---

**Step 2 — Why Laravel might return empty actions**

Laravel returns actions only when there is an **active workflow** for the **same event** and **same tenant**. Check these:

| Check | What to verify |
|-------|----------------|
| **Event value** | Laravel expects the **exact** event string: **`funnel.opt_in`** (with a **dot**). If n8n sends **`funnel_opt_in`** (underscore) or anything else, Laravel will not match and will return `actions: []`. |
| **tenant_id** | Must match the tenant that owns the workflow (e.g. `1`). Usually from `body.tenant_id`. |
| **Workflow in Laravel** | In the app: **Automation → Workflows**. There must be at least one workflow with **Trigger** = “Funnel opt-in” (saved as `funnel.opt_in`), **Action** = “Start Sequence” (or “Send Email”), and **Status** = Active. |
| **Trigger must match the event** | When you **opt in** (submit a funnel form), the webhook sends **event = `funnel.opt_in`**. Laravel only returns actions for workflows whose **Trigger** matches that event. If your workflow has **Trigger = “Lead created”** (`lead.created`), it will **not** run on opt-in — so you get `actions: []` and Split Actions stops. For opt-in, you need a workflow with **Trigger = “Funnel opt-in”**. |
| **Sequence (if Start Sequence)** | If the action is “Start Sequence”, the chosen sequence must exist, be active, and have at least one step. |

---

**Step 2b — Trigger must match what you’re testing (very common)**

- You’re testing **opt-in** (submitting the funnel form). The webhook sends **`event: "funnel.opt_in"`**.  
- Laravel returns actions only for workflows whose **Trigger** = that event. So it looks for workflows with **Trigger = “Funnel opt-in”** (`funnel.opt_in`).
- If your workflow has **Trigger = “Lead created”** (`lead.created`), it runs only when a **lead is created**, not when someone opts in. So for opt-in you get **no matching workflow** → **`actions: []`** → Split Actions outputs nothing and the run stops.
- **Fix:** In Laravel, either **edit** your workflow and set **Trigger** to **“Funnel opt-in”**, or **create a new workflow** with Trigger = **Funnel opt-in**, Action = Start Sequence, Sequence = your “optin” sequence. Then run the opt-in again. The “Lead created” workflow can stay for when you want the sequence to run on new leads instead.

---

**Step 3 — Fix the event in n8n (most common fix)**

Your **Webhook** receives `body.event = "funnel.opt_in"` (correct). The **Get Tenant Actions** node must send that **same value** to Laravel.

1. Open **Get Tenant Actions (Laravel)** → **Parameters** → body (JSON).
2. Find the **`event`** field. It should use the value from the **Webhook** (or from the node that has the webhook payload), e.g.:
   ```text
   {{ $json.body.event }}
   ```
   If your flow passes data from the **Event Router**, the item might already be the webhook payload, so `$json.body.event` is correct. If the previous node outputs a different structure, use the expression that points to the **actual event** from the webhook (e.g. from the Webhook node by name).
3. **Important:** The string sent in the request body must be **`funnel.opt_in`** (with a dot). If you see `funnel_opt_in` or anything else in the request or in Laravel logs, fix the expression so the API receives **`funnel.opt_in`**.
4. **tenant_id** should be the number for the tenant, e.g. `{{ $json.body.tenant_id }}` (no quotes around the expression if the field is numeric in JSON).

After fixing, run the workflow again. Check **Get Tenant Actions** OUTPUT: `actions` should now contain at least one object (e.g. `type: "start_sequence"` or `"send_email"`). Then Split Actions will output items and **“is start_sequence?”** will run.

---

**Step 4 — Quick checklist so the opt-in user receives the email**

1. **Laravel:** Automation → Workflows → one active workflow for **Funnel opt-in** → Action **Start Sequence** (or Send Email) → sequence has steps and is active.
2. **n8n:** Get Tenant Actions sends **`event`** = `funnel.opt_in` (dot) and correct **tenant_id**.
3. **n8n:** Get Tenant Actions OUTPUT shows **`actions`** with at least one item.
4. Then Split Actions outputs items → **“is start_sequence?”** runs → sequence steps run → email is sent.

---

## PART 6 — How to Test It

### 1. Prepare Laravel and n8n

- In Laravel, create a **workflow** that:
  - **Trigger:** e.g. `funnel.opt_in`.
  - **Action:** “Start Sequence”.
  - **Sequence:** Choose a sequence that has at least one email step and, if you want, one delay step (e.g. “Welcome Series”: email → delay 2 days → email).
- Activate the workflow and the sequence.
- In n8n, open your SaaS Event Router workflow and make sure the **Webhook** and **Get Tenant Actions** nodes are correct (same as before).

### 2. Trigger a real opt-in

- Use your real funnel opt-in form (or a test form that posts to the same funnel).
- Submit with a valid email you can check.
- In n8n, open **Executions** and open the latest run.

### 3. Check “Get Tenant Actions” output

- Click **Get Tenant Actions (Laravel)** and open **OUTPUT**.
- You should see `actions` as an array.
- One of the items should have:
  - `type`: `"start_sequence"`
  - `sequence_id`, `sequence_name`
  - `steps`: array of objects with `step_order`, `type`, and email or delay fields.

### 4. Check “Split Actions” output

- Click **Split Actions**.
- You should see **one item per action**. For the start_sequence action, that item should have `type: "start_sequence"` and a `steps` array.

### 5. Check “Is Start Sequence?” and “Split Sequence Steps”

- **Is Start Sequence?** True output should have one item (the start_sequence action).
- **Split Sequence Steps** should have **one item per step**, in order (step_order 1, 2, 3…).

### 6. Check email and wait

- For an **email** step, open **Sequence – Send Email** and check that:
  - **To** is the lead (or agent) email from the webhook.
  - **Subject** and **Body** match the step.
- For a **delay** step, open **Sequence – Wait** and check that **Duration** and **Unit** match the step (e.g. 2, days).
- Optionally, use a short delay (e.g. 1 minute) first to confirm the next step runs after the wait.

### 7. Common test mistakes

- **Wrong node name in expressions:** If your Webhook node is not named “Webhook”, replace `$('Webhook')` with `$('YourWebhookNodeName')`.
- **No start_sequence in actions:** Make sure the workflow in Laravel is “Start Sequence” and the sequence is active and has steps.
- **Steps run in parallel:** Ensure “Run Once for Each Item” (or equivalent) is set so steps run in order.

---

## PART 7 — Common Mistakes to Avoid

1. **Using the wrong “json” for lead email**  
   After “Split Sequence Steps”, `$json` is the **step** (subject, body, recipient). The **lead email** is not in `$json`. Always use the Webhook node for that, e.g. `$('Webhook').first().json.body.lead.email`.

2. **Wrong Webhook node name**  
   If your node is named “Webhook 1” or “Funnel Webhook”, use that exact name: `$('Webhook 1').first().json.body.lead.email`.

3. **Forgetting that “To” must be a real address**  
   `recipient` in the step is the **key** (`lead.email` or `assigned_agent.email`). You must **resolve** it to the actual email from `body.lead.email` or `body.assigned_agent.email`. Use the expressions from Step 5 above.

4. **All steps running in parallel**  
   If you don’t set “Run Once for Each Item” (or use a loop), n8n may run all step items at once. Then the delay doesn’t “wait before the next email” — it runs at the same time as the others. So enable “Run Once for Each Item” on the node that outputs the steps (or use a proper loop).

5. **Wait node not using expressions**  
   Duration and unit must come from the step: `{{ $json.duration }}` and `{{ $json.unit }}`. Don’t hard-code 2 days for every delay.

6. **Processing all actions instead of only start_sequence**  
   Use the IF node so only items with `type === "start_sequence"` go to “Split Sequence Steps”. Otherwise you might try to split `steps` on a send_email action (which doesn’t have steps) and get errors or empty runs.

7. **Respond to Webhook not receiving the webhook context**  
   The branch that does start_sequence must eventually connect to **Respond to Webhook** in a way that the webhook request is still in context. Don’t end the branch without responding if your webhook is “Wait for response”; otherwise the request can time out.

8. **Laravel returns empty `actions[]` so execution never reaches “is start_sequence?”**
   If **Get Tenant Actions** OUTPUT shows `"actions": []`, Split Actions has nothing to split, so it returns no data and the IF node never runs. Fix it in Laravel (active workflow for the right event + tenant) and in n8n (send **`event`** = **`funnel.opt_in`** with a dot, not `funnel_opt_in`). See **PART 5.5 — Troubleshooting** above.

9. **“Is Start Sequence?” always gives False (opt-in runs but sequence branch never runs)**  
   Laravel is returning an action with **`type: "send_email"`**, so the IF correctly sends it to the False branch. To get **True**, the workflow that runs for that trigger must be a **Start Sequence** workflow, not a Send Email one. In **Laravel → Automation → Workflows**, find the row whose **Trigger** is “Funnel opt-in”. If its **Action** column says “Send Email (Lead email)” or “Send Email (Assigned agent email)”, that workflow is the one being used and it produces `send_email`. Either **edit** that workflow and change **Action type** to **“Start Sequence”** and pick your sequence, then save; or **create a new workflow** with Trigger = Funnel opt-in, Action = Start Sequence, Sequence = your sequence, and leave the old one inactive or delete it.    After that, **Get Tenant Actions** will return an action with `type: "start_sequence"` and the IF will go to True.

10. **Execution stops at Split Sequence Steps (no output)**  
   The node is looking for an array to split. If your item has **`actions`** as an object with **`actions.steps`** inside it (not `steps` at root), set **Field to Split Out** in **expression mode (fx)** to: `{{ $json.actions.steps }}`. That way the node splits the steps array and outputs one item per step. If the field does not support expressions, try the path **`actions.steps`** (dot notation), or add an **Edit Fields** node before this one to set a root-level `steps` from `actions.steps`, then split on `steps`.

11. **Sequence – Send Email: "No recipients defined"**  
   In that node the current item (`$json`) is one **step** (subject, body, recipient). So `$json.body` is the email body text, not the webhook. Using **To Email** = `$json.body.lead.email` is empty and causes "No recipients defined". **Fix:** set **To Email** to `{{ $('Webhook').first().json.body.lead.email }}` (or your Webhook node name). See Step 5 for full expressions.

---

## Quick reference — Expressions

| What you need        | Expression (use your Webhook node name if different) |
|----------------------|------------------------------------------------------|
| Event (for router)   | `{{ $json.body.event }}`                              |
| From email (tenant)  | `{{ $('Get Tenant Actions').first().json.from_email \|\| 'noreply@yourdomain.com' }}` |
| Lead email           | `{{ $('Webhook').first().json.body.lead.email }}`    |
| Assigned agent email | `{{ $('Webhook').first().json.body.assigned_agent.email }}` |
| Tenant ID (for API)  | `{{ $json.body.tenant_id }}`                         |
| Action type          | `{{ $json.type }}` (after Split Actions)             |
| Step type            | `{{ $json.type }}` (after Split Steps)               |
| Email subject        | `{{ $json.subject }}` (current step)                  |
| Email body           | `{{ $json.body }}` (current step)                    |
| Delay duration       | `{{ $json.duration }}` (current step)                 |
| Delay unit           | `{{ $json.unit }}` (current step)                    |

---

End of guide. You now have a start_sequence branch that runs sequence steps (email and delay) in order, using your existing Webhook and Get Tenant Actions setup.
