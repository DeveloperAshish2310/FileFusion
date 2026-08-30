@extends('layout.backend')
@push('title', 'Secret Categories Vault Unlock')
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
                <div class="ff-modal-title" style="font-size:20px;">Secret Categories Vault</div>
                <p class="ff-hint" id="vault-subtitle" style="margin-top:6px; line-height:1.5;">
                    @if ($isTotpDefault)
                        Enter the 6-digit code from your Authenticator app.
                    @else
                        Secret categories are protected. Enter your vault password to view them.
                    @endif
                </p>
            </div>

            @if ($errors->any())
                <div class="ff-alert is-error" style="margin-bottom:0;">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div id="email-otp-toast" style="display: none; padding: 10px 12px; border-radius: 8px; font-size: 12.5px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #10b981; margin: 12px 0; text-align: center;"></div>

            @if ($u && $u->isVaultBiometricEnabled())
            <div id="biometricAuthContainer" style="display:none; margin: 14px 0;">
                <button type="button" id="btnBiometricUnlock" class="ff-btn ff-btn-outline ff-btn-block" style="display:flex; align-items:center; justify-content:center; gap:8px; border-color:var(--ff-accent); color:var(--ff-accent); padding:10px 14px; font-weight:600; border-radius:10px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10c0 4.418-2.865 8.166-6.839 9.489"></path>
                        <path d="M12 7a5 5 0 0 0-5 5c0 1.38.56 2.63 1.464 3.536"></path>
                        <path d="M12 11a1 1 0 0 0-1 1c0 .55.45 1 1 1s1-.45 1-1"></path>
                        <path d="M16.5 16.5A5.98 5.98 0 0 0 18 12a6 6 0 0 0-6-6"></path>
                        <path d="M7 19.5c1.45.95 3.17 1.5 5 1.5 1.43 0 2.78-.34 3.98-.95"></path>
                    </svg>
                    <span>Unlock with Fingerprint</span>
                </button>
                <div style="display:flex; align-items:center; gap:10px; margin: 14px 0 6px;">
                    <div style="flex:1; height:1px; background:var(--ff-border);"></div>
                    <span style="font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:var(--ff-text-muted);">or enter vault password</span>
                    <div style="flex:1; height:1px; background:var(--ff-border);"></div>
                </div>
            </div>
            @endif

            <form action="{{ route('panel.categories.hiddenAuth') }}" method="POST" class="ff-stack-sm" style="margin-top: 6px;">
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

                <button type="submit" class="ff-btn ff-btn-primary ff-btn-block" style="margin-top: 8px;">Unlock Secret Vault</button>
            </form>

            <div class="ff-divider"></div>
            <div style="text-align:center;">
                <a href="{{ route('panel.categories.index') }}" class="ff-hint" style="text-decoration:none;">
                    ← Back to public categories
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
            if (window.FileFusionNative && window.FileFusionNative.biometrics) {
                window.FileFusionNative.biometrics.isAvailable().then(avail => {
                    const bioCont = document.getElementById('biometricAuthContainer');
                    if (bioCont && (avail || typeof window.FileFusionBiometrics !== 'undefined')) {
                        bioCont.style.display = 'block';
                        
                        if (window.FileFusionNative.isNative || typeof window.FileFusionBiometrics !== 'undefined') {
                            setTimeout(() => {
                                window.FileFusionNative.biometrics.unlockVault('categories');
                            }, 400);
                        }
                    }
                });

                const bioBtn = document.getElementById('btnBiometricUnlock');
                if (bioBtn) {
                    bioBtn.onclick = function() {
                        window.FileFusionNative.biometrics.unlockVault('categories');
                    };
                }
            }

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
                        authInput.placeholder = '000000';
                        authInput.style.letterSpacing = '0.35em';
                        authInput.style.fontSize = '20px';
                        btnToggle.innerHTML = '🔑 Use Vault Password instead';
                        subtitle.textContent = 'Enter the 6-digit code from your Authenticator app.';
                    } else {
                        authLabel.textContent = 'Vault Password';
                        authInput.type = 'password';
                        authInput.placeholder = '••••••••';
                        authInput.style.letterSpacing = '0.25em';
                        authInput.style.fontSize = '16px';
                        btnToggle.innerHTML = '📱 Use 2FA Authenticator Code instead';
                        subtitle.textContent = 'Secret categories are protected. Enter your vault password to view them.';
                    }
                    authInput.value = '';
                    authInput.focus();
                });
            }
        });
    </script>
@endsection
