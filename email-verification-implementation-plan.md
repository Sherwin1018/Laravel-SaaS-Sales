# Email Verification — Team Guide (What We Built & How It Works)

> **Location:** Project root (`email-verification-implementation-plan.md`) so it is tracked in Git.

This document explains **what was implemented** for email verification in the Laravel SaaS Sales Funnel System, **how it behaves**, and **which docs** the team should read for review.

---

## 1. Two separate features (do not confuse them)

| Feature | Who | Purpose |
|--------|-----|---------|
| **App user email verification** | Tenant team (Account Owner, Marketing Manager, Sales Agent, Finance, etc.) | Security: prove the login email is owned by the user before full app access. |
| **Lead double opt-in (DOI)** | Funnel visitors (leads) | Marketing: confirm the email address before `funnel.opt_in` automation runs in n8n. |

They use **different** code paths: Laravel `MustVerifyEmail` for users; signed URLs + `Lead` notifications for leads.

---

## 2. Phase A — App users (implemented)

### What we did

- **`User` model** implements `Illuminate\Contracts\Auth\MustVerifyEmail`.
- **Routes** (in `routes/web.php`):
  - `GET /email/verify` — “verify your email” notice (auth).
  - `GET /email/verify/{id}/{hash}` — signed link (auth + `signed` middleware).
  - `POST /email/verification-notification` — resend (auth + throttle).
- **Controller:** `App\Http\Controllers\Auth\EmailVerificationController`.
- **View:** `resources/views/auth/verify-email.blade.php` (aligned with login styling).
- **Middleware:** `verified` registered in `bootstrap/app.php` and applied to admin/tenant dashboards, profile, CRM, funnels, automation, payments, customer dashboard — **not** on public funnel routes or verification routes.
- **Super Admin → new tenant / Account Owner:** `TenantController` sets `email_verified_at` after create (trusted onboarding).
- **Account Owner → new team member:** `UserController@store` calls `sendEmailVerificationNotification()`; if mail fails, user is still created and a **warning** toast is shown (see troubleshooting).
- **Existing users:** migration `2026_03_21_014039_add_email_verified_at_to_existing_users.php` backfills `email_verified_at` for users where it was null so nobody is locked out on deploy.

### How it behaves

- Unverified users can log in but hit **`verified`** middleware on protected pages → redirect to `/email/verify`.
- After clicking the link in the email → `email_verified_at` set → redirect to app home (`/`).

---

## 3. Phase B — Lead double opt-in (implemented)

### Data model

- **`leads.email_verified_at`** — set when the lead clicks the verification link in the email.
- **`funnels.require_double_opt_in`** — boolean, default `false`. When `true`, opt-in uses DOI for that funnel.

**Migrations:** `2026_03_21_023922_add_lead_double_opt_in_fields.php`

### Lead model

- `Notifiable` + `routeNotificationForMail()` returns lead email.
- `hasVerifiedEmail()` / `getEmailForVerification()` for verification logic.

### Funnel settings (tenant UI)

- **Create funnel:** checkbox “Require email confirmation (double opt-in)”.
- **Edit funnel (builder):** **DOI** checkbox in the top bar — **must click “Save Tags”** to persist (same `<form>` as `funnels.update`). The big green **Save** button only saves builder layout, **not** DOI.

### Opt-in flow when DOI is ON

1. Visitor submits opt-in → `FunnelPortalController::optIn` saves/updates the lead.
2. **`funnel.opt_in` is NOT dispatched** yet; no +20 “Form Submitted” scoring on that path (DOI path).
3. `LeadVerifyEmail` notification is sent (synchronous mail send in request).
4. Redirect to **`GET /f/{funnelSlug}/confirm-email`** (“Check your email” page).

**Routing note:** `GET /f/{funnelSlug}/confirm-email` is registered **before** `GET /f/{funnelSlug}/{stepSlug?}` in `routes/web.php` so `confirm-email` is not treated as a step slug (404 fix).

### When the lead clicks the email link

