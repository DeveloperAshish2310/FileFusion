@extends('errors.layout')

@section('title', '401 - Unauthorized')

@section('content')
    <!-- Vault / Keyhole Glowing Lock Animation -->
    <div style="position:relative; width:200px; height:200px; display:flex; align-items:center; justify-content:center; animation:ff-fade 0.6s ease both;">
        <div style="position:absolute; inset:0; border-radius:50%; background:radial-gradient(circle, var(--ff-accent-glow) 0%, transparent 68%); animation:ff-pulse 4s ease-in-out infinite;"></div>
        <div style="position:absolute; width:200px; height:200px; border-radius:50%; border:2px dashed color-mix(in srgb, var(--ff-accent) 60%, transparent); animation:ff-spinCW 26s linear infinite;"></div>
        <div style="position:absolute; width:160px; height:160px; border-radius:50%; border:2px dashed var(--ff-border); animation:ff-spinCCW 20s linear infinite;"></div>
        <div style="position:relative; width:124px; height:124px; border-radius:50%; background:linear-gradient(135deg, var(--ff-accent), var(--ff-accent-2)); display:flex; align-items:center; justify-content:center; box-shadow:0 24px 50px -16px var(--ff-accent-glow), inset 0 3px 10px rgba(255,255,255,0.25);">
            <svg width="54" height="54" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="4" y="10.5" width="16" height="10" rx="2.5"></rect>
                <path d="M8 10.5V7a4 4 0 0 1 8 0v3.5"></path>
                <circle cx="12" cy="15" r="1.6"></circle>
                <path d="M12 15.8v2.2"></path>
            </svg>
        </div>
    </div>

    <!-- Error Code & Badges -->
    <div class="ff-error-code">401</div>
    <div class="ff-error-tag">Error 401 &bull; Locked</div>

    <!-- Headings & Copy -->
    <h1 class="ff-error-heading ff-stag" style="animation-delay:0.1s;">Authentication required</h1>
    <p class="ff-error-desc ff-stag" style="animation-delay:0.18s;">
        Your session has expired or you're not signed in. Please sign in to verify your identity and unlock this credential or resource.
    </p>

    <!-- Keycard Swipe Motif -->
    <div class="ff-stag" style="animation-delay:0.26s; margin-top:24px; width:240px; height:50px; border-radius:12px; background:var(--ff-card-bg); border:1px solid var(--ff-border); display:flex; align-items:center; padding:0 8px; overflow:hidden; position:relative;">
        <div style="width:52px; height:34px; border-radius:6px; background:linear-gradient(135deg, var(--ff-accent), var(--ff-accent-2)); display:flex; align-items:center; justify-content:flex-end; padding-right:6px; animation:ff-swipe 3s ease-in-out infinite; box-shadow:0 4px 12px var(--ff-accent-glow);">
            <div style="width:7px; height:20px; border-radius:2px; background:rgba(255,255,255,0.6);"></div>
        </div>
        <div style="position:absolute; right:16px; font-family:'JetBrains Mono',monospace; font-size:11px; font-weight:700; color:var(--ff-text-soft);">swipe to unlock</div>
    </div>

    <!-- Action Buttons -->
    <div class="ff-error-actions ff-stag" style="animation-delay:0.34s;">
        <a href="{{ route('login') }}" class="ff-btn-p">
            <i data-lucide="log-in" class="w-4 h-4"></i> Sign in
        </a>
        <a href="javascript:history.back()" class="ff-btn-s">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Go back
        </a>
    </div>
@endsection
