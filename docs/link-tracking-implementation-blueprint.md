# Link Tracking & Source Attribution — Full Implementation Blueprint

This document is a **development guide and blueprint** for implementing link click tracking, visit attribution, and funnel analytics in the Laravel SaaS Sales Funnel project. Use it when you are ready to build the feature.

## Related docs
- [lead-source-tracking-guide.md](lead-source-tracking-guide.md) — User-facing guide (UTM, source_campaign, reporting).
- [automation-n8n-implementation.md](automation-n8n-implementation.md) — Automation events and n8n integration.

---

## 1. Scope and Goals

### 1.1 What We Are Building

| Piece | Description | Lives in |
|-------|-------------|----------|
| **Pre-lead visit/click logging** | When a visitor lands on a funnel URL with UTM (or referrer), log one record: tenant, funnel, source, timestamp. | Laravel only |
| **Source on lead at opt-in** | When they submit the opt-in form, set `lead.source_campaign` from UTM (or session). | Laravel only |
| **Known-lead link tracking** | Laravel rewrites outbound email bodies so clicks go through `GET /r/{token}`. The token is signed (no DB token table). Click → decode token → log click + score + pipeline rules → redirect. | Laravel only |
| **Funnel analytics UI** | Tenant-facing: conversion funnel (Visits → Opt-ins → Pipeline → Won) and/or clicks by source. | Laravel (views + controllers) |
| **Pipeline enhancements** | On lead cards: show `source_campaign` and `Link Clicks` summary. Also auto-move pipeline stage based on link keywords and payment status. | Laravel (views + controllers) |
| **Optional automation** | Fire `lead.link_clicked` (or similar) to n8n so workflows can react. | Laravel + n8n |

### 1.2 Out of Scope (for This Blueprint)

- n8n workflow design (only “how to emit an event” is specified).
- Email delivery and template authoring (we only define how tracked URLs are generated and resolved).
- GDPR/consent UI (assume first-party, in-app tracking; add consent later if required).

---

## 2. Architecture Overview

```mermaid
flowchart TB
  subgraph preLead[Pre-lead]
    V[Visitor hits /f/funnelSlug?utm_source=fb]
    FPC[FunnelPortalController::show]
    LogVisit[Log to funnel_visits (always)]
    FPC --> LogVisit
    V --> FPC
  end
  subgraph optIn[Opt-in]
    Form[Submit opt-in form]
    OptInMethod[FunnelPortalController::optIn]
    SetSource[Set lead.source_campaign from UTM/session]
    Form --> OptInMethod --> SetSource
  end
  subgraph postLead[Post-lead]
    Email[Email with tracked link]
    Click[User clicks link]
    R[GET /r/token]
    Resolve[Decode signed token to lead + link]
    LogClick[Log to lead_link_clicks + update lead score/status rules]
    Redirect[Redirect to destination]
    Email --> Click --> R --> Resolve --> LogClick --> Redirect
  end
  subgraph ui[Tenant UI]
    FunnelUI[Funnel analytics page]
    Dashboard[Marketing dashboard widgets]
    Pipeline[Pipeline lead cards]
    FunnelUI --> Dashboard
    Pipeline --> Dashboard
  end
  preLead --> optIn
  postLead --> ui
```

- **Tracking and storage:** 100% Laravel (DB, controllers, redirect route).
- **Automation:** Optional; Laravel emits an event (e.g. `lead.link_clicked`) and sends payload to n8n; n8n workflows are configured separately.

---

## 3. Data Model

### 3.1 New Tables

#### 3.1.1 Funnel visits (pre-lead, source attribution)

Stores **every** funnel landing (even without UTM). This is what powers:
- “How many times the funnel link is clicked/visited”
- “Which source (Facebook/YouTube/etc.) came in”