- **Route:** `GET /funnel/verify` (signed URL; query params include `id`, `hash`, `funnel_id`, `opt_in_step_id`).
- **`LeadVerificationController@verify`:**
  - Validates signature + hash vs `sha1(lead.email)`.
  - Sets `email_verified_at`, +20 score, activity “Email Verified”.
  - Dispatches **`funnel.opt_in`** via `AutomationWebhookService` (same event as non-DOI, so **n8n workflows do not need a new Switch branch**).
  - Puts `funnel_lead_{funnelId}` in session for later steps.
  - **Redirects to the funnel step after the opt-in step** they submitted (uses `opt_in_step_id` in the signed URL). If no next step, goes to last active step; fallback is `/funnel/verified` success page.

### When DOI is OFF

- Same as before: immediate scoring + activity + `dispatchFunnelOptInWebhook` + redirect to next step.

### One-time verification

- First successful verify: sets `email_verified_at`, runs n8n payload once.
- **Second click** on an old link: `hasVerifiedEmail()` is true → “already verified” UX; **does not** dispatch `funnel.opt_in` again.
- Link expiry: Laravel signed URL TTL (default from `auth.verification.expire`, typically 60 minutes).

### Optional: strict “cannot browse funnel until verified”

- **Not implemented by default.** A lead could still open later step URLs if they know the slug; **automation** is what we gate. Ask if the product should add middleware to block portal steps until `email_verified_at` when DOI is on.

---

## 4. Debugging / logging (DOI)

- `FunnelPortalController::optIn` logs:
  - `DOI: sending lead verification email` (with `lead_id`, `lead_email`, `funnel_id`).
  - `DOI: lead verification email notify() returned` after `notify()`.

Check `storage/logs/laravel.log` if smtp4dev or inbox looks empty but the confirm page appears.

---

## 5. Mail & local dev

- Configure `MAIL_*` in `.env` (e.g. smtp4dev: `MAIL_HOST=127.0.0.1`, `MAIL_PORT=25`, `MAIL_ENCRYPTION=null`).
- Remove **duplicate** `MAIL_MAILER` lines in `.env` (last one wins — can confuse debugging).
- After `.env` changes: `php artisan config:clear`.
- **`APP_URL`** must match the URL used in verification links (users and signed lead URLs).

---

## 6. Interaction with n8n

| Trigger | When `funnel.opt_in` fires |
|---------|-----------------------------|
| DOI **off** | On opt-in form submit |
| DOI **on** | When lead clicks verification link (after `email_verified_at` is set) |

Team n8n router can keep routing on `event === funnel.opt_in` without a separate `lead.email_verified` event.

---

## 7. Docs for team review

| Document | What it covers |
|----------|----------------|
| **`automation-architecture.md`** (repo root) | Automation overview; `funnel.opt_in` may fire on **verify** when DOI is on (not only on form submit). |
| **`docs/workflow-saas-event-router-funnel-opt-in.md`** | SaaS event router for **`funnel.opt_in`** — primary doc when wiring or debugging n8n for opt-in. |
| **`docs/automation-n8n-implementation.md`** | Technical automation / webhook reference. |
| **`docs/automation-opt-in-and-sequences-overview.md`** | High-level opt-in + sequences. |
| **`docs/automation-user-flow-and-scenarios.md`** | User flows and scenario walkthroughs. |
| **`docs/workflow-n8n-start-sequence-branch-guide.md`** | n8n `start_sequence` branch. |
| **`docs/workflow-n8n-send-email-and-notify-guide.md`** | n8n `send_email` / `notify_sales` branches. |
| **`docs/workflow-lead-created-automation-guide.md`** | Lead-created workflows (related context). |
| **`docs/workflow-lead-status-changed-automation-guide.md`** | Lead status–changed workflows. |

**Optional / local drafts** (may appear as untracked): `docs/automation-feature-blueprint.md`, `docs/workflow-funnel-opt-in-automation-guide.md` — merge into the list above when committed.

If your team’s `.gitignore` excludes `docs/`, copy those files from a teammate or adjust ignore rules for documentation.

---

## 8. Key files (implementation map)

