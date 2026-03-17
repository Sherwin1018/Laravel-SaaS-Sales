# Lead Created — Add Automation (Beginner-Friendly Guide)

This guide adds **automation** to your **Lead Created** n8n workflow: a **Wait** (delay), an **IF** (condition), **Send Email**, and an optional **SMS** placeholder. Every step tells you **where to click** and **what you should see**. You need your existing flow: **Webhook → Edit Fields → Respond to Webhook**.

---

## Part 0 — Quick Map of the n8n Screen

When you have a workflow open, the screen is roughly like this:

| Area | Where it is | What it’s for |
|------|-------------|----------------|
| **Left sidebar** | Far left of the window | Links like **Workflows**, **Executions**, **Settings**. Use this to open the list of workflows or to see past runs. |
| **Top bar** | Top of the page | Workflow name (e.g. “Lead Created”), **Save** button, **Publish** / Active toggle. |
| **Tabs under the name** | Just below the workflow name | **Editor** (the flow you edit), **Executions** (list of runs). Stay on **Editor** while building. |
| **Canvas** | Big middle area with boxes and lines | This is your workflow. Each **box** is a **node**. **Lines** connect nodes. You add nodes and draw lines here. |
| **Right panel** | Opens on the right when you click a node | Shows that node’s **Parameters** and **Settings**. You type or choose options here. Close it by clicking the **X** or clicking on empty canvas. |
| **Small dots on nodes** | Left and right side of each node box | **Left dot** = input (data comes in). **Right dot** = output (data goes out). You connect nodes by dragging from one node’s **right dot** to another node’s **left dot**. |

**To add a new node:** Click the **+** button that appears when you hover between two nodes, **or** double‑click on an **empty** part of the canvas. A **node list** (and search box) will open so you can pick a node.

---

## Part 1 — What You’ll Add (Simple Explanation)

| What | In plain words |
|------|-----------------|
| **Wait** | Pause the workflow for a while (e.g. 1 minute) before the next step. |
| **IF** | If the lead’s status is `new`, send the welcome email; otherwise skip the email. |
| **Email** | Send one **email** to the **lead** — the welcome/first email to the lead’s email address (from the webhook). This is the start of the project’s automation (later you can add more emails for a sequence). |
| **SMS (optional)** | Placeholder for sending an SMS to the lead later (e.g. Twilio). Add the node and fill credentials when ready. |

**Safe way to test email:** Use a sandbox like **Mailtrap** or a test Gmail account so you don’t email real customers by mistake.

---

## Part 2 — How to Find Your Lead Data in n8n

You are following this guide step-by-step; the instructions below tell you **exactly** what to do and what to write down. Do each step in order. This is so you know how to reference the lead data (email, name, status, etc.) in later nodes and so your team has a single source of truth.

---

### Step 2.1 — Open the Executions list

1. In n8n, look at the **left sidebar** (the narrow column on the far left).
2. Click **Executions** (or **Workflow** → **Executions** if it’s under a menu).  
   **What you should see:** A list of past runs of your workflows (date, time, “Succeeded” or “Failed”). This list may show all workflows or the current one, depending on your n8n version.
3. If you don’t see any executions, create a lead in Laravel first (with the queue worker running) or send the PowerShell POST from the main Lead Created guide, then come back and refresh this list.

---

### Step 2.2 — Open one execution that has lead data

1. In the Executions list, click **one row** that you know was triggered by a **real lead** (e.g. when you created a lead in Laravel or ran the `Invoke-RestMethod` command). Pick the most recent one if unsure.  
   **What you should see:** The main area now shows the **execution detail**: a diagram of the workflow with nodes (Webhook, Edit Fields, Respond to Webhook, etc.) and possibly a **Logs** or **OUTPUT** section below or to the side.
2. If the view is zoomed out and you see many nodes, use the **zoom** controls (usually bottom-left or on the canvas) to zoom in so you can clearly see each node box.

---

### Step 2.3 — Open the Webhook node’s output

1. In the execution diagram, find the **first node** — it should be labeled **Webhook** (the trigger that receives the POST from Laravel).
2. **Click** the **Webhook** node once.  
   **What you should see:** A **panel** opens (usually on the right or below) with tabs like **Parameters**, **Settings**, and **OUTPUT** (or **Input** / **Output**). The **OUTPUT** tab shows the data that this node produced — i.e. what the webhook received from Laravel.