| Table name | Actual: `funnel_visits` |
|------------|---------------------------|
| `tenant_id` | foreignId → tenants |
| `funnel_id` | foreignId → funnels |
| `funnel_step_id` | nullable foreignId → funnel_steps |
| `utm_source` | string, nullable |
| `utm_medium` | string, nullable |
| `utm_campaign` | string, nullable |
| `referrer` | string, nullable |
| `visited_at` | timestamp |

**Indexes:** `(tenant_id, funnel_id, visited_at)`, `(tenant_id, utm_source, visited_at)`.

#### 3.1.2 Lead link clicks (known leads, post-email engagement)

One row per click on a tracked link that was rewritten in an email for a specific `lead_id`.

| Table name | Actual: `lead_link_clicks` |
|------------|-------------------------------|
| `tenant_id` | foreignId → tenants |
| `lead_id` | foreignId → leads |
| `workflow_id` | nullable (automation workflow) |
| `sequence_id` | nullable (sequence) |
| `sequence_step_order` | nullable |
| `link_name` | nullable (derived from anchor text / URL) |
| `destination_url` | text |
| `click_number` | unsigned int, default 1 |
| `clicked_at` | timestamp |

**Indexes:** `(tenant_id, lead_id)` and `(tenant_id, lead_id, destination_url)`.

> Note: this implementation uses **signed tokens** and does **not** create `tracked_links` or `link_click_tokens` tables.

### 3.2 Models to Create

- **FunnelVisit** — represents `funnel_visits`.
- **LeadLinkClick** — represents `lead_link_clicks`.

### 3.3 Existing Tables Used

- **leads** — already has `source_campaign`. No schema change required for Phase 1; we only set the value on opt-in.
- **funnels**, **funnel_steps** — for funnel_visits.funnel_id, funnel_step_id.

---

## 4. Phase 1 — Pre-Lead Tracking (Case 2)

### 4.1 Migration

1. Create migration `create_funnel_visits_table` with columns from §3.1.1.
2. Run `php artisan migrate`.

### 4.2 FunnelPortalController::show

**File:** [app/Http/Controllers/FunnelPortalController.php](app/Http/Controllers/FunnelPortalController.php)

**Where:** At the end of `show()`, after you have `$funnel` and `$step`, and **before** `return view(...)`.

**Logic:**
1. Read from request:
   - `utm_source`, `utm_medium`, `utm_campaign` (query params).
   - `referrer` from `$request->header('referer')` (optional; truncate to 500 chars).
2. **Always** create one row in `funnel_visits`:
   - `tenant_id` = `$funnel->tenant_id`
   - `funnel_id` = `$funnel->id`
   - `funnel_step_id` = `$step->id`
   - `utm_*` fields and `referrer` can be `null`
3. Only when at least one tracking field exists:
   - store a payload in `session()` under `funnel_utm_{funnel->id}` so `optIn()` can resolve `lead.source_campaign`.

**Performance:** one insert; tracking must never break the funnel UI (try/catch + report).

### 4.3 FunnelPortalController::optIn — Set source_campaign

**File:** [app/Http/Controllers/FunnelPortalController.php](app/Http/Controllers/FunnelPortalController.php)

**Where:** After building `$lead` and before `$lead->save();`.

**Logic:**
1. Resolve source:
   - Prefer `utm_source` from the **current request** query string.
   - Else read stored `utm_source`/`referrer` from `session()` (saved in `show()` when the visitor first landed).
   - Else (fallback) infer a coarse source from the stored `referrer` (e.g. contains `facebook`, `youtube`, `instagram`, `tiktok`).
2. If resolved source is non-empty:
   - `$lead->source_campaign = $resolvedSource;` (normalized, max length 100).

**Persistence:** `source_campaign` is already on `leads`; no migration. Ensure `Lead` model has `source_campaign` in `$fillable` (already present in the project).

### 4.4 Optional: Persist UTM in Session on First Visit

In `show()`, after logging the funnel visit, we store UTM/referrer in session so that moving between funnel steps keeps the same attribution:

