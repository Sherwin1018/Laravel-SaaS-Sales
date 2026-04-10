<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales & Marketing Funnel System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css">
    <link rel="stylesheet" href="{{ asset('css/landing-page.css') }}">
</head>
<body>
    @if(session('success') || session('error') || $errors->any())
        <div id="statusToastContainer" style="position:fixed;top:18px;right:18px;z-index:99999;">
            <div style="display:flex;gap:12px;align-items:center;background:#fff;border-radius:14px;width:min(90vw,420px);padding:12px 38px 12px 12px;border:1px solid #E6E1EF;box-shadow:0 18px 38px rgba(15,23,42,.2);position:relative;">
                <i aria-hidden="true" style="font-size:22px;color:{{ session('success') ? '#65A30D' : '#B91C1C' }};">{{ session('success') ? '✓' : '!' }}</i>
                <div>
                    <h4 style="margin:0 0 6px 0;font-size:15px;font-weight:800;color:{{ session('success') ? '#65A30D' : '#B91C1C' }};">
                        {{ session('success') ? 'Success' : 'Attention' }}
                    </h4>
                    <p style="margin:0;color:#334155;font-size:14px;font-weight:500;line-height:1.25;">
                        {{ $errors->any() ? $errors->first() : (session('success') ?? session('error')) }}
                    </p>
                </div>
                <button type="button" onclick="landingCloseStatusToast()" aria-label="Close notification"
                    style="position:absolute;top:8px;right:8px;border:none;background:transparent;color:#334155;font-size:16px;cursor:pointer;line-height:1;">
                    x
                </button>
            </div>
        </div>
    @endif
    <div class="landing-page">
        <header class="landing-header">
            <div class="landing-header__inner">
                <a href="{{ route('landing') }}" class="brand-lockup" aria-label="Sales and Marketing Funnel System home">
                    <img src="{{ asset('images/saas_logo.png') }}" alt="Sales and Marketing Funnel System Logo" class="brand-lockup__funnel">
                    <img src="{{ asset('images/logo2.png') }}" alt="Nehemiah Solutions Logo" class="brand-lockup__company">
                </a>

                <button type="button" class="nav-toggle" id="navToggle" aria-expanded="false" aria-controls="landingNav">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <nav class="landing-nav" id="landingNav">
                    <a href="#services">Services</a>
                    <a href="#works">Featured Works</a>
                    <a href="#pricing">Pricing</a>
                    <a href="#faq">FAQ</a>
                    <a href="{{ route('login') }}" class="landing-nav__signin">Sign In</a>
                    <a href="#" class="landing-nav__cta" data-open-onboarding data-plan-code="{{ request('plan', 'growth') }}">Get Started</a>
                </nav>
            </div>
        </header>

        <main class="landing-main">
            <section class="hero">
                <div class="hero__content" data-aos="fade-right" data-aos-duration="900">
                    <span class="hero__eyebrow">Crafted for business growth</span>
                    <h1>Built for Sales and Marketing Funnel Performance.</h1>
                    <p>
                        We help businesses launch funnel journeys, capture leads, connect offers to payment,
                        and operate with role-based dashboards that support account owners, marketing, sales, and finance teams.
                    </p>

                    <div class="hero__actions">
                        <a href="#" class="button button--primary" data-open-onboarding data-plan-code="{{ request('plan', 'growth') }}">Get Started</a>
                        <a href="{{ route('register', ['trial' => 1]) }}" class="button button--trial">Free Trial</a>
                        <a href="#services" class="button button--secondary">Explore Services</a>
                    </div>
                    <p class="hero__trial-note">Start with a 7-day free trial before choosing your full plan.</p>
                </div>

                <div class="hero__visual" data-aos="fade-left" data-aos-duration="900" data-aos-delay="100">
                    <div class="hero-card">
                        <div class="hero-card__glow"></div>
                        <div class="hero-card__screen">
                            <div class="hero-card__metrics">
                                <article>
                                    <span>Funnels</span>
                                    <strong>24</strong>
                                </article>
                                <article>
                                    <span>Leads</span>
                                    <strong>1.2K</strong>
                                </article>
                                <article>
                                    <span>Paid</span>
                                    <strong>96%</strong>
                                </article>
                            </div>
                            @php
                                $heroVideoReady = filled($landingHeroVideoUrl ?? null);
                                $heroVideoWidth = (int) ($landingHeroVideoWidth ?? 1280);
                                $heroVideoHeight = (int) ($landingHeroVideoHeight ?? 720);
                                $heroVideoStyle = ($heroVideoWidth > 0 && $heroVideoHeight > 0)
                                    ? 'aspect-ratio: ' . $heroVideoWidth . ' / ' . $heroVideoHeight . ';'
                                    : '';
                            @endphp
                            <div class="hero-card__device">
                                <div class="hero-demo {{ $heroVideoReady ? '' : 'is-empty' }}" data-hero-demo style="{{ $heroVideoStyle }}">
                                    <video class="hero-demo__video" preload="metadata" playsinline>
                                        @if($heroVideoReady)
                                            <source src="{{ $landingHeroVideoUrl }}" type="video/mp4">
                                        @endif
                                        Your browser does not support the video tag.
                                    </video>
                                    <button type="button" class="hero-demo__play" data-hero-demo-play aria-label="Play product demo video">
                                        <span class="hero-demo__play-icon" aria-hidden="true"></span>
                                    </button>
                                    <div class="hero-demo__meta">
                                        <strong>Watch Product Demo</strong>
                                        <span>3 minutes</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="services" class="services">
                <div class="section-heading section-heading--center" data-aos="fade-up">
                    <h2>Our Services</h2>
                    <p>Built to support the full funnel lifecycle from page creation to payment conversion and dashboard visibility.</p>
                </div>

                <div class="services__grid">
                    <article class="service-card" data-aos="fade-up" data-aos-delay="0">
                        <div class="service-card__icon">01</div>
                        <h3>Funnel Design</h3>
                        <p>Design opt-in, checkout, upsell, downsell, and thank-you steps that match your conversion path.</p>
                        <a href="#" data-open-onboarding data-plan-code="growth">Learn More</a>
                    </article>
                    <article class="service-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="service-card__icon">02</div>
                        <h3>Website and Page Building</h3>
                        <p>Structure branded landing experiences that guide visitors into lead capture and registration.</p>
                        <a href="#" data-open-onboarding data-plan-code="growth">Learn More</a>
                    </article>
                    <article class="service-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="service-card__icon">03</div>
                        <h3>Digital Marketing Flow</h3>
                        <p>Connect campaigns, lead qualification, and team actions so marketing and sales work from one system.</p>
                        <a href="#" data-open-onboarding data-plan-code="growth">Learn More</a>
                    </article>
                </div>
            </section>

            <section id="works" class="featured-works">
                <div class="section-heading section-heading--center" data-aos="fade-up">
                    <h2>Featured Works</h2>
                    <p>A simplified view of how the platform supports acquisition, conversion, and operational visibility.</p>
                </div>

                <div class="featured-works__layout">
                    <aside class="featured-works__menu" data-aos="fade-right">
                        <button type="button" class="work-chip work-chip--active" data-work-filter="all" data-aos="zoom-in" data-aos-delay="0">All Work</button>
                        <button type="button" class="work-chip" data-work-filter="branding" data-aos="zoom-in" data-aos-delay="50">Branding</button>
                        <button type="button" class="work-chip" data-work-filter="marketing" data-aos="zoom-in" data-aos-delay="100">Marketing</button>
                        <button type="button" class="work-chip" data-work-filter="planning" data-aos="zoom-in" data-aos-delay="150">Planning</button>
                        <button type="button" class="work-chip" data-work-filter="research" data-aos="zoom-in" data-aos-delay="200">Research</button>
                    </aside>

                    <div class="featured-works__gallery">
                        <article class="work-panel work-panel--primary work-panel--visible" data-work-card data-work-category="branding marketing" data-aos="fade-up" data-aos-delay="50">
                            <span>Lead Capture Journeys</span>
                            <h3>Pages that move visitors from interest to registration.</h3>
                            <p>Build branded landing experiences that guide visitors into opt-ins, account creation, and offer discovery.</p>
                        </article>
                        <article class="work-panel work-panel--secondary work-panel--visible" data-work-card data-work-category="marketing research" data-aos="fade-up" data-aos-delay="150">
                            <span>Pipeline Management</span>
                            <h3>Track qualified leads, team assignments, and funnel progress.</h3>
                            <p>Monitor where leads are coming from, how they are scored, and which teams should act next.</p>
                        </article>
                        <article class="work-panel work-panel--wide work-panel--visible" data-work-card data-work-category="planning branding" data-aos="fade-up" data-aos-delay="250">
                            <span>Payment and Onboarding</span>
                            <h3>Connect pricing, PayMongo checkout, and Account Owner dashboard access in one smooth experience.</h3>
                            <p>Move from plan selection to billing confirmation to workspace activation without dropping the user out of the flow.</p>
                        </article>
                        <article class="work-panel work-panel--marketing" data-work-card data-work-category="marketing" hidden>
                            <span>Campaign Automation</span>
                            <h3>Launch nurture sequences that keep leads warm until they are ready to buy.</h3>
                            <p>Coordinate campaign messaging, follow-up timing, and handoff points between marketing and sales teams.</p>
                        </article>
                        <article class="work-panel work-panel--planning" data-work-card data-work-category="planning" hidden>
                            <span>Funnel Planning</span>
                            <h3>Map every stage from traffic source to payment conversion before launch.</h3>
                            <p>Design the route for opt-ins, upsells, downsells, and onboarding touchpoints with clear ownership.</p>
                        </article>
                        <article class="work-panel work-panel--wide work-panel--research" data-work-card data-work-category="research" hidden>
                            <span>Performance Research</span>
                            <h3>Use funnel visibility to see what converts, what stalls, and what needs optimization.</h3>
                            <p>Review acquisition quality, pipeline movement, and checkout completion so the next campaign is sharper.</p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="pricing" class="pricing">
                <div class="section-heading section-heading--center" data-aos="fade-up">
                    <h2>Pricing Plans</h2>
                    <p>Choose a plan that fits your stage and move directly into the registration and payment flow.</p>
                </div>

                <div class="pricing__grid">
                    @foreach($plans as $index => $plan)
                        <article class="pricing-card {{ $plan['spotlight'] ? 'pricing-card--featured' : '' }}" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                            @php
                                $isTrialPlan = $plan['code'] === 'free-trial';
                                $formatLimit = static fn ($value) => is_null($value) ? 'Unlimited' : number_format((int) $value);
                            @endphp

                            <div class="pricing-card__limits">
                                <button type="button" class="pricing-card__limits-trigger" aria-label="View {{ $plan['name'] }} plan limits">
                                    <span aria-hidden="true">i</span>
                                </button>
                                <div class="pricing-card__limits-panel">
                                    <h4>Plan Limits</h4>
                                    <ul>
                                        <li><span>Max Users</span><strong>{{ $formatLimit($plan['max_users'] ?? null) }}</strong></li>
                                        <li><span>Max Leads</span><strong>{{ $formatLimit($plan['max_leads'] ?? null) }}</strong></li>
                                        <li><span>Max Funnels</span><strong>{{ $formatLimit($plan['max_funnels'] ?? null) }}</strong></li>
                                        <li><span>Max Workflows</span><strong>{{ $formatLimit($plan['max_workflows'] ?? null) }}</strong></li>
                                        <li><span>Max Monthly Messages</span><strong>{{ $formatLimit($plan['max_monthly_messages'] ?? null) }}</strong></li>
                                    </ul>
                                </div>
                            </div>

                            @if($plan['spotlight'])
                                <span class="pricing-card__badge">{{ $plan['spotlight'] }}</span>
                            @endif
                            <h3>{{ $plan['name'] }}</h3>
                            <p class="pricing-card__summary">{{ $plan['summary'] }}</p>
                            <div class="pricing-card__price">
                                <strong>{{ (float) $plan['price'] <= 0 ? 'Free' : 'PHP ' . number_format($plan['price'], 0) }}</strong>
                                <span>{{ $plan['period'] }}</span>
                            </div>
                            <ul>
                                @foreach($plan['features'] as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                            @if($isTrialPlan)
                                <a href="{{ route('register', ['trial' => 1]) }}" class="button button--trial-card">
                                    Start Free Trial
                                </a>
                            @else
                                <a href="#" data-open-onboarding data-plan-code="{{ $plan['code'] }}" class="button {{ $plan['spotlight'] ? 'button--primary' : 'button--secondary' }}">
                                    Get Started
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="faq" class="faq">
                <div class="section-heading section-heading--center" data-aos="fade-up">
                    <h2>Frequently Asked Questions</h2>
                    <p>Clear answers for new users before they register and complete payment.</p>
                </div>

                <div class="faq__list">
                    <details class="faq__item" open data-aos="fade-up" data-aos-delay="0">
                        <summary>What happens after the user completes payment?</summary>
                        <p>After successful payment, the system creates the tenant and Account Owner in pending activation, then sends a setup email. The user must verify and set password before dashboard access.</p>
                    </details>
                    <details class="faq__item" data-aos="fade-up" data-aos-delay="100">
                        <summary>Will the super admin see the new account?</summary>
                        <p>Yes. Successful paid registrations are recorded so the super admin side can view the new onboarded account and billing-related data.</p>
                    </details>
                    <details class="faq__item" data-aos="fade-up" data-aos-delay="200">
                        <summary>Can the platform support both sales and marketing teams?</summary>
                        <p>Yes. The system is built for shared funnel visibility while still giving different roles the dashboards and permissions they need.</p>
                    </details>
                    <details class="faq__item" data-aos="fade-up" data-aos-delay="300">
                        <summary>Can super admins update the landing hero demo video?</summary>
                        <p>Yes. Super admins can upload, replace, or delete the landing hero demo video from the dashboard settings, and the fallback demo card appears automatically when no video is set.</p>
                    </details>
                </div>
            </section>
        </main>

        <div id="onboardingModal" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); display: none; align-items: center; justify-content: center; padding: 18px; z-index: 9999;">
            <div role="dialog" aria-modal="true" aria-labelledby="onboardingModalTitle" style="width: min(100%, 620px); max-height: 92vh; overflow: auto; background: #ffffff; border-radius: 18px; box-shadow: 0 25px 80px rgba(15, 23, 42, 0.25); padding: 22px;">
                <div style="display: flex; justify-content: space-between; align-items: start; gap: 12px; margin-bottom: 16px;">
                    <div>
                        <h3 id="onboardingModalTitle" style="margin: 0 0 6px; font-size: 1.3rem;">Start Your Account Owner Onboarding</h3>
                        <p style="margin: 0; color: #475569;">Complete this short form, then proceed to payment.</p>
                    </div>
                    <button type="button" id="onboardingModalClose" aria-label="Close onboarding modal" style="width: 38px; height: 38px; border: none; border-radius: 999px; background: #E2E8F0; color: #0F172A; font-size: 22px; cursor: pointer;">&times;</button>
                </div>

                @php
                    $googleVerified = is_array($googleSignupVerified ?? null) ? $googleSignupVerified : [];
                    $googleContext = is_array($googleSignupContext ?? null) ? $googleSignupContext : [];
                    $hasGoogleVerified = $googleVerified !== [];
                @endphp
                <form id="landingOnboardingForm" method="POST" action="{{ route('register.post') }}">
                    @csrf
                    <input type="hidden" name="plan" id="onboardingPlanInput" value="{{ old('plan', (string) ($googleContext['plan'] ?? request('plan', 'growth'))) }}">

                    <div style="display: grid; gap: 12px;">
                        <div>
                            <label for="full_name" style="display:block;margin-bottom:6px;font-weight:600;color:#0F172A;">Full Name</label>
                            <input id="full_name" name="full_name" type="text" maxlength="255" required value="{{ old('full_name', (string) ($googleVerified['full_name'] ?? $googleContext['full_name'] ?? '')) }}" {{ $hasGoogleVerified ? 'readonly' : '' }} style="width:100%;padding:10px 12px;border:1px solid #CBD5E1;border-radius:10px;">
                        </div>

                        <div>
                            <label for="email" style="display:block;margin-bottom:6px;font-weight:600;color:#0F172A;">Email Address</label>
                            <input id="email" name="email" type="email" maxlength="255" required value="{{ old('email', (string) ($googleVerified['email'] ?? $googleContext['email'] ?? '')) }}" {{ $hasGoogleVerified ? 'readonly' : '' }} style="width:100%;padding:10px 12px;border:1px solid #CBD5E1;border-radius:10px;">
                            <small style="display:block;margin-top:6px;color:#280137;">Please enter a valid email address</small>
                        </div>

                        <div>
                            <label for="mobile" style="display:block;margin-bottom:6px;font-weight:600;color:#0F172A;">Mobile Number</label>
                            <input id="mobile" name="mobile" type="text" pattern="^09\d{9}$" placeholder="09XXXXXXXXX" required value="{{ old('mobile', (string) ($googleContext['mobile'] ?? '')) }}" style="width:100%;padding:10px 12px;border:1px solid #CBD5E1;border-radius:10px;">
                        </div>

                        <div>
                            <label for="company_name" style="display:block;margin-bottom:6px;font-weight:600;color:#0F172A;">Company Name</label>
                            <input id="company_name" name="company_name" type="text" maxlength="255" required value="{{ old('company_name', (string) ($googleContext['company_name'] ?? '')) }}" style="width:100%;padding:10px 12px;border:1px solid #CBD5E1;border-radius:10px;">
                        </div>

                        <div>
                            <label for="onboardingPlanPreview" style="display:block;margin-bottom:6px;font-weight:600;color:#0F172A;">Selected Plan</label>
                            <input id="onboardingPlanPreview" type="text" readonly value="{{ strtoupper((string) old('plan', (string) ($googleContext['plan'] ?? request('plan', 'growth')))) }}" style="width:100%;padding:10px 12px;border:1px solid #CBD5E1;border-radius:10px;background:#F8FAFC;font-weight:700;">
                        </div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;flex-wrap:wrap;">
                        <button type="button" id="onboardingModalCancel" style="padding:10px 16px;border:none;border-radius:10px;background:#E2E8F0;color:#0F172A;font-weight:600;cursor:pointer;">Cancel</button>
                        <button type="button" id="onboardingGoogleButton" style="padding:10px 16px;border:1px solid #CBD5E1;border-radius:10px;background:#FFFFFF;color:#0F172A;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:8px;">
                            <span aria-hidden="true" style="display:inline-flex;width:18px;height:18px;border-radius:50%;align-items:center;justify-content:center;background:#fff;border:1px solid #CBD5E1;color:#ea4335;font-weight:800;font-size:12px;">G</span>
                            Continue with Google
                        </button>
                        <button type="submit" style="padding:10px 16px;border:none;border-radius:10px;background:#240E35;color:#ffffff;font-weight:700;cursor:pointer;">Continue to Payment</button>
                    </div>
                </form>
                <form id="landingGoogleSignupForm" method="POST" action="{{ route('auth.google.signup.redirect') }}" style="display:none;">
                    @csrf
                    <input type="hidden" name="full_name" id="google_signup_full_name">
                    <input type="hidden" name="email" id="google_signup_email">
                    <input type="hidden" name="mobile" id="google_signup_mobile">
                    <input type="hidden" name="company_name" id="google_signup_company_name">
                    <input type="hidden" name="plan" id="google_signup_plan">
                </form>
            </div>
        </div>

        <footer class="landing-footer">
            <div class="landing-footer__top" data-aos="fade-up">
                <div class="landing-footer__brand">
                    <div class="landing-footer__logos">
                        <img src="{{ asset('images/saas_logo.png') }}" alt="Sales and Marketing Funnel System Logo" class="landing-footer__funnel">
                        <img src="{{ asset('images/logo2.png') }}" alt="Nehemiah Solutions Logo" class="landing-footer__company">
                    </div>
                    <p>
                        Built for teams that want to run campaigns, capture leads, and move people through pricing and onboarding with less friction.
                    </p>
                </div>

                <div class="landing-footer__columns">
                    <div class="landing-footer__column">
                        <h4>Platform</h4>
                        <a href="#services">Services</a>
                        <a href="#works">Featured Works</a>
                        <a href="#pricing">Pricing</a>
                    </div>
                    <div class="landing-footer__column">
                        <h4>Company</h4>
                        <a href="#" data-open-onboarding data-plan-code="{{ request('plan', 'growth') }}">Register</a>
                        <a href="{{ route('login') }}">Sign In</a>
                        <a href="{{ route('register', ['trial' => 1]) }}">Free Trial</a>
                    </div>
                    <div class="landing-footer__column">
                        <h4>Support</h4>
                        <a href="#faq">FAQ</a>
                        <a href="#pricing">Plans</a>
                        <a href="#" data-open-onboarding data-plan-code="{{ request('plan', 'growth') }}">Contact Team</a>
                    </div>
                </div>
            </div>

            <div class="landing-footer__bottom">
                <span>&copy; {{ now()->year }} Nehemiah Solutions. All rights reserved.</span>
                <div class="landing-footer__legal">
                    <a href="#faq">Terms</a>
                    <a href="#faq">Privacy</a>
                </div>
            </div>
        </footer>
    </div>

    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>
        function landingCloseStatusToast() {
            const toastContainer = document.getElementById('statusToastContainer');
            if (toastContainer) {
                toastContainer.style.display = 'none';
            }
        }
        if (document.getElementById('statusToastContainer')) {
            setTimeout(landingCloseStatusToast, 3500);
        }

        if (window.AOS) {
            AOS.init({
                duration: 800,
                easing: 'ease-out-cubic',
                once: false,
                mirror: true,
                offset: 80,
            });
        }

        const navToggle = document.getElementById('navToggle');
        const landingNav = document.getElementById('landingNav');
        const workFilterButtons = document.querySelectorAll('[data-work-filter]');
        const workCards = document.querySelectorAll('[data-work-card]');
        const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const onboardingModal = document.getElementById('onboardingModal');
        const onboardingPlanInput = document.getElementById('onboardingPlanInput');
        const onboardingPlanPreview = document.getElementById('onboardingPlanPreview');
        const onboardingOpeners = document.querySelectorAll('[data-open-onboarding]');
        const onboardingModalClose = document.getElementById('onboardingModalClose');
        const onboardingModalCancel = document.getElementById('onboardingModalCancel');
        const onboardingForm = document.getElementById('landingOnboardingForm');
        const onboardingGoogleButton = document.getElementById('onboardingGoogleButton');
        const landingGoogleSignupForm = document.getElementById('landingGoogleSignupForm');

        const openOnboardingModal = (planCode) => {
            if (!onboardingModal) return;
            if (onboardingPlanInput) {
                onboardingPlanInput.value = planCode || 'growth';
            }
            if (onboardingPlanPreview) {
                onboardingPlanPreview.value = String(planCode || 'growth').toUpperCase();
            }
            onboardingModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        };

        const closeOnboardingModal = () => {
            if (!onboardingModal) return;
            onboardingModal.style.display = 'none';
            document.body.style.overflow = '';
        };

        const validateOnboardingForm = () => {
            if (!onboardingForm) return false;
            const nativeValid = onboardingForm.reportValidity();
            if (!nativeValid) return false;
            const mobileInput = onboardingForm.querySelector('#mobile');
            if (mobileInput) {
                const mobileValue = String(mobileInput.value || '').trim();
                const isValidMobile = /^09\d{9}$/.test(mobileValue);
                if (!isValidMobile) {
                    mobileInput.setCustomValidity('Mobile number must be in 09XXXXXXXXX format.');
                    onboardingForm.reportValidity();
                    return false;
                }
                mobileInput.setCustomValidity('');
            }

            return true;
        };

        onboardingOpeners.forEach((opener) => {
            opener.addEventListener('click', (event) => {
                event.preventDefault();
                openOnboardingModal(opener.dataset.planCode || 'growth');
            });
        });

        if (onboardingModalClose) {
            onboardingModalClose.addEventListener('click', closeOnboardingModal);
        }
        if (onboardingModalCancel) {
            onboardingModalCancel.addEventListener('click', closeOnboardingModal);
        }
        if (onboardingModal) {
            onboardingModal.addEventListener('click', (event) => {
                if (event.target === onboardingModal) {
                    closeOnboardingModal();
                }
            });
        }
        if (onboardingGoogleButton && onboardingForm && landingGoogleSignupForm) {
            onboardingGoogleButton.addEventListener('click', () => {
                const fullName = onboardingForm.querySelector('#full_name');
                const email = onboardingForm.querySelector('#email');
                const mobile = onboardingForm.querySelector('#mobile');
                const companyName = onboardingForm.querySelector('#company_name');
                const plan = onboardingForm.querySelector('#onboardingPlanInput');

                landingGoogleSignupForm.querySelector('#google_signup_full_name').value = fullName ? fullName.value : '';
                landingGoogleSignupForm.querySelector('#google_signup_email').value = email ? email.value : '';
                landingGoogleSignupForm.querySelector('#google_signup_mobile').value = mobile ? mobile.value : '';
                landingGoogleSignupForm.querySelector('#google_signup_company_name').value = companyName ? companyName.value : '';
                landingGoogleSignupForm.querySelector('#google_signup_plan').value = plan ? plan.value : 'growth';
                landingGoogleSignupForm.submit();
            });
        }
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeOnboardingModal();
            }
        });

        @if(session('open_onboarding_modal') || $errors->any())
            openOnboardingModal(@json((string) request('plan', 'growth')));
        @endif

        const setActiveNavHash = (hash) => {
            if (!landingNav) return;

            const navLinks = Array.from(landingNav.querySelectorAll('a[href^="#"]'));
            navLinks.forEach((link) => {
                const isActive = link.getAttribute('href') === hash;
                link.classList.toggle('is-active', isActive);
                link.setAttribute('aria-current', isActive ? 'true' : 'false');
            });
        };

        if (navToggle && landingNav) {
            navToggle.addEventListener('click', () => {
                const isOpen = landingNav.classList.toggle('open');
                navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            landingNav.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', (event) => {
                    const href = link.getAttribute('href') || '';
                    const isHashLink = href.startsWith('#') && href.length > 1;

                    if (isHashLink) {
                        const target = document.querySelector(href);
                        if (target) {
                            event.preventDefault();
                            history.pushState(null, '', href);
                            target.scrollIntoView({ behavior: prefersReducedMotion ? 'auto' : 'smooth', block: 'start' });
                        }
                    }

                    landingNav.classList.remove('open');
                    navToggle.setAttribute('aria-expanded', 'false');
                });
            });
        }

        if (landingNav) {
            const navLinks = Array.from(landingNav.querySelectorAll('a[href^="#"]'));
            const sections = navLinks
                .map((link) => {
                    const href = link.getAttribute('href') || '';
                    return href.length > 1 ? document.querySelector(href) : null;
                })
                .filter((section) => section && section.id);

            const updateActiveSection = () => {
                if (!sections.length) {
                    setActiveNavHash('');
                    return;
                }

                const headerHeight = document.querySelector('.landing-header')?.offsetHeight || 0;
                const viewportProbe = window.scrollY + headerHeight + 140;
                let activeHash = '';

                sections.forEach((section) => {
                    const top = section.offsetTop;
                    const bottom = top + section.offsetHeight;
                    if (viewportProbe >= top && viewportProbe < bottom) {
                        activeHash = `#${section.id}`;
                    }
                });

                setActiveNavHash(activeHash);
            };

            setActiveNavHash('');
            window.addEventListener('scroll', updateActiveSection, { passive: true });
            window.addEventListener('resize', updateActiveSection);
            window.addEventListener('hashchange', updateActiveSection);
            updateActiveSection();
        }

        if (workFilterButtons.length && workCards.length) {
            const setWorkFilter = (filter) => {
                workFilterButtons.forEach((button) => {
                    button.classList.toggle('work-chip--active', button.dataset.workFilter === filter);
                });

                let visibleCount = 0;
                workCards.forEach((card) => {
                    const categories = (card.dataset.workCategory || '').split(' ').filter(Boolean);
                    const shouldShow = filter === 'all' || categories.includes(filter);

                    card.hidden = !shouldShow;
                    card.classList.toggle('work-panel--visible', shouldShow);
                    if (shouldShow) {
                        visibleCount += 1;
                    }
                });

                const worksGallery = document.querySelector('.featured-works__gallery');
                if (worksGallery) {
                    worksGallery.dataset.visibleCount = String(visibleCount);
                    worksGallery.classList.toggle('is-filtered', filter !== 'all');
                }

                if (window.AOS) {
                    AOS.refresh();
                }
            };

            workFilterButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    setWorkFilter(button.dataset.workFilter || 'all');
                });
            });

            setWorkFilter('all');
        }

        const heroDemos = document.querySelectorAll('[data-hero-demo]');
        heroDemos.forEach((demo) => {
            const video = demo.querySelector('.hero-demo__video');
            const playButton = demo.querySelector('[data-hero-demo-play]');
            if (!video || !playButton) return;
            const source = video.querySelector('source');
            const hasVideoSource = !!(source && source.getAttribute('src'));

            if (!hasVideoSource) {
                playButton.setAttribute('aria-disabled', 'true');
                return;
            }

            playButton.addEventListener('click', () => {
                const isPlaying = !video.paused && !video.ended;
                if (isPlaying) {
                    video.pause();
                    return;
                }

                demo.classList.add('is-playing');
                video.setAttribute('controls', 'controls');
                video.play().catch(() => {
                    demo.classList.remove('is-playing');
                });
            });

            video.addEventListener('pause', () => {
                demo.classList.remove('is-playing');
            });

            video.addEventListener('ended', () => {
                demo.classList.remove('is-playing');
            });
        });
    </script>
</body>
</html>
