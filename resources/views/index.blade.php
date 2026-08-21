@extends('layout.frontend')
@push('title', config('app.name', 'FileFusion') . ' - ' . \App\Models\LandingPageSetting::get('hero_title'))

@section('content')
    @include('navbar')

    <main class="flex-grow">
        <!-- HOME / HERO SECTION -->
        <section id="home" class="py-16 md:py-24 bg-[var(--ff-bg2)]">
            <div class="max-w-[1240px] mx-auto px-7 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-14 items-center">
                <div class="animate-ff-fade">
                    <div class="inline-block text-[12.5px] font-bold tracking-wider text-[var(--ff-accent)] bg-[var(--ff-icon-bg)] px-3.5 py-1.5 rounded-full mb-5">
                        {{ \App\Models\LandingPageSetting::get('hero_eyebrow') }}
                    </div>
                    <h1 class="font-outfit text-4xl sm:text-5xl lg:text-[48px] font-extrabold leading-[1.08] tracking-[-1.5px] mb-5 text-[var(--ff-text)]">
                        {{ \App\Models\LandingPageSetting::get('hero_title') }}
                    </h1>
                    <p class="text-lg leading-relaxed text-[var(--ff-text-secondary)] mb-7 max-w-[520px]">
                        {{ \App\Models\LandingPageSetting::get('hero_description') }}
                    </p>
                    <div class="flex flex-wrap items-center gap-4.5">
                        @auth
                            <a href="{{ route('panel.dashboard') }}" class="ff-btn-primary">
                                Go to Dashboard &rarr;
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="ff-btn-primary">
                                {{ \App\Models\LandingPageSetting::get('hero_cta_primary') }}
                            </a>
                            <a href="#features" class="text-[15px] font-semibold text-[var(--ff-text)] hover:text-[var(--ff-accent)] transition-colors px-2 py-1 text-decoration-none">
                                {{ \App\Models\LandingPageSetting::get('hero_cta_secondary') }}
                            </a>
                        @endauth
                    </div>
                    <div class="text-[13.5px] text-[var(--ff-text-soft)] mt-4">
                        {{ \App\Models\LandingPageSetting::get('hero_subtext') }}
                    </div>
                </div>

                <!-- Product Browser Mockup -->
                <div class="hidden lg:block animate-ff-fade">
                    <div class="rounded-xl border border-[var(--ff-border)] bg-white shadow-2xl overflow-hidden">
                        <div class="flex items-center gap-1.5 px-4 py-3 border-b border-[var(--ff-border)] bg-[var(--ff-bg)]">
                            <div class="w-2.5 h-2.5 rounded-full bg-red-500"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                            <div class="ml-3 text-[12px] text-[var(--ff-text-soft)] bg-[var(--ff-bg2)] rounded-md px-3 py-1 flex-1 font-mono">
                                {{ \App\Models\LandingPageSetting::get('hero_mockup_url') }}
                            </div>
                        </div>
                        <div class="aspect-[16/11] bg-[var(--ff-bg2)] flex items-center justify-center p-8 relative overflow-hidden"
                            style="background-image: repeating-linear-gradient(135deg, var(--ff-border) 0px, var(--ff-border) 1px, transparent 1px, transparent 10px);">
                            <div class="bg-white/90 backdrop-blur border border-[var(--ff-border)] rounded-2xl p-6 shadow-xl max-w-sm w-full text-center">
                                <div class="w-12 h-12 rounded-xl bg-[var(--ff-icon-bg)] flex items-center justify-center mx-auto mb-3">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                    </svg>
                                </div>
                                <div class="font-outfit font-bold text-lg text-[var(--ff-text)] mb-1">{{ \App\Models\LandingPageSetting::get('hero_mockup_title') }}</div>
                                <div class="text-xs text-[var(--ff-text-secondary)]">{{ \App\Models\LandingPageSetting::get('hero_mockup_sub') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FEATURES SECTION -->
        <section id="features" class="py-20 md:py-24">
            <div class="max-w-[1240px] mx-auto px-7">
                <div class="text-center max-w-[620px] mx-auto mb-14">
                    <h2 class="font-outfit text-3xl md:text-4xl font-extrabold text-[var(--ff-text)] tracking-tight mb-3">
                        {{ \App\Models\LandingPageSetting::get('features_title') }}
                    </h2>
                    <p class="text-base md:text-lg text-[var(--ff-text-secondary)] leading-relaxed">
                        {{ \App\Models\LandingPageSetting::get('features_subtitle') }}
                    </p>
                </div>

                @php
                    $features = json_decode(\App\Models\LandingPageSetting::get('features_list'), true) ?: [];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7">
                    @foreach($features as $f)
                        <div class="ff-card">
                            <div class="w-11 h-11 rounded-xl bg-[var(--ff-icon-bg)] flex items-center justify-center">
                                @if(($f['icon'] ?? '') == 'file')
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                @elseif(($f['icon'] ?? '') == 'link')
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                                @elseif(($f['icon'] ?? '') == 'tag')
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.6 12.6L12.4 20.8a2 2 0 0 1-2.8 0l-8-8a2 2 0 0 1 0-2.8L9.8 1.8a2 2 0 0 1 2.8 0l8 8a2 2 0 0 1 0 2.8z"></path><circle cx="7" cy="7" r="1.2" fill="var(--ff-accent)" stroke="none"></circle></svg>
                                @elseif(($f['icon'] ?? '') == 'search')
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                @elseif(($f['icon'] ?? '') == 'share')
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v7a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg>
                                @else
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                @endif
                            </div>
                            <div class="font-outfit text-lg font-bold text-[var(--ff-text)] mt-4 mb-2">{{ $f['title'] ?? '' }}</div>
                            <div class="text-[14.5px] leading-relaxed text-[var(--ff-text-secondary)]">
                                {{ $f['description'] ?? '' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- PRICING SECTION -->
        <section id="pricing" class="py-20 md:py-24 bg-[var(--ff-bg2)]">
            <div class="max-w-[1240px] mx-auto px-7">
                <div class="text-center max-w-[620px] mx-auto mb-6">
                    <h2 class="font-outfit text-3xl md:text-4xl font-extrabold text-[var(--ff-text)] tracking-tight mb-3">
                        {{ \App\Models\LandingPageSetting::get('pricing_title') }}
                    </h2>
                    <p class="text-base md:text-lg text-[var(--ff-text-secondary)] leading-relaxed">
                        {{ \App\Models\LandingPageSetting::get('pricing_subtitle') }}
                    </p>
                </div>

                <!-- Billing Toggle Switcher -->
                <div class="flex items-center justify-center gap-3.5 mb-11">
                    <span id="label-monthly" class="text-sm font-semibold text-[var(--ff-text)]">Monthly</span>
                    <button id="ff-billing-toggle" type="button" class="w-11 h-6 rounded-full relative cursor-pointer bg-[var(--ff-border)] transition-colors focus:outline-none" aria-label="Toggle Annual Billing">
                        <div id="ff-billing-knob" class="w-4.5 h-4.5 rounded-full bg-white absolute top-0.75 left-0.75 transition-all shadow-sm"></div>
                    </button>
                    <span id="label-annual" class="text-sm font-semibold text-[var(--ff-text-soft)]">
                        Annual <span class="text-[11px] font-bold text-[var(--ff-accent)] bg-[var(--ff-icon-bg)] px-2 py-0.5 rounded-full ml-1">Save 20%</span>
                    </span>
                </div>

                @php
                    $plans = json_decode(\App\Models\LandingPageSetting::get('pricing_plans'), true) ?: [];
                @endphp

                <!-- Pricing Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                    @foreach($plans as $pIdx => $p)
                        <div class="bg-white rounded-2xl p-7 relative {{ ($p['popular'] ?? false) ? 'border-2 border-[var(--ff-accent)] shadow-xl' : 'border border-[var(--ff-border)]' }}">
                            @if($p['popular'] ?? false)
                                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-[11.5px] font-bold text-white bg-gradient-to-r from-[var(--ff-accent)] to-[var(--ff-accent2)] px-3.5 py-1 rounded-full shadow-sm">
                                    Most Popular
                                </div>
                            @endif
                            <div class="font-outfit text-xl font-bold text-[var(--ff-text)]">{{ $p['name'] ?? '' }}</div>
                            <div class="my-3.5 flex items-baseline gap-1">
                                <span id="price-plan-{{ $pIdx }}" data-monthly="{{ $p['price_monthly'] ?? '$0' }}" data-annual="{{ $p['price_annual'] ?? '$0' }}" class="font-outfit text-4xl font-extrabold text-[var(--ff-text)]">{{ $p['price_monthly'] ?? '$0' }}</span>
                                <span class="text-sm text-[var(--ff-text-soft)]">{{ $p['period'] ?? '/month' }}</span>
                            </div>
                            <div class="text-[13.5px] text-[var(--ff-text-secondary)] mb-5">{{ $p['tagline'] ?? '' }}</div>
                            <a href="{{ route('register') }}" class="{{ ($p['popular'] ?? false) ? 'ff-btn-primary w-full text-center' : 'block py-3 rounded-lg border border-[var(--ff-border)] text-[var(--ff-text)] font-bold text-[14.5px] text-center hover:bg-[var(--ff-bg2)] transition-colors text-decoration-none' }}">
                                {{ $p['cta_label'] ?? 'Get Started' }}
                            </a>
                            <div class="h-px bg-[var(--ff-border)] my-4.5"></div>
                            <div class="space-y-2.5">
                                @foreach($p['perks'] ?? [] as $perk)
                                    <div class="flex items-center gap-2.5 text-sm text-[var(--ff-text-secondary)]">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        {{ $perk }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ABOUT SECTION -->
        <section id="about" class="py-20 md:py-24">
            <div class="max-w-[900px] mx-auto px-7 text-center">
                <h2 class="font-outfit text-3xl md:text-4xl font-extrabold text-[var(--ff-text)] tracking-tight mb-5">
                    {{ \App\Models\LandingPageSetting::get('about_title') }}
                </h2>
                <p class="text-base md:text-[16.5px] leading-relaxed text-[var(--ff-text-secondary)] max-w-[700px] mx-auto">
                    {{ \App\Models\LandingPageSetting::get('about_description') }}
                </p>

                @php
                    $values = json_decode(\App\Models\LandingPageSetting::get('about_values'), true) ?: [];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-14 text-left">
                    @foreach($values as $v)
                        <div class="bg-[var(--ff-bg2)] border border-[var(--ff-border)] rounded-2xl p-5.5">
                            <div class="w-11 h-11 rounded-xl bg-[var(--ff-icon-bg)] flex items-center justify-center">
                                @if(($v['icon'] ?? '') == 'check')
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="M9 12l2 2 4-4"></path></svg>
                                @elseif(($v['icon'] ?? '') == 'shield')
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                @else
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                @endif
                            </div>
                            <div class="font-outfit text-[16.5px] font-bold text-[var(--ff-text)] mt-3.5 mb-1.5">{{ $v['title'] ?? '' }}</div>
                            <div class="text-sm leading-relaxed text-[var(--ff-text-secondary)]">{{ $v['description'] ?? '' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- CONTACT SECTION -->
        <section id="contact" class="py-20 md:py-24 bg-[var(--ff-bg2)]">
            <div class="max-w-[1000px] mx-auto px-7">
                <div class="text-center max-w-[620px] mx-auto mb-14">
                    <h2 class="font-outfit text-3xl md:text-4xl font-extrabold text-[var(--ff-text)] tracking-tight mb-3">
                        {{ \App\Models\LandingPageSetting::get('contact_title') }}
                    </h2>
                    <p class="text-base md:text-lg text-[var(--ff-text-secondary)] leading-relaxed">
                        {{ \App\Models\LandingPageSetting::get('contact_subtitle') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-7 items-start">
                    <div class="bg-white border border-[var(--ff-border)] rounded-2xl p-7">
                        <form action="#" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-[var(--ff-text-secondary)] mb-1.5">Name</label>
                                <input type="text" placeholder="Your name" class="w-full px-3.5 py-2.5 rounded-lg border border-[var(--ff-border)] bg-[var(--ff-bg2)] text-[var(--ff-text)] text-[14.5px] focus:outline-none focus:border-[var(--ff-accent)]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[var(--ff-text-secondary)] mb-1.5">Email</label>
                                <input type="email" placeholder="you@example.com" class="w-full px-3.5 py-2.5 rounded-lg border border-[var(--ff-border)] bg-[var(--ff-bg2)] text-[var(--ff-text)] text-[14.5px] focus:outline-none focus:border-[var(--ff-accent)]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[var(--ff-text-secondary)] mb-1.5">Message</label>
                                <textarea placeholder="How can we help?" rows="4" class="w-full px-3.5 py-2.5 rounded-lg border border-[var(--ff-border)] bg-[var(--ff-bg2)] text-[var(--ff-text)] text-[14.5px] focus:outline-none focus:border-[var(--ff-accent)] resize-y"></textarea>
                            </div>
                            <button type="submit" class="ff-btn-primary w-full text-center border-none cursor-pointer">
                                Send Message
                            </button>
                        </form>
                    </div>

                    <div class="bg-white border border-[var(--ff-border)] rounded-2xl p-7">
                        <div class="font-outfit font-bold text-lg text-[var(--ff-text)] mb-4">Contact details</div>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8.5 h-8.5 rounded-lg bg-[var(--ff-icon-bg)] flex items-center justify-center shrink-0">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 4h16v16H4z" opacity="0"></path>
                                        <path d="M22 6l-10 7L2 6"></path>
                                        <path d="M2 6h20v12H2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-[13px] text-[var(--ff-text-soft)]">Email</div>
                                    <div class="text-[14.5px] font-semibold text-[var(--ff-text)]">{{ \App\Models\LandingPageSetting::get('contact_email') }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8.5 h-8.5 rounded-lg bg-[var(--ff-icon-bg)] flex items-center justify-center shrink-0">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-[13px] text-[var(--ff-text-soft)]">Support hours</div>
                                    <div class="text-[14.5px] font-semibold text-[var(--ff-text)]">{{ \App\Models\LandingPageSetting::get('contact_hours') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="h-px bg-[var(--ff-border)] my-4.5"></div>
                        <div class="text-[13.5px] text-[var(--ff-text-secondary)] leading-relaxed">
                            {{ \App\Models\LandingPageSetting::get('contact_response_time') }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- AUTH SECTION -->
        <section id="auth" class="py-20 md:py-24">
            <div class="max-w-[1000px] mx-auto px-7">
                <div class="text-center max-w-[620px] mx-auto mb-14">
                    <h2 class="font-outfit text-3xl md:text-4xl font-extrabold text-[var(--ff-text)] tracking-tight mb-3">
                        Join FileFusion
                    </h2>
                    <p class="text-base md:text-lg text-[var(--ff-text-secondary)] leading-relaxed">
                        Already unifying your files? Log in. New here? Create a free account.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-7 items-start">
                    <!-- Login Form Card -->
                    <div class="bg-white border border-[var(--ff-border)] rounded-2xl p-7">
                        <div class="font-outfit font-bold text-lg text-[var(--ff-text)] mb-4">Log in</div>
                        <form action="{{ route('loginaction') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-[var(--ff-text-secondary)] mb-1.5">Email</label>
                                <input type="email" name="email" placeholder="you@example.com" required class="w-full px-3.5 py-2.5 rounded-lg border border-[var(--ff-border)] bg-[var(--ff-bg2)] text-[var(--ff-text)] text-[14.5px] focus:outline-none focus:border-[var(--ff-accent)]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[var(--ff-text-secondary)] mb-1.5">Password</label>
                                <input type="password" name="password" placeholder="••••••••" required class="w-full px-3.5 py-2.5 rounded-lg border border-[var(--ff-border)] bg-[var(--ff-bg2)] text-[var(--ff-text)] text-[14.5px] focus:outline-none focus:border-[var(--ff-accent)]">
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <a href="{{ route('password.request') }}" class="font-semibold text-[var(--ff-accent)] hover:underline">Forgot password?</a>
                            </div>
                            <button type="submit" class="ff-btn-primary w-full text-center border-none cursor-pointer mt-2">
                                Log In
                            </button>
                        </form>
                    </div>

                    <!-- Register Form Card -->
                    <div class="bg-white border border-[var(--ff-border)] rounded-2xl p-7">
                        <div class="font-outfit font-bold text-lg text-[var(--ff-text)] mb-4">Create free account</div>
                        <form action="{{ route('register.submit') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-[var(--ff-text-secondary)] mb-1.5">Full name</label>
                                <input type="text" name="name" placeholder="Ashish Kumar" required class="w-full px-3.5 py-2.5 rounded-lg border border-[var(--ff-border)] bg-[var(--ff-bg2)] text-[var(--ff-text)] text-[14.5px] focus:outline-none focus:border-[var(--ff-accent)]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[var(--ff-text-secondary)] mb-1.5">Email</label>
                                <input type="email" name="email" placeholder="you@example.com" required class="w-full px-3.5 py-2.5 rounded-lg border border-[var(--ff-border)] bg-[var(--ff-bg2)] text-[var(--ff-text)] text-[14.5px] focus:outline-none focus:border-[var(--ff-accent)]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[var(--ff-text-secondary)] mb-1.5">Password</label>
                                <input type="password" name="password" placeholder="At least 8 characters" required class="w-full px-3.5 py-2.5 rounded-lg border border-[var(--ff-border)] bg-[var(--ff-bg2)] text-[var(--ff-text)] text-[14.5px] focus:outline-none focus:border-[var(--ff-accent)]">
                            </div>
                            <button type="submit" class="ff-btn-primary w-full text-center border-none cursor-pointer mt-1">
                                Create Account
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA BAND -->
        <section class="py-16 px-7 bg-gradient-to-r from-[var(--ff-accent)] to-[var(--ff-accent2)] text-center text-white">
            <div class="max-w-[640px] mx-auto">
                <h2 class="font-outfit text-3xl md:text-4xl font-extrabold mb-3 tracking-tight">
                    {{ \App\Models\LandingPageSetting::get('cta_title') }}
                </h2>
                <p class="text-[15.5px] text-white/80 mb-7 leading-relaxed">
                    {{ \App\Models\LandingPageSetting::get('cta_description') }}
                </p>
                <a href="#auth" class="inline-flex items-center gap-2 px-7 py-3 rounded-xl bg-white text-[var(--ff-accent)] font-bold text-[15px] hover:bg-gray-50 transition-colors text-decoration-none shadow-lg">
                    {{ \App\Models\LandingPageSetting::get('cta_button_text') }}
                </a>
            </div>
        </section>
    </main>

    @include('footer')
@endsection

@section('push-script')
    <script>
        $(document).ready(function() {
            let isAnnual = false;
            $('#ff-billing-toggle').on('click', function() {
                isAnnual = !isAnnual;
                if (isAnnual) {
                    $('#ff-billing-toggle').addClass('bg-[var(--ff-accent)]').removeClass('bg-[var(--ff-border)]');
                    $('#ff-billing-knob').css('left', '22px');
                    $('[id^="price-plan-"]').each(function() {
                        $(this).text($(this).data('annual'));
                    });
                    $('#label-annual').addClass('text-[var(--ff-text)]').removeClass('text-[var(--ff-text-soft)]');
                    $('#label-monthly').addClass('text-[var(--ff-text-soft)]').removeClass('text-[var(--ff-text)]');
                } else {
                    $('#ff-billing-toggle').removeClass('bg-[var(--ff-accent)]').addClass('bg-[var(--ff-border)]');
                    $('#ff-billing-knob').css('left', '3px');
                    $('[id^="price-plan-"]').each(function() {
                        $(this).text($(this).data('monthly'));
                    });
                    $('#label-monthly').addClass('text-[var(--ff-text)]').removeClass('text-[var(--ff-text-soft)]');
                    $('#label-annual').addClass('text-[var(--ff-text-soft)]').removeClass('text-[var(--ff-text)]');
                }
            });
        });
    </script>
@endsection