- `session()->put("funnel_utm_{$funnel->id}", ['utm_source' => $utmSource, 'utm_medium' => $utmMedium, 'utm_campaign' => $utmCampaign, 'referrer' => $referrer])`
- In `optIn()`, we:
  - prefer `utm_source` from the current request query string
  - else read `utm_source`/`referrer` from `session("funnel_utm_{$funnel->id}")`
  - else infer a coarse source from the stored `referrer`

### 4.5 Phase 1 Reporting (Simple)

- **Total visits:** `FunnelVisit::where('tenant_id', $tenantId)->count()` (optional: date range).
- **Visits by source:** `FunnelVisit::where('tenant_id', $tenantId)->selectRaw('utm_source as source, COUNT(*) as total')->groupBy('utm_source')->orderByDesc('total')->get()`.

You can expose these in a simple report route and Blade view, or reuse in the Funnel analytics page (Phase 3).

---

## 5. Phase 2 — Known-Lead Link Tracking (Case 1)

### 5.1 Migrations
1. `create_lead_link_clicks_table` — store clicks per `lead_id` + `destination_url`.

Run migrations.

### 5.2 Token Lifecycle

Implemented approach in this project:

1. **Token is signed, not stored**
   - `app/Services/LeadLinkTrackingService.php` generates a signed token that includes claims:
     - `tenant_id`, `lead_id`
     - `workflow_id`, `sequence_id`, `sequence_step_order` (when available)
     - `link_name` (derived from anchor text or the URL itself)
     - `destination_url`

2. **Token is created while compiling automation email bodies**
   - `app/Http/Controllers/Api/TenantAutomationRunController.php` rewrites email HTML for:
     - `send_email` actions
     - `start_sequence` email steps
   - If the email body has `<a href="https://...">`, it rewrites anchor `href` to `/r/{token}`.
   - If the email body contains plain `https://...` without `<a>` tags, it wraps the plain URLs into `<a>` tags and rewrites them.

3. **Resolve token on click**
   - `GET /r/{token}` decodes the token (no DB lookup), logs the click in `lead_link_clicks`, applies pipeline rules, then redirects to `destination_url`.

**Token format:** signed payload (HMAC/secret), URL-safe. No uniqueness table required.

### 5.3 Redirect Route and Controller

**Route:** `GET /r/{token}` — public, no auth.

**Controller:** `LinkTrackingController::redirect` (public redirect handler).

Logic:
1. Decode the signed token (`app/Services/LeadLinkTrackingService.php`). If invalid, redirect safely.
2. Load the `Lead` by `tenant_id` + `lead_id`.
3. Insert into `lead_link_clicks`:
   - `destination_url`
   - derived `link_name`
   - `click_number` = count(existing clicks for same tenant+lead+destination_url) + 1
4. Increment lead score (+10) and create a “Scoring” activity note.
5. Apply pipeline movement rules (next section).
6. Redirect to `destination_url`.

**Performance:** token decode + one DB insert + redirect.

### 5.4 Pipeline Movement Rules (link clicks + payment)

The redirect controller applies the “final combined strategy”:

1. **First click (any tracked link):** `new → contacted`
2. **High intent click:** when the *lead status is* `new` or `contacted` AND `link_name` contains:
   - `book`, `call`, `demo`, `schedule`
   - then status becomes `proposal_sent`
3. **More clicks:** no further status changes (unless the status is updated elsewhere)
4. **Payment:** when payment is `paid` (`payment.paid`) => `closed_won`
5. **Lost deal:** `payment.failed` does not auto-set `closed_lost` (manual only)

Keyword matching detail (important for your team):
- `link_name` comes from the **email clickable text**:
  - if the email has HTML `<a>...</a>`, we use the anchor text
  - if the email only contains plain URLs, we wrap the URL into an `<a>` and use the URL-derived name

