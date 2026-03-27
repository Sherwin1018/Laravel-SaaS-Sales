<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/register-page.css') }}">
</head>
<body>
    @if(session('success') || session('error') || $errors->any() || $paymentCancelled)
        <div id="statusToastContainer" class="status-toast-container">
            <div class="status-toast {{ session('success') ? 'success' : 'error' }}">
                <i class="fas {{ session('success') ? 'fa-check' : 'fa-times' }}"></i>
                <div>
                    <h4>{{ session('success') ? 'Success!' : 'Attention' }}</h4>
                    <p>
                        @if($paymentCancelled)
                            Payment was cancelled. You can continue your registration anytime.
                        @elseif($errors->any())
                            {{ $errors->first() }}
                        @else
                            {{ session('success') ?? session('error') }}
                        @endif
                    </p>
                </div>
                <button type="button" onclick="closeStatusToast()" aria-label="Close notification">
                    <i class="fas fa-times-circle"></i>
                </button>
            </div>
        </div>
    @endif

    <div class="login-container register-container">
        <div class="info-panel">
            <img src="{{ asset('images/logo3.png') }}" alt="Funnel System Logo" class="info-logo">
            <h1>Start Your Funnel Growth Journey</h1>
            <p class="info-subtitle">Create your account, choose your plan, and move into a payment-connected onboarding flow.</p>

            <ul class="features">
                <li><strong>Account Owner Setup:</strong> Successful payment creates the tenant and Account Owner account</li>
                <li><strong>Pricing Modal:</strong> Choose the best plan before continuing to checkout</li>
                <li><strong>Direct Dashboard Access:</strong> Paid users are redirected into the Account Owner dashboard</li>
            </ul>
        </div>

        <div class="login-card register-card">
            <a href="{{ route('landing') }}" class="back-link">
                <i class="fas fa-arrow-left"></i>
                <span>Back to landing page</span>
            </a>

            <img src="{{ asset('images/logo2.png') }}" alt="Funnel System Logo" class="login-logo">

            <h1>Register to Funnel System</h1>
            <p class="subtitle">
                {{ $trialMode ? 'Create your account and start your 7-day free trial with no upfront payment.' : 'Create your account before selecting a pricing plan' }}
            </p>

            <form id="registerForm" method="POST" action="{{ route('register.post') }}" novalidate>
                @csrf
                <input type="hidden" name="trial_mode" id="trialModeInput" value="{{ $trialMode ? '1' : '0' }}">
                <input type="hidden" name="plan" id="selectedPlanInput" value="{{ old('plan', $selectedPlan ?: 'growth') }}">

                <label for="full_name">Full Name</label>
                <input type="text" name="full_name" id="full_name" value="{{ old('full_name') }}" placeholder="Full Name" required>

                <label for="company_name">Company Name</label>
                <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}" placeholder="Company Name" required>

                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="Email Address" required>

                <label for="password">Password</label>
                <div class="password-container">
                    <input type="password" name="password" id="passwordField" placeholder="Password" required>
                    <i class="fas fa-eye toggle-password" data-target="passwordField"></i>
                </div>

                <label for="password_confirmation">Confirm Password</label>
                <div class="password-container">
                    <input type="password" name="password_confirmation" id="passwordConfirmationField" placeholder="Confirm Password" required>
                    <i class="fas fa-eye toggle-password" data-target="passwordConfirmationField"></i>
                </div>

                <p class="password-hint">Use 12 to 64 characters with uppercase, lowercase, number, and special character.</p>

                <button type="button" id="openPricingModalButton">{{ $trialMode ? 'Start Free Trial' : 'Register and Choose Plan' }}</button>
            </form>

            <p class="register-link">
                Already have an account? <a href="{{ route('login') }}">Login here</a>
            </p>
        </div>
    </div>

    <div class="modal-backdrop" id="pricingModal" aria-hidden="true">
        <div class="pricing-modal" role="dialog" aria-modal="true" aria-labelledby="pricingModalTitle">
            <button type="button" class="modal-close" id="closePricingModalButton" aria-label="Close pricing modal">
                <i class="fas fa-times"></i>
            </button>

            <div class="modal-header">
                <h3 id="pricingModalTitle">Choose your pricing plan</h3>
                <p>Select a plan below to continue to the PayMongo-connected payment flow.</p>
            </div>

            <div class="modal-grid">
                @foreach($plans as $plan)
                    <article class="plan-card {{ $plan['code'] === ($selectedPlan ?: 'growth') ? 'selected' : '' }}" data-plan-card="{{ $plan['code'] }}">
                        @if($plan['spotlight'])
                            <span class="plan-badge">{{ $plan['spotlight'] }}</span>
                        @endif
                        <h4>{{ $plan['name'] }}</h4>
                        <p class="plan-price">PHP {{ number_format($plan['price'], 0) }}</p>
                        <p class="plan-period">{{ $plan['period'] }}</p>
                        <p class="plan-summary">{{ $plan['summary'] }}</p>
                        <ul>
                            @foreach($plan['features'] as $feature)
                                <li>{{ $feature }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="plan-button" data-plan="{{ $plan['code'] }}">Continue with {{ $plan['name'] }}</button>
                    </article>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('registerForm');
        const pricingModal = document.getElementById('pricingModal');
        const openPricingModalButton = document.getElementById('openPricingModalButton');
        const closePricingModalButton = document.getElementById('closePricingModalButton');
        const selectedPlanInput = document.getElementById('selectedPlanInput');
        const trialModeInput = document.getElementById('trialModeInput');
        const storageKey = 'funnel-motion-register-form';
        const isTrialMode = trialModeInput && trialModeInput.value === '1';

        function closeStatusToast() {
            const toastContainer = document.getElementById('statusToastContainer');
            if (toastContainer) {
                toastContainer.style.display = 'none';
            }
        }

        if (document.getElementById('statusToastContainer')) {
            setTimeout(closeStatusToast, 3000);
        }

        document.querySelectorAll('.toggle-password').forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const target = document.getElementById(toggle.dataset.target);
                if (!target) return;

                if (target.type === 'password') {
                    target.type = 'text';
                    toggle.classList.remove('fa-eye');
                    toggle.classList.add('fa-eye-slash');
                } else {
                    target.type = 'password';
                    toggle.classList.remove('fa-eye-slash');
                    toggle.classList.add('fa-eye');
                }
            });
        });

        function saveFormState() {
            const payload = {
                full_name: form.querySelector('[name="full_name"]').value,
                company_name: form.querySelector('[name="company_name"]').value,
                email: form.querySelector('[name="email"]').value,
                plan: selectedPlanInput.value
            };

            localStorage.setItem(storageKey, JSON.stringify(payload));
        }

        function restoreFormState() {
            try {
                const payload = JSON.parse(localStorage.getItem(storageKey) || '{}');
                ['full_name', 'company_name', 'email'].forEach((field) => {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input && !input.value && payload[field]) {
                        input.value = payload[field];
                    }
                });

                if (payload.plan && !selectedPlanInput.value) {
                    selectedPlanInput.value = payload.plan;
                }
            } catch (error) {
                localStorage.removeItem(storageKey);
            }
        }

        function openPricingModal() {
            pricingModal.classList.add('open');
            pricingModal.setAttribute('aria-hidden', 'false');
            saveFormState();
        }

        function closePricingModal() {
            pricingModal.classList.remove('open');
            pricingModal.setAttribute('aria-hidden', 'true');
        }

        function validateForm() {
            if (!form.reportValidity()) {
                return false;
            }

            const password = document.getElementById('passwordField').value;
            const confirmation = document.getElementById('passwordConfirmationField').value;
            if (password !== confirmation) {
                document.getElementById('passwordConfirmationField').setCustomValidity('Passwords do not match.');
                form.reportValidity();
                return false;
            }

            document.getElementById('passwordConfirmationField').setCustomValidity('');
            return true;
        }

        openPricingModalButton.addEventListener('click', () => {
            if (!validateForm()) {
                return;
            }

            if (isTrialMode) {
                saveFormState();
                form.submit();
                return;
            }

            openPricingModal();
        });

        closePricingModalButton.addEventListener('click', closePricingModal);
        pricingModal.addEventListener('click', (event) => {
            if (event.target === pricingModal) {
                closePricingModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closePricingModal();
            }
        });

        form.querySelectorAll('input').forEach((input) => {
            input.addEventListener('input', saveFormState);
        });

        document.querySelectorAll('.plan-button').forEach((button) => {
            button.addEventListener('click', () => {
                selectedPlanInput.value = button.dataset.plan;
                saveFormState();
                form.submit();
            });
        });

        document.querySelectorAll('.plan-card').forEach((card) => {
            card.addEventListener('mouseenter', () => {
                selectedPlanInput.value = card.dataset.planCard;
            });
        });

        restoreFormState();
    </script>
</body>
</html>