3. Click the **OUTPUT** tab if it’s not already selected. You may also see **Schema**, **Table**, **JSON** — switch to **JSON** or **Table** so you can see the raw structure.

---

### Step 2.4 — Identify where the lead fields are (body vs root)

Look at the **OUTPUT** content. You will see one of two patterns:

**Pattern A — Fields inside `body`**  
You see an object that has a key **`body`**, and inside `body` you see keys like `event`, `tenant_id`, `lead_id`, `name`, `email`, `phone`, `source_campaign`, `status`.  
Example shape:
```json
{
  "body": {
    "event": "lead.created",
    "tenant_id": 1,
    "lead_id": 14,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "phone": "09171234567",
    "source_campaign": "Direct",
    "status": "new"
  },
  "headers": { ... },
  ...
}
```

**Pattern B — Fields at the top level**  
You see `event`, `tenant_id`, `lead_id`, `name`, `email`, etc. **directly** in the first level of the object (no `body` wrapper).  
Example shape:
```json
{
  "event": "lead.created",
  "tenant_id": 1,
  "lead_id": 14,
  "name": "Jane Doe",
  "email": "jane@example.com",
  ...
}
```

---

### Step 2.5 — Write down which pattern you have (for you and your team)

**Copy exactly one of the two blocks below into a text file or your project documentation (e.g. in `docs/` or in your n8n Sticky Note — see Part 2.6).** Use the block that matches what you saw.

**If you saw Pattern A (fields inside `body`), write:**

```
Lead Created workflow — Data location: INSIDE body
Use these expressions in n8n nodes:
- Email:    {{ $json.body.email }}
- Name:    {{ $json.body.name }}
- Phone:   {{ $json.body.phone }}
- Status:  {{ $json.body.status }}
- Tenant:  {{ $json.body.tenant_id }}
- Lead ID: {{ $json.body.lead_id }}
- Source:  {{ $json.body.source_campaign }}
Payload from Laravel is in the "body" object of the Webhook output.
```

**If you saw Pattern B (fields at root), write:**

```
Lead Created workflow — Data location: AT ROOT (no body)
Use these expressions in n8n nodes:
- Email:    {{ $json.email }}
- Name:    {{ $json.name }}
- Phone:   {{ $json.phone }}
- Status:  {{ $json.status }}
- Tenant:  {{ $json.tenant_id }}
- Lead ID: {{ $json.lead_id }}
- Source:  {{ $json.source_campaign }}
Payload from Laravel is at the root of the Webhook output.
```

**Why this matters:** In Part 6 (Send Email) and elsewhere you will type expressions like “To: …” and “Subject: …”. You must use either `$json.body.*` or `$json.*` consistently. If you use the wrong one, the email will be blank or the node will error. Writing it down once here avoids mistakes and helps your team use the same expressions.

---

### Step 2.6 — Add a Sticky Note in n8n (documentation for the team)

**Purpose of these notes:** The sticky notes in this workflow are **for the team to understand the workflow**. They explain what each part is and what it’s for — so anyone who opens the workflow (now or later) can quickly see what it does, without reading configuration steps or external docs. Keep the text short and informative: what is this, and what should we know or do here.

**How to add a Sticky Note:**

1. Go back to the **Editor** (click the **Editor** tab at the top if you’re still in Executions).
2. **Right‑click** on an **empty** part of the canvas (not on a node).  
   **Or:** Double‑click on empty canvas, or click the **+** that appears between nodes; in the node list, search for **Sticky Note** or **Note** and click it.
3. A **note** (a rectangular box you can type in) appears on the canvas. You can **drag** it by its title bar to move it.
4. **Where to place it:** Put this first note **above** or **to the left** of the **Webhook** node so it’s clear it describes the trigger and the data.
5. **What to type in the note** — copy and paste this. These are **informative** notes for the team (what it is, what to do), not configuration steps. Adjust the expressions if you have Pattern B (fields at root, use `$json.*` instead of `$json.body.*`).

**Note 1 — At the start (above or left of Webhook):**

```
LEAD CREATED
Runs when a lead is added (Laravel). Data comes in "body" — use {{ $json.body.email }}, {{ $json.body.name }}, etc. below.
```

6. Resize the note if needed (drag a corner or edge). Click **Save** so the note is stored with the workflow.

**What you should see:** A short note that tells the team what this workflow is and where the data comes from.

---

### Step 2.7 — Reference: Sticky notes for the rest of the workflow (add as you build)

