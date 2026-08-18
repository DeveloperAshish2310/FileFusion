@extends('layout.frontend')
@push('title', 'Pricing - ' . config('app.name', 'FileFusion'))

@section('content')
    @include('navbar')

    <main class="flex-grow">
        <!-- HERO -->
        <section class="py-16 md:py-20 bg-[var(--ff-bg2)] text-center border-b border-[var(--ff-border)]">
            <div class="max-w-[760px] mx-auto px-7">
                <div class="inline-block text-[12.5px] font-bold tracking-wider text-[var(--ff-accent)] bg-[var(--ff-icon-bg)] px-3.5 py-1.5 rounded-full mb-4">
                    Simple, Transparent Pricing
                </div>
                <h1 class="font-outfit text-4xl md:text-5xl font-extrabold text-[var(--ff-text)] tracking-tight mb-4">
                    {{ \App\Models\LandingPageSetting::get('pricing_title') }}
                </h1>
                <p class="text-base md:text-lg text-[var(--ff-text-secondary)] leading-relaxed">
                    {{ \App\Models\LandingPageSetting::get('pricing_subtitle') }}
                </p>

                <!-- Billing Toggle Switcher -->
                <div class="flex items-center justify-center gap-3.5 mt-8">
                    <span id="label-monthly-pg" class="text-sm font-semibold text-[var(--ff-text)]">Monthly</span>
                    <button id="ff-billing-toggle-pg" type="button" class="w-11 h-6 rounded-full relative cursor-pointer bg-[var(--ff-border)] transition-colors focus:outline-none" aria-label="Toggle Annual Billing">
                        <div id="ff-billing-knob-pg" class="w-4.5 h-4.5 rounded-full bg-white absolute top-0.75 left-0.75 transition-all shadow-sm"></div>
                    </button>
                    <span id="label-annual-pg" class="text-sm font-semibold text-[var(--ff-text-soft)]">
                        Annual <span class="text-[11px] font-bold text-[var(--ff-accent)] bg-[var(--ff-icon-bg)] px-2 py-0.5 rounded-full ml-1">Save 20%</span>
                    </span>
                </div>
            </div>
        </section>

        <!-- PRICING CARDS -->
        <section class="py-20 md:py-24">
            <div class="max-w-[1240px] mx-auto px-7">
                @php
                    $plans = json_decode(\App\Models\LandingPageSetting::get('pricing_plans'), true) ?: [];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 gap-7 items-start">
                    @foreach($plans as $pIdx => $p)
                        <div class="bg-white rounded-2xl p-7 relative {{ ($p['popular'] ?? false) ? 'border-2 border-[var(--ff-accent)] shadow-xl' : 'border border-[var(--ff-border)]' }}">
                            @if($p['popular'] ?? false)
                                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-[11.5px] font-bold text-white bg-gradient-to-r from-[var(--ff-accent)] to-[var(--ff-accent2)] px-3.5 py-1 rounded-full shadow-sm">
                                    Most Popular
                                </div>
                            @endif
                            <div class="font-outfit text-xl font-bold text-[var(--ff-text)]">{{ $p['name'] ?? '' }}</div>
                            <div class="my-3.5 flex items-baseline gap-1">
                                <span id="price-plan-pg-{{ $pIdx }}" data-monthly="{{ $p['price_monthly'] ?? '$0' }}" data-annual="{{ $p['price_annual'] ?? '$0' }}" class="font-outfit text-4xl font-extrabold text-[var(--ff-text)]">{{ $p['price_monthly'] ?? '$0' }}</span>
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

        <!-- FAQ SECTION -->
        <section class="py-20 md:py-24 bg-[var(--ff-bg2)] border-t border-[var(--ff-border)]">
            <div class="max-w-[960px] mx-auto px-7">
                <h2 class="font-outfit text-3xl md:text-4xl font-extrabold text-center text-[var(--ff-text)] tracking-tight mb-14">
                    Frequently Asked Questions
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-7">
                    <div class="bg-white border border-[var(--ff-border)] rounded-2xl p-6">
                        <h3 class="font-outfit text-lg font-bold text-[var(--ff-text)] mb-2">Can I change my plan later?</h3>
                        <p class="text-[14.5px] leading-relaxed text-[var(--ff-text-secondary)]">
                            Yes, you can upgrade or downgrade your plan at any time. Changes take effect immediately.
                        </p>
                    </div>

                    <div class="bg-white border border-[var(--ff-border)] rounded-2xl p-6">
                        <h3 class="font-outfit text-lg font-bold text-[var(--ff-text)] mb-2">Is my data encrypted?</h3>
                        <p class="text-[14.5px] leading-relaxed text-[var(--ff-text-secondary)]">
                            All files and credentials are protected with AES-256 envelope encryption both in transit and at rest.
                        </p>
                    </div>

                    <div class="bg-white border border-[var(--ff-border)] rounded-2xl p-6">
                        <h3 class="font-outfit text-lg font-bold text-[var(--ff-text)] mb-2">Is there a long-term contract?</h3>
                        <p class="text-[14.5px] leading-relaxed text-[var(--ff-text-secondary)]">
                            No, all plans are month-to-month or annual with no cancellation fees or lock-in commitments.
                        </p>
                    </div>

                    <div class="bg-white border border-[var(--ff-border)] rounded-2xl p-6">
                        <h3 class="font-outfit text-lg font-bold text-[var(--ff-text)] mb-2">Do you offer free accounts?</h3>
                        <p class="text-[14.5px] leading-relaxed text-[var(--ff-text-secondary)]">
                            Yes! Our free plan includes 25GB storage and unlimited saved links with no credit card required.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    @include('footer')
@endsection

@section('push-script')
    <script>
        $(document).ready(function() {
            let isAnnualPg = false;
            $('#ff-billing-toggle-pg').on('click', function() {
                isAnnualPg = !isAnnualPg;
                if (isAnnualPg) {
                    $('#ff-billing-toggle-pg').addClass('bg-[var(--ff-accent)]').removeClass('bg-[var(--ff-border)]');
                    $('#ff-billing-knob-pg').css('left', '22px');
                    $('[id^="price-plan-pg-"]').each(function() {
                        $(this).text($(this).data('annual'));
                    });
                    $('#label-annual-pg').addClass('text-[var(--ff-text)]').removeClass('text-[var(--ff-text-soft)]');
                    $('#label-monthly-pg').addClass('text-[var(--ff-text-soft)]').removeClass('text-[var(--ff-text)]');
                } else {
                    $('#ff-billing-toggle-pg').removeClass('bg-[var(--ff-accent)]').addClass('bg-[var(--ff-border)]');
                    $('#ff-billing-knob-pg').css('left', '3px');
                    $('[id^="price-plan-pg-"]').each(function() {
                        $(this).text($(this).data('monthly'));
                    });
                    $('#label-monthly-pg').addClass('text-[var(--ff-text)]').removeClass('text-[var(--ff-text-soft)]');
                    $('#label-annual-pg').addClass('text-[var(--ff-text-soft)]').removeClass('text-[var(--ff-text)]');
                }
            });
        });
    </script>
@endsection
