# Member 2 Task Brief

## Role
Overall System Programmer

## Primary Responsibility
Own the core business engine of the platform outside the funnel-builder UI and outside the n8n automation implementation.

You are responsible for making sure the system is:
- consistent
- approval-ready
- role-safe
- tenant-safe
- analytics-complete
- billing-complete enough for the brief

## Current State Summary
What is already present in the project:
- multi-tenant foundation
- role-based dashboards and routes
- basic CRM
- basic lead assignment and scoring
- basic Kanban-style pipeline
- trial flow
- payment records
- PayMongo integration foundation
- owner/admin/sales/finance dashboard foundations

What is still missing or incomplete:
- CRM custom fields
- configurable scoring rules
- stronger pipeline analytics
- advanced analytics dashboards
- SaaS-owner analytics
- subscription lifecycle completeness
- usage-limit enforcement
- plan-based feature controls
- system-wide consistency and integration tests

## Main Goal
Complete the core system logic so the platform matches the project brief as a complete SaaS system, not just a set of working pages.

## Required Deliverables

### 1. CRM Completion
Finish the CRM module beyond basic CRUD.

Add:
- tenant-scoped custom fields
- lead field mapping support
- stronger tag handling consistency
- configurable lead scoring rules
- stage change history / audit trail
- stronger pipeline reporting

Expected output:
- CRM can support different tenant business needs
- lead records are more complete and structured
- lead movement is measurable and auditable

### 2. Pipeline Reporting and Kanban Support
The brief expects a sales pipeline with Kanban view and meaningful use.

Add:
- leads by stage
- stage-to-stage conversion
- time in stage
- won/lost summary
- aging report by stage
- cleaner analytics around pipeline movement

Expected output:
- the pipeline is not only visual but analytically useful

### 3. Advanced Analytics and Reporting Dashboard
This is one of the main approval blockers.

You need to complete:

For tenant/client dashboards:
- conversion rate per funnel
- revenue per funnel
- abandoned cart rate
- useful date-based summaries

For SaaS-owner/admin dashboards:
- MRR
- churn rate
- active tenants
- ARPU
- usage metrics
- tenant growth summaries

Expected output:
- dashboards become decision-grade, not just basic counters

### 4. SaaS Business Controls
The brief expects SaaS monetization controls.

Add:
- tiered pricing enforcement
- usage limits
- plan-based feature gating
- tenant usage summaries
- stronger trial lifecycle policy
- subscription lifecycle controls

Expected output:
- plans are not just listed, they actually affect system behavior

### 5. Billing and Subscription Lifecycle Completion
The system already has payment foundations, but lifecycle handling still needs work.

Add:
- one-time payment flow completion
- subscription billing flow completion
- clearer tenant subscription states
- upgrade/downgrade/cancel flow logic
- billing history improvements
- failed payment recovery flow
- better reconciliation and webhook-safe logic

Expected output:
- the billing system behaves like a real SaaS platform

### 6. System Consistency and Rule Enforcement
Review and standardize:
- lead statuses
- tenant statuses
- payment statuses
- funnel statuses
- subscription states
- workflow states if introduced

Expected output:
- consistent status naming and logic across all modules
- fewer analytics/reporting bugs caused by mismatched values

### 7. Testing and Approval Hardening
You own most of the cross-module test protection.

Add tests for:
- auth and role access
- tenant isolation
- CRM lead CRUD and assignment
- scoring rules
- billing/subscription rules
- analytics calculations
- dashboard access and key outputs
- integration path from funnel/payment events into reporting

Expected output:
- core business flows are verifiable
- system becomes safer for approval and demo

## Detailed Task Breakdown

### Task Group A: CRM Data Design
Create a clean approach for:
- tenant-defined custom field definitions
- values stored per lead
- safe validation by field type
- edit/display integration

Possible field types:
- text
- textarea
- number
- date
- select
- checkbox

### Task Group B: Scoring and Pipeline Rules
Move beyond fixed score buttons only.

Add support for:
- configurable score events
- stage-change scoring
- payment-completion scoring if needed
- source/campaign-based scoring if useful

Also track:
- when stage changed
- from which stage to which stage
- by which user
- at what time

### Task Group C: Dashboard Completion
Define the formulas and outputs for:
- funnel conversion
- paid conversion
- revenue totals
- abandoned cart
- MRR
- churn
- ARPU
- usage counts

Important:
- keep formulas documented
- use consistent date ranges
- make metrics role-safe

### Task Group D: SaaS Controls
Make plans actually enforce behavior.

Examples:
- max number of users
- max number of funnels
- max number of leads
- max number of automation workflows
- max number of outbound messages if relevant

Also show users:
- current usage
- current limit
- what happens when the limit is exceeded

### Task Group E: Billing Completion
Review and improve:
- trial -> active transitions
- active -> inactive transitions
- failed payment consequences
- recovery behavior
- billing history visibility
- invoice/reference consistency
- webhook idempotency handling

## Dependencies
You depend on:
- Member 1 for clean funnel tracking events and per-funnel event data
- Member 3 for automation event contract and workflow response expectations

You should provide to them:
- final data/status standards
- analytics formulas
- business rules for tenant/billing/subscription behavior

## Definition Of Done
Your work is done when:
- CRM supports tags, custom fields, stage tracking, and reporting
- dashboards match the brief more fully
- billing/subscription lifecycle is much more complete
- SaaS plans and limits are enforceable
- business rules are consistent across modules
- critical feature/integration tests exist

## Priority Order
1. Status consistency and analytics data structure
2. Advanced dashboards
3. CRM completion
4. Billing/subscription lifecycle completion
5. SaaS controls and usage limits
6. Integration tests

## Handover Notes
Before final handoff, provide:
- status/enum map for all core modules
- analytics metric formulas
- list of completed SaaS controls
- list of tests added
- list of remaining business-rule risks
