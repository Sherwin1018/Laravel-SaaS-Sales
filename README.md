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
- [ ] Lead form capture and tagging

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

## Completed Today (2026-02-18)
- [x] Updated role dashboards (`account-owner`, `marketing-manager`, `sales-agent`, `finance`) to show company badge/name right-aligned beside the welcome header.
- [x] Standardized reusable header company chip styles in `public/css/admin-dashboard.css` for consistent desktop/mobile alignment.
- [x] Verified Super Admin `Active Tenants` KPI logic: it counts `tenants.status = active` (not active account-owner users).

## Detailed Updates (2026-02-18)

### Profile and Account UX
- [x] Replaced Account Owner text with company badge in `resources/views/dashboard/account-owner.blade.php`:
  company logo if uploaded, otherwise auto-colored initials fallback.
- [x] Added full Manage Profile page for all roles in `resources/views/profile/show.blade.php`:
  editable name/phone/secondary phone, read-only email/role, password change section, profile picture upload/delete, static notification toggle, last login, account created date, and company block (with Account Owner company-logo upload/delete).
- [x] Updated sidebar Manage Profile link in `resources/views/layouts/admin.blade.php` to `route('profile.show')`.
- [x] Added sidebar avatar behavior in `resources/views/layouts/admin.blade.php` + `public/css/admin-dashboard.css`:
  uploaded photo or initials with auto-generated color fallback.
- [x] Improved sidebar account spacing in `public/css/admin-dashboard.css`:
  changed `.account-info` to `justify-content: flex-start`, made `.account-details` flexible, and pushed three-dot menu right via `margin-left: auto`.

### Backend and Routing
- [x] Added `ProfileController.php` and profile routes in `routes/web.php`.
- [x] Updated login flow in `app/Http/Controllers/AuthController.php` to store `last_login_at`.
- [x] Updated model fields in `app/Models/User.php` and `app/Models/Tenant.php`.
- [x] Added migrations:
  `2026_02_18_000002_add_profile_fields_to_users_table.php`
  `2026_02_18_000003_add_logo_path_to_tenants_table.php`
  `2026_02_18_000001_add_suspension_reason_to_users_table.php`

### Notifications and Feedback UI
- [x] Switched notifications to toast-style cards with heading/icon/close button and 4-second auto-close.
- [x] Updated:
  `resources/views/layouts/admin.blade.php`
  `resources/views/auth/login.blade.php`
  `public/css/admin-dashboard.css`

### Forms, Validation, and Data Rules
- [x] Removed underline in Add actions by replacing nested `<a><button>` patterns with styled `.btn-create` links/buttons in index pages and `public/css/admin-dashboard.css`.
- [x] Fixed Account Owner team role search by adding role name/slug filtering in `app/Http/Controllers/UserController.php`.
- [x] Standardized bold data emphasis in table/body rows and role/status badges across `_rows.blade.php` partials and `public/css/admin-dashboard.css`.
- [x] Standardized password policy for account creation (Super Admin + Account Owner) in:
  `app/Http/Controllers/TenantController.php`
  `app/Http/Controllers/UserController.php`
  with confirm-password fields in create forms.
- [x] Enforced required lead/account form fields and stricter lead creation/edit validation in:
  `app/Http/Controllers/LeadController.php`
  `resources/views/leads/create.blade.php`
  `resources/views/leads/edit.blade.php`
- [x] Enforced Philippine phone format (`^09\d{9}$`, exactly 11 digits) in lead and profile flows.

### Pipeline, Role Controls, and Dashboard Logic
- [x] Enhanced lead pipeline UX:
  scrollable columns, lead-name search/filter, latest-12 card limit per status, plus View Lead Pipeline and Assign Lead toggles.
- [x] Added Super Admin Account Owner activate/deactivate controls with suspension reason and blocked-login messaging:
  route `admin.users.status`, controller action `UserController@toggleOwnerStatus`, admin users UI updates, and login blocking in `AuthController.php`.
- [x] Hardened Account Owner conversion-rate logic in `app/Http/Controllers/DashboardController.php` to handle status variants (`closed_won`, `Closed Won`, etc.).
- [x] Standardized success/failure messages across key controllers:
  `AuthController.php`, `UserController.php`, `TenantController.php`, `LeadController.php`, `PaymentController.php`.

### Validation and Run Commands
- [x] PHP syntax validation completed (`php -l`) for modified PHP files.
- [ ] Required command: `php artisan migrate`
- [ ] Required command (if not linked): `php artisan storage:link`

------

## Modules and Scope

### 1. Multi-Tenant Permission System
- [x] Super Admin
- [x] Account Owner
- [x] Marketing Manager
- [x] Sales Agent
- [x] Finance
- [x] Customer role and portal

### 2. Funnel Builder System
- [x] Drag-and-drop builder
- [x] Landing, opt-in, and sales pages
- [x] Checkout pages
- [x] Upsell/downsell logic

### 3. Lead Flow and CRM
- [x] Lead database
- [ ] Tagging and custom fields
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
- [ ] Add lead tags and custom fields.
- [ ] Start funnel builder MVP (Week 3 scope).
- [ ] Add automated email sequence infrastructure.
- [ ] Integrate payment gateway for real transactions.
- [ ] Add feature tests for assignment, scoring, pipeline, and payments.
