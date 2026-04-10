# Test Account Events Integration

This guide helps you test the account events integration with your enhanced SaaS Event Router workflow.

## Current Branch Behavior

The current `automation` branch is in a hybrid state for account-event delivery.

### Two outbound n8n paths exist

1. Unified router/outbox path
- Laravel services:
  - `App\Services\AccountEventService`
  - `App\Services\AutomationWebhookService`
  - `App\Jobs\SendN8nWebhookJob`
- Config:
  - `config/n8n.php`
- Payload shape:
  - uses `event`
  - includes `event_id`
- Target:
  - unified SaaS Event Router, typically `/webhook/saas-events`

2. Direct account-email dispatch path
- Laravel services/controllers:
  - `App\Services\N8nEmailOrchestrator`
  - `App\Services\SignupOnboardingService`
  - `App\Http\Controllers\UserController`
  - `App\Http\Controllers\SetupAccessController`
- Config:
  - `config/services.php`
- Payload shape:
  - uses `event_name`
  - does not use `event_id`
- Target:
  - `N8N_WEBHOOK_URL`

### What the live app currently uses

These events are currently sent by the real onboarding/invite flow through the direct path:

- `account_owner_paid_signup_created`
  - Trigger: paid non-Google account-owner signup
  - Source: `SignupOnboardingService::finalize()` -> `queueSetupEmail()`
- `account_owner_google_paid_signup_created`
  - Trigger: paid Google signup with auto-activation
  - Source: `SignupOnboardingService::finalize()`
- `team_member_invited`
  - Trigger: team member invite and resend invite
  - Source: `UserController::store()` and `UserController::resendVerification()`
- `customer_portal_invited`
  - Trigger: customer invite and resend invite
  - Source: `UserController::store()` and `UserController::resendVerification()`
- `setup_link_expiring`
  - Trigger: setup resend
  - Source: `SetupAccessController::resend()`

The following event is supported by the router/outbox services, but is not currently wired into a live app trigger:

- `setup_link_expired`

### Expected n8n compatibility

Your merged n8n workflow should accept both contracts until Laravel is fully unified:

1. Router contract
- key field: `event`
- used by `AccountEventService` / `AutomationWebhookService`

2. Direct email contract
- key field: `event_name`
- used by `N8nEmailOrchestrator`

Recommended n8n behavior:
- normalize `event` and `event_name` into one internal event value
- route account events from either payload style
- send email
- callback to Laravel at `POST /api/n8n/email-status`

### Callback expectations

Laravel currently expects the n8n callback endpoint:

- `POST /api/n8n/email-status`

Required fields:
- `event_name`
- `email`
- `status`

Optional fields:
- `user_id`
- `sent_at`

Allowed statuses in the active controller:
- `sent`
- `failed`

### Important note for testing

The quick tests below use `AccountEventService`, which exercises the unified router/outbox path.

That is useful for validating your SaaS Event Router integration, but it is not exactly the same code path currently used by the live onboarding UI. The real onboarding UI still sends most account emails through `N8nEmailOrchestrator`.

## Prerequisites

- Laravel application running
- n8n instance running with enhanced workflow
- Brevo SMTP configured in `.env`
- All Step 9 Laravel integration completed

## Quick Test Commands

### 1. Test Account Owner Activation

Run this in your Laravel terminal:

```bash
php artisan tinker
```

Then paste this code:

```php
use App\Services\AccountEventService;

$service = app(AccountEventService::class);

// Test account owner activation
$result = $service->dispatchAccountOwnerPaidSignupCreated(
    'test@example.com',
    'Test User',
    'http://127.0.0.1:8000/setup/test-token-123',
    '2026-04-10 23:59:59',
    1, // user_id
    1  // tenant_id
);

echo "Account activation dispatched: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
```

### 2. Test Team Member Invitation

```php
// Test team member invitation
$result = $service->dispatchTeamMemberInvited(
    'teammember@example.com',
    'Team Member',
    'http://127.0.0.1:8000/setup/team-token-456',
    '2026-04-10 23:59:59',
    2, // user_id
    1  // tenant_id
);

echo "Team invitation dispatched: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
```

### 3. Test Customer Portal Invitation

```php
// Test customer portal invitation
$result = $service->dispatchCustomerPortalInvited(
    'customer@example.com',
    'Customer User',
    'http://127.0.0.1:8000/setup/customer-token-789',
    '2026-04-10 23:59:59',
    null, // user_id
    1     // tenant_id
);

echo "Customer portal dispatched: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
```

### 4. Test Setup Link Expiring

