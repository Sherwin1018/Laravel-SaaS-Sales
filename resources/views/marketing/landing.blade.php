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
                    <a href="{{ route('register') }}" class="landing-nav__cta">Get Started</a>
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
                        <a href="{{ route('register') }}" class="button button--primary">Get Started</a>
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
                            <div class="hero-card__device">
                                <div class="hero-card__content">
                                    <div class="hero-card__stage hero-card__stage--primary">
                                        <span>Top of Funnel</span>
                                        <strong>Traffic to Opt-In</strong>
                                        <p>Capture visitors with campaign-driven landing pages and lead entry points.</p>
                                    </div>
                                    <div class="hero-card__stage-grid">
                                        <div class="hero-card__stage">
                                            <span>Middle Funnel</span>
                                            <strong>Lead Nurturing</strong>
                                            <p>Score, qualify, and route leads into the right sales actions.</p>
                                        </div>
                                        <div class="hero-card__stage">
                                            <span>Bottom Funnel</span>
                                            <strong>Checkout to Owner</strong>
                                            <p>Connect pricing, payment, and Account Owner onboarding in one path.</p>
                                        </div>
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
                        <a href="{{ route('register') }}">Learn More</a>
                    </article>
                    <article class="service-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="service-card__icon">02</div>
                        <h3>Website and Page Building</h3>
                        <p>Structure branded landing experiences that guide visitors into lead capture and registration.</p>
                        <a href="{{ route('register') }}">Learn More</a>
                    </article>
                    <article class="service-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="service-card__icon">03</div>
                        <h3>Digital Marketing Flow</h3>
                        <p>Connect campaigns, lead qualification, and team actions so marketing and sales work from one system.</p>
                        <a href="{{ route('register') }}">Learn More</a>
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
                        <article class="work-panel work-panel--research" data-work-card data-work-category="research" hidden>
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
                            @if($plan['spotlight'])
                                <span class="pricing-card__badge">{{ $plan['spotlight'] }}</span>
                            @endif
                            <h3>{{ $plan['name'] }}</h3>
                            <p class="pricing-card__summary">{{ $plan['summary'] }}</p>
                            <div class="pricing-card__price">
                                <strong>PHP {{ number_format($plan['price'], 0) }}</strong>
                                <span>{{ $plan['period'] }}</span>
                            </div>
                            <ul>
                                @foreach($plan['features'] as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                            <a href="{{ route('register', ['plan' => $plan['code']]) }}" class="button {{ $plan['spotlight'] ? 'button--primary' : 'button--secondary' }}">
                                Get Started
                            </a>
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
                        <p>After successful payment, the system creates the tenant, records the registration, assigns the Account Owner role, and redirects the user to the Account Owner dashboard.</p>
                    </details>
                    <details class="faq__item" data-aos="fade-up" data-aos-delay="100">
                        <summary>Will the super admin see the new account?</summary>
                        <p>Yes. Successful paid registrations are recorded so the super admin side can view the new onboarded account and billing-related data.</p>
                    </details>
                    <details class="faq__item" data-aos="fade-up" data-aos-delay="200">
                        <summary>Can the platform support both sales and marketing teams?</summary>
                        <p>Yes. The system is built for shared funnel visibility while still giving different roles the dashboards and permissions they need.</p>
                    </details>
                </div>
            </section>
        </main>

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
                        <a href="{{ route('register') }}">Register</a>
                        <a href="{{ route('login') }}">Sign In</a>
                        <a href="{{ route('register', ['trial' => 1]) }}">Free Trial</a>
                    </div>
                    <div class="landing-footer__column">
                        <h4>Support</h4>
                        <a href="#faq">FAQ</a>
                        <a href="#pricing">Plans</a>
                        <a href="{{ route('register') }}">Contact Team</a>
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
        if (window.AOS) {
            AOS.init({
                duration: 800,
                easing: 'ease-out-cubic',
                once: false,
                offset: 80,
            });
        }

        const navToggle = document.getElementById('navToggle');
        const landingNav = document.getElementById('landingNav');
        const workFilterButtons = document.querySelectorAll('[data-work-filter]');
        const workCards = document.querySelectorAll('[data-work-card]');
        const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

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
            const sectionIds = navLinks.map((link) => link.getAttribute('href')).filter(Boolean);
            const sections = sectionIds
                .map((id) => document.querySelector(id))
                .filter((section) => section && section.id);

            // Always start in default state on hero/top.
            setActiveNavHash('');

            if ('IntersectionObserver' in window && sections.length) {
                const observer = new IntersectionObserver(
                    (entries) => {
                        // Pick the most visible intersecting section (handles fast scroll).
                        const visible = entries
                            .filter((entry) => entry.isIntersecting)
                            .sort((a, b) => (b.intersectionRatio || 0) - (a.intersectionRatio || 0));

                        if (!visible.length) {
                            setActiveNavHash('');
                            return;
                        }

                        const section = visible[0].target;
                        if (section && section.id) {
                            setActiveNavHash(`#${section.id}`);
                        }
                    },
                    {
                        root: null,
                        // Trigger when a section crosses the middle of the viewport.
                        rootMargin: '-45% 0px -55% 0px',
                        threshold: [0, 0.1, 0.25, 0.5, 0.75, 1],
                    }
                );

                sections.forEach((section) => observer.observe(section));
            } else if (sections.length) {
                // Fallback for older browsers without IntersectionObserver.
                const onScroll = () => {
                    const viewportMiddle = window.scrollY + window.innerHeight / 2;
                    let activeSection = null;

                    sections.forEach((section) => {
                        const rect = section.getBoundingClientRect();
                        const top = rect.top + window.scrollY;
                        const bottom = top + rect.height;
                        if (viewportMiddle >= top && viewportMiddle < bottom) {
                            activeSection = section;
                        }
                    });

                    if (activeSection && activeSection.id) {
                        setActiveNavHash(`#${activeSection.id}`);
                        return;
                    }

                    setActiveNavHash('');
                };

                window.addEventListener('scroll', onScroll, { passive: true });
                onScroll();
            }
        }

        if (workFilterButtons.length && workCards.length) {
            const setWorkFilter = (filter) => {
                workFilterButtons.forEach((button) => {
                    button.classList.toggle('work-chip--active', button.dataset.workFilter === filter);
                });

                workCards.forEach((card) => {
                    const categories = (card.dataset.workCategory || '').split(' ').filter(Boolean);
                    const shouldShow = filter === 'all' || categories.includes(filter);

                    card.hidden = !shouldShow;
                    card.classList.toggle('work-panel--visible', shouldShow);
                });

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
    </script>
</body>
</html>
