# SaaS Sales and Marketing Funnel System

Last updated: May 6, 2026

This file is the single source of truth for the current project status, preserved findings, major gaps, rollout priorities, architecture rules, and the path from the current build to approval-ready completion.

## 1. Project Summary

This project is a multi-tenant SaaS Sales and Marketing Funnel System where multiple client businesses operate independently on one platform.

The intended end-to-end flow is:

- funnel -> CRM -> automation -> payment -> analytics

The target product includes:

- secure multi-tenant architecture
- role-based operations
- CRM lead management
- funnel creation and publishing
- one-time and subscription payments
- analytics and reporting
- SaaS billing controls
- automation through n8n

## 2. Current Executive Assessment

As of May 6, 2026, the codebase is well beyond a rough prototype, but it is not yet a confident 85% to 100% complete in approval-ready terms.

Practical overall assessment:

- System build completeness: about 75%
- Demo or approval readiness: about 65% to 70%

Why:

- major foundations are already implemented
- the remaining work is concentrated in the hardest layers
- the main gaps are integration depth, analytics correctness, automation maturity, billing edge cases, SaaS rule enforcement, and reliable verification

Repository maturity signals observed in the workspace:

- around 80 Laravel migrations
- 18 feature test files
- a large route surface already present
- implemented models, controllers, services, dashboards, billing, funnel, and payout-related modules

## 3. Module-by-Module Completion Estimate

| Module | Estimated Completion | Status |
|---|---:|---|
| Multi-tenant + Roles + Access | 85% | Strong foundation, but still needs approval-grade verification and consistency hardening |
| CRM / Lead Management | 80% | Core CRUD, assignment, scoring, pipeline, and activities exist; custom-field maturity and deeper reporting are still finishing |
| Funnel Builder | 78% | Builder, portal steps, publishing, reviews, and analytics screens exist; end-to-end tracking and public hardening are still incomplete |
| Automation / n8n Integration | 60% | Good webhook foundation and inbound endpoints exist, but real-time event coverage and workflow maturity are still incomplete |
| Billing / Payments / Subscriptions | 72% | Plans, billing views, PayMongo foundation, payouts, commissions, and lifecycle pieces exist; production-grade lifecycle confidence is not there yet |
| Analytics / Reporting | 68% | Dashboard and reporting services exist, but advanced metrics are still a major blocker |
| SaaS Controls / Plan Enforcement | 70% | Plan models, middleware, usage summaries, and enforcement pieces exist, but the system still feels partial rather than fully locked down |
| QA / Feature Coverage / Approval Readiness | 45% | The suite exists, but current environment validation is blocked and confidence is still low |

## 4. Strong Areas Already Present

The current system already has meaningful implementation across these areas:

- multi-tenant foundation
- role-based dashboards and access structure
- basic CRM lead management
- lead assignment and lead activity logging
- sales pipeline foundation
- basic lead scoring support
- funnel builder UI and public funnel routes
- landing, opt-in, sales, checkout, upsell, downsell, and thank-you step support
- payment and PayMongo foundation
- trial and subscription foundation
- billing and plan pages
- dashboard foundation for owners and admins
- payout account and commission-related foundations
- automation webhook endpoints and some outbound event support

## 5. Biggest Current Gaps

These are the main incomplete areas preserved from the previous planning files and the repo review:

### Funnel analytics completion

Still needs:

- full visit-to-paid event tracking
- stronger per-step conversion measurement
- abandoned checkout logic
- stronger publish-readiness validation
- public route throttling and anti-spam hardening

### Automation event completeness

Still needs:

- stronger outbound event contract
- real-time funnel events sent to n8n
- reliable idempotency keys
- email sequence workflows
- SMS workflows
- delay and conditional workflow support
- workflow logs and retry behavior

### Trial, billing, and subscription lifecycle hardening

Still needs:

- cleaner tenant billing states
- upgrade, downgrade, renew, cancel handling
- failed payment consequence rules
- recovery flow safety
- duplicate webhook protection
- clearer reconciliation behavior

### Advanced analytics formulas

Still needs:

