# SaaS Sales and Marketing Funnel System - Delivery Blueprint

## 0. Purpose
This blueprint is the full implementation plan from current state to production-ready SaaS.

Current baseline (as of 2026-02-19):
- 1. Multi-tenant + roles: Fully done
- 2. CRM foundation + user structures: Fully done
- 3. Funnel builder + lead capture: Initial
- 4. Automation + communication: Not done
- 5. Checkout/subscriptions/payments: Initial
- 6. Analytics + SaaS business controls: Initial

## 1. End-State Definition (Project Done Criteria)
The project is considered complete when all conditions are true:
- All modules are interconnected end-to-end: funnel event -> CRM lead lifecycle -> automation -> payment/subscription -> analytics.
- Customer journey supports acquisition, conversion, payment, onboarding, retention.
- Role-based UX is complete and permission-safe for Super Admin, Account Owner, Marketing Manager, Sales Agent, Finance, Customer.
- Billing and subscription lifecycle is fully automated with gateway webhooks and recovery logic.
- Production quality is verified by feature/integration tests, monitoring, backups, and rollback plans.

## 2. Execution Principles
- Ship in small vertical slices with acceptance criteria per slice.
- No new feature without role permissions and audit logging.
- Every workflow change must include test coverage and user-visible status messaging.
- Keep tenant isolation enforced in every query and action.

## 3. Phase Plan (Start to Finish)

### Phase A - Stabilize Foundations (1-2 weeks)
Goal: lock quality of already-built modules before adding major features.

Steps:
1. Permission and tenancy hardening pass.
- Verify all controllers, policies, and routes enforce tenant ownership.
- Verify no cross-tenant record access via IDs or URLs.
- Add/complete authorization tests for each role.

2. Data integrity hardening.
- Add DB constraints/indexes for key relations: leads, payments, funnels, users, tenants.
- Normalize status values (e.g., `closed_won`, `closed_lost`) at model/service level.
- Add migration guard notes and rollback docs.

3. Baseline QA gate.
- Create smoke tests for login, role redirects, dashboard load, CRUD basics.
- Define bug severity matrix (P1-P4) and release blockers.

Acceptance criteria:
- No critical cross-tenant access issues.
- Role permission matrix passes manual and automated checks.
- Smoke test suite passes in CI/local.

---

### Phase B - Complete Funnel Builder + Lead Capture (2-3 weeks)
Goal: move stage 3 from Initial -> Fully done.

Steps:
1. Funnel editor completion.
- Step templates: landing, opt-in, sales, checkout, upsell, downsell, thank-you.
- Better content editing controls and validation for required fields.
- Publish workflow with version-safe updates.

2. Lead capture completion.
- Add lead tags and custom fields (tenant-scoped schema).
- Funnel form mapping to standard + custom lead fields.
- Source attribution capture (campaign/source/medium).

3. Funnel analytics minimum.
- Track step visits, drop-off, opt-in conversion, checkout conversion.
- Show per-funnel conversion summary for owners/marketing.

4. Security and abuse controls.
- Basic rate limiting and spam protection for public forms.
- Validate and sanitize all form payloads.

Acceptance criteria:
- Funnel can be built, published, and receives real leads with tags/custom fields.
- Conversion metrics are visible per funnel.
- Public funnel forms are validated and abuse-protected.

---

### Phase C - Build Automation + Communications (2-3 weeks)
Goal: move stage 4 from Not done -> Fully done.

Steps:
1. Event bus/domain events.
- Define core events: `lead_created`, `lead_updated`, `lead_status_changed`, `payment_paid`, `payment_failed`, `subscription_renewed`.
- Centralize event dispatching from controllers/services.

2. Workflow engine (MVP rules).
- Trigger -> condition -> action model.
- Time-delay actions and simple branching.
- Execution logs and retry handling.

3. Email sequence infrastructure.
- Sequence builder: immediate + delayed emails.
- Queue-backed sending, template support, unsubscribe-safe links.
- Delivery/open/click logging to lead activity timeline.

4. n8n/webhook integration.
- Outbound webhook for key events.
- Optional inbound webhook endpoint with signature verification.
- Retry + dead-letter behavior for failed automation calls.

Acceptance criteria:
- A lead can automatically receive scheduled emails after trigger events.
- Workflow executions are auditable and retryable.
- External automation (n8n) can receive reliable event payloads.

---

### Phase D - Complete Payments + Subscriptions (2-3 weeks)
Goal: move stage 5 from Initial -> Fully done.

Steps:
1. Payment gateway integration.
- Integrate one real gateway (primary) with webhook verification.
- Add transaction status mapping (`paid`, `pending`, `failed`, `refunded`).

2. Checkout workflows.
- One-time payment checkout end-to-end.
- Subscription checkout flow with plans, trial options, renewal dates.

