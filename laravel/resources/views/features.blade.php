@extends('layout.frontend')
@push('title', 'Features - ' . config('app.name', 'FileFusion'))

@section('content')
    @include('navbar')

    <main class="flex-grow">
        <!-- HEADER HERO -->
        <section class="py-16 md:py-20 bg-[var(--ff-bg2)] text-center border-b border-[var(--ff-border)]">
            <div class="max-w-[760px] mx-auto px-7">
                <div class="inline-block text-[12.5px] font-bold tracking-wider text-[var(--ff-accent)] bg-[var(--ff-icon-bg)] px-3.5 py-1.5 rounded-full mb-4">
                    Six Tools, One Workspace
                </div>
                <h1 class="font-outfit text-4xl md:text-5xl font-extrabold text-[var(--ff-text)] tracking-tight mb-4">
                    {{ \App\Models\LandingPageSetting::get('features_title') }}
                </h1>
                <p class="text-base md:text-lg text-[var(--ff-text-secondary)] leading-relaxed">
                    {{ \App\Models\LandingPageSetting::get('features_subtitle') }}
                </p>
            </div>
        </section>

        <!-- FEATURES GRID -->
        <section class="py-20 md:py-24">
            <div class="max-w-[1240px] mx-auto px-7">
                @php
                    $features = json_decode(\App\Models\LandingPageSetting::get('features_list'), true) ?: [];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($features as $f)
                        <div class="ff-card">
                            <div class="w-11 h-11 rounded-xl bg-[var(--ff-icon-bg)] flex items-center justify-center mb-4">
                                @if(($f['icon'] ?? '') == 'file')
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                @elseif(($f['icon'] ?? '') == 'link')
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                                @elseif(($f['icon'] ?? '') == 'tag')
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.6 12.6L12.4 20.8a2 2 0 0 1-2.8 0l-8-8a2 2 0 0 1 0-2.8L9.8 1.8a2 2 0 0 1 2.8 0l8 8a2 2 0 0 1 0 2.8z"></path><circle cx="7" cy="7" r="1.2" fill="var(--ff-accent)" stroke="none"></circle></svg>
                                @elseif(($f['icon'] ?? '') == 'search')
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                @elseif(($f['icon'] ?? '') == 'share')
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v7a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg>
                                @else
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                @endif
                            </div>
                            <h2 class="font-outfit text-xl font-bold text-[var(--ff-text)] mb-2">{{ $f['title'] ?? '' }}</h2>
                            <p class="text-[14.5px] leading-relaxed text-[var(--ff-text-secondary)]">
                                {{ $f['description'] ?? '' }}
                            </p>
                        </div>
                    @endforeach
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
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-7 py-3 rounded-xl bg-white text-[var(--ff-accent)] font-bold text-[15px] hover:bg-gray-50 transition-colors text-decoration-none shadow-lg">
                    {{ \App\Models\LandingPageSetting::get('cta_button_text') }}
                </a>
            </div>
        </section>
    </main>

    @include('footer')
@endsection
