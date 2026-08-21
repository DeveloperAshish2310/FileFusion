@extends('layout.frontend')
@push('title', 'Sign Up & Sign In')

@section('css')
<style>
    html, body, .app {
        background: oklch(13% 0.012 280) !important;
        min-height: 100vh !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    @keyframes au-fade {
        from { opacity: 0; transform: translateY(16px) scale(0.985); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes au-rise {
        from { opacity: 0; transform: translateY(14px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes au-pop {
        0% { opacity: 0; transform: scale(0.6) rotate(-12deg); }
        60% { transform: scale(1.08) rotate(3deg); }
        100% { opacity: 1; transform: scale(1) rotate(0); }
    }
    @keyframes au-glow {
        0%, 100% { box-shadow: 0 12px 28px -8px color-mix(in oklch, #D97757 55%, transparent); }
        50% { box-shadow: 0 16px 40px -6px color-mix(in oklch, #D97757 85%, transparent); }
    }
    @keyframes au-sheen {
        from { transform: translateX(-120%) skewX(-18deg); }
        to { transform: translateX(320%) skewX(-18deg); }
    }

    .au-page-wrap {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        min-height: 100vh;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px 20px 60px;
        background: radial-gradient(120% 90% at 50% 0%, oklch(17% 0.02 280) 0%, oklch(13% 0.012 280) 60%);
        color: oklch(96% 0.006 280);
        position: relative;
        overflow: hidden;
    }

    /* Prevent browser autofill from turning inputs white */
    .au-input,
    input.au-input,
    .au-field input {
        background-color: transparent !important;
    }
    .au-input:-webkit-autofill,
    .au-input:-webkit-autofill:hover, 
    .au-input:-webkit-autofill:focus, 
    .au-input:-webkit-autofill:active {
        -webkit-box-shadow: 0 0 0 40px oklch(17% 0.014 280) inset !important;
        -webkit-text-fill-color: oklch(96% 0.006 280) !important;
        caret-color: oklch(96% 0.006 280) !important;
        transition: background-color 5000s ease-in-out 0s !important;
    }

    .au-tabs-wrap {
        display: inline-flex;
        gap: 4px;
        padding: 4px;
        margin-bottom: 28px;
        background: oklch(20% 0.014 280);
        border: 1px solid oklch(28% 0.015 280);
        border-radius: 12px;
        position: relative;
        z-index: 10;
    }
    .au-tab-btn {
        padding: 8px 24px;
        border-radius: 9px;
        border: none;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        font-size: 13.5px;
        font-weight: 600;
        transition: all 0.2s ease;
        background: transparent;
        color: oklch(65% 0.012 280);
    }
    .au-tab-btn.is-active {
        background: linear-gradient(135deg, #D97757, #B8542E);
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(217, 119, 87, 0.4);
    }

    .au-card {
        width: 440px;
        max-width: 100%;
        background: oklch(21% 0.015 280);
        border: 1px solid oklch(29% 0.015 280);
        border-radius: 22px;
        padding: 40px 36px 32px 36px;
        box-shadow: 0 40px 90px -30px rgba(0, 0, 0, 0.6);
        animation: au-fade 0.4s ease;
        position: relative;
        z-index: 10;
    }

    .au-icon {
        width: 60px;
        height: 60px;
        border-radius: 18px;
        background: linear-gradient(135deg, #D97757, #B8542E);
        display: flex;
        align-items: center;
        justify-content: center;
        animation: au-pop .6s cubic-bezier(.34, 1.56, .64, 1) both, au-glow 3s ease-in-out 1s infinite;
    }

    .au-title {
        font-family: 'Outfit', sans-serif;
        font-size: 28px;
        font-weight: 700;
        color: oklch(96% 0.006 280);
        margin-top: 18px;
        letter-spacing: -0.5px;
    }
    .au-sub {
        font-size: 14px;
        color: oklch(62% 0.012 280);
        margin-top: 6px;
    }

    .au-field {
        display: flex;
        align-items: center;
        gap: 10px;
        background: oklch(17% 0.014 280);
        border: 1px solid oklch(30% 0.015 280);
        border-radius: 12px;
        padding: 0 14px;
        transition: border-color .2s ease, background .2s ease, box-shadow .2s ease;
    }
    .au-field:focus-within {
        border-color: color-mix(in oklch, #D97757 60%, transparent);
        background: oklch(19% 0.014 280);
        box-shadow: 0 0 0 3px color-mix(in oklch, #D97757 18%, transparent);
    }
    .au-input {
        flex: 1;
        min-width: 0;
        border: none;
        outline: none;
        background: transparent;
        color: oklch(96% 0.006 280);
        font-size: 14.5px;
        font-family: 'Inter', sans-serif;
        padding: 13px 0;
    }
    .au-input::placeholder {
        color: oklch(52% 0.012 280);
    }

    .au-primary {
        width: 100%;
        margin-top: 22px;
        padding: 15px;
        border-radius: 12px;
        border: none;
        cursor: pointer;
        background: linear-gradient(135deg, #D97757, #B8542E);
        color: #fff;
        font-family: 'Inter', sans-serif;
        font-size: 15px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        box-shadow: 0 10px 26px -8px color-mix(in oklch, #D97757 70%, transparent);
        position: relative;
        overflow: hidden;
        transition: transform .16s ease, filter .16s ease;
    }
    .au-primary:hover {
        transform: translateY(-2px);
        filter: brightness(1.07);
    }
    .au-primary:active {
        transform: translateY(0);
    }
    .au-primary::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 40%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .35), transparent);
        animation: au-sheen 2.6s ease-in-out 1s infinite;
    }

    .au-social {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 11px;
        border-radius: 11px;
        border: 1px solid oklch(30% 0.015 280);
        background: oklch(17% 0.014 280);
        color: oklch(88% 0.006 280);
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: transform .16s ease, border-color .2s ease, background .2s ease;
        text-decoration: none;
    }
    .au-social:hover {
        transform: translateY(-2px);
        border-color: color-mix(in oklch, #D97757 45%, transparent);
        background: oklch(20% 0.014 280);
    }

    .au-link {
        color: #D97757;
        text-decoration: none;
        transition: color .18s ease;
    }
    .au-link:hover {
        color: #E08A6E;
    }

    .au-stag {
        animation: au-rise .5s cubic-bezier(.2, .7, .3, 1) both;
    }

    .au-alert {
        margin-bottom: 20px;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 13.5px;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: au-rise 0.3s ease;
    }
    .au-alert.is-success {
        background: rgba(16, 185, 129, 0.14);
        border: 1px solid rgba(16, 185, 129, 0.35);
        color: #34d399;
    }
    .au-alert.is-error {
        background: rgba(239, 68, 68, 0.14);
        border: 1px solid rgba(239, 68, 68, 0.35);
        color: #f87171;
    }
</style>
@endsection

@section('content')
<div class="au-page-wrap">

    {{-- Top Segmented Switcher --}}
    <div class="au-tabs-wrap">
        <button type="button" id="tabBtnSignIn" class="au-tab-btn" onclick="switchAuthView('signin')">Sign in</button>
        <button type="button" id="tabBtnSignUp" class="au-tab-btn is-active" onclick="switchAuthView('signup')">Sign up</button>
    </div>

    {{-- Alert Messages --}}
    <div style="width:440px; max-width:100%;">
        @if (session('success'))
            <div class="au-alert is-success">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="au-alert is-error">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Card Container --}}
    <div class="au-card">

        {{-- SIGN IN FORM --}}
        <div id="viewSignIn" style="display:none;">
            <div style="display:flex; flex-direction:column; align-items:center; text-align:center;">
                <div class="au-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="#fff"><path d="M12 2l2.09 6.26L20 8.5l-4.9 3.74L17 20l-5-3.6L7 20l1.9-7.76L4 8.5l5.91-.24z"></path></svg>
                </div>
                <div class="au-title au-stag" style="animation-delay:.08s;">Welcome Back</div>
                <div class="au-sub au-stag" style="animation-delay:.14s;">Enter your credentials to continue</div>
            </div>

            <form action="{{ route('loginaction') }}" method="POST" style="margin-top:28px;">
                @csrf
                <div class="au-stag" style="animation-delay:.2s;">
                    <div style="font-size:13px; font-weight:600; color:oklch(78% 0.01 280); margin-bottom:8px;">Email Address</div>
                    <div class="au-field">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="oklch(58% 0.012 280)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-10 5L2 7"></path></svg>
                        <input type="email" name="email" class="au-input" placeholder="you@company.com" value="{{ old('email') }}" required>
                    </div>
                </div>

                <div class="au-stag" style="animation-delay:.28s; margin-top:18px;">
                    <div style="font-size:13px; font-weight:600; color:oklch(78% 0.01 280); margin-bottom:8px;">Password</div>
                    <div class="au-field">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="oklch(58% 0.012 280)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <input type="password" id="loginPasswordReg" name="password" class="au-input" placeholder="••••••••••" required>
                        <button type="button" onclick="togglePasswordVisibility('loginPasswordReg', 'eyeIconLoginReg')" style="background:none; border:none; cursor:pointer; padding:0; display:flex; color:oklch(58% 0.012 280); flex-shrink:0;">
                            <svg id="eyeIconLoginReg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                </div>

                <div class="au-stag" style="animation-delay:.36s; display:flex; align-items:center; justify-content:space-between; margin-top:16px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; color:oklch(72% 0.01 280);">
                        <input type="checkbox" name="remember" value="1" checked style="accent-color:#D97757; width:16px; height:16px; border-radius:4px; cursor:pointer;">
                        Remember me
                    </label>
                    <a href="{{ route('password.request') }}" class="au-link" style="font-size:13px; font-weight:600;">Forgot password?</a>
                </div>

                <button type="submit" class="au-primary au-stag" style="animation-delay:.44s;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                    Sign In
                </button>
            </form>

            <div style="text-align:center; margin-top:22px; font-size:13.5px; color:oklch(62% 0.012 280);">
                Don't have an account? <a href="javascript:void(0)" onclick="switchAuthView('signup')" class="au-link" style="font-weight:600;">Sign up</a>
            </div>
        </div>

        {{-- SIGN UP FORM --}}
        <div id="viewSignUp">
            <div style="display:flex; flex-direction:column; align-items:center; text-align:center;">
                <div class="au-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="#fff"><path d="M12 2l2.09 6.26L20 8.5l-4.9 3.74L17 20l-5-3.6L7 20l1.9-7.76L4 8.5l5.91-.24z"></path></svg>
                </div>
                <div class="au-title au-stag" style="animation-delay:.08s;">Create Account</div>
                <div class="au-sub au-stag" style="animation-delay:.14s;">Start sharing files securely</div>
            </div>

            <div class="au-stag" style="animation-delay:.2s; display:flex; gap:10px; margin-top:24px;">
                <button type="button" class="au-social" onclick="window.ff?.toast ? window.ff.toast('OAuth login can be enabled in settings.', 'info') : null">
                    <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#EA4335" d="M12 10.8v3.6h5.1c-.2 1.3-1.6 3.9-5.1 3.9-3.1 0-5.6-2.6-5.6-5.7s2.5-5.7 5.6-5.7c1.8 0 2.9.7 3.6 1.4l2.5-2.4C16.9 4 14.7 3 12 3 6.9 3 2.8 7.1 2.8 12S6.9 21 12 21c5.2 0 8.6-3.6 8.6-8.7 0-.6-.1-1-.1-1.5z"></path></svg>
                    Google
                </button>
                <button type="button" class="au-social" onclick="window.ff?.toast ? window.ff.toast('OAuth login can be enabled in settings.', 'info') : null">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="oklch(90% 0.006 280)"><path d="M12 2C6.5 2 2 6.6 2 12.3c0 4.5 2.9 8.4 6.8 9.7.5.1.7-.2.7-.5v-1.7c-2.8.6-3.4-1.4-3.4-1.4-.5-1.2-1.1-1.5-1.1-1.5-.9-.6.1-.6.1-.6 1 .1 1.5 1 1.5 1 .9 1.6 2.4 1.1 3 .9.1-.7.4-1.1.6-1.4-2.2-.3-4.6-1.1-4.6-5 0-1.1.4-2 1-2.7-.1-.3-.4-1.3.1-2.7 0 0 .8-.3 2.7 1a9.4 9.4 0 0 1 5 0c1.9-1.3 2.7-1 2.7-1 .5 1.4.2 2.4.1 2.7.6.7 1 1.6 1 2.7 0 3.9-2.4 4.7-4.6 5 .4.3.7.9.7 1.9v2.8c0 .3.2.6.7.5A10.1 10.1 0 0 0 22 12.3C22 6.6 17.5 2 12 2z"></path></svg>
                    GitHub
                </button>
            </div>

            <div class="au-stag" style="animation-delay:.28s; display:flex; align-items:center; gap:12px; margin:20px 0 6px 0;">
                <div style="flex:1; height:1px; background:oklch(30% 0.015 280);"></div>
                <span style="font-size:12px; color:oklch(55% 0.012 280);">or sign up with e-mail</span>
                <div style="flex:1; height:1px; background:oklch(30% 0.015 280);"></div>
            </div>

            <form action="{{ route('register.submit') }}" method="POST" style="margin-top:16px;">
                @csrf
                <div class="au-stag" style="animation-delay:.32s; display:flex; flex-direction:column; gap:13px;">
                    <div>
                        <div class="au-field">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="oklch(58% 0.012 280)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <input type="text" id="regName" name="name" class="au-input" placeholder="Full name" value="{{ old('name') }}" required oninput="autoSyncUsername(this.value)" autofocus>
                        </div>
                    </div>

                    <div>
                        <div class="au-field">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="oklch(58% 0.012 280)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="12" cy="12" r="4"></circle><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"></path></svg>
                            <input type="text" id="regUsername" name="username" class="au-input" placeholder="Username" value="{{ old('username') }}" required>
                        </div>
                    </div>

                    <div>
                        <div class="au-field">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="oklch(58% 0.012 280)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-10 5L2 7"></path></svg>
                            <input type="email" name="email" class="au-input" placeholder="Email address" value="{{ old('email') }}" required>
                        </div>
                    </div>

                    <div>
                        <div class="au-field">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="oklch(58% 0.012 280)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            <input type="password" id="regPassword" name="password" class="au-input" placeholder="Password (min 6 characters)" required>
                            <button type="button" onclick="togglePasswordVisibility('regPassword', 'eyeIconReg')" style="background:none; border:none; cursor:pointer; padding:0; display:flex; color:oklch(58% 0.012 280); flex-shrink:0;">
                                <svg id="eyeIconReg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <div class="au-field">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="oklch(58% 0.012 280)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            <input type="password" id="regPasswordConfirm" name="password_confirmation" class="au-input" placeholder="Confirm password" required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="au-primary au-stag" style="animation-delay:.44s;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="19" y1="8" x2="19" y2="14"></line><line x1="22" y1="11" x2="16" y2="11"></line></svg>
                    Sign Up
                </button>
            </form>

            <div style="text-align:center; margin-top:16px; font-size:12px; line-height:1.6; color:oklch(55% 0.012 280);">
                By signing up you agree to our <a href="{{ Route::has('terms') ? route('terms') : '#' }}" class="au-link">Terms of Service</a> and <a href="{{ Route::has('privacy') ? route('privacy') : '#' }}" class="au-link">Privacy Policy</a>.
            </div>

            <div style="text-align:center; margin-top:14px; font-size:13.5px; color:oklch(62% 0.012 280);">
                Already have an account? <a href="javascript:void(0)" onclick="switchAuthView('signin')" class="au-link" style="font-weight:600;">Sign in</a>
            </div>
        </div>

    </div>

    {{-- Powered by --}}
    <div style="margin-top:26px; font-size:12px; font-weight:600; color:oklch(50% 0.012 280); letter-spacing:0.02em;">
        Powered by <span style="color:oklch(70% 0.012 280);">FileFusion</span>
    </div>

</div>

<script>
    function switchAuthView(view) {
        const viewSignIn = document.getElementById('viewSignIn');
        const viewSignUp = document.getElementById('viewSignUp');
        const tabBtnSignIn = document.getElementById('tabBtnSignIn');
        const tabBtnSignUp = document.getElementById('tabBtnSignUp');

        if (view === 'signin') {
            viewSignIn.style.display = 'block';
            viewSignUp.style.display = 'none';
            tabBtnSignIn.classList.add('is-active');
            tabBtnSignUp.classList.remove('is-active');
            history.replaceState(null, '', '?view=signin');
        } else {
            viewSignIn.style.display = 'none';
            viewSignUp.style.display = 'block';
            tabBtnSignIn.classList.remove('is-active');
            tabBtnSignUp.classList.add('is-active');
            history.replaceState(null, '', '?view=signup');
        }
    }

    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (!input) return;

        if (input.type === 'password') {
            input.type = 'text';
            if (icon) icon.innerHTML = '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path><line x1="2" y1="2" x2="22" y2="22"></line>';
        } else {
            input.type = 'password';
            if (icon) icon.innerHTML = '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7"></path><circle cx="12" cy="12" r="3"></circle>';
        }
    }

    function autoSyncUsername(val) {
        const userInp = document.getElementById('regUsername');
        if (userInp && (!userInp.value || userInp.dataset.manual !== 'true')) {
            userInp.value = val.toLowerCase().replace(/[^a-z0-9_]/g, '');
        }
    }

    document.getElementById('regUsername')?.addEventListener('input', function() {
        this.dataset.manual = 'true';
    });

    // Handle initial state from URL query or errors
    (function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('view') === 'signin') {
            switchAuthView('signin');
        } else {
            switchAuthView('signup');
        }
    })();
</script>
@endsection