- conversion per funnel
- revenue per funnel
- abandoned cart rate
- MRR
- churn
- ARPU
- usage metrics
- role-safe and date-consistent formulas

### Test hardening and environment readiness

Still needs:

- better feature and integration coverage
- end-to-end flow protection
- reliable local test environment support
- passable database-backed test execution

Current test environment note:

- `php artisan test` could not provide trustworthy completion confidence in this environment because SQLite driver support was missing
- the observed issue was effectively missing `pdo_sqlite` / `sqlite3` support for in-memory test execution

## 6. What Must Be Done To Reach 85% To 100%

This section converts the findings into a practical completion path.

### Multi-tenant + Roles + Access

Current: 85%

To reach 90%:

- standardize status naming across modules
- audit route and controller access rules
- confirm tenant isolation on cross-record access
- complete more role and tenant feature tests

To reach 100%:

- full policy coverage
- no cross-tenant leaks in manual or automated checks
- passing access regression suite

### CRM / Lead Management

Current: 80%

To reach 85%:

- tenant-scoped custom fields
- better tag normalization
- configurable score events
- stage history and audit trail

To reach 100%:

- lead field mapping from funnel data
- full scoring configuration
- stronger pipeline analytics
- test protection for CRUD, scoring, assignment, stage changes, and tenant scoping

### Funnel Builder

Current: 78%

To reach 85%:

- step view tracking
- opt-in tracking
- checkout-start tracking
- paid conversion tracking
- stronger publish validation

To reach 100%:

- abandoned checkout metric
- safer public submission hardening
- reliable duplicate-submission handling
- upsell and downsell conversion analytics
- full funnel-specific feature coverage

### Automation / n8n Integration

Current: 60%

To reach 85%:

- define the final event contract
- send funnel opt-in, checkout start, payment paid, and abandoned checkout events outbound
- implement email sequence and reminder workflows
- add delivery logging and retry logic

To reach 100%:

- SMS workflows
- time delays
- conditional branching
- idempotent replay protection
- auditable automation history for demo and approval

### Billing / Payments / Subscriptions

Current: 72%

To reach 85%:

- stronger subscription state handling
- failed payment recovery flow
- clearer billing state transitions
- safer webhook and reconciliation logic

To reach 100%:

- mature renew, cancel, upgrade, downgrade logic
- clearer tenant lifecycle enforcement
- stable receipt, payout, and commission interactions
- reliable billing regression tests

### Analytics / Reporting

Current: 68%

To reach 85%:

- finish funnel conversion reporting
- add revenue-per-funnel reporting
- add abandoned checkout reporting
- improve owner and admin metric summaries

To reach 100%:

- documented formulas for MRR, churn, ARPU, and usage metrics
- consistent date filtering
- export reliability
- verified dashboard calculation tests

### SaaS Controls / Plan Enforcement

Current: 70%

To reach 85%:

- enforce user, lead, and funnel limits consistently
- show current usage versus current limit clearly
- add upgrade prompts when blocked

To reach 100%:

- reliable backend feature gates
- tenant-safe usage summaries
- old over-limit tenant handling rules
- full enforcement test coverage

### QA / Feature Coverage / Approval Readiness

Current: 45%

To reach 85%:

- repair local test database support
- run feature tests successfully
- add missing tests for CRM, funnel, billing, analytics, and plan enforcement

To reach 100%:

- full integration validation from funnel to analytics
- repeatable environment setup
- regression confidence for release or presentation

## 7. Recommended Priority Order

The safest order to move the system from the current state toward 85% to 100% is:

1. Status, rule, and access consistency
2. Funnel event tracking and funnel analytics foundation
3. Automation event contract and outbound delivery
4. CRM completion with custom fields and stage history
5. Advanced analytics formulas and dashboards
6. Billing and subscription lifecycle hardening
7. SaaS controls and usage-limit enforcement
8. Feature, integration, and end-to-end test hardening

## 8. Detailed Module Gap Summary

### 8.1 Funnel Builder

Current state:

- strong partial

Preserved key needs:

- event tracking
- conversion analytics
- abandoned checkout logic
- stronger publish validation
- public route throttling and anti-spam
- funnel-specific tests