### 5.5 Where Tokens Are Created

Tokens are created on-the-fly when Laravel compiles automation actions for n8n:
- `app/Http/Controllers/Api/TenantAutomationRunController.php`
  - rewrites `send_email.body`
  - rewrites `start_sequence.steps[].body` for email steps
- rewriting is handled by `app/Services/LeadLinkTrackingService.php`:
  - if the body contains `<a href="https://...">`, it rewrites those anchor `href`s
  - if the body contains plain `https://...` URLs (no `<a>`), it wraps and rewrites them

### 5.6 Tracked Links Registry

Not required in this implementation. Instead of a `tracked_links` registry, the system rewrites **every** `http://`/`https://` link found in the email HTML body (or plain URL text) into a tracked `/r/{token}` redirect.

---

## 6. Phase 3 — Tenant-Facing UI

### 6.1 Funnel Analytics Page (Conversion Funnel)

**Current state:** not implemented as a dedicated “Visits → Opt-ins → Pipeline → Won” conversion page in this repo.

**What you have right now:**
- funnel landings are logged in `funnel_visits`
- marketing dashboard shows **Funnel Visits by source** (UTM breakdown)
- lead edit page shows **Link Clicks** + `source_campaign`

Optional next step: build the full conversion funnel UI once you decide how to associate `FunnelVisit` rows to created `Lead` records.

### 6.2 Marketing Dashboard Add-Ons

- **Widget: “Funnel link visits by source”** — Same query as §4.5 (visits by utm_source). Display as a small table or bar chart on [dashboard/marketing](resources/views/dashboard/marketing.blade.php). Reuse `DashboardController::marketing()` and pass a new variable, e.g. `$visitsBySource`.
- **Notes:** In the current implementation, link-click performance is shown on the **Lead edit page** (not on the marketing dashboard).

### 6.3 Pipeline Lead Cards
**File:** `resources/views/leads/edit.blade.php`

**Enhancement (implemented):**
- Source/Campaign dropdown now keeps whatever value is already stored on the lead (even if it’s not in the default list).
- A **Link Clicks** card is displayed:
  - **Top Links** (grouped by `link_name`, count clicks)
  - **Recent Clicks** (last ~15 clicks with time + destination URL)

---

## 7. Optional — Automation Integration

Optional: let n8n react to link-clicks.

In this repo, link clicks already:
- insert into `lead_link_clicks`
- update lead score
- auto-move pipeline stage (new/contacted/high-intent/payment)

If you still want an external n8n workflow, you can add an emitted event right after the redirect controller logs the click, e.g. `lead.link_clicked`, with a payload like:
- `tenant_id`, `lead_id`
- `link_name`, `destination_url`
- `clicked_at`, `click_number`

Then add a branch in the SaaS Event Router and document the payload contract in [automation-n8n-implementation.md](automation-n8n-implementation.md).

---

## 8. Files Checklist

### 8.1 Phase 1

| Action | File / artifact |
|--------|------------------|
| Create | `database/migrations/YYYY_MM_DD_HHMMSS_create_funnel_visits_table.php` |
| Create | `app/Models/FunnelVisit.php` |
| Modify | `app/Http/Controllers/FunnelPortalController.php` — log visit in `show()`, set source in `optIn()` |
| Create (optional) | `app/Http/Controllers/AnalyticsController.php` or add method in `DashboardController` for simple “visits by source” report |
| Create (optional) | View for “visits by source” or embed in existing marketing dashboard |

