@extends('errors.layout')

@section('title', '403 - Access Forbidden')

@section('content')
    <!-- Hazard Barrier & Restricted Badge Animation -->
    <div style="display:flex; flex-direction:column; align-items:center; animation:ff-fade 0.6s ease both; width:100%; max-width:520px;">
        <!-- Diagonal Hazard Strip -->
        <div style="position:relative; width:100%; border-radius:16px; overflow:hidden; border:1px solid var(--ff-border); box-shadow:0 12px 30px -10px rgba(0,0,0,0.3);">
            <div style="height:116px; background:repeating-linear-gradient(-45deg, var(--ff-accent-2) 0 26px, var(--ff-hazard-dark) 26px 52px); display:flex; align-items:center; justify-content:center;">
                <div style="animation:ff-stamp 0.7s cubic-bezier(0.34,1.56,0.64,1) both; padding:10px 24px; border:3.5px solid #ffffff; border-radius:10px; background:rgba(20,18,26,0.78); backdrop-filter:blur(4px); box-shadow:0 8px 24px rgba(0,0,0,0.5);">
                    <span style="font-family:'Outfit',sans-serif; font-size:28px; font-weight:800; letter-spacing:0.14em; color:#ffffff;">RESTRICTED</span>
                </div>
            </div>
        </div>

        <!-- Shield Lock Node Overlapping Barrier -->
        <div style="width:92px; height:92px; margin-top:-46px; border-radius:50%; background:var(--ff-card); border:1px solid var(--ff-border); display:flex; align-items:center; justify-content:center; position:relative; z-index:2; box-shadow:0 18px 40px -14px rgba(0,0,0,0.6);">
            <div style="width:68px; height:68px; border-radius:50%; background:linear-gradient(135deg, var(--ff-accent), var(--ff-accent-2)); display:flex; align-items:center; justify-content:center; box-shadow:inset 0 2px 8px rgba(255,255,255,0.25), 0 8px 20px var(--ff-accent-glow);">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6z"></path>
                    <line x1="9" y1="12" x2="15" y2="12"></line>
                </svg>
            </div>
        </div>
    </div>

    <!-- Error Code & Badges -->
    <div class="ff-error-code">403</div>
    <div class="ff-error-tag">Error 403 &bull; Forbidden</div>

    <!-- Headings & Copy -->
    <h1 class="ff-error-heading ff-stag" style="animation-delay:0.12s;">Access forbidden</h1>
    <p class="ff-error-desc ff-stag" style="animation-delay:0.2s;">
        You don't have permission to open this resource or perform this action. Ask the owner for access or sign in with an authorized account.
    </p>

    <!-- Action Buttons -->
    <div class="ff-error-actions ff-stag" style="animation-delay:0.3s;">
        <a href="{{ route('contact') }}" class="ff-btn-p">
            <i data-lucide="message-square" class="w-4 h-4"></i> Request access
        </a>
        <a href="{{ url('/') }}" class="ff-btn-s">
            <i data-lucide="home" class="w-4 h-4"></i> Back to home
        </a>
    </div>
@endsection