Definition of done:

- funnel path is measurable from visit to paid
- public routes are hardened
- funnel tests exist and pass

### 8.2 CRM / Lead Management

Current state:

- usable but still basic

Preserved key needs:

- custom fields
- stronger tag structure
- configurable lead scoring
- stage history and audit trail
- stronger pipeline analytics

Definition of done:

- CRM supports different tenant business needs
- lead movement is measurable and auditable
- pipeline is analytical, not only visual

### 8.3 Automation Engine

Current state:

- major missing module relative to the brief

Preserved key needs:

- event-based automation
- email sequences
- SMS integration
- delay and condition support
- workflow logging
- n8n integration and retries

Definition of done:

- system events are reliably sent to n8n
- workflows execute actual email and SMS automations
- automation runs are auditable

### 8.4 Billing / Payments / Subscription Lifecycle

Current state:

- partial

Preserved key needs:

- one-time checkout maturity
- clearer subscription states
- failed payment recovery
- safer reconciliation
- tenant billing state clarity

Definition of done:

- the billing system behaves like a real SaaS platform
- transitions are predictable and safe
- duplicate gateway events do not duplicate effects

### 8.5 Analytics and Reporting

Current state:

- basic dashboards exist

Preserved key needs:

- conversion per funnel
- revenue per funnel
- abandoned cart rate
- MRR
- churn
- ARPU
- usage metrics
- role-safe reporting

Definition of done:

- dashboards are decision-grade
- formulas are documented and trusted
- date handling is consistent

### 8.6 SaaS Business Controls

Current state:

- partial

Preserved key needs:

- usage limits
- plan enforcement
- feature gates
- trial and subscription control rules

Definition of done:

- plans do not only exist in UI
- plans actually control tenant behavior
- blocked actions are explained clearly

### 8.7 Testing and Approval Readiness

Current state:

- weak compared to the system scope

Preserved key needs:

- auth and role access tests
- tenant isolation tests
- CRM tests
- funnel tests
- billing and subscription tests
- analytics calculation tests
- integration path tests

Definition of done:

- critical flows are verifiable
- regressions are easier to detect
- approval and demo confidence become realistic

## 9. Preserved n8n Gap Findings

The most important automation-specific findings retained from prior notes are:

### Outbound events already supported in some form

- account-owner onboarding and invite-related events
- payment-related events in partial form
- lead capture and lead stage change events

### Major outbound gaps

- funnel opt-in events are not fully dispatched in a mature contract
- checkout-start events are not fully matured for real-time automation
- funnel payment-paid events need stronger automation readiness
- abandoned checkout events need first-class support
- upsell and downsell decision events need cleaner emission
- trial reminder and trial-expiry outbound events are still incomplete

### Contract gaps

- no strong durable event id everywhere
- idempotency protection needs improvement
- retry-safe delivery needs stronger design

### Workflow gaps

- email sequences need full operational workflows
- SMS workflows need completion
- condition and delay workflows need completion
- automation logs need to be evidence-friendly

## 10. Preserved Billing, Payout, and Commission Architecture Rules

These architecture decisions should remain stable while the system is completed.

### Separate the three money domains

Keep these separate:

1. platform subscription billing
2. tenant funnel earnings
3. internal tenant commissions

This reduces confusion and makes the system easier to scale and audit.

### Core business rule

- platform subscription payments belong to the SaaS billing domain
- funnel customer payments belong to the tenant
- tenant earnings should settle to the tenant account owner's verified payout account
- super admin should not automatically receive tenant earnings

### Payout account principles

- payout configuration should be tenant-level
- sensitive destination values should be protected
- masked values should be shown in UI
- verification state should be tracked

### Recommended payout fields

- `payout_method`
- `destination_type`
- `destination_value`
- `account_name`
- `provider_reference`
- `verification_status`
- `verified_at`
- `verified_by`
- `notes`

### Receipt and proof-of-payment principles

- manual receipt upload may exist where needed
- exact-match verification can auto-approve safe cases
- mismatches should go to finance review

Recommended receipt statuses:

- `pending_review`
- `verified`
- `rejected`
- `auto_approved`