These notes are **for the team to understand the workflow**. Each note explains what that part of the flow is and what we use it for — so anyone opening the workflow can follow it without extra docs. Keep the text short; no “how to configure” in the note. **Placement:** put each note **above** or **beside** the node it describes.

| Where to place the note | Text to paste into the Sticky Note |
|-------------------------|------------------------------------|
| **Above or beside the Wait node** | `WAIT — Pause before next step. Now: 1 min (testing). Later: change to 5–15 min or 1 hr for production.` |
| **Above or beside the IF node** | `IF — Email only when status is "new". Otherwise skip email, go to response.` |
| **Above or beside the email node (Send Email / Gmail / SMTP)** | `EMAIL — Welcome email to the lead (To = lead’s email from webhook). Test inbox in dev; real SMTP when live.` |
| **Above or beside the Edit Fields node** | `EDIT FIELDS — Builds reply (ok, event) to Laravel. Required for webhook response.` |
| **Above or beside the Respond to Webhook node** | `RESPOND TO WEBHOOK — Sends reply to Laravel. Every path must reach here. Do not remove.` |

To add each note: **right‑click** on empty canvas (or **+** / double‑click and search **Sticky Note**) → add the note → **drag** it next to the right node → paste the text from the table → **Save**.

---

## Part 3 — The Flow You’ll Build

You’ll build this order:

```
Webhook → Wait → IF → Send Email → Edit Fields → Respond to Webhook
                  ↘ (when status is not "new") → Edit Fields → Respond to Webhook
```

So: after the Webhook, we wait, then we check “is status = new?”. If **yes**, we send email then go to Edit Fields and Respond to Webhook. If **no**, we skip email and go straight to Edit Fields and Respond to Webhook. That way the webhook **always** gets a response.

---

## Part 4 — Add the Wait Node (Step-by-Step)

**Goal:** Add a pause (e.g. 1 minute) after the webhook runs.

### Step 4.1 — Open your workflow and go to the Editor

1. In the **left sidebar**, click **Workflows** (or the menu that shows your workflows).
2. Click the workflow named **Lead Created**.
3. At the top, make sure the **Editor** tab is selected (not Executions). You should see your workflow: Webhook, Edit Fields, Respond to Webhook, with lines between them.

### Step 4.2 — Remove the line from Webhook to Edit Fields

1. Find the **line** (connection) that goes from **Webhook** to **Edit Fields**.
2. **Click** that line once so it’s selected (it may highlight).
3. Press **Delete** (or **Backspace**) on your keyboard to remove it.  
   **Or:** Hover over the line, right‑click, and choose **Delete** if you see that option.

**What you should see:** The Webhook and Edit Fields nodes are still there, but the line between them is gone.

### Step 4.3 — Add the Wait node

1. Move the mouse over the **empty space** between the Webhook and Edit Fields (or near the Webhook’s **right** side).
2. You should see a **+** button appear. **Click** it.  
   **Or:** **Double‑click** on an empty part of the canvas.
3. A **panel** or **menu** will open (often on the left or center) with a **search box** at the top.
4. In the search box, type: **Wait**.
5. In the list below, click the node named **Wait** (it might say “Wait” or “Wait for trigger” etc.).
6. The Wait node should appear on the canvas.

**What you should see:** A new box (node) labeled “Wait” on the canvas.

### Step 4.4 — Connect Webhook → Wait → Edit Fields

1. Find the **right** side of the **Webhook** node. There is a small **dot** (output).
2. **Click and hold** on that dot, then **drag** the mouse to the **left** dot of the **Wait** node. Release the mouse. A **line** should appear.
3. Now **click and hold** the **right** dot of the **Wait** node and **drag** to the **left** dot of the **Edit Fields** node. Release. Another line should appear.

**What you should see:** Webhook → Wait → Edit Fields, all connected by lines.

### Step 4.5 — Configure the Wait node

1. **Click** the **Wait** node once. The **right panel** should open with the node’s settings.
2. Find **Wait Type** or **Resume** (or similar). Choose **“Time interval”** or **“For a specific time”** (whatever your n8n shows).
3. Set the **duration**. For testing, choose **1 minute** (or 30 seconds if you have that option). You can change this later for real use (e.g. 1 hour).
4. Click somewhere on the **canvas** (or press Escape) to close the right panel.
5. Click **Save** in the **top right** of the page.

**What you should see:** The Wait node is configured. When the workflow runs, it will pause at Wait for the time you set, then continue.