```php
// Test setup link expiring
$result = $service->dispatchSetupLinkExpiring(
    'expiring@example.com',
    'Expiring User',
    'http://127.0.0.1:8000/setup/expiring-token-999',
    '2026-04-10 23:59:59',
    3, // user_id
    1  // tenant_id
);

echo "Setup expiring dispatched: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
```

### 5. Test Setup Link Expired

```php
// Test setup link expired
$result = $service->dispatchSetupLinkExpired(
    'expired@example.com',
    'Expired User',
    4, // user_id
    1  // tenant_id
);

echo "Setup expired dispatched: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
```

## Manual Webhook Test

You can also test the webhook directly using curl:

### Test Account Owner Activation

```bash


```

## Verify Results

### 1. Check Laravel Logs

```bash
tail -f storage/logs/laravel.log
```

Look for:
- `Email status updated in outbox`
- `Account activation dispatched: SUCCESS`
- Any error messages

### 2. Check Database

```sql
-- Check automation_event_outbox
SELECT * FROM automation_event_outbox 
WHERE event IN ('account_owner_paid_signup_created', 'team_member_invited') 
ORDER BY created_at DESC;

-- Check signup_intent updates (if applicable)
SELECT * FROM signup_intent 
WHERE email = 'test@example.com' 
ORDER BY updated_at DESC;
```

### 3. Check n8n Workflow

1. Open your n8n instance
2. Go to "SaaS Event Router - Enhanced" workflow
3. Click "Executions" tab
4. Look for recent executions
5. Verify the events were processed correctly

### 4. Check Email Delivery

1. Check your Brevo dashboard
2. Look for emails sent to test addresses
3. Verify email content and delivery status

## Troubleshooting

### Event Not Dispatching

**Problem**: `dispatchEvent` returns false
**Solution**: Check the validation in `AutomationWebhookService`

```bash
# Check Laravel logs for validation errors
grep "Missing required field" storage/logs/laravel.log
```

### Webhook Not Reaching n8n

**Problem**: No execution in n8n
**Solution**: Check webhook configuration

```bash
# Check your .env settings
echo $N8N_WEBHOOK_BASE_URL
echo $N8N_USE_ROUTER
echo $N8N_ROUTER_PATH
```

### Email Not Sending

**Problem**: No email received
**Solution**: Check Brevo configuration

```bash
# Test Brevo SMTP connection
php artisan tinker
Mail::raw('Test email', function($message) {
    $message->to('test@example.com')->subject('Test');
});
```

### Status Update Failing

**Problem**: Email status not updating
**Solution**: Check API endpoint

```bash
# Test the email status endpoint
curl -X POST http://127.0.0.1:8000/api/n8n/email-status \
  -H "Content-Type: application/json" \
  -d '{"event_name":"test","email":"test@example.com","status":"sent"}'
```

## Complete Integration Test

Once all individual tests pass, run this complete test:

```php
// Complete integration test
$service = app(AccountEventService::class);

$tests = [
    'account_owner_paid_signup_created' => $service->dispatchAccountOwnerPaidSignupCreated(
        'test@example.com', 'Test User', 'http://127.0.0.1:8000/setup/test', '2026-04-10 23:59:59', 1, 1
    ),
    'team_member_invited' => $service->dispatchTeamMemberInvited(
        'team@example.com', 'Team Member', 'http://127.0.0.1:8000/setup/team', '2026-04-10 23:59:59', 2, 1
    ),
    'customer_portal_invited' => $service->dispatchCustomerPortalInvited(
        'customer@example.com', 'Customer', 'http://127.0.0.1:8000/setup/customer', '2026-04-10 23:59:59', null, 1
    ),
    'setup_link_expiring' => $service->dispatchSetupLinkExpiring(
        'expiring@example.com', 'Expiring', 'http://127.0.0.1:8000/setup/expiring', '2026-04-10 23:59:59', 3, 1
    ),
    'setup_link_expired' => $service->dispatchSetupLinkExpired(
        'expired@example.com', 'Expired', 4, 1
    )
];

foreach ($tests as $event => $result) {
    echo "{$event}: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
}
```

## Success Indicators

You'll know the integration is working when:

- [ ] All 5 events dispatch successfully
- [ ] n8n workflow processes each event
- [ ] Emails are sent via Brevo
- [ ] Laravel receives status updates
- [ ] Database records are updated
- [ ] No errors in logs

## Next Steps

Once integration is confirmed:

1. **Add to your application code** - Use `AccountEventService` in your controllers
2. **Set up monitoring** - Track email delivery rates
3. **Add more events** - Expand with additional account events
4. **Optimize templates** - Improve email content and design

Your enhanced SaaS Event Router is now ready for production!
