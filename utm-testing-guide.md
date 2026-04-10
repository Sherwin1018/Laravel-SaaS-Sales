# UTM System Testing Guide

## 🎯 Quick Test Steps

### Step 1: Test UTM Capture
```bash
# Visit a funnel with UTM parameters
http://localhost:8000/f/your-funnel-slug?utm_source=facebook&utm_medium=cpc&utm_campaign=test_campaign&utm_term=spring_sale

# Check if UTM data is captured in session
# (Look for session data: link_tracking_utm_{funnelId})
```

### Step 2: Test Lead Creation with UTM
1. Opt-in through the funnel form
2. Check the created lead:
```bash
php artisan tinker
$lead = App\Models\Lead::latest()->first();
echo "Source Campaign: " . $lead->source_campaign . PHP_EOL;
```

### Step 3: Test Link Tracking
1. Send an email with tracked links to a lead
2. Click the tracked link (should be `/r/{token}` format)
3. Check if LeadLinkClick is created with UTM data:
```bash
php artisan tinker
$click = App\Models\LeadLinkClick::latest()->first();
echo "UTM Source: " . $click->utm_source . PHP_EOL;
echo "UTM Medium: " . $click->utm_medium . PHP_EOL;
echo "UTM Campaign: " . $click->utm_campaign . PHP_EOL;
```

### Step 4: Test Analytics Dashboard
1. Visit: `/dashboard/marketing`
2. Check sections:
   - Link Performance Intelligence
   - Source Performance Analysis  
   - Overall Conversion Summary

## 🔧 Manual Testing Commands

### Check Database Schema
```bash
# Verify UTM columns exist
php artisan tinker
$schema = Schema::getColumnListing('lead_link_clicks');
print_r(array_intersect($schema, ['utm_source', 'utm_medium', 'utm_campaign']));
```

### Test Session Storage
```bash
# Simulate UTM capture
php artisan tinker
session(['link_tracking_utm_1' => ['utm_source' => 'facebook', 'utm_medium' => 'cpc', 'utm_campaign' => 'test']]);
echo session('link_tracking_utm_1');
```

### Test Link Token Generation
```bash
php artisan tinker
$service = app(App\Services\LeadLinkTrackingService::class);
$token = $service->generateToken([
    'tenant_id' => 1,
    'lead_id' => 1,
    'destination_url' => 'https://example.com'
]);
echo "Generated token: " . $token . PHP_EOL;
echo "Tracked URL: " . url('/r/' . $token) . PHP_EOL;
```

## 🚀 Full End-to-End Test

### 1. Create Test Data
```bash
php artisan tinker
$tenant = App\Models\Tenant::first();
$funnel = App\Models\Funnel::where('tenant_id', $tenant->id)->first();
echo "Funnel slug: " . $funnel->slug . PHP_EOL;
```

### 2. Test URL Generation
```bash
# Build test URL with UTM parameters
$baseUrl = 'http://localhost:8000';
$funnelSlug = 'your-funnel-slug'; // Replace with actual slug
$utmParams = '?utm_source=facebook&utm_medium=cpc&utm_campaign=test_campaign';
$testUrl = $baseUrl . '/f/' . $funnelSlug . $utmParams;
echo "Test URL: " . $testUrl . PHP_EOL;
```

### 3. Verify Data Flow
```bash
# Check funnel visits
php artisan tinker
$visits = App\Models\FunnelVisit::where('utm_source', 'facebook')->get();
echo "Funnel visits with UTM: " . $visits->count() . PHP_EOL;

# Check leads with UTM
$leads = App\Models\Lead::where('source_campaign', 'facebook')->get();
echo "Leads with UTM source: " . $leads->count() . PHP_EOL;

# Check link clicks with UTM
$clicks = App\Models\LeadLinkClick::where('utm_source', 'facebook')->get();
echo "Link clicks with UTM: " . $clicks->count() . PHP_EOL;
```

## 🐛 Common Issues & Solutions

### Issue 1: UTM not captured
**Symptoms**: No UTM data in session or database
**Solutions**:
- Check funnel URL format
- Verify FunnelPortalController::show() method
- Check session configuration

### Issue 2: Link tracking not working
**Symptoms**: `/r/{token}` returns 404
**Solutions**:
- Verify route exists: `php artisan route:list | grep link.tracking`
- Check LinkTrackingController::redirect() method
- Test token generation/decoding

### Issue 3: Analytics showing no data
**Symptoms**: Dashboard shows empty tables
**Solutions**:
- Verify UTMAnalyticsService methods
- Check database queries
- Test with sample data

## 📊 Expected Results

### Successful Test Should Show:
1. ✅ UTM parameters in session after funnel visit
2. ✅ Lead created with source_campaign field populated
3. ✅ Link clicks recorded with UTM fields
4. ✅ Dashboard showing UTM analytics data
5. ✅ Source performance table populated
6. ✅ Link performance with UTM attribution

### Test Data Examples:
```
UTM Source: facebook
UTM Medium: cpc  
UTM Campaign: test_campaign
Link Clicks: 5+
Leads Generated: 2+
Conversion Rate: Calculated percentage
```

## 🎯 Quick Validation Script

Run this to verify everything works:
```bash
php artisan tinker
echo "=== UTM System Validation ===" . PHP_EOL;
echo "1. UTM Columns in lead_link_clicks: " . (Schema::hasColumn('lead_link_clicks', 'utm_source') ? '✅' : '❌') . PHP_EOL;
echo "2. Link Tracking Route: " . (Route::has('link.tracking') ? '✅' : '❌') . PHP_EOL;
echo "3. Total Funnels: " . App\Models\Funnel::count() . PHP_EOL;
echo "4. Total Leads: " . App\Models\Lead::count() . PHP_EOL;
echo "5. Link Clicks with UTM: " . App\Models\LeadLinkClick::whereNotNull('utm_source')->count() . PHP_EOL;
echo "=== End Validation ===" . PHP_EOL;
```

## 📞 Next Steps

1. **Run the validation script** above
2. **Test manual funnel visit** with UTM parameters
3. **Create a test lead** through the funnel
4. **Test link tracking** with generated tokens
5. **Verify dashboard analytics** display correctly

If any step fails, check the specific component mentioned in the troubleshooting section.