### Commission model principles

- commissions should be tenant-configurable
- platform fee should be modeled separately from role commissions
- commissions should be based on net eligible amount

Recommended commission lifecycle:

- `pending`
- `held`
- `approved`
- `payable`
- `paid`
- `reversed`
- `cancelled`

### Approval workflow summary

Recommended high-level flow:

1. payment is created
2. payment becomes paid through webhook, manual approval, or verified receipt
3. net eligible amount is calculated
4. commission entries are created where attribution is valid
5. entries start held
6. hold period passes without dispute, reversal, or refund
7. entries become payable
8. finance reviews payout readiness
9. payout is processed to the verified tenant payout account
10. entries become paid

## 11. Rollout Phases

The preserved rollout path is:

### Phase 1: Status, Rule, and Access Consistency

- standardize statuses
- tighten tenant and role access
- add baseline access tests

### Phase 2: CRM Structure Completion

- add tenant-scoped custom fields
- improve tags and scoring
- add stage history and audit trail

### Phase 3: Pipeline Reporting and Kanban Intelligence

- add stage counts
- conversion reporting
- time-in-stage
- aging and won/lost summaries

### Phase 4: Funnel Analytics Completion

- visits
- opt-ins
- checkout starts
- paid conversions
- drop-off
- abandoned checkout
- revenue per funnel

### Phase 5: SaaS Plan Enforcement and Usage Limits

- enforce tiered pricing
- apply feature gates
- show usage versus limits
- block over-limit creation safely

### Phase 6: Trial, Billing, and Subscription Lifecycle Completion

- improve trial enforcement
- clarify subscription states
- harden billing behavior
- improve recovery and webhook safety

### Phase 7: Advanced Analytics and Platform Metrics

- conversion rate
- revenue per funnel
- abandoned cart rate
- MRR
- churn
- ARPU
- active tenant metrics
- usage metrics

### Phase 8: Feature and Integration Test Hardening

- expand feature coverage
- expand integration coverage
- protect end-to-end workflows

## 12. Shared Dependencies and Coordination Rules

Important cross-module coordination rules preserved from the deleted plans:

- event names must be standardized
- status names must be standardized
- payload contracts must be shared
- done criteria must be aligned before parallel work diverges

Critical dependency chain:

- funnel outputs feed CRM, automation, payment, and analytics
- analytics quality depends on clean event and status consistency
- automation reliability depends on stable event contracts
- billing and SaaS controls affect access, metrics, and user trust

## 13. Approval-Ready Definition of Done

The project should only be called approval-ready when these are true:

- all required modules exist
- modules are connected end-to-end
- analytics are visible and accurate
- automation actually runs
- billing and SaaS controls are enforceable
- public flows are safe enough
- role and tenant rules are verified
- key workflows are test-covered

Final approval checklist:

- funnel builder is fully usable and measurable
- CRM supports real lead management needs
- automation engine executes email and SMS workflows
- payments and subscriptions behave reliably
- analytics dashboard matches the brief
- SaaS controls are enforced by plan and lifecycle state
- tenant isolation is verified
- core feature and integration tests are in place

## 14. Immediate Next Actions

If the goal is to move from the current 75% build completeness toward 85% quickly, the highest-value next actions are:

1. finalize shared event names and status names
2. complete funnel event tracking from visit to paid
3. emit missing outbound automation events to n8n with idempotency fields
4. complete custom fields, scoring rules, and stage history in CRM
5. finish advanced analytics formulas for funnel, tenant, and platform views
6. harden billing and subscription lifecycle rules
7. repair the local test database setup and run the feature suite successfully

## 15. Final Position

This Laravel system is already substantial and real.

It is not best described as unfinished from scratch. Instead, it is a strong foundation with a difficult remaining last stretch.

The path to 85% is realistic if the team closes:

- funnel analytics and public hardening
- outbound automation completeness
- CRM structure maturity
- advanced analytics formulas
- billing lifecycle hardening
- plan enforcement consistency
- environment-backed test validation

The path to 100% requires not only more features, but confidence, consistency, verification, and stable end-to-end behavior.
