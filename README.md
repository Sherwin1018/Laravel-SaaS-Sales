# SaaS Sales and Marketing Funnel System

## Project Brief
This project builds a multi-tenant SaaS Sales and Marketing Funnel System where multiple client businesses operate independently in one platform.

The platform combines funnel creation, CRM, automation, payments, and analytics to support end-to-end digital sales operations.

Primary goal: deliver a scalable, secure, and commercially viable SaaS product with clear monetization and role-based operations.

## Project Objective
Deliver a platform that:

- Supports secure multi-tenant architecture.
- Enables sales funnel creation and management.
- Captures, tracks, and scores leads in CRM.
- Automates follow-up flows (email, SMS, workflows).
- Processes one-time and subscription payments.
- Provides client + super-admin analytics.
- Supports billing tiers, limits, and subscription controls.

## Current Progress Checklist

### Step 1 (Weeks 1-2): Foundation + Business Functionality
- [x] Multi-tenant architecture (tenant isolation in core entities)
- [x] Role-based access (Super Admin, Account Owner, Marketing Manager, Sales Agent, Finance)
- [x] Basic CRM lead management (create, edit, list, delete)
- [x] Lead activity logging
- [x] Lead assignment (`assigned_to`) to sales agents
- [x] Sales-agent restricted lead visibility (assigned leads only)
- [x] Pipeline statuses (`new`, `contacted`, `proposal_sent`, `closed_won`, `closed_lost`)
- [x] Pipeline section on leads page
- [x] Lead scoring support (`+5`, `+10`, `+20` event actions)
- [x] Basic email activity logging in lead activities
- [x] Account Owner analytics (total leads, leads this month, conversion rate, leads by status)
- [x] Super Admin analytics expansion (total tenants, active tenants, users, leads)
- [x] Payment tracking module (payments table, billing page, owner/finance access)

### Step 2 (Week 3): Funnel Builder MVP
- [x] Drag-and-drop funnel/page builder
- [x] Landing pages and opt-in forms
- [x] Sales pages
- [x] Checkout page builder integration
- [x] Lead form capture and tagging

### Step 3 (Week 4): Automation Engine
- [ ] Email sequences
- [ ] SMS integration
- [ ] Time-delay actions
- [ ] Conditional workflows
- [ ] Event-based automation triggers

### Step 4 (Week 5): Checkout + Subscriptions
- [ ] One-time payments flow
- [ ] Subscription products
- [ ] Coupon/discount handling
- [ ] Failed payment recovery
- [ ] Payment gateway integration

### Step 5 (Week 6): Advanced Analytics + SaaS Controls
- [ ] Client analytics (conversion, revenue per funnel, abandoned cart)
- [ ] SaaS owner analytics (MRR, churn, ARPU, usage metrics)
- [ ] Tiered pricing plans
- [ ] Usage limits
- [ ] Trial management
- [ ] Subscription billing controls

## Modules and Scope

### 1. Multi-Tenant Permission System
- [x] Super Admin
- [x] Account Owner
- [x] Marketing Manager
- [x] Sales Agent
- [x] Finance
- [ ] Customer role and portal

### 2. Funnel Builder System
- [ ] Drag-and-drop builder
- [x] Landing, opt-in, and sales pages
- [x] Checkout pages
- [ ] Upsell/downsell logic

### 3. Lead Flow and CRM
- [x] Lead database
- [x] Tagging and custom fields (tags table, lead_tag pivot, capture with tag_ids)
- [x] Lead scoring (basic)
- [x] Activity tracking
- [x] Sales pipeline view (basic Kanban-style section)

### 4. Automation Engine
- [ ] Email sequences
- [ ] SMS workflows
- [ ] Conditional logic
- [ ] Trigger-based automation

### 5. Checkout and Payment System
- [x] Basic payment tracking records
- [ ] One-time checkout workflow
- [ ] Subscription checkout workflow
- [ ] Coupons
- [ ] Failed payment recovery
- [ ] Payment gateway integration

### 6. Analytics and Reporting Dashboard
- [x] Account Owner core lead metrics
- [x] Super Admin core platform metrics
- [ ] Funnel conversion analytics
- [ ] Revenue per funnel
- [ ] Abandoned cart rate
- [ ] MRR, churn, ARPU, usage analytics

### 7. SaaS Business Controls
- [ ] Tiered pricing
- [ ] Usage limits
- [ ] Trial rules
- [ ] Subscription lifecycle controls

## Team Roles

### Development
Responsible for architecture, database, module logic, security, and billing integration.

### UI/UX
Responsible for builder interface, dashboards, and usability in tenant/admin views.

### QA and Documentation Support
Responsible for validation, workflow verification, and project documentation.

## Collaboration Process
Build sequence:

1. Multi-tenant architecture and role permissions.
2. CRM foundation and user structures.
3. Funnel builder and lead capture.
4. Automation and communication integrations.
5. Checkout/subscriptions/payments.
6. Analytics and SaaS business controls.

All modules must remain interconnected: CRM, funnel tracking, automation, and payments should function as one flow.

## Expected Outcome
At completion, the platform should provide:

- A scalable SaaS funnel system.
- Structured lead management and automation.
- Integrated payment and subscription handling.
- Clear performance reporting for client tenants and platform owners.

## Quality Standards
- Clarity: Interfaces are understandable for non-technical users.
- Accuracy: Workflows match real sales/marketing operations.
- Consistency: Naming and logic align across all modules.
- Security: Tenant data remains isolated and protected.
- Usefulness: Features directly support growth and automation.

## Recommended Immediate Next Tasks
- [x] Add lead tags and custom fields.
- [x] Start funnel builder MVP (Week 3 scope).
- [ ] Add automated email sequence infrastructure.
- [ ] Integrate payment gateway for real transactions.
- [ ] Add feature tests for assignment, scoring, pipeline, and payments.
