# Team Master Plan

## Project
SaaS Sales and Marketing Funnel System

## Purpose
This file is the shared team tracker for all 3 members. It summarizes:
- the current project state
- the major approval gaps
- each member's responsibilities
- execution order
- dependencies
- definition of done

This file is intended to help the team stay aligned while working on the project brief requirements.

## Team Members

### Member 1
Role: Funnel Builder Programmer

Main focus:
- funnel builder completion
- funnel event tracking
- funnel analytics foundation
- public funnel protection
- funnel-specific tests

Main file:
- `MEMBER_1_FUNNEL_BUILDER_TASKS.md`

### Member 2
Role: Overall System Programmer

Main focus:
- CRM completion
- advanced analytics
- billing and subscription lifecycle
- SaaS business controls
- overall system consistency
- feature and integration testing

Main file:
- `MEMBER_2_OVERALL_SYSTEM_TASKS.md`

### Member 3
Role: n8n Automation Engineer

Main focus:
- automation engine
- event contract
- email and SMS workflows
- n8n integration
- retries and automation logs

Main file:
- `MEMBER_3_N8N_AUTOMATION_TASKS.md`

## Current Overall Assessment

### Strong Areas
- multi-tenant foundation
- role-based access structure
- basic CRM
- funnel builder UI
- public funnel flow foundation
- payment/PayMongo foundation
- trial/subscription foundation
- dashboard foundation

### Main Approval Blockers
- automation engine not fully implemented
- advanced analytics and funnel reporting incomplete
- SaaS controls and usage limits incomplete
- subscription lifecycle incomplete
- public funnel hardening incomplete
- low feature/integration test coverage

## Main Gap Summary By Module

### 1. Funnel Builder
Current state:
- strong partial

Needs:
- event tracking
- conversion analytics
- abandoned checkout logic
- stronger publish validation
- public route throttling and anti-spam
- funnel tests

Owner:
- Member 1

### 2. CRM / Lead Management
Current state:
- usable but still basic

Needs:
- custom fields
- stronger tag/custom structure
- configurable lead scoring
- stage history/audit trail
- better pipeline analytics

Owner:
- Member 2

### 3. Automation Engine
Current state:
- major missing module

Needs:
- event-based automation
- email sequences
- SMS integration
- delays and conditions
- workflow logging
- n8n integration

Owner:
- Member 3

### 4. Checkout / Billing / Subscription
Current state:
- partial

Needs:
- full one-time checkout flow maturity
- stronger subscription lifecycle
- coupons
- failed payment recovery
- better reconciliation
- clearer tenant billing states

Owner:
- Member 2

### 5. Analytics and Reporting
Current state:
- basic dashboards exist

Needs:
- conversion per funnel
- revenue per funnel
- abandoned cart rate
- MRR
- churn
- ARPU
- usage metrics
- role-safe reporting

Owner:
- Member 2

Support from:
- Member 1 for funnel event data
- Member 3 for automation-related event flow

### 6. SaaS Business Controls
Current state:
- partial

Needs:
- usage limits
- plan enforcement
- feature gates
- trial and subscription control rules

Owner:
- Member 2

### 7. Testing and Approval Readiness
Current state:
- weak

Needs:
- feature tests
- integration tests
- end-to-end validation
- role and tenant isolation verification

Owners:
- Member 1 for funnel-related tests
- Member 2 for overall feature/integration tests
- Member 3 for automation workflow validation

## Team Execution Strategy

### Shared Rule
No member should work in isolation without agreeing on:
- event names
- status naming
- payload contracts
- done criteria

The team must keep modules interconnected:
- funnel -> CRM -> automation -> payment -> analytics

## Priority Order For The Whole Team
1. Funnel event tracking and analytics foundation
2. Automation engine event contract and workflow integration
3. Advanced analytics dashboard completion
4. CRM completion with custom fields and stage reporting
5. Billing and subscription lifecycle completion
6. SaaS controls and usage limits
7. Full feature and integration tests

## Task Ownership Tracker

### Member 1 Tracker
Status: Assigned

Must deliver:
- funnel event tracking
- step visit tracking
- opt-in conversion tracking
- checkout conversion tracking
- revenue per funnel foundation
- abandoned checkout tracking
- publish-readiness hardening
- public route protection
- funnel-specific tests

Done when:
- funnel path is measurable from visit -> paid
- public routes are safer
- funnel tests exist and pass

### Member 2 Tracker
Status: Assigned

Must deliver:
- CRM completion
- custom fields
- stage history and pipeline analytics
- advanced analytics dashboard
- SaaS-owner metrics
- billing and subscription lifecycle completion
- SaaS controls and plan enforcement
- feature and integration tests

Done when:
- core business rules are complete and consistent
- dashboards satisfy the brief more fully
- billing and plan behavior are enforceable

### Member 3 Tracker
Status: Assigned

Must deliver:
- automation event contract
- outbound event delivery to n8n
- email sequence workflows
- SMS workflows
- delay and condition support
- retries and logs
- approval/demo automation scenarios

Done when:
- automation really executes from app events
- email/SMS workflows are usable
- automation runs are auditable

## Dependencies

### Member 1 Depends On
- final shared event naming with Member 2 and Member 3

### Member 2 Depends On
- funnel event outputs from Member 1
- automation event alignment with Member 3

### Member 3 Depends On
- app event generation from Member 1 and Member 2
- stable payload contracts and status naming

## Suggested Weekly Plan

### Week 1
Member 1:
- implement funnel event tracking
- design conversion event structure
- start public route hardening

Member 2:
- standardize statuses and core business rules
- design analytics calculations and data model
- design CRM custom fields approach

Member 3:
- define event payload contract
- prepare n8n workflows and webhook intake
- define retry/idempotency approach

### Week 2
Member 1:
- complete funnel analytics foundation
- improve publish validation

Member 2:
- implement advanced dashboard metrics
- improve pipeline analytics
- start billing/subscription lifecycle improvements

Member 3:
- implement email automation workflows
- implement SMS workflows
- implement delay and conditional logic

### Week 3
Member 1:
- add funnel tests
- fix funnel edge cases

Member 2:
- complete SaaS controls
- complete billing flows
- add feature/integration tests

Member 3:
- add execution logs
- add retries/failure handling
- complete automation demo flows

### Week 4
All members:
- connect all modules end-to-end
- run QA and UAT
- fix blockers
- prepare approval/demo checklist

## Shared Definition Of Done
The project is considered approval-ready only when:
- all required modules exist
- modules are connected end-to-end
- analytics are visible and accurate
- automation actually runs
- billing and SaaS controls are enforceable
- public flows are safe enough
- role and tenant rules are verified
- key workflows are test-covered

## Final Approval Checklist
- Funnel builder fully usable and measurable
- CRM supports real lead management needs
- Automation engine executes email/SMS workflows
- Payments and subscriptions behave reliably
- Analytics dashboard matches project brief
- SaaS controls are enforced by plan/state
- Tenant isolation is verified
- Core feature/integration tests are in place

## Notes For Team Coordination
- Keep one shared event-name document.
- Keep one shared status-name document.
- Do not rename statuses independently.
- Test end-to-end flows every week, not only at the end.
- Log blockers early so dependencies do not stall other members.

## Related Files
- `MEMBER_1_FUNNEL_BUILDER_TASKS.md`
- `MEMBER_2_OVERALL_SYSTEM_TASKS.md`
- `MEMBER_3_N8N_AUTOMATION_TASKS.md`