| Area | Path |
|------|------|
| User verification | `app/Models/User.php`, `app/Http/Controllers/Auth/EmailVerificationController.php`, `resources/views/auth/verify-email.blade.php` |
| Middleware alias | `bootstrap/app.php` (`verified`) |
| Routes | `routes/web.php` (verification + funnel portal + DOI routes **order** matters) |
| Tenant / team create | `app/Http/Controllers/TenantController.php`, `app/Http/Controllers/UserController.php` |
| Lead DOI opt-in | `app/Http/Controllers/FunnelPortalController.php` (`optIn`) |
| Lead verify + redirect | `app/Http/Controllers/LeadVerificationController.php` |
| Lead email | `app/Notifications/LeadVerifyEmail.php` |
| Lead model | `app/Models/Lead.php` |
| Funnel flag | `app/Models/Funnel.php`, `FunnelController@store` / `@update`, `resources/views/funnels/create.blade.php`, `resources/views/funnels/edit.blade.php` |
| Webhook payload | `app/Services/AutomationWebhookService.php` (unchanged contract; timing changes in controller) |

---

## 9. Summary

| Audience | Status | Mechanism |
|----------|--------|-----------|
| **App users** | Done | `MustVerifyEmail` + `verified` middleware + verification routes + pre-verify Account Owner on tenant create + team invite sends verification |
| **Leads (DOI)** | Done | Per-funnel `require_double_opt_in`; signed `/funnel/verify` link; `funnel.opt_in` after verify; post-verify redirect into funnel next step |

---

## 10. Open / optional follow-ups

- [ ] Feature tests for verification + DOI paths.
- [ ] **Resend lead verification** from confirm page (throttled), if product wants it.
- [ ] Optional: block all funnel portal steps until verified when DOI is on (stricter UX).
- [ ] Add a short “DOI timing” note to `automation-architecture.md` or `docs/workflow-saas-event-router-funnel-opt-in.md` if either is missing it.

---

## 11. Quick file review (short explanation)

- `app/Models/User.php` — Turned on built-in Laravel email verification for app users.
- `app/Http/Controllers/Auth/EmailVerificationController.php` — Handles verify notice, signed verification link, and resend action.
- `resources/views/auth/verify-email.blade.php` — Added UI page telling unverified users to verify email.
- `bootstrap/app.php` — Added `verified` middleware alias used on protected app routes.
- `routes/web.php` — Added verification routes and fixed route order so `/f/{funnelSlug}/confirm-email` does not 404.
- `app/Http/Controllers/TenantController.php` — Marks new Account Owner as pre-verified during tenant creation.
- `app/Http/Controllers/UserController.php` — Sends verification mail to team members; keeps create flow safe if mail fails.
- `resources/views/layouts/admin.blade.php` — Added warning toast support for non-blocking mail errors.
- `public/css/admin-dashboard.css` — Added warning toast styling.
- `database/migrations/2026_03_21_014039_add_email_verified_at_to_existing_users.php` — Backfilled old users to avoid lockout after rollout.
- `database/migrations/2026_03_21_023922_add_lead_double_opt_in_fields.php` — Added `leads.email_verified_at` and `funnels.require_double_opt_in`.
- `app/Models/Lead.php` — Added lead email verification helpers and mail notification routing.
- `app/Models/Funnel.php` — Added persisted boolean setting for per-funnel DOI.
- `app/Http/Controllers/FunnelController.php` — Saves DOI setting on create and edit.
- `resources/views/funnels/create.blade.php` — Added DOI checkbox in create funnel form.
- `resources/views/funnels/edit.blade.php` — Added DOI checkbox in builder top form (saved via **Save Tags**).
- `app/Http/Controllers/FunnelPortalController.php` — When DOI is ON, sends verification email first and delays `funnel.opt_in`.
- `app/Notifications/LeadVerifyEmail.php` — Generates signed verification link containing lead and funnel context.
- `app/Http/Controllers/LeadVerificationController.php` — Verifies lead, dispatches `funnel.opt_in`, then redirects to next funnel step.
- `resources/views/funnels/portal/confirm-email.blade.php` — Added “check your inbox” page after opt-in submit.
- `resources/views/funnels/portal/verified.blade.php` — Added fallback success page after lead verification.
