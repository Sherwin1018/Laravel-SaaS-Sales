# Member 3 Task Brief

## Role
n8n Automation Engineer

## Primary Responsibility
Own the Automation Engine side of the project, using n8n as the main execution layer for workflow automation.

You are responsible for making sure automation is:
- real
- trigger-based
- auditable
- reliable
- connected to app events
- aligned with the project brief

## Current State Summary
What is already present in the project:
- CRM actions and lead events exist in basic form
- funnel submissions and payment records exist
- some webhook/payment foundation exists
- the system has enough core actions to generate automation events

What is still missing or incomplete:
- actual automation engine
- workflow execution path
- email sequences
- SMS integration
- time-delay actions
- conditional workflows
- event-based automation triggers
- automation execution logs
- reliable n8n contract

## Main Goal
Build the project’s Automation Engine in a way that satisfies the brief using n8n and app-triggered events.

## Required Deliverables

### 1. Automation Event Contract
Define the event contract between the Laravel app and n8n.

Core event candidates:
- lead_created
- lead_updated
- lead_status_changed
- funnel_opt_in_submitted
- funnel_checkout_started
- payment_paid
- payment_failed
- subscription_renewed
- subscription_cancelled
- trial_expired
- checkout_abandoned

Each event payload should include, where applicable:
- event name
- event id / idempotency key
- timestamp
- tenant id
- tenant name if useful
- lead id
- funnel id
- step id
- payment id
- user id
- status values
- key business context

Expected output:
- app and n8n speak the same event language

### 2. Outbound Webhook Integration
Create the n8n-facing outbound automation flow.

This should include:
- sending events from the app to n8n
- retry behavior
- delivery logging
- failure handling
- idempotency protection

Expected output:
- important system events are reliably delivered to n8n

### 3. Email Sequence Automation
Implement email follow-up flows using n8n.

Examples:
- after lead created, send welcome email
- after opt-in, send nurture sequence
- after no action, send reminder email
- after payment success, send confirmation/onboarding email

Expected output:
- the system can run actual email sequences, not just log manual email activity

### 4. SMS Automation
Add SMS integration through n8n/provider workflows.

Examples:
- lead confirmation SMS
- reminder SMS
- payment success SMS
- abandoned checkout reminder SMS

Expected output:
- SMS workflows exist for the required brief scope

### 5. Time Delays and Conditional Workflows
The brief explicitly requires:
- time delays
- conditional workflows

Examples:
- wait 1 hour after opt-in, then send email
- if lead has not paid after X time, send reminder
- if lead status becomes qualified, notify sales
- if checkout abandoned, trigger follow-up sequence

Expected output:
- workflows can branch and delay, not just fire immediately

### 6. Automation Logs and Observability
Track automation behavior:
- event sent
- event received
- workflow started
- workflow completed
- workflow failed
- retry count
- last error

Expected output:
- automation can be audited during demo, QA, and approval

### 7. Automation Use Cases Required For Demo/Approval
At minimum, prepare usable workflows for:
- lead created -> send welcome email
- opt-in submitted -> send nurture email sequence
- stage changed to qualified -> notify sales
- payment paid -> send success/onboarding message
- payment failed -> send recovery message
- checkout abandoned -> send reminder follow-up

Expected output:
- the Automation Engine can be demonstrated clearly during presentation/UAT

## Detailed Task Breakdown

### Task Group A: Event Intake Design
Decide how n8n receives events:
- direct webhook trigger from Laravel
- authenticated/signed requests
- retry-safe processing
- idempotent deduplication

You need a documented payload contract that the Laravel side can implement consistently.

### Task Group B: Workflow Templates
Prepare reusable n8n workflow templates for:
- lead nurture
- sales notification
- payment confirmation
- failed payment reminder
- abandoned checkout follow-up
- trial expiration or upgrade reminder

### Task Group C: Delivery Providers
Identify the message providers to use through n8n:
- email provider
- SMS provider

For each provider, define:
- required credentials
- expected request format
- error handling
- rate limits if applicable

### Task Group D: Retry and Failure Handling
Automation is not complete unless it is reliable.

Build logic for:
- retry on temporary failure
- stop on permanent invalid data
- log failure reason
- prevent duplicate sends

### Task Group E: Audit and Visibility
Provide evidence-friendly outputs:
- workflow execution history
- message send log
- event delivery log
- failure log

This is important for approval because it proves the automation engine is real and traceable.

## Dependencies
You depend on:
- Member 1 for funnel event generation and abandoned-checkout event/state
- Member 2 for core business events, payment/subscription states, and shared data rules

You should provide to them:
- final webhook payload contract
- required fields for each event
- workflow result expectations
- retry/idempotency requirements

## Definition Of Done
Your work is done when:
- system events are sent to n8n reliably
- n8n can execute email and SMS automations
- workflows support delay and conditions
- automation runs are logged and auditable
- approval/demo workflows can be shown live

## Priority Order
1. Event contract
2. Outbound webhook delivery
3. Email sequence workflows
4. SMS workflows
5. Delay/condition support
6. Logs and retries

## Handover Notes
Before final handoff, provide:
- event contract document
- list of workflow names
- list of providers used
- list of automations ready for demo
- known limitations or manual setup steps
