@extends('errors.layout')

@section('title', '404 - Page Not Found')

@section('content')
    <!-- Radar Scanning Animation -->
    <div style="position:relative; width:220px; height:220px; animation:ff-fade 0.6s ease both;">
        <div style="position:absolute; inset:0; border-radius:50%; background:var(--ff-radar-bg); border:1px solid var(--ff-radar-ring); box-shadow:inset 0 0 60px -20px var(--ff-accent-glow);"></div>
        <div style="position:absolute; inset:24px; border-radius:50%; border:1px solid var(--ff-radar-ring);"></div>
        <div style="position:absolute; inset:60px; border-radius:50%; border:1px solid var(--ff-radar-ring);"></div>
        <div style="position:absolute; inset:96px; border-radius:50%; border:1px solid var(--ff-radar-ring);"></div>
        <div style="position:absolute; left:50%; top:12px; bottom:12px; width:1px; background:var(--ff-radar-ring); transform:translateX(-50%);"></div>
        <div style="position:absolute; top:50%; left:12px; right:12px; height:1px; background:var(--ff-radar-ring); transform:translateY(-50%);"></div>
        
        <!-- Expanding Ping Ring -->
        <div style="position:absolute; inset:0; border-radius:50%; border:2px solid var(--ff-accent); animation:ff-ring 3s ease-out infinite;"></div>
        
        <!-- Conic Gradient Rotating Sweep (Clipped to Circle) -->
        <div style="position:absolute; inset:0; border-radius:50%; overflow:hidden;">
            <div style="position:absolute; left:50%; top:50%; width:50%; height:50%; transform-origin:top left; animation:ff-sweep 4s linear infinite; background:conic-gradient(from 0deg at top left, color-mix(in srgb, var(--ff-accent) 70%, transparent) 0deg, transparent 55deg);"></div>
            <div style="position:absolute; left:50%; top:50%; width:50%; height:2px; transform-origin:left center; animation:ff-sweep 4s linear infinite; background:linear-gradient(90deg, var(--ff-accent), transparent);"></div>
        </div>
        
        <!-- Blips -->
        <div style="position:absolute; left:66%; top:38%; width:9px; height:9px; border-radius:50%; background:var(--ff-accent); box-shadow:0 0 12px 2px var(--ff-accent-glow); animation:ff-blip 2.2s ease-in-out infinite;"></div>
        <div style="position:absolute; left:34%; top:64%; width:7px; height:7px; border-radius:50%; background:var(--ff-accent-2); animation:ff-blip 2.6s ease-in-out 0.6s infinite;"></div>
        
        <!-- Center Core -->
        <div style="position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); width:16px; height:16px; border-radius:50%; background:linear-gradient(135deg, var(--ff-accent), var(--ff-accent-2)); box-shadow:0 0 16px 3px var(--ff-accent-glow);"></div>
    </div>

    <!-- Error Code & Badges -->
    <div class="ff-error-code">404</div>
    <div class="ff-error-tag">Signal Lost</div>

    <!-- Headings & Copy -->
    <h1 class="ff-error-heading ff-stag" style="animation-delay:0.14s;">We can't locate that page</h1>
    <p class="ff-error-desc ff-stag" style="animation-delay:0.22s;">
        The file, link, or transfer you're scanning for isn't on our radar. It may have expired, moved, or never existed.
    </p>

    <!-- Action Buttons -->
    <div class="ff-error-actions ff-stag" style="animation-delay:0.3s;">
        <a href="{{ url('/') }}" class="ff-btn-p">
            <i data-lucide="home" class="w-4 h-4"></i> Back to home
        </a>
        <a href="{{ route('panel.filelist') }}" class="ff-btn-s">
            <i data-lucide="search" class="w-4 h-4"></i> Search files
        </a>
    </div>
@endsection