**Documentation:** Add a **Sticky Note** above or beside the Wait node with the text from **Part 2, Step 2.7** (Wait row). Right‑click canvas → add Sticky Note (or search “Sticky Note” in the node list) → paste the text → drag the note next to the Wait node → Save.

---

## Part 5 — Add the IF Node (Step-by-Step)

**Goal:** Only send email when the lead’s status is `new`. Otherwise skip the email but still respond to the webhook.

### Step 5.1 — Disconnect Wait from Edit Fields

1. **Click** the **line** that goes from **Wait** to **Edit Fields**.
2. Press **Delete** (or Backspace) to remove that line.

**What you should see:** Wait is no longer connected to Edit Fields.

### Step 5.2 — Add the IF node

1. **Double‑click** on an empty part of the canvas, or click the **+** that appears between nodes.
2. In the search box, type: **IF**.
3. Click the **IF** node in the list. It should appear on the canvas.
4. **Connect** the **Wait** node to the **IF** node: drag from the **right dot** of Wait to the **left dot** of IF.

**What you should see:** The IF node has **two output dots** on the right (often labeled **true** and **false** or with green/red).

### Step 5.3 — Configure the IF node

1. **Click** the **IF** node. The right panel opens.
2. Under **Condition 1** (or the first condition):
   - **Value 1** (or “First value”): click in the box and type: `{{ $json.body.status }}`  
     (If your data is at the root, use `{{ $json.status }}` instead.)
   - **Operation:** open the dropdown and choose **equals** (or “is equal to”).
   - **Value 2** (or “Second value”): type: `new`
3. So the condition means: “If status equals `new`, go to the **true** branch; otherwise go to the **false** branch.”
4. Close the panel (click canvas or Escape).

**What you should see:** The IF node is configured. You’ll connect the **true** output to Send Email and the **false** output to Edit Fields in the next steps.

### Step 5.4 — Connect the false branch to Edit Fields

1. Find the **right** side of the **IF** node. There are two output dots. One is usually **false** (or the second one).
2. **Drag** from that **false** output dot to the **left** dot of **Edit Fields**.

**What you should see:** A line from IF (false) to Edit Fields. So when status is not `new`, the flow goes straight to Edit Fields and then Respond to Webhook.

**Documentation:** Add a **Sticky Note** above or beside the IF node with the text from **Part 2, Step 2.7** (IF row). Right‑click canvas → add Sticky Note → paste the text → drag the note next to the IF node → Save.

---

## Part 6 — Add the Email Node (Welcome Email to the Lead)

**What this is:** This step is **email** — we send one **outbound email** to the **lead’s email address** (the one Laravel sent in the webhook). In this project it’s the **welcome / first email** when a lead is created (start of the automation engine; later you can add more delayed emails for a full sequence). The lead receives the email in their inbox.

**In n8n:** The node that does this may be named **Send Email**, **Gmail**, **SMTP**, **SendGrid**, or similar — use whichever node **sends an email** (one message to a recipient). We call it “Send Email” in the guide for short.

**Goal:** When lead status is `new`, send a welcome email **to the lead** (To = lead’s email from the webhook).

---

### Step 6.1 — Add the email node

1. **Double‑click** on an empty part of the canvas (or click **+**).
2. In the search box, type: **Send Email** or **Email** or **SMTP** or **Gmail** (depending on what your n8n has).
3. Click the node that **sends an email** (e.g. **Send Email**, **Gmail**, **SMTP**, **SendGrid**). It appears on the canvas.
4. **Connect** the **true** output of the **IF** node to this new node: drag from the **true** (first) output dot of IF to the **left** dot of the email node.
5. **Connect** the email node to **Edit Fields**: drag from the **right** dot of the email node to the **left** dot of Edit Fields.

**What you should see:** Flow: IF (true) → [Email node] → Edit Fields. And IF (false) → Edit Fields. So both paths eventually reach Edit Fields and then Respond to Webhook.

### Step 6.2 — Configure the email node (To = lead’s email)

