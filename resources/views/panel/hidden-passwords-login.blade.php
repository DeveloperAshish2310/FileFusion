@extends('layout.backend')
@push('title', 'Hidden Passwords Vault Unlock')
@section('page-width', 'ff-page-narrow')

@section('content')
    @php
        $u = auth()->user();
        $has2fa = $u ? $u->hasTwoFactorEnabled() : false;
        $isTotpDefault = $has2fa && $u->requiresTwoFactorFor('vault');
    @endphp

    <div style="max-width:420px; margin:6vh auto 0;">
        <div class="ff-form-card">
            <div style="text-align:center;">
                <span class="ff-lock-icon" style="margin:0 auto 16px;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="10" rx="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                </span>
                <div class="ff-modal-title" style="font-size:20px;">Hidden Passwords Vault</div>
                <p class="ff-hint" id="vault-subtitle" style="margin-top:6px; line-height:1.5;">
                    @if ($isTotpDefault)
                        Enter the 6-digit code from your Authenticator app.
                    @else
                        Hidden credentials are protected. Enter your vault password to view them.
                    @endif
                </p>
            </div>

            @if (session('error'))
                <div class="ff-alert is-error" style="margin-bottom:0;">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <div id="email-otp-toast" style="display: none; padding: 10px 12px; border-radius: 8px; font-size: 12.5px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #10b981; margin: 12px 0; text-align: center;"></div>

            <form action="{{ route('panel.hiddenPasswordsAuth') }}" method="POST" class="ff-stack-sm" style="margin-top: 14px;">
                @csrf
                <input type="hidden" name="auth_mode" id="authMode" value="{{ $isTotpDefault ? 'totp' : 'password' }}">

                <div class="ff-field">
                    <label class="ff-label" id="authLabel" for="authInput">
                        {{ $isTotpDefault ? 'Security Code (2FA / TOTP)' : 'Vault Password' }}
                    </label>
                    <input type="{{ $isTotpDefault ? 'text' : 'password' }}" id="authInput" name="{{ $isTotpDefault ? 'code' : 'vault_pass' }}" class="ff-input ff-mono"
                        style="letter-spacing:{{ $isTotpDefault ? '0.35em' : '0.25em' }}; text-align:center; font-size: {{ $isTotpDefault ? '20px' : '16px' }}; font-weight: 700;"
                        placeholder="{{ $isTotpDefault ? '000000' : '••••••••' }}" autocomplete="off" maxlength="{{ $isTotpDefault ? '12' : '64' }}" required autofocus>
                </div>

                @if ($has2fa)
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; font-size: 12.5px;">
                        <button type="button" id="btnToggleAuthMode" class="ff-hint" style="background: none; border: none; padding: 0; cursor: pointer; color: var(--ff-accent); text-decoration: underline;">
                            @if ($isTotpDefault)
                                🔑 Use Vault Password instead
                            @else
                                📱 Use 2FA Authenticator Code instead
                            @endif
                        </button>

                        @if ($u && ($u->two_factor_type === 'email' || $u->two_factor_type === 'both'))
                            <button type="button" id="btnSendEmailOtp" class="ff-hint" style="background: none; border: none; padding: 0; cursor: pointer; color: var(--ff-text-soft);">
                                ✉️ Send code via email
                            </button>
                        @endif
                    </div>
                @endif

                <button type="submit" class="ff-btn ff-btn-primary ff-btn-block" style="margin-top: 8px;">Unlock Vault</button>
            </form>

            <div class="ff-divider"></div>
            <div style="text-align:center;">
                <a href="{{ route('panel.passwords') }}" class="ff-hint" style="text-decoration:none;">
                    ← Back to passwords
                </a>
            </div>
        </div>

        <p class="ff-hint" style="text-align:center; margin-top:14px;">
            Vault protection settings can be customized in <a href="{{ route('panel.settings') }}" style="color: var(--ff-accent);">Settings</a>.
        </p>
    </div>
@endsection

@section('push-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const authInput = document.getElementById('authInput');
            const authLabel = document.getElementById('authLabel');
            const authMode = document.getElementById('authMode');
            const subtitle = document.getElementById('vault-subtitle');
            const btnToggle = document.getElementById('btnToggleAuthMode');
            const btnSendEmail = document.getElementById('btnSendEmailOtp');
            const emailToast = document.getElementById('email-otp-toast');

            let isTotp = authMode.value === 'totp';
            if (authInput) authInput.focus();

            if (btnToggle) {
                btnToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    isTotp = !isTotp;
                    authMode.value = isTotp ? 'totp' : 'password';

                    if (isTotp) {
                        authLabel.textContent = 'Security Code (2FA / TOTP)';
                        authInput.type = 'text';
                        authInput.name = 'code';
                        authInput.placeholder = '000000';
                        authInput.maxLength = 12;
                        authInput.style.letterSpacing = '0.35em';
                        authInput.style.fontSize = '20px';
                        subtitle.textContent = 'Enter the 6-digit code from your Authenticator app or Email.';
                        btnToggle.textContent = '🔑 Use Vault Password instead';
                    } else {
                        authLabel.textContent = 'Vault Password';
                        authInput.type = 'password';
                        authInput.name = 'vault_pass';
                        authInput.placeholder = '••••••••';
                        authInput.maxLength = 64;
                        authInput.style.letterSpacing = '0.25em';
                        authInput.style.fontSize = '16px';
                        subtitle.textContent = 'Hidden credentials are protected. Enter your vault password to view them.';
                        btnToggle.textContent = '📱 Use 2FA Authenticator Code instead';
                    }

                    authInput.value = '';
                    authInput.focus();
                });
            }

            if (btnSendEmail) {
                btnSendEmail.addEventListener('click', function(e) {
                    e.preventDefault();
                    btnSendEmail.disabled = true;
                    btnSendEmail.textContent = 'Sending email...';

                    fetch("{{ route('two-factor.email-otp') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ action: 'Hidden Vault Unlock' })
                    })
                    .then(r => r.json())
                    .then(data => {
                        emailToast.style.display = 'block';
                        emailToast.textContent = data.message || 'Security code sent to your email!';
                        if (!isTotp && btnToggle) btnToggle.click();
                        btnSendEmail.textContent = '✉️ Code Sent';
                        setTimeout(() => { btnSendEmail.disabled = false; btnSendEmail.textContent = '✉️ Resend email code'; }, 30000);
                    })
                    .catch(err => {
                        btnSendEmail.disabled = false;
                        btnSendEmail.textContent = '✉️ Send code via email';
                        alert('Failed to send email: ' + err.message);
                    });
                });
            }

            window.ff.icons();
        });
    </script>
@endsection
