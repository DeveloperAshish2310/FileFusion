@extends('layout.frontend')
@push('title', 'Contact - ' . config('app.name', 'FileFusion'))

@section('content')
    @include('navbar')

    <main class="flex-grow">
        <!-- HERO HEADER -->
        <section class="py-16 md:py-20 bg-[var(--ff-bg2)] text-center border-b border-[var(--ff-border)]">
            <div class="max-w-[760px] mx-auto px-7">
                <div class="inline-block text-[12.5px] font-bold tracking-wider text-[var(--ff-accent)] bg-[var(--ff-icon-bg)] px-3.5 py-1.5 rounded-full mb-4">
                    We're Here to Help
                </div>
                <h1 class="font-outfit text-4xl md:text-5xl font-extrabold text-[var(--ff-text)] tracking-tight mb-4">
                    {{ \App\Models\LandingPageSetting::get('contact_title') }}
                </h1>
                <p class="text-base md:text-lg text-[var(--ff-text-secondary)] leading-relaxed">
                    {{ \App\Models\LandingPageSetting::get('contact_subtitle') }}
                </p>
            </div>
        </section>

        <!-- FORM & CONTACT DETAILS -->
        <section class="py-20 md:py-24">
            <div class="max-w-[1000px] mx-auto px-7">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-7 items-start">
                    <div class="bg-white border border-[var(--ff-border)] rounded-2xl p-7 shadow-sm">
                        <form action="#" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-[var(--ff-text-secondary)] mb-1.5">Name</label>
                                <input type="text" name="name" placeholder="Your name" required class="w-full px-3.5 py-2.5 rounded-lg border border-[var(--ff-border)] bg-[var(--ff-bg2)] text-[var(--ff-text)] text-[14.5px] focus:outline-none focus:border-[var(--ff-accent)]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[var(--ff-text-secondary)] mb-1.5">Email</label>
                                <input type="email" name="email" placeholder="you@example.com" required class="w-full px-3.5 py-2.5 rounded-lg border border-[var(--ff-border)] bg-[var(--ff-bg2)] text-[var(--ff-text)] text-[14.5px] focus:outline-none focus:border-[var(--ff-accent)]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[var(--ff-text-secondary)] mb-1.5">Subject</label>
                                <input type="text" name="subject" placeholder="What is this regarding?" required class="w-full px-3.5 py-2.5 rounded-lg border border-[var(--ff-border)] bg-[var(--ff-bg2)] text-[var(--ff-text)] text-[14.5px] focus:outline-none focus:border-[var(--ff-accent)]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[var(--ff-text-secondary)] mb-1.5">Message</label>
                                <textarea name="message" placeholder="How can we help?" rows="4" required class="w-full px-3.5 py-2.5 rounded-lg border border-[var(--ff-border)] bg-[var(--ff-bg2)] text-[var(--ff-text)] text-[14.5px] focus:outline-none focus:border-[var(--ff-accent)] resize-y"></textarea>
                            </div>
                            <button type="submit" class="ff-btn-primary w-full text-center border-none cursor-pointer">
                                Send Message
                            </button>
                        </form>
                    </div>

                    <div class="bg-white border border-[var(--ff-border)] rounded-2xl p-7 shadow-sm">
                        <div class="font-outfit font-bold text-lg text-[var(--ff-text)] mb-4">Contact Details</div>
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

                            <div class="flex items-center gap-3">
                                <div class="w-8.5 h-8.5 rounded-lg bg-[var(--ff-icon-bg)] flex items-center justify-center shrink-0">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-[13px] text-[var(--ff-text-soft)]">Location</div>
                                    <div class="text-[14.5px] font-semibold text-[var(--ff-text)]">{{ \App\Models\LandingPageSetting::get('contact_location') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="h-px bg-[var(--ff-border)] my-5"></div>
                        <div class="text-[13.5px] text-[var(--ff-text-secondary)] leading-relaxed mb-4">
                            {{ \App\Models\LandingPageSetting::get('contact_response_time') }}
                        </div>

                        <div class="font-outfit font-bold text-sm text-[var(--ff-text)] mb-3">
                            {{ \App\Models\LandingPageSetting::get('social_heading', 'Connect With Us') }}
                        </div>
                        <div class="flex gap-3">
                            @if(\App\Models\LandingPageSetting::get('social_github'))
                                <a href="{{ \App\Models\LandingPageSetting::get('social_github') }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg border border-[var(--ff-border)] flex items-center justify-center text-[var(--ff-text)] hover:text-[var(--ff-accent)] transition-colors" title="GitHub">
                                    <i class="ri-github-fill text-lg"></i>
                                </a>
                            @endif
                            @if(\App\Models\LandingPageSetting::get('social_twitter') && \App\Models\LandingPageSetting::get('social_twitter') !== '#')
                                <a href="{{ \App\Models\LandingPageSetting::get('social_twitter') }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg border border-[var(--ff-border)] flex items-center justify-center text-[var(--ff-text)] hover:text-[var(--ff-accent)] transition-colors" title="Twitter / X">
                                    <i class="ri-twitter-fill text-lg"></i>
                                </a>
                            @endif
                            @if(\App\Models\LandingPageSetting::get('social_linkedin') && \App\Models\LandingPageSetting::get('social_linkedin') !== '#')
                                <a href="{{ \App\Models\LandingPageSetting::get('social_linkedin') }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg border border-[var(--ff-border)] flex items-center justify-center text-[var(--ff-text)] hover:text-[var(--ff-accent)] transition-colors" title="LinkedIn">
                                    <i class="ri-linkedin-fill text-lg"></i>
                                </a>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </main>

    @include('footer')
@endsection