3. Billing operations.
- Coupons/discounts with limits/expiry.
- Failed payment recovery (retry schedule + reminder notifications).
- Invoice generation and downloadable billing history.

4. Finance + customer visibility.
- Finance dashboard for collection tracking and outstanding amounts.
- Customer portal for subscription status, invoices, payment history.

Acceptance criteria:
- Real transactions complete through gateway and reconcile in system records.
- Subscriptions renew/cancel correctly with webhook-driven status updates.
- Failed payments trigger recovery flow and user notifications.

---

### Phase E - Advanced Analytics + SaaS Controls (2-3 weeks)
Goal: move stage 6 from Initial -> Fully done.

Steps:
1. Analytics completion.
- Funnel conversion analytics (step-to-step drop-off).
- Revenue per funnel/campaign.
- Abandoned checkout/cart metrics.
- Platform metrics: MRR, churn, ARPU, tenant growth.

2. SaaS business controls.
- Tiered plans and feature entitlements.
- Usage limits (users, leads, funnels, automations, emails).
- Trial lifecycle (start, warning, expiration, grace period).
- Subscription lifecycle controls (upgrade/downgrade/proration rules).

3. Role-tailored dashboard refinement.
- Keep top cards to 4-6 KPIs max.
- Add one trend chart + one distribution chart + one actionable table per role.
- Ensure data consistency across all role dashboards.

Acceptance criteria:
- Decision-grade analytics are available for tenant and super-admin views.
- Plan limits are enforceable and user-visible.
- Trial and subscription states transition correctly by policy.

---

### Phase F - Production Readiness and Launch (1-2 weeks)
Goal: operationally ready system.

Steps:
1. Test completion.
- Feature tests: assignment, scoring, pipeline, payments, subscriptions, automation.
- Integration tests: funnel -> lead -> automation -> payment -> analytics chain.
- Regression test pack for role permissions and tenant isolation.

2. Non-functional hardening.
- Performance profiling for key queries and dashboards.
- Queue reliability checks and worker supervision.
- Security checklist: validation, authz/authn, CSRF, file upload checks.

3. Observability and operations.
- Centralized error logging and alert thresholds.
- Backup/restore rehearsal and incident runbook.
- Deployment checklist and rollback procedure.

4. UAT and sign-off.
- UAT scripts per role.
- Track and close all P1/P2 defects.
- Final release approval with known limitations list.

Acceptance criteria:
- All high-severity defects resolved.
- End-to-end tests and UAT pass.
- Launch checklist signed and rollback tested.

## 4. Role-Based UAT Checklist (Minimum)
- Super Admin: tenant lifecycle, owner suspension/reactivation, platform KPIs, user/lead visibility.
- Account Owner: team management, lead assignment, funnel publish, revenue view, company/profile settings.
- Marketing Manager: campaign lead generation, funnel performance, lead segmentation.
- Sales Agent: assigned leads, pipeline progression, activity logging, conversion outcomes.
- Finance: payment recording, status reconciliation, outstanding tracking, reporting.
- Customer: portal access, profile, subscription status, invoice/payment history.

## 5. Definition of Done per Stage
- Fully done means: feature complete + permission-safe + tested + documented.
- Initial means: usable MVP exists but lacks complete coverage, integrations, or test hardening.
- Not done means: no production-usable workflow yet.

## 6. Recommended Implementation Order From Today
1. Complete Stage 3 (tags/custom fields + full lead capture analytics).
2. Complete Stage 4 (automation engine + email sequences + event triggers).
3. Complete Stage 5 (real gateway + subscriptions + recovery).
4. Complete Stage 6 (advanced analytics + SaaS controls).
5. Run Phase F production readiness and UAT sign-off.

## 7. Risk Register (Top Project Risks)
- Inconsistent status/state naming across modules causes analytics/report errors.
- Missing webhook idempotency causes duplicate payments or duplicate automation actions.
- Weak tenant scoping introduces data leakage risk.
- Delayed test coverage increases regression risk as modules become interconnected.

Mitigations:
- Central enums/constants for statuses and stage mappings.
- Idempotency keys for webhook and automation handlers.
- Mandatory tenancy guards/policies in all data-access points.
- CI gate requiring feature tests for changed critical flows.

## 8. Success Metrics for Project Completion
- Conversion funnel visibility: visit -> opt-in -> checkout -> paid, with reliable stage percentages.
- Operational reliability: low failed job rate and fast recovery for webhook/queue errors.
- Billing quality: successful renewals, lower failed-payment backlog.
- Business insights: MRR/churn/ARPU dashboards trusted by stakeholders.

## 9. Final Note
The system is currently strong in foundation and core CRM, with practical funnel/payment MVP links. Finishing automation, real billing, and advanced SaaS controls will move it from MVP/early production to full production-grade SaaS.