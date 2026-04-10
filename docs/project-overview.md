# Project Overview

This is a mature multi-tenant SaaS platform built with Laravel 12.0 that combines CRM, funnel building, automation, payments, and analytics for digital sales operations. The system is production-ready with comprehensive functionality for marketing automation and sales pipeline management.

## Core Architecture

### Multi-Tenant Foundation
- Framework: Laravel
- Database: MySQL with 60+ migrations
- Queue: Database queue system for background processing
- Frontend: Blade templates with Vite asset management
- Automation: n8n integration via webhooks

### Role-Based Access Control
- Super Admin: Platform management, tenant oversight
- Account Owner: Company management, billing, team administration
- Marketing Manager: Campaigns, funnels, automation workflows
- Sales Agent: Lead management, pipeline operations for assigned leads only
- Finance: Payment tracking, billing reports
- Customer: Portal access for purchased services

### Data Isolation
All core entities use `tenant_id` for complete data separation.

- Models: `Tenant`, `User`, `Role`, `Lead`, `Funnel`, `Payment`, `Automation*`
- Global scopes enforce tenant isolation automatically
- Middleware validates tenant access per request

## Key Modules And Implementation

### 1. CRM And Lead Management
Models: `Lead`, `LeadActivity`, `LeadCustomFieldValue`, `LeadStageHistory`

Features:
- Lead scoring system with `+5`, `+10`, and `+20` point events
- Pipeline stages: `new -> contacted -> proposal_sent -> closed_won/closed_lost`
- Activity tracking and history
- Custom fields support
- Assignment to sales agents with visibility restrictions
- Email verification system
- Lead source campaign tracking

Controllers:
- `LeadController`
- `LeadCustomFieldController`
- `LeadVerificationController`

### 2. Funnel Builder System
Models: `Funnel`, `FunnelStep`, `FunnelVisit`, `FunnelEvent`, `FunnelBuilderAsset`

Features:
- Drag-and-drop page builder
- Landing pages, opt-in forms, sales pages, and checkout pages
- Public funnel URLs with tracking via `/f/{slug}`
- Visit analytics and conversion tracking
- Theme customization and asset management
- Link tracking with UTM parameters

Controllers:
- `FunnelController`
- `FunnelPortalController`
- `LinkTrackingController`

### 3. Automation Engine With n8n Integration
Models: `AutomationWorkflow`, `AutomationSequence`, `AutomationSequenceStep`, `AutomationLog`, `AutomationEventOutbox`

Architecture:
- Event-driven webhooks to n8n
- Outbox pattern for reliability
- Queue-based async processing
- Idempotency via UUID event IDs

Events:
- `lead.created`: New lead in CRM
- `funnel.opt_in`: Lead opts into funnel after verification
- `lead.status_changed`: Pipeline stage updates
- `payment.paid` / `payment.failed`: Payment events

Workflow Types:
- Single actions: `send_email`, `notify_sales`
- Multi-step sequences: email, delay, SMS pending
- Conditional logic pending

Services:
- `AutomationWebhookService`
- `SendN8nWebhookJob`

Controllers:
- `AutomationController`
- `TenantAutomationRunController` (API)

### 4. Payment System
Models: `Payment`, `Plan`, `SignupIntent`

Gateway:
- PayMongo integration focused on the Philippines

Features:
- One-time and subscription payments
- Webhook handling for payment events
- Payment status tracking
- Integration with funnel checkout

Controllers:
- `PaymentController`
- `PayMongoCheckoutController`
- `PayMongoWebhookController`

### 5. Analytics And Dashboards
Services:
- `AnalyticsDashboardService`
- `UTMAnalyticsService`

Role-specific dashboards:
- Account Owner: Lead metrics, conversion rates, team performance
- Marketing Manager: Campaign analytics, funnel performance
- Sales Agent: Personal pipeline, lead conversion
- Finance: Revenue tracking, payment reports
- Super Admin: Platform metrics, tenant analytics

Controllers:
- `DashboardController`
- `AnalyticsController`

### 6. SaaS Business Controls
Models: `Tenant`, `Plan`, `TrialSubscriptionController`

Features:
- Trial management with expiration
- Subscription lifecycle
- Usage tracking basics
- Billing status management

## Database Schema Highlights

### Core Tables
- `tenants`: Multi-tenant foundation
- `users`: User management with role assignments
- `leads`: CRM lead database
- `funnels`: Funnel definitions
- `funnel_steps`: Individual funnel pages
- `automation_workflows`: Trigger-action definitions
- `automation_sequences`: Multi-step automation flows
- `payments`: Payment tracking
- `roles`: Role definitions

### Relationship Patterns
- All entities belong to `Tenant` except Super Admin
- `Lead` has many `LeadActivity` and `LeadCustomFieldValue`
- `Funnel` has many `FunnelStep` and `FunnelVisit`
- `AutomationWorkflow` can reference `AutomationSequence`
- `Payment` can belong to `Lead`

## API Endpoints And Routing

### Public Routes
- `/`: Landing page
- `/f/{slug}`: Public funnel portal
- `/register`, `/login`: Authentication

### Admin Dashboard Routes
- `/dashboard/*`: Role-based dashboards
- `/leads/*`: Lead management
- `/funnels/*`: Funnel builder
- `/automation/*`: Workflow management
- `/payments/*`: Payment tracking
- `/admin/*`: Super admin functions