1. **Click** the email node (Send Email / Gmail / SMTP). The right panel opens.
2. **Credentials:** Create or select the credential for your mail provider. For **testing**, use **Mailtrap** (mailtrap.io) or a test Gmail; for production use your real SMTP or provider.
3. **From Email:** This is **not** the tenant’s or lead’s email. It is the **sender** address (e.g. `noreply@yourdomain.com` or your Mailtrap/test address). You need **one real email address** that your app sends from (the SMTP account you configure in n8n). Replace `yourdomain.com` with your real domain for production so the lead sees a proper “From” and to avoid “No recipients defined” or empty From errors.
4. **To Email:** Use **exactly** `{{ $json.body.email }}` — this is the **lead’s email** (the person who was just created). If you use anything else (e.g. `email_subject`), the node can fail with “No recipients defined”. If your data is at root, use `{{ $json.email }}`.
5. **Subject:** Use the content from the **Automation** tab in Laravel. Laravel sends a ready-to-use subject with placeholders already replaced:
   - **Recommended:** `{{ $json.body.email_subject }}` (uses the first email step from the Automation tab).
   - Fallback: `{{ $json.body.steps[0].subject }}` (same step; placeholders like `{{ name }}` are **not** replaced in n8n).
6. **Message / Body (HTML):** Use the content from the **Automation** tab:
   - **Recommended:** `{{ $json.body.email_body }}` (Laravel has already replaced `{{ name }}`, `{{ email }}`, `{{ phone }}` with the lead’s data).
   - Fallback: `{{ $json.body.steps[0].body }}` (raw template; you’d need an extra n8n step to replace `{{ name }}` etc. if you want the lead’s name in the body).
7. Close the panel and **Save** the workflow.

**From Email and tenants:** You do **not** need to create a real email address per tenant for the “From” field. Use one real sender address for the app (e.g. `noreply@yourdomain.com`). The **To** address is always the lead’s email (`$json.body.email`), which comes from the webhook.

**What you should see:** When a lead is created with status `new`, this node sends **one email to the lead’s email address** (welcome email). Use Mailtrap or a test inbox to confirm. The subject and body match what the account owner set in the Laravel **Automation** tab.

**Documentation:** Add a **Sticky Note** above or beside this node with the text from **Part 2, Step 2.7** (Send Email row). Right‑click canvas → add Sticky Note → paste the text → drag the note next to the node → Save.

---

## Part 7 — Add the SMS Placeholder (Optional)

**Goal:** Add a node that will send SMS later (e.g. Twilio). You can leave credentials empty for now.

### Step 7.1 — Add the SMS node

1. **Double‑click** on the canvas (or click **+**).
2. Search for **Twilio** or **SMS** or **Send SMS**.
3. Click the node (e.g. **Twilio**) and it appears on the canvas.
4. You can place it **after** Send Email: **disconnect** Send Email from Edit Fields, then connect **Send Email** → **SMS** → **Edit Fields**.  
   Or leave the flow as is and add the SMS node in parallel; then connect SMS to Edit Fields as well. For simplicity, you can skip this step and add SMS later.

### Step 7.2 — Configure as placeholder

1. Click the SMS/Twilio node.
2. **To** (phone number): type `{{ $json.body.phone }}` (or `{{ $json.phone }}`).
3. **Message:** type `Hi {{ $json.body.name }}, thanks for signing up.`
4. **Credentials:** Create a placeholder or leave for later. The node may show an error until you add real Twilio (or other) credentials.
5. You can rename the node to “SMS – configure later” by double‑clicking its title on the canvas.

**What you should see:** The workflow has an SMS step. It will work once you add valid credentials.

---

### Step 7.3 — Configure Edit Fields (response body to Laravel)

**Why this matters:** The **Edit Fields** node (sometimes called **Set** in n8n) builds the data that **Respond to Webhook** sends back to Laravel. Laravel’s job waits for an HTTP response (up to 15 seconds); if n8n never responds, the request can time out. Sending a clear JSON body also helps with debugging.

1. Click the **Edit Fields** node to open its parameters in the right panel.
2. Ensure **Mode** is **Define Below** (or **Manual mapping** / **Key-Value**, depending on your n8n version).
3. Add two fields (click **Add Value** or **Add Field** for each):
   - **Name:** `ok` → **Value:** `true` (choose **Boolean** if there’s a type dropdown).
   - **Name:** `event` → **Value:** `lead.created` (string).
4. If there is an option like **“Include other input fields”** or **“Pass through”**, you can leave it as is or turn it off so the response contains only `ok` and `event`.
5. Save the workflow.

**What you should see:** Edit Fields outputs one item with `{ "ok": true, "event": "lead.created" }`. That item is what Respond to Webhook will send.

---

### Step 7.4 — Configure Respond to Webhook

