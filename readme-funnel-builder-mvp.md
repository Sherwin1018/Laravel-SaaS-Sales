# Complete Funnel Builder MVP (Target Scope)

## Core Builder UI
- [ ] Funnel list, create, edit, delete
- [ ] Step-based flow editor with drag-and-drop reorder
- [ ] Step types: landing, opt-in, sales, checkout, upsell, downsell, thank-you
- [ ] Per-step status (active/inactive) and funnel status (draft/published)
- [ ] Public funnel URL preview

## Page/Step Content
- [ ] Editable title, headline, body, CTA label
- [ ] Basic styling controls (colors, button style, spacing)
- [ ] Mobile-responsive layout
- [ ] Validation for required fields per step type

## Lead Capture (Opt-in)
- [ ] Form fields: name, email, phone (plus required validation)
- [ ] Save/update lead in tenant CRM
- [ ] Source/campaign attribution
- [ ] Auto-assign lead (or assignment rule fallback)

## Flow Logic
- [ ] Sequential step routing
- [ ] Conditional logic for upsell/downsell:
- [ ] Accept upsell -> skip downsell
- [ ] Decline upsell -> go downsell
- [ ] Restart/re-entry behavior for public funnel

## Checkout (MVP Level)
- [ ] Step-level price and amount handling
- [ ] Create payment records from checkout/offer actions
- [ ] Payment status tracking (paid/pending/failed) in system
- [ ] Link payment to lead and tenant

## Role + Tenant Controls
- [ ] Tenant isolation on all funnels/steps/leads/payments
- [ ] Access for Account Owner + Marketing Manager (as designed)
- [ ] Safe publish/edit controls and authorization checks

## Analytics (MVP Minimum)
- [ ] Views/visits per step
- [ ] Opt-in conversion rate
- [ ] Checkout conversion rate
- [ ] Funnel summary: leads captured, paid count, revenue (recorded)

## Quality + Reliability
- [ ] Server-side validation and sanitized inputs
- [ ] Basic anti-spam/rate limiting for public forms
- [ ] Error/success feedback on all key actions
- [ ] Feature tests for: publish, opt-in capture, routing, checkout record creation

## Completion Rule
If these are present and working end-to-end, Funnel Builder MVP is considered fully complete for MVP scope.