### 8.2 Phase 2
| Action | File / artifact |
|--------|------------------|
| Create | Migration: `database/migrations/2026_03_20_000002_create_lead_link_clicks_table.php` |
| Create | Model: `app/Models/LeadLinkClick.php` |
| Create | `app/Http/Controllers/LinkTrackingController.php` — decode token, log click, apply pipeline rules, redirect |
| Create | `app/Services/LeadLinkTrackingService.php` — rewrite email links + generate signed tokens |
| Modify | `app/Http/Controllers/Api/TenantAutomationRunController.php` — rewrite `send_email` + `start_sequence` email bodies |
| Add route | `Route::get('/r/{token}', [LinkTrackingController::class, 'redirect'])->name('link.track.redirect');` |
| Modify | `app/Http/Controllers/PaymentController.php` — payment.paid => `closed_won` |
| Modify | `app/Http/Controllers/FunnelPortalController.php` — funnel paid => `closed_won` |

### 8.3 Phase 3
| Action | File / artifact |
|--------|------------------|
| Modify | `app/Http/Controllers/DashboardController.php` — add Funnel Visits by source queries |
| Modify | `resources/views/dashboard/marketing.blade.php` — display Funnel Visits KPI + chart |
| Modify | `app/Http/Controllers/LeadController.php` — load `recentLinkClicks` + `topLinkClicks` |
| Modify | `resources/views/leads/edit.blade.php` — display “Link Clicks” card + keep `source_campaign` value |

### 8.4 Optional Automation

| Action | File / artifact |
|--------|------------------|
| Optional | `LinkTrackingController` — after logging the click, optionally emit a new automation event like `lead.link_clicked` (not required for pipeline movement because pipeline rules already run in Laravel). |
| Document | Update [automation-n8n-implementation.md](automation-n8n-implementation.md) with the payload contract for `lead.link_clicked` if you implement the n8n branch. |

---

## 9. Testing and Acceptance

### 9.1 Phase 1

- Visit `/f/{funnelSlug}?utm_source=facebook`. Assert one row in `funnel_visits` with utm_source = 'facebook', correct tenant_id and funnel_id.
- Visit without UTM: a row still exists, but `utm_source`/`referrer` are `null` (your reporting should show it as “Unspecified” or similar).
- Submit opt-in from a page that was loaded with `?utm_source=youtube`. Assert lead has `source_campaign` = 'youtube'.
- Report: “Visits by source” shows Facebook and YouTube with correct counts.

### 9.2 Phase 2
- Create/run an automation (sequence email or send_email) that includes a real `https://...` link (preferably inside an HTML `<a href="...">`).
- Click the link (this opens `GET /r/{token}` behind the scenes). Assert:
  - a row is inserted into `lead_link_clicks`
  - lead score increments (+10) and a scoring activity note is created
  - pipeline stage updates according to rules:
    - first tracked click when lead is `new` => `contacted`
    - high-intent link text (keywords `book/call/demo/schedule`) when old status is `new` or `contacted` => `proposal_sent`
    - after stage becomes `contacted`/`proposal_sent`, more clicks do not advance further
- Payment tests:
  - create a `payment` with status `paid` for a lead => lead becomes `closed_won`
  - create a `payment` with status `failed` => lead does NOT auto-move to `closed_lost` (manual only)

### 9.3 Phase 3
- Marketing dashboard shows Funnel Visits by source.
- Lead edit page shows:
  - `source_campaign`
  - Link Clicks card (Top Links + Recent Clicks).

---

## 10. Order of Implementation (Summary)

1. **Phase 1:** Migration `funnel_visits` + model → `FunnelPortalController::show` (log visit) → `FunnelPortalController::optIn` (set source_campaign) → simple report or widget (visits by source).
2. **Phase 2:** Migration `lead_link_clicks` + service to rewrite outbound email bodies + signed redirect route → log clicks + apply pipeline movement rules + payment paid => closed_won.
3. **Phase 3:** Dashboard widgets (funnel visits by source) + Lead edit UI (link clicks + source dropdown behavior).
4. **Optional:** Emit `lead.link_clicked` in redirect controller; document and add n8n branch.

This blueprint is self-contained so that development can proceed in the order above with minimal ambiguity. Update this doc if you change table names, add columns, or introduce new endpoints.
