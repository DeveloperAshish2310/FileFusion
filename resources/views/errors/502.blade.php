@extends('errors.layout')

@section('title', '502 - Bad Gateway')

@section('content')
    <!-- Two Nodes + Severed Link Animation -->
    <div style="display:flex; align-items:center; justify-content:center; animation:ff-fade 0.6s ease both;">
        <!-- Server Node -->
        <div style="width:80px; height:80px; border-radius:18px; background:var(--ff-node-bg); border:1px solid var(--ff-border); display:flex; align-items:center; justify-content:center; box-shadow:0 16px 36px -16px rgba(0,0,0,0.5);">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="var(--ff-text-2)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="4" y="9" width="16" height="6" rx="1.5"></rect>
                <rect x="4" y="15.5" width="16" height="5" rx="1.5" opacity="0.5"></rect>
                <line x1="7.5" y1="12" x2="7.6" y2="12"></line>
            </svg>
        </div>

        <!-- Severed Circuit Connection -->
        <div style="position:relative; width:120px; display:flex; align-items:center; justify-content:center;">
            <svg width="120" height="40" viewBox="0 0 120 40" fill="none">
                <path d="M2 20h44" stroke="var(--ff-accent)" stroke-width="3" stroke-linecap="round" stroke-dasharray="8 6" style="animation:ff-dash 1s linear infinite;"></path>
                <path d="M74 20h44" stroke="var(--ff-border)" stroke-width="3" stroke-linecap="round" stroke-dasharray="8 6"></path>
            </svg>
            <div style="position:absolute; width:26px; height:26px; border-radius:50%; background:radial-gradient(circle, var(--ff-accent), var(--ff-accent-2)); display:flex; align-items:center; justify-content:center; animation:ff-spark 1.4s ease-in-out infinite; box-shadow:0 0 22px 4px var(--ff-accent-glow);">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="#ffffff">
                    <path d="M13 2L4 14h6l-1 8 9-12h-6z"></path>
                </svg>
            </div>
        </div>

        <!-- Upstream Cloud Node with Shake Effect -->
        <div style="width:80px; height:80px; border-radius:18px; background:linear-gradient(135deg, var(--ff-accent), var(--ff-accent-2)); display:flex; align-items:center; justify-content:center; box-shadow:0 20px 44px -16px var(--ff-accent-glow); animation:ff-shake 3s ease-in-out infinite;">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.5 19a4.5 4.5 0 0 0 .5-8.98A6 6 0 0 0 6.2 9.2 4 4 0 0 0 6.5 19"></path>
                <line x1="12" y1="12.5" x2="12" y2="16"></line>
                <line x1="12" y1="18.5" x2="12.01" y2="18.5"></line>
            </svg>
        </div>
    </div>

    <!-- Terminal Readout -->
    <div style="margin-top:28px; width:360px; max-width:100%; border-radius:12px; overflow:hidden; border:1px solid var(--ff-border); background:var(--ff-terminal-bg); text-align:left; box-shadow:0 12px 28px -10px rgba(0,0,0,0.4);">
        <div style="display:flex; align-items:center; gap:7px; padding:9px 14px; background:var(--ff-terminal-bar); border-bottom:1px solid var(--ff-border);">
            <span style="width:9px; height:9px; border-radius:50%; background:#e0604f;"></span>
            <span style="width:9px; height:9px; border-radius:50%; background:#e0a24f;"></span>
            <span style="width:9px; height:9px; border-radius:50%; background:#5fb87e;"></span>
        </div>
        <div style="padding:14px 16px; font-family:'JetBrains Mono',monospace; font-size:12.5px; line-height:1.75;">
            <div style="color:var(--ff-text-soft);">$ GET /transfer/download</div>
            <div style="color:var(--ff-accent); font-weight:700;">502 Bad Gateway</div>
            <div style="color:var(--ff-text-2);">upstream did not respond<span style="color:var(--ff-accent); animation:ff-blip 1s infinite;">_</span></div>
        </div>
    </div>

    <!-- Error Code & Badges -->
    <div class="ff-error-code">502</div>
    <div class="ff-error-tag">Error 502 &bull; Bad Gateway</div>

    <!-- Headings & Copy -->
    <h1 class="ff-error-heading ff-stag" style="animation-delay:0.14s;">Bad gateway</h1>
    <p class="ff-error-desc ff-stag" style="animation-delay:0.22s;">
        Our server couldn't reach the upstream processing service. This is almost always temporary — give it a moment and try again.
    </p>

    <!-- Action Buttons -->
    <div class="ff-error-actions ff-stag" style="animation-delay:0.3s;">
        <a href="javascript:location.reload()" class="ff-btn-p">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i> Try again
        </a>
        <a href="{{ url('/') }}" class="ff-btn-s">
            <i data-lucide="home" class="w-4 h-4"></i> Back to home
        </a>
    </div>
@endsection
