@extends('errors.layout')

@section('title', '500 - Internal Server Error')

@section('content')
    <!-- Server Processing Warning Animation -->
    <div style="position:relative; width:180px; height:180px; display:flex; align-items:center; justify-content:center; animation:ff-fade 0.6s ease both;">
        <div style="position:absolute; inset:0; border-radius:50%; background:radial-gradient(circle, var(--ff-accent-glow) 0%, transparent 70%); animation:ff-pulse 3.5s ease-in-out infinite;"></div>
        <div style="position:absolute; width:180px; height:180px; border-radius:50%; border:2px dashed var(--ff-accent); animation:ff-spinCW 28s linear infinite;"></div>
        <div style="position:relative; width:110px; height:110px; border-radius:24px; background:linear-gradient(135deg, var(--ff-accent), var(--ff-accent-2)); display:flex; align-items:center; justify-content:center; box-shadow:0 24px 50px -16px var(--ff-accent-glow), inset 0 2px 8px rgba(255,255,255,0.25);">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
    </div>

    <!-- Error Code & Badges -->
    <div class="ff-error-code">500</div>
    <div class="ff-error-tag">Error 500 &bull; Server Exception</div>

    <!-- Headings & Copy -->
    <h1 class="ff-error-heading ff-stag" style="animation-delay:0.12s;">Internal server error</h1>
    <p class="ff-error-desc ff-stag" style="animation-delay:0.2s;">
        Something unexpected occurred on our end while processing your request. Our automated telemetry has logged the issue for investigation.
    </p>

    <!-- Action Buttons -->
    <div class="ff-error-actions ff-stag" style="animation-delay:0.3s;">
        <a href="javascript:location.reload()" class="ff-btn-p">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i> Reload page
        </a>
        <a href="{{ url('/') }}" class="ff-btn-s">
            <i data-lucide="home" class="w-4 h-4"></i> Back to home
        </a>
    </div>
@endsection
