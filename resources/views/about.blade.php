@extends('layout.frontend')
@push('title', 'About - ' . config('app.name', 'FileFusion'))

@section('content')
    @include('navbar')

    <main class="flex-grow">
        <!-- HERO -->
        <section class="py-16 md:py-20 bg-[var(--ff-bg2)] text-center border-b border-[var(--ff-border)]">
            <div class="max-w-[760px] mx-auto px-7">
                <div class="inline-block text-[12.5px] font-bold tracking-wider text-[var(--ff-accent)] bg-[var(--ff-icon-bg)] px-3.5 py-1.5 rounded-full mb-4">
                    Our Story & Values
                </div>
                <h1 class="font-outfit text-4xl md:text-5xl font-extrabold text-[var(--ff-text)] tracking-tight mb-4">
                    {{ \App\Models\LandingPageSetting::get('about_title') }}
                </h1>
                <p class="text-base md:text-lg text-[var(--ff-text-secondary)] leading-relaxed">
                    Revolutionizing the way individuals and teams store, organize, and collaborate on files, links, and code.
                </p>
            </div>
        </section>

        <!-- STORY & MISSION -->
        <section class="py-20 md:py-24">
            <div class="max-w-[1000px] mx-auto px-7">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center mb-20">
                    <div>
                        <div class="rounded-2xl border border-[var(--ff-border)] bg-white p-6 shadow-lg">
                            <div class="w-12 h-12 rounded-xl bg-[var(--ff-icon-bg)] flex items-center justify-center mb-4">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                </svg>
                            </div>
                            <h3 class="font-outfit text-2xl font-bold text-[var(--ff-text)] mb-2">Built for Focus</h3>
                            <p class="text-[14.5px] leading-relaxed text-[var(--ff-text-secondary)]">
                                {{ \App\Models\LandingPageSetting::get('about_description') }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <h2 class="font-outfit text-3xl font-extrabold text-[var(--ff-text)] mb-4 tracking-tight">Our Story</h2>
                        <p class="text-[15px] leading-relaxed text-[var(--ff-text-secondary)] mb-4">
                            {{ \App\Models\LandingPageSetting::get('about_description') }}
                        </p>
                    </div>
                </div>

                <!-- VALUES GRID -->
                <div class="text-center max-w-[620px] mx-auto mb-12">
                    <h2 class="font-outfit text-3xl font-extrabold text-[var(--ff-text)] tracking-tight mb-3">Our Core Values</h2>
                    <p class="text-base text-[var(--ff-text-secondary)]">The principles that guide everything we build.</p>
                </div>

                @php
                    $values = json_decode(\App\Models\LandingPageSetting::get('about_values'), true) ?: [];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($values as $v)
                        <div class="ff-card">
                            <div class="w-11 h-11 rounded-xl bg-[var(--ff-icon-bg)] flex items-center justify-center mb-4">
                                @if(($v['icon'] ?? '') == 'check')
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="M9 12l2 2 4-4"></path></svg>
                                @elseif(($v['icon'] ?? '') == 'shield')
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                @else
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                @endif
                            </div>
                            <h3 class="font-outfit text-lg font-bold text-[var(--ff-text)] mb-2">{{ $v['title'] ?? '' }}</h3>
                            <p class="text-sm leading-relaxed text-[var(--ff-text-secondary)]">{{ $v['description'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>

                <!-- FOUNDER SHOWCASE -->
                <div class="mt-20 border-t border-[var(--ff-border)] pt-16 text-center">
                    <img src="{{ \App\Models\LandingPageSetting::get('founder_avatar') }}" alt="{{ \App\Models\LandingPageSetting::get('founder_name') }}" class="w-28 h-28 rounded-full mx-auto mb-4 border-2 border-[var(--ff-accent)] p-0.5 shadow-md">
                    <h3 class="font-outfit text-2xl font-bold text-[var(--ff-text)]">{{ \App\Models\LandingPageSetting::get('founder_name') }}</h3>
                    <p class="text-sm font-semibold text-[var(--ff-accent)] mb-3">{{ \App\Models\LandingPageSetting::get('founder_title') }}</p>
                    <p class="text-[14.5px] leading-relaxed text-[var(--ff-text-secondary)] max-w-xl mx-auto mb-6">
                        {{ \App\Models\LandingPageSetting::get('founder_bio') }}
                    </p>
                    <div class="flex justify-center gap-4">
                        <a href="https://github.com/ashishkumar2310" target="_blank" rel="noopener" class="w-9 h-9 rounded-full bg-[var(--ff-bg2)] flex items-center justify-center text-[var(--ff-text)] hover:text-[var(--ff-accent)] transition-colors">
                            <i class="ri-github-fill text-xl"></i>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-full bg-[var(--ff-bg2)] flex items-center justify-center text-[var(--ff-text)] hover:text-[var(--ff-accent)] transition-colors">
                            <i class="ri-linkedin-fill text-xl"></i>
                        </a>
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
                <a href="{{ route('pricing') }}" class="inline-flex items-center gap-2 px-7 py-3 rounded-xl bg-white text-[var(--ff-accent)] font-bold text-[15px] hover:bg-gray-50 transition-colors text-decoration-none shadow-lg">
                    {{ \App\Models\LandingPageSetting::get('cta_button_text') }}
                </a>
            </div>
        </section>
    </main>

    @include('footer')
@endsection