### API Routes
- `/api/automation/tenant/run`: n8n callback endpoint
- Webhook endpoints for PayMongo

### Webhook Integration
- Outbound: Laravel to n8n for automation events
- Inbound: n8n to Laravel for action execution
- Configuration via `config/n8n.php`

## Security And Multi-Tenancy

### Data Isolation
- Global scopes on all tenant-scoped models
- Middleware validates tenant ownership
- Role-based access control via `CheckRole` middleware

### Authentication
- Laravel-based authentication
- Google OAuth integration
- Email verification for leads and users
- Setup token system for initial user registration

### Security Features
- CSRF protection on all forms
- Tenant data isolation enforced at model level
- Signed URLs for sensitive operations
- Rate limiting on setup endpoints

## Development Workflow

### Composer Scripts
- `composer setup`: Full project initialization
- `composer dev`: Development server with queue and logs
- `composer test`: Test suite execution

### Queue System
- Database queues for background processing
- Essential for automation webhooks
- Run with `php artisan queue:work`

### Asset Management
- Vite for frontend asset compilation
- Custom CSS in `public/css/`
- Theme customization per tenant

## Current Implementation Status

### Completed Features
- Multi-tenant architecture with role-based access
- Complete CRM with lead management and scoring
- Funnel builder with drag-and-drop interface
- Automation engine with n8n integration
- Payment tracking with PayMongo
- Analytics dashboards for all roles
- Email verification system
- Custom fields for leads
- Theme customization

### In Progress
- Payment gateway integration configured but still needs testing
- SMS integration for automation requires provider selection
- Conditional workflows in automation
- Advanced analytics and reporting

### Pending
- SaaS tiered pricing and usage limits
- Advanced funnel analytics
- Subscription billing automation
- SMS provider integration

## Key Business Logic

### Lead Scoring
- Events add points: `+5` basic, `+10` moderate, `+20` high value
- Used for lead prioritization and automation triggers

### Pipeline Management
- Kanban-style visualization
- Status changes trigger automation events
- Assignment restrictions for sales agents

### Conversion Tracking
- Funnel step analytics
- UTM parameter tracking
- Link click attribution

### Automation Flow
- Laravel event -> webhook service -> outbox table
- Queue job -> HTTP POST to n8n
- n8n processing -> callback to Laravel API
- Action compilation -> execution for email, delay, and future SMS

## Development Guidelines

### Code Organization
- Controllers organized by feature area
- Services for complex business logic
- Models with relationships and scopes
- Views organized by module under `resources/views/`

### Naming Conventions
- Database tables: snake_case plural
- Models: PascalCase singular
- Controllers: PascalCase plus `Controller`
- Routes: kebab-case

### Testing Strategy
- PHPUnit setup with feature tests
- Focus on integration between modules
- Test automation flows end-to-end

## Environment Configuration

### Required Environment Variables
```bash
# Application
APP_URL=http://localhost:8000

# n8n Integration
N8N_WEBHOOK_BASE_URL=http://localhost:5678
N8N_WEBHOOK_SEGMENT=webhook
N8N_USE_ROUTER=true
N8N_ROUTER_PATH=saas-events
AUTOMATION_RUNNER_TOKEN=your-secret-token

# Payment Gateway (PayMongo)
PAYMONGO_SECRET_KEY=your-paymongo-key
PAYMONGO_WEBHOOK_SECRET=your-webhook-secret
```

### Database Setup
- SQLite database at `database/database.sqlite`
- Run `php artisan migrate` for schema setup
- Seed data available for development

## Common Development Tasks

### Adding New Automation Events
1. Add the event type to `AutomationWebhookService`.
2. Dispatch the event in the appropriate controller or service.
3. Add webhook path configuration if needed.
4. Update the n8n router workflow.

### Creating New Dashboard Metrics
1. Add the calculation to `AnalyticsDashboardService`.
2. Update the appropriate dashboard controller.
3. Add the display to the relevant role-specific view.

### Extending Funnel Builder
1. Add the new step type to `FunnelStep`.
2. Update the builder UI in funnel views.
3. Add rendering and handling in `FunnelPortalController`.

### Adding Payment Methods
1. Extend `Payment` with new gateway fields.
2. Add webhook handling for the new gateway.
3. Update the checkout flow in funnels.

## Architecture Patterns

### Event-Driven Design
- Laravel events trigger automation
- Decoupled through the webhook system
- Async processing for reliability

### Multi-Tenant Patterns
- Tenant-scoped queries via global scopes
- Middleware-based access control
- Role-based UI customization

### Service Layer Architecture
- Controllers handle HTTP concerns
- Services contain business logic
- Models manage data relationships

### Queue-Based Processing
- Background jobs for heavy operations
- Webhook delivery via queues
- Retry mechanisms for reliability

## Future Development Considerations

### Scalability
- Consider Redis for queue and cache
- Optimize the database for large tenant counts
- Add CDN integration for funnel assets

### Feature Expansion
- Advanced reporting and analytics
- SMS provider integration
- Conditional automation logic
- SaaS tier management

### Integration Opportunities
- Email service providers such as SendGrid and Mailgun
- CRM systems such as Salesforce and HubSpot
- Analytics platforms such as Google Analytics
- Payment gateways such as Stripe and PayPal

This document is the architectural baseline for future development, debugging, and enhancement work. Changes should preserve tenant isolation, role-based access control, and service-layer consistency.
