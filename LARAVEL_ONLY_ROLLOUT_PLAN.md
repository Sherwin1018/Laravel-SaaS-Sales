# Laravel-Only Rollout Plan

## Purpose
This document is the safe phased rollout plan for implementing the Laravel-only functions and features in the project.

It focuses on:
- reducing risk
- avoiding confusion during rollout
- helping the team understand what users will notice
- making testing expectations clear per phase

This is **not** the n8n automation plan.
This is the rollout plan for the Laravel-side business logic, reporting, CRM, billing, SaaS controls, and system consistency work.

## Rollout Principle
Do **not** apply all Laravel-only changes at once.

Instead:
1. implement in phases
2. validate data impact
3. test old and new behavior
4. communicate visible changes before rollout

If implemented safely, the result should be:
- stronger business rules
- cleaner data
- better analytics
- safer access control
- more approval-ready project behavior

## Phase 1: Status, Rule, and Access Consistency

### What Changes
- standardize statuses across:
  - leads
  - payments
  - funnels
  - tenants
  - subscriptions
- document official business rules
- tighten tenant and role access control
- add baseline tests for role access and tenant isolation
- normalize obvious inconsistent values in existing records

### What Users Will Notice
- some reports may show slightly different counts
- some old inconsistent status names may disappear
- some users may lose access they should not have had
- dashboards may look more consistent

### What To Test
- old status values still resolve correctly after normalization
- reports do not duplicate counts because of mixed statuses
- role dashboards only show allowed records
- cross-tenant access is blocked
- old records still appear correctly after cleanup

### Risk Level
`Medium`

### Notes
This phase is important because many later analytics and billing features depend on clean statuses and safe access rules.

## Phase 2: CRM Structure Completion

### What Changes
- add tenant-scoped custom fields
- add lead field mapping support
- strengthen tag normalization
- add configurable scoring rules
- add stage-change history / audit trail
- improve lead record structure

### What Users Will Notice
- lead forms may become more detailed
- some tenants may now see custom fields
- tag values may look cleaner and more consistent
- lead scores may change because of new scoring rules
- lead history may show who changed stage and when

### What To Test
- each tenant only sees its own custom fields
- mapped funnel fields store into the correct lead fields
- lead values remain valid after edits
- tag normalization does not create duplicates
- scoring rules calculate correctly
- stage history logs:
  - when stage changed
  - from which stage
  - to which stage
  - by which user

### Risk Level
`Medium`

### Notes
This phase changes CRM data shape and should be rolled out carefully so old leads remain intact.

## Phase 3: Pipeline Reporting and Kanban Intelligence

### What Changes
- add stronger pipeline reporting:
  - leads by stage
  - stage-to-stage conversion
  - time in stage
  - won/lost summary
  - aging report by stage
- improve pipeline analytics around lead movement

### What Users Will Notice
- Kanban becomes more analytical, not just visual
- managers can see bottlenecks more clearly
- sales follow-up delays may become more obvious
- reports may highlight weak stages in the sales process

### What To Test
- stage counts match actual leads
- stage conversion formulas are correct
- time-in-stage uses correct timestamps
- aging buckets are correct
- won/lost reporting matches stored lead outcomes
- Sales Agents only see assigned-lead pipeline analytics where required

### Risk Level
`Low to Medium`

### Notes
This phase mostly affects reporting quality, but wrong formulas can confuse users.

## Phase 4: Funnel Analytics Completion

### What Changes
- complete funnel analytics tracking and reporting:
  - visits
  - opt-ins
  - checkout starts
  - paid conversions
  - drop-off
  - abandoned checkout
  - revenue per funnel
- align analytics outputs with dashboard/reporting needs

### What Users Will Notice
- funnel reports become more detailed
- conversion numbers may change from older assumptions
- abandoned-cart metrics may appear
- some funnels may look weaker or stronger than expected

### What To Test
- step visits count correctly
- opt-in conversion counts correctly
- checkout starts count correctly
- paid totals match real paid payments
- abandoned checkout logic is correct
- revenue per funnel matches linked payments
- date filtering works correctly

### Risk Level
`Medium`

### Notes
If tracking is added now, historical analytics may be partial unless older data is backfilled.

## Phase 5: SaaS Plan Enforcement and Usage Limits

### What Changes
- enforce tiered pricing behavior
- enforce usage limits
- apply plan-based feature gating
- show current usage vs current limit
- block over-limit actions with upgrade prompts