1. Click the **Respond to Webhook** node.
2. Set **Respond With** to **First Incoming Item** (so it uses the data from Edit Fields).
3. Open **Options** (or **Add option**) and set **Response Code** to **200**.
4. If your n8n has **Response Body** under options, you can leave it empty — the body comes from the **First Incoming Item** (Edit Fields). If you had to set a fixed body elsewhere, ensure it doesn’t override the Edit Fields output.
5. Save the workflow.

**What you should see:** When the workflow runs, Respond to Webhook sends HTTP 200 with body `{ "ok": true, "event": "lead.created" }` (or whatever Edit Fields produced). Laravel’s `SendN8nWebhookJob` gets a successful response and logs success.

---

## Part 8 — Make Sure Respond to Webhook Still Runs

**Important:** Laravel expects a response. So **every path** must eventually reach **Respond to Webhook**.

- **Path 1:** Webhook → Wait → IF (true) → Send Email → Edit Fields → Respond to Webhook.  
- **Path 2:** Webhook → Wait → IF (false) → Edit Fields → Respond to Webhook.

Check that **Edit Fields** is still connected to **Respond to Webhook**. If you removed that line by mistake, drag from the **right** dot of Edit Fields to the **left** dot of Respond to Webhook.

**What you should see:** No “dead ends.” Every branch from the Webhook leads to Edit Fields and then Respond to Webhook.

---

## Part 9 — Test the Automation

### 9.1 — Test with a new lead (status = New)

1. In **Laravel**, create a new lead. Set **Status** to **New** and use an email you can check (e.g. Mailtrap inbox or your test email).
2. In **n8n**, in the left sidebar click **Executions**. Open the **latest** execution (top of the list).
3. You should see the flow: **Webhook** (green) → **Wait** (green) → **IF** (green) → **Send Email** (green) → **Edit Fields** (green) → **Respond to Webhook** (green).
4. Check your **Mailtrap** (or test inbox) for the welcome email.

### 9.2 — Test when status is not New

1. Create a lead in Laravel with status **Contacted** (or any status other than New).
2. In n8n **Executions**, open the new run. The flow should go: Webhook → Wait → IF → **Edit Fields** (the false branch). No Send Email node should run.
3. The execution should still **succeed** and Respond to Webhook should run.

### 9.3 — If name or email is empty in the email

If the email arrives but “Hi” and the recipient look wrong or empty, the **expression** path is wrong.  
- Open an execution and click the **Webhook** node. Look at **OUTPUT** and see if the lead data is under **body** or at the top level.  
- Then in the **Send Email** node, use either `{{ $json.body.email }}` and `{{ $json.body.name }}` or `{{ $json.email }}` and `{{ $json.name }}` to match what you see.

### 9.4 — “No recipients defined” or “(no subject)” / “{{ name }}” in body

- **No recipients defined:** The **To Email** field must be the lead’s email. Set it to `{{ $json.body.email }}` (not `email_subject` or any other field).
- **(no subject):** Use `{{ $json.body.email_subject }}` for Subject (Laravel sends this with the first email step’s subject). If you use `{{ $json.body.subject }}`, that key may be missing; the subject is under `steps[0].subject` or the top-level `email_subject`.
- **Literal “{{ name }}” or “{{ $json.body.name }}” in the email body:** Use **HTML (Body)** = `{{ $json.body.email_body }}`. Laravel replaces `{{ name }}`, `{{ email }}`, `{{ phone }}` in the Automation tab content before sending; `email_body` is that replaced text. If you use `steps[0].body` only, n8n does not replace those placeholders.

---

## Part 10 — Summary Checklist

- [ ] Wait node added between Webhook and the rest; **Webhook → Wait** connected.
- [ ] IF node added; **Wait → IF** connected; condition: `{{ $json.body.status }}` equals `new`.
- [ ] **IF (false)** connected to **Edit Fields**.
- [ ] Send Email node added; **IF (true)** → **Send Email** → **Edit Fields**; **To** = `{{ $json.body.email }}`, **Subject** = `{{ $json.body.email_subject }}`, **Body** = `{{ $json.body.email_body }}`; **From** = one real sender address (e.g. noreply@yourdomain.com).
- [ ] **Edit Fields** still connected to **Respond to Webhook**.
- [ ] Workflow **Saved** and (if you use production) **Active**.
- [ ] Tested with a lead (status New) and saw the email; tested with status not New and saw no email but execution still succeeded.

Once this works, you can add more steps (e.g. another Wait and another email) using the same pattern: add node, connect with the dots, configure in the right panel, save.
