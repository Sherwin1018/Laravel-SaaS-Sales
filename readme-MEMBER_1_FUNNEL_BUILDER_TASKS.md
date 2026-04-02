# Member 1 Task Brief

## Role
Funnel Builder Programmer

## Primary Responsibility
Own the funnel builder completion work, especially the parts that move it from "working UI" to "approval-ready module" based on the project brief.

You are responsible for making sure the funnel system is:
- measurable
- publish-safe
- conversion-aware
- protected against public abuse
- test-covered

## Current State Summary
What is already present in the project:
- drag-and-drop funnel/page builder
- landing, opt-in, sales, checkout, upsell, downsell, thank-you steps
- create, edit, delete, reorder, preview, publish/unpublish
- public funnel routes
- opt-in lead capture
- checkout flow foundation
- upsell/downsell logic foundation

What is still missing or incomplete:
- funnel analytics tracking end-to-end
- visit-to-conversion measurement
- abandoned-cart style tracking
- stronger publish-readiness validation
- public route throttling / anti-spam
- funnel-specific test coverage

## Main Goal
Complete the Funnel Builder module so it satisfies the project brief and becomes approval-ready.

## Required Deliverables

### 1. Funnel Event Tracking
Add reliable tracking for the funnel journey:
- step page viewed
- opt-in form submitted
- checkout started
- payment completed
- upsell accepted
- upsell declined
- downsell accepted
- downsell declined

Expected output:
- each important funnel action is recorded as a structured event
- events can be filtered by tenant, funnel, step, and date
- event data can be consumed by dashboards and automation

### 2. Funnel Analytics Foundation
Implement the data needed for reporting:
- total visits per step
- opt-in count
- checkout-start count
- paid count
- upsell acceptance rate
- downsell acceptance rate
- revenue per funnel
- basic drop-off between steps

Expected output:
- analytics can show visit -> opt-in -> checkout -> paid
- each funnel has measurable conversion performance
- data is stored consistently enough for charts/tables

### 3. Abandoned Checkout Logic
Create the minimum logic for abandoned-cart / abandoned-checkout reporting.

Suggested rule:
- if a user reaches checkout or starts checkout but no paid record is completed within the expected session/reporting logic, count it as abandoned

Expected output:
- abandoned checkout metric per funnel
- event or state that can also be used by automation later

### 4. Publish-Readiness Hardening
Strengthen funnel publish rules.

Current publish validation is already partly present, but needs to be more business-safe.

Add checks like:
- required active steps exist
- slug flow is valid
- opt-in step contains actual form component
- checkout step contains valid checkout action and amount
- upsell/downsell steps have both accept and decline actions
- no broken step routing
- thank-you flow resolves safely

Expected output:
- users cannot publish incomplete or broken funnels
- validation messages are clear and helpful

### 5. Public Funnel Protection
Add abuse protection to public routes:
- throttling / rate limiting
- anti-spam protection for form submissions
- stricter validation on public input
- safe handling of repeated checkout submissions

Expected output:
- public funnel forms are safer
- spam/bot abuse is reduced
- repeated form/checkout actions are controlled

### 6. Funnel-Specific Test Coverage
Add feature/integration tests for:
- funnel publish
- funnel preview access
- opt-in lead capture
- checkout record creation
- upsell/downsell routing
- tenant isolation for funnel access
- public route validation

Expected output:
- the funnel builder has real test protection
- regressions are easier to detect

## Detailed Task Breakdown

### Task Group A: Tracking and Data Model
You should define the tracking structure for:
- funnel id
- tenant id
- step id
- event type
- lead id if available
- payment id if available
- timestamp
- session/reference identifier where applicable

Coordinate with Member 2 and Member 3 so event names are shared system-wide.

Recommended event names:
- funnel_step_viewed
- funnel_opt_in_submitted
- funnel_checkout_started
- funnel_payment_paid
- funnel_upsell_accepted
- funnel_upsell_declined
- funnel_downsell_accepted
- funnel_downsell_declined
- funnel_checkout_abandoned

### Task Group B: Reporting Data
Build or expose data that can support:
- total visits by step
- funnel conversion rate
- opt-in conversion rate
- checkout conversion rate
- paid conversion rate
- revenue per funnel
- step drop-off
- abandoned checkout rate

### Task Group C: Public Flow Hardening
Review these areas carefully:
- public step routes
- opt-in submission routes
- checkout route
- offer decision route

Questions to resolve:
- are repeated submissions prevented?
- are routes throttled?
- is invalid payload safely rejected?
- are duplicate actions possible?

### Task Group D: Quality and Edge Cases
Check edge cases like:
- user refreshes checkout
- user submits opt-in multiple times
- step slug mismatch
- funnel published with missing active steps
- upsell/downsell route chain breaks
- payment succeeds after delayed return flow

## Dependencies
You depend on:
- Member 2 for shared analytics/dashboards and overall data consistency
- Member 3 for event payload alignment for automation and n8n workflows

You should provide to them:
- final event names
- event payload shape
- funnel tracking outputs
- abandoned checkout trigger/state

## Definition Of Done
Your work is done when:
- funnel path is fully measurable from visit to paid
- per-funnel performance metrics are available
- publish validation blocks broken funnels
- public routes are hardened with throttling/anti-spam
- funnel tests exist and pass

## Priority Order
1. Funnel event tracking
2. Funnel analytics calculations
3. Publish-readiness validation
4. Public route hardening
5. Funnel-specific tests

## Handover Notes
Before final handoff, provide:
- list of events added
- list of metrics supported
- list of routes hardened
- list of tests added
- known edge cases still not covered