### What Users Will Notice
- some create actions may be blocked
- some features may disappear or become disabled
- owners may see usage counters and upgrade messages
- existing over-limit tenants may keep old records but may be unable to create more

### What To Test
- plan limits enforce correctly
- blocked actions show correct messages
- existing records remain visible
- only new creation is blocked where intended
- feature-gated routes are blocked on backend, not just hidden in UI
- usage displays are accurate

### Risk Level
`High`

### Notes
This is one of the most visible and potentially surprising phases because users will feel restrictions directly.

## Phase 6: Trial, Billing, and Subscription Lifecycle Completion

### What Changes
- strengthen trial lifecycle policy
- improve subscription lifecycle controls
- improve one-time payment and subscription billing behavior
- clarify tenant subscription states
- improve upgrade/downgrade/cancel logic
- improve billing history behavior
- improve failed payment consequences and recovery-safe billing logic

### What Users Will Notice
- trial expiry may be enforced more consistently
- owners may be redirected to billing more reliably
- inactive/overdue accounts may be restricted more clearly
- billing history may show more complete references and payment states
- upgrade/downgrade/cancel actions may have clearer effects

### What To Test
- trial expiration before and after exact end time
- active/inactive transitions
- upgrade/downgrade timing rules
- cancel/renew state changes
- failed payment consequences
- billing history accuracy
- existing tenants with unusual old states are migrated safely
- duplicate gateway events do not duplicate billing effects

### Risk Level
`High`

### Notes
This phase affects user access and money-related behavior. It must be rolled out carefully.

## Phase 7: Advanced Analytics and Platform Metrics

### What Changes
- complete advanced analytics:
  - conversion rate per funnel
  - revenue per funnel
  - abandoned cart rate
  - MRR
  - churn
  - ARPU
  - active tenants
  - usage metrics
  - tenant growth summaries
- ensure metrics are role-safe and formula-driven

### What Users Will Notice
- dashboards become more business-focused
- Super Admin may see stronger platform reporting
- Account Owners may see more meaningful tenant performance metrics
- numbers may differ from previous simpler calculations

### What To Test
- formulas match documented definitions
- date ranges are handled consistently
- tenant users only see tenant-safe data
- Super Admin gets platform-wide aggregated data only where intended
- MRR/churn/ARPU values are correct for selected periods
- usage metrics reflect real stored records

### Risk Level
`Medium`

### Notes
This phase is highly visible. If formulas are wrong, user trust in dashboards drops quickly.

## Phase 8: Feature and Integration Test Hardening

### What Changes
- add broader feature tests and integration tests
- protect core workflows with automated validation
- reduce regression risk as modules become interconnected

### What Users Will Notice
- usually no immediate UI changes
- internal development may become slower, but stability should improve

### What To Test
- auth and role access
- tenant isolation
- lead CRUD and assignment
- scoring rules
- funnel analytics
- billing and subscription rules
- dashboard outputs
- webhook idempotency
- over-limit behavior
- end-to-end reporting path from funnel/payment events

### Risk Level
`Low`

### Notes
This phase reduces system risk and should continue alongside earlier phases when possible.

## Best Rollout Order
1. Phase 1: status, rule, and access consistency
2. Phase 2: CRM structure completion
3. Phase 3: pipeline reporting and Kanban intelligence
4. Phase 4: funnel analytics completion
5. Phase 5: SaaS plan enforcement and usage limits
6. Phase 6: trial, billing, and subscription lifecycle completion
7. Phase 7: advanced analytics and platform metrics
8. Phase 8: feature and integration test hardening

## Most Likely User Shock Points
The phases most likely to surprise users are:
- Phase 5: plan limits and feature gating
- Phase 6: trial, billing, and subscription restrictions
- Phase 4 and Phase 7: analytics numbers changing

## Safe Expectations To Communicate Before Rollout
Before rollout, tell the team and users to expect:
- stricter validations
- more accurate dashboard numbers
- cleaner status values
- stronger role restrictions
- visible usage limits
- clearer billing and trial behavior
- no intended account deletion
- no intended data destruction

## Things That Must Be Migrated Carefully
Watch these carefully during rollout:
- old mixed status values
- old trial and subscription states
- old payment records
- tenants already above intended limits
- old leads that need new field structures
- historical analytics that may be incomplete before new tracking

## Final Reminder
Laravel-only improvements should make the project:
- more accurate
- more controlled
- more approval-ready

They should **not** destroy or damage the project if introduced carefully.

The biggest surprise is usually not data loss.
The biggest surprise is:
- tighter rules
- different metrics
- new limits
- stricter access behavior
