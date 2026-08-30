@extends('layout.backend')
@push('title', 'Hidden Passwords')

@section('content')
    <div class="ff-banner-vault">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.7 18.7 0 0 1 5.06-5.94" />
            <line x1="1" y1="1" x2="23" y2="23" />
        </svg>
        Vault unlocked — showing hidden credentials only
    </div>

    @php
        $currentPerPage = \App\Helpers\SettingHelper::getItemsPerPage(12);
    @endphp

    <div class="ff-row-between" style="align-items:flex-start; margin-bottom:22px;">
        <div>
            <h1 class="ff-h1">Hidden Passwords</h1>
            <p class="ff-sub" style="margin-bottom:0;">Credentials you have marked as private</p>
        </div>

        <div class="ff-row" style="gap:10px;">
            <span class="ff-badge-type" id="session-timer" title="Vault session remaining">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
                <span id="timer-display">{{ ($remainingTime ?? 1800) === 0 ? 'Ask Always' : sprintf('%02d:%02d', floor(($remainingTime ?? 1800) / 60), ($remainingTime ?? 1800) % 60) }}</span>
            </span>
            <a href="{{ route('panel.passwords') }}" class="ff-btn ff-btn-sm">Back to Passwords</a>
            <form action="{{ route('panel.logoutHiddenPasswords') }}" method="POST">
                @csrf
                <button type="submit" class="ff-btn ff-btn-danger ff-btn-sm">Lock Vault</button>
            </form>
        </div>
    </div>

    <div class="ff-row-between" style="margin-bottom:20px; align-items:center; gap:12px; flex-wrap:wrap;">
        <label class="ff-input-icon" style="flex:1; min-width:240px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="search" 
                id="hiddenPwSearch" 
                name="hidden_passwords_search_query"
                placeholder="Search hidden passwords..." 
                autocomplete="new-password"
                autocorrect="off"
                autocapitalize="off"
                spellcheck="false"
                data-lpignore="true"
                data-form-type="other"
                data-dashlane-ignore="true"
                readonly
                onfocus="this.removeAttribute('readonly');">
        </label>

        <div style="display:inline-flex; align-items:center; gap:6px; background:var(--ff-surface); border:1px solid var(--ff-border); padding:4px 10px; border-radius:10px; flex-shrink:0;">
            <span style="font-size:12px; color:var(--ff-muted); font-weight:600;">Show:</span>
            <select id="hiddenPwPerPageSelect" class="ff-select" style="border:none; background:transparent; padding:2px 4px; font-size:12px; font-weight:600; cursor:pointer; color:var(--ff-text); outline:none;" title="Items visible per page">
                <option value="12" {{ $currentPerPage == 12 ? 'selected' : '' }}>12 / page</option>
                <option value="24" {{ $currentPerPage == 24 ? 'selected' : '' }}>24 / page</option>
                <option value="48" {{ $currentPerPage == 48 ? 'selected' : '' }}>48 / page</option>
                <option value="96" {{ $currentPerPage == 96 ? 'selected' : '' }}>96 / page</option>
            </select>
        </div>
    </div>

    <div class="ff-list">
        <div class="ff-list-head ff-hide-mobile">
            <span class="ff-list-col ff-grow">Name</span>
            <span class="ff-list-col" style="width:200px;">Password</span>
            <span class="ff-list-col" style="width:230px;">Secrets</span>
            <span class="ff-list-col" style="width:40px;"></span>
        </div>

        @forelse ($passwords as $pw)
            @php
                $fields = $pw->auth_fields ?? [];
                $primary = $fields[0] ?? null;
            @endphp

            <div class="ff-list-row" data-pw-row="{{ $pw->reveal_token }}">
                <button type="button" class="ff-list-main ff-pw-open"
                    style="background:none; border:none; text-align:left; cursor:pointer; padding:0;"
                    data-reveal-token="{{ $pw->reveal_token }}">
                    <span class="ff-tile-icon ff-tile-icon-sm">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="10" rx="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </span>
                    <div class="ff-min0">
                        <div class="ff-row" style="gap:8px;">
                            <span class="ff-list-title">{{ $pw->title }}</span>
                            <span class="ff-badge-hidden">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.7 18.7 0 0 1 5.06-5.94" />
                                    <line x1="1" y1="1" x2="23" y2="23" />
                                </svg>
                                Hidden
                            </span>
                        </div>
                        <div class="ff-list-subtitle">{{ $pw->username }}</div>
                    </div>
                </button>

                <span class="ff-list-cell ff-hide-mobile ff-row" style="width:200px; gap:8px;">
                    <span class="ff-mono ff-secret-text" data-reveal-token="{{ $pw->reveal_token }}"
                        style="font-size:13px;">••••••••</span>
                    <button type="button" class="ff-menu-btn ff-jit-reveal-btn" data-reveal-token="{{ $pw->reveal_token }}" style="width:24px; height:24px;"
                        aria-label="Reveal password">
                        <i data-lucide="eye" class="w-[14px] h-[14px]"></i>
                    </button>
                </span>

                <span class="ff-list-cell ff-hide-mobile ff-row" style="width:230px; gap:8px; overflow:hidden;">
                    @if ($primary)
                        <span class="ff-hint" style="flex-shrink:0;">{{ $primary['label'] }}:</span>
                        <span class="ff-mono ff-secret-text ff-truncate" data-reveal-token="{{ $pw->reveal_token }}" data-field-index="{{ $primary['index'] ?? 0 }}"
                            style="font-size:13px;">••••••••</span>
                        <button type="button" class="ff-menu-btn ff-jit-reveal-btn" data-reveal-token="{{ $pw->reveal_token }}" data-field-index="{{ $primary['index'] ?? 0 }}"
                            style="width:24px; height:24px; flex-shrink:0;" aria-label="Reveal secret">
                            <i data-lucide="eye" class="w-[14px] h-[14px]"></i>
                        </button>
                    @else
                        <span class="ff-muted">—</span>
                    @endif
                </span>

                <div class="ff-row" style="gap:4px; align-items:center;">
                    <button type="button" class="ff-menu-btn ff-jit-copy-btn" data-reveal-token="{{ $pw->reveal_token }}" title="Copy Password" aria-label="Copy password">
                        <i data-lucide="copy" class="w-[15px] h-[15px]"></i>
                    </button>
                    <button type="button" class="ff-menu-btn open-password-share-modal" data-pw-id="{{ encrypt($pw->id) }}" data-title="{{ $pw->title }}" title="Share Secret" aria-label="Share secret">
                        <i data-lucide="share-2" class="w-[15px] h-[15px]"></i>
                    </button>
                    @include('panel.ajax.partials.password_menu', ['pw' => $pw])
                </div>
            </div>
        @empty
            <div class="ff-empty">
                <span class="ff-empty-icon" style="width:64px; height:64px; border-radius:16px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.7 18.7 0 0 1 5.06-5.94" />
                        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                        <line x1="1" y1="1" x2="23" y2="23" />
                    </svg>
                </span>
                <div class="ff-section-title" style="font-size:17px;">No hidden credentials</div>
                <p class="ff-empty-text" style="max-width:380px; margin:0;">
                    Credentials you mark as hidden will show up here, behind your vault password.
                </p>
                <a href="{{ route('panel.passwords') }}" class="ff-btn" style="margin-top:6px;">Back to Passwords</a>
            </div>
        @endforelse
    </div>

    @foreach ($passwords as $pw)
        <form id="pw-delete-{{ $pw->reveal_token }}" action="{{ route('panel.deletepassword', encrypt($pw->id)) }}" method="POST" hidden>@csrf</form>
        <form id="pw-hide-{{ $pw->reveal_token }}" action="{{ route('panel.toggleHidePassword') }}" method="POST" hidden>
            @csrf
            <input type="hidden" name="password_id" value="{{ encrypt($pw->id) }}">
        </form>
    @endforeach

    <div id="pwDetailHost" hidden></div>

    @php
        $detailPayload = $passwords->map(
            fn($p) => [
                'id' => encrypt($p->id),
                'title' => $p->title,
                'username' => $p->username,
                'url' => $p->url,
                'notes' => $p->notes,
                'revealToken' => $p->reveal_token,
                'authFields' => $p->auth_fields ?? [],
            ],
        )->values();
    @endphp

    {{-- ==================== PASSWORD SHARE MODAL (ZERO-KNOWLEDGE) ==================== --}}
    <div id="sharePasswordModal" class="ff-modal-overlay" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.7); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:16px;">
        <div class="ff-modal-card" style="background:var(--ff-card, #ffffff) !important; color:var(--ff-text, #0f172a) !important; border:1px solid var(--ff-border, #e2e8f0); border-radius:16px; width:100%; max-width:500px; max-height:90vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.3);">
            <div style="padding:18px 24px; border-bottom:1px solid var(--ff-border, #e2e8f0); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="ff-section-icon" style="width:34px; height:34px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; background:rgba(244,63,94,0.15); color:#f43f5e;">
                        <i data-lucide="shield-alert" style="width:18px; height:18px;"></i>
                    </span>
                    <div>
                        <div style="font-weight:700; font-size:16px; color:var(--ff-text, #0f172a);">Share Confidential Secret</div>
                        <div id="sharePasswordModalSubtitle" style="font-size:12px; color:var(--ff-text-2, var(--ff-muted, #64748b));">Zero-knowledge 1-time encrypted link</div>
                    </div>
                </div>
                <button type="button" class="ff-menu-btn closeSharePasswordModalBtn" style="border:none; background:transparent; cursor:pointer; color:var(--ff-muted, #64748b);">
                    <i data-lucide="x" style="width:18px; height:18px;"></i>
                </button>
            </div>

            <div style="padding:20px 24px; overflow-y:auto; display:flex; flex-direction:column; gap:14px;">
                <input type="hidden" id="sharePasswordIdInput">

                <div style="padding:12px 14px; background:rgba(239, 68, 68, 0.08); border:1px solid rgba(239, 68, 68, 0.25); border-radius:10px; font-size:12.5px; color:var(--ff-text, #0f172a); display:flex; gap:10px; align-items:flex-start;">
                    <i data-lucide="alert-triangle" style="width:18px; height:18px; color:#ef4444; flex-shrink:0; margin-top:2px;"></i>
                    <div>
                        <strong style="color:#ef4444; display:block; margin-bottom:2px;">⚠️ High-Security Action</strong>
                        You are about to export an external encrypted zero-knowledge secret link. Only share this link with trusted recipients. It will self-destruct immediately after decryption.
                    </div>
                </div>

                <div class="ff-field">
                    <label class="ff-label" style="font-size:12px; margin-bottom:4px; font-weight:600;">
                        Enter Your Account / Login Password to Authorize <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="password" id="sharePasswordAccountPassInput" class="ff-input" placeholder="Enter your login password" autocomplete="current-password" required style="width:100%;">
                    <span class="ff-hint" style="font-size:11px;">Verification is required to ensure only authorized owners can export credentials.</span>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="ff-field">
                        <label class="ff-label" style="font-size:12px; margin-bottom:4px; font-weight:600;">Expiration Window</label>
                        <select id="sharePasswordExpirySelect" class="ff-select" style="width:100%;">
                            <option value="60">1 Hour</option>
                            <option value="1440" selected>24 Hours</option>
                            <option value="10080">7 Days</option>
                        </select>
                    </div>

                    <div class="ff-field">
                        <label class="ff-label" style="font-size:12px; margin-bottom:4px; font-weight:600;">Recipient PIN (Optional)</label>
                        <input type="password" id="sharePasswordPasscodeInput" class="ff-input" placeholder="Optional PIN code" style="width:100%;">
                    </div>
                </div>

                <div id="sharePasswordResultBox" style="display:none; padding:14px; background:rgba(244, 63, 94, 0.08); border:1px solid rgba(244, 63, 94, 0.3); border-radius:10px;">
                    <div style="font-size:12px; font-weight:700; color:#f43f5e; margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                        <i data-lucide="check-circle-2" style="width:14px; height:14px;"></i> Secret Share Link Created!
                    </div>
                    <div style="display:flex; gap:8px;">
                        <input type="text" id="sharePasswordResultUrl" class="ff-input" readonly style="font-size:12px; flex:1; font-family:monospace; background:rgba(0,0,0,0.2); color:#fda4af;">
                        <button type="button" class="ff-btn ff-btn-primary" id="copySharePasswordResultBtn" style="font-size:12px; padding:6px 12px; background:#f43f5e; border-color:#f43f5e;">Copy</button>
                    </div>
                </div>
            </div>

            <div style="padding:14px 24px; border-top:1px solid var(--ff-border, #e2e8f0); background:var(--ff-surface-subtle, rgba(0,0,0,0.02)); display:flex; align-items:center; justify-content:flex-end; gap:10px;">
                <button type="button" class="ff-btn closeSharePasswordModalBtn">Close</button>
                <button type="button" id="executeCreateSharePasswordBtn" class="ff-btn ff-btn-primary" style="background:#f43f5e; border-color:#f43f5e;">Authorize &amp; Generate Secret Link</button>
            </div>
        </div>
    </div>

    {{-- Reveal JIT 2FA / Vault Passcode Challenge Modal --}}
    @php
        $u = auth()->user();
        $has2fa = $u ? $u->hasTwoFactorEnabled() : false;
        $hasVaultPass = $u && !empty($u->vault_pass);
        $isTotpOn = $has2fa && $u->requiresTwoFactorFor('password_reveal');
    @endphp
    <div id="modal-reveal-auth" class="ff-modal-backdrop" style="z-index: 99999;" hidden>
        <div class="ff-modal" style="max-width: 420px; width: 100%;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 16px;">
                <div style="flex: 1; min-width: 0;">
                    <div class="ff-modal-title" style="font-size: 16px; font-weight: 700;">🔐 Confidential Password Reveal</div>
                    <div class="ff-modal-sub" id="reveal-auth-sub" style="font-size: 13px; line-height: 1.45; color: var(--ff-text-2); margin-top: 4px;">
                        {{ $isTotpOn ? 'Enter your 6-digit Authenticator code to reveal passwords.' : ($hasVaultPass ? 'Enter your vault passcode to reveal confidential credentials.' : 'Enter your password to reveal confidential credentials.') }}
                    </div>
                </div>
                <button type="button" class="ff-modal-close btn-close-reveal-auth" aria-label="Close modal" style="background: none; border: none; color: var(--ff-text-soft); cursor: pointer; padding: 4px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: -2px; margin-right: -4px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <div id="reveal-email-toast" style="display: none; padding: 8px 12px; border-radius: 8px; font-size: 12px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #10b981; margin-bottom: 12px; text-align: center;"></div>

            @if ($u && $u->isVaultBiometricEnabled())
            <div id="biometricRevealContainer" style="display:none; margin-bottom: 14px;">
                <button type="button" id="btnBiometricRevealUnlock" class="ff-btn ff-btn-outline ff-btn-block" style="display:flex; align-items:center; justify-content:center; gap:8px; border-color:var(--ff-accent); color:var(--ff-accent); padding:9px 12px; font-weight:600; border-radius:8px;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10c0 4.418-2.865 8.166-6.839 9.489"></path>
                        <path d="M12 7a5 5 0 0 0-5 5c0 1.38.56 2.63 1.464 3.536"></path>
                        <path d="M12 11a1 1 0 0 0-1 1c0 .55.45 1 1 1s1-.45 1-1"></path>
                        <path d="M16.5 16.5A5.98 5.98 0 0 0 18 12a6 6 0 0 0-6-6"></path>
                        <path d="M7 19.5c1.45.95 3.17 1.5 5 1.5 1.43 0 2.78-.34 3.98-.95"></path>
                    </svg>
                    <span>Verify with Fingerprint</span>
                </button>
                <div style="display:flex; align-items:center; gap:10px; margin: 12px 0 4px;">
                    <div style="flex:1; height:1px; background:var(--ff-border);"></div>
                    <span style="font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:var(--ff-text-muted);">or enter code / password</span>
                    <div style="flex:1; height:1px; background:var(--ff-border);"></div>
                </div>
            </div>
            @endif

            <form id="form-reveal-auth" class="ff-stack-sm">
                @csrf
                <input type="hidden" id="revealAuthMode" value="{{ $isTotpOn ? 'totp' : 'password' }}">

                <div class="ff-field">
                    <label class="ff-label" id="revealAuthLabel" style="font-weight: 600;">
                        {{ $isTotpOn ? 'Security Code (2FA / TOTP)' : ($hasVaultPass ? 'Vault Passcode' : 'Account Password') }}
                    </label>
                    <input type="{{ $isTotpOn ? 'text' : 'password' }}"
                        id="revealAuthInput" class="ff-input ff-mono"
                        placeholder="{{ $isTotpOn ? '000000' : '••••••••' }}"
                        style="text-align: center; font-size: {{ $isTotpOn ? '22px' : '16px' }}; font-weight: 700; letter-spacing: {{ $isTotpOn ? '0.35em' : '0.2em' }};"
                        autocomplete="off" maxlength="{{ $isTotpOn ? '12' : '64' }}" required>
                    <div id="reveal-auth-error" class="ff-hint" style="color: var(--ff-danger); display: none; margin-top: 6px;"></div>
                </div>

                @if ($has2fa)
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 12.5px;">
                        <button type="button" id="btnToggleRevealMode" class="ff-hint" style="background: none; border: none; padding: 0; cursor: pointer; color: var(--ff-accent); text-decoration: underline;">
                            @if ($isTotpOn)
                                🔑 Use Account / Vault Password instead
                            @else
                                📱 Use 2FA Authenticator Code instead
                            @endif
                        </button>

                        @if ($u && ($u->two_factor_type === 'email' || $u->two_factor_type === 'both'))
                            <button type="button" id="btnSendRevealEmailOtp" class="ff-hint" style="background: none; border: none; padding: 0; cursor: pointer; color: var(--ff-text-soft);">
                                ✉️ Send code via email
                            </button>
                        @endif
                    </div>
                @endif

                <div class="ff-row" style="gap: 10px; justify-content: flex-end; margin-top: 10px;">
                    <button type="button" class="ff-btn btn-close-reveal-auth">Cancel</button>
                    <button type="submit" id="btnSubmitRevealAuth" class="ff-btn ff-btn-primary">
                        Verify &amp; Unlock
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('push-script')
    <script>
        const ffPasswordData = @json($detailPayload);
        const ffSecretCache = new Map();
        const ffSecretTimers = new Map();

        // ------------------------------------------------ open password detail modal
        $(document).on('click', '.ff-pw-open', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (window.ff && window.ff.closeAllMenus) {
                window.ff.closeAllMenus();
            }
            const token = $(this).data('reveal-token') || $(this).attr('data-reveal-token');
            if (token) {
                ffOpenPasswordDetail(token);
            }
        });

        // Auto close dropdowns on clicking item
        $(document).on('click', '[data-ff-menu-panel] button, [data-ff-menu-panel] a', function() {
            if (window.ff && window.ff.closeAllMenus) {
                window.ff.closeAllMenus();
            }
        });

        function ffEscape(v) {
            return String(v == null ? '' : v).replace(/[&<>"']/g, function(c) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c];
            });
        }

        function ffOpenPasswordDetail(tokenOrId) {
            var pw = ffPasswordData.find(function(p) {
                return String(p.revealToken) === String(tokenOrId) || String(p.id) === String(tokenOrId);
            });
            if (!pw) return;

            var fieldsHtml = (pw.authFields || []).map(function(f) {
                return `
                    <div class="ff-field" style="margin-top:14px;">
                        <label class="ff-label">${ffEscape(f.label)}</label>
                        <div class="ff-row" style="gap:8px;">
                            <span class="ff-mono ff-grow ff-truncate ff-secret-text" data-reveal-token="${pw.revealToken}" data-field-index="${f.index}" style="font-size:14px; color:var(--ff-text);">••••••••</span>
                            <button type="button" class="ff-menu-btn ff-jit-reveal-btn" data-reveal-token="${pw.revealToken}" data-field-index="${f.index}" aria-label="Reveal">
                                <i data-lucide="eye" class="w-[15px] h-[15px]"></i>
                            </button>
                        </div>
                    </div>`;
            }).join('');

            var notesHtml = pw.notes ?
                `<div class="ff-field" style="margin-top:14px;">
                        <label class="ff-label">Notes</label>
                        <p class="ff-hint" style="margin:0; line-height:1.55;">${ffEscape(pw.notes)}</p>
                     </div>` :
                '';

            var urlHtml = pw.url ?
                `<div class="ff-field" style="margin-top:14px;">
                        <label class="ff-label">Website</label>
                        <a href="${ffEscape(pw.url)}" target="_blank" rel="noopener noreferrer" class="ff-accent-text ff-truncate" style="font-size:13.5px; text-decoration:none;">${ffEscape(pw.url)}</a>
                     </div>` :
                '';

            var host = document.getElementById('pwDetailHost');
            host.hidden = false;
            host.innerHTML = `
                <div class="ff-modal-backdrop" onclick="if (event.target === this) ffClosePasswordDetail()">
                    <div class="ff-modal" style="width:440px;">
                        <div class="ff-row-between" style="flex-wrap:nowrap; align-items:flex-start;">
                            <div class="ff-row" style="gap:12px; min-width:0;">
                                <span class="ff-tile-icon ff-tile-icon-sm" style="width:38px;height:38px;border-radius:10px;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="10" rx="2"/>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                    </svg>
                                </span>
                                <div class="ff-min0">
                                    <div class="ff-modal-title" style="font-size:16.5px;">${ffEscape(pw.title)}</div>
                                    <div class="ff-modal-sub">${ffEscape(pw.username)}</div>
                                </div>
                            </div>
                            <button type="button" class="ff-modal-close" onclick="ffClosePasswordDetail()" aria-label="Close">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </button>
                        </div>

                        <div class="ff-divider" style="margin:18px 0 0;"></div>

                        <div class="ff-field" style="margin-top:14px;">
                            <label class="ff-label">Password</label>
                            <div class="ff-row" style="gap:8px;">
                                <span class="ff-mono ff-grow ff-secret-text" data-reveal-token="${pw.revealToken}" style="font-size:14px; color:var(--ff-text);">••••••••</span>
                                <button type="button" class="ff-menu-btn ff-jit-reveal-btn" data-reveal-token="${pw.revealToken}" aria-label="Reveal password">
                                    <i data-lucide="eye" class="w-[15px] h-[15px]"></i>
                                </button>
                            </div>
                        </div>
                        ${fieldsHtml}
                        ${urlHtml}
                        ${notesHtml}

                        <div class="ff-divider" style="margin:18px 0 0;"></div>
                        <div class="ff-form-actions" style="margin-top:16px;">
                            <button type="button" class="ff-btn" onclick="ffClosePasswordDetail()">Close</button>
                            <a href="{{ url('panel/edit-password') }}/${encodeURIComponent(pw.id)}" class="ff-btn ff-btn-primary">Edit</a>
                        </div>
                    </div>
                </div>`;

            window.ff.icons();
        }

        function ffClosePasswordDetail() {
            var host = document.getElementById('pwDetailHost');
            host.hidden = true;
            host.innerHTML = '';
        }

        // -------------------------------------------------------- delete / hide actions
        $(document).on('click', '.ff-pw-delete-btn', async function(e) {
            e.preventDefault();
            const token = $(this).data('token');
            const title = $(this).data('title') || 'this credential';
            const confirmed = await window.ff.confirm({
                title: '🗑️ Delete Credential',
                message: `Permanently delete "${title}"? This action cannot be undone.`,
                confirmText: 'Delete Credential',
                isDanger: true
            });
            if (confirmed) {
                const form = document.getElementById('pw-delete-' + token);
                if (form) form.submit();
            }
        });

        $(document).on('click', '.ff-pw-hide-btn', async function(e) {
            e.preventDefault();
            const token = $(this).data('token');
            const isHidden = $(this).data('hidden') === '1';
            const confirmed = await window.ff.confirm({
                title: isHidden ? '👁️ Restore Credential' : '👁️ Hide Credential',
                message: isHidden ? 'Unhide this credential and move it to your regular list?' : 'Move this credential to your private hidden vault?',
                confirmText: isHidden ? 'Unhide' : 'Hide',
                isDanger: false
            });
            if (confirmed) {
                const form = document.getElementById('pw-hide-' + token);
                if (form) form.submit();
            }
        });

        const ffRevealLifetime = {{ (int) $u->getPasswordRevealLifetime() }};
        const ffSecretCache = new Map();
        const ffSecretTimers = new Map();

        // ------------------------------------------------ JIT Reveal Handler
        $(document).on('click', '.ff-jit-reveal-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var btn = $(this).closest('.ff-jit-reveal-btn');
            var revealToken = btn.attr('data-reveal-token') || btn.data('reveal-token') || btn.attr('data-pw-id') || btn.data('pw-id') || btn.closest('.ff-row').find('.ff-secret-text').attr('data-reveal-token') || btn.closest('.ff-row').find('.ff-secret-text').data('reveal-token') || btn.closest('.ff-list-row').find('[data-reveal-token]').attr('data-reveal-token');
            var fieldIndex = btn.attr('data-field-index') !== undefined ? btn.attr('data-field-index') : btn.data('field-index');
            var wrap = btn.closest('.ff-row');
            var textSpan = wrap.find('.ff-secret-text').first();
            var cacheKey = String(revealToken) + '_' + (fieldIndex !== undefined && fieldIndex !== null ? fieldIndex : 'main');

            if (!revealToken || revealToken === 'undefined') {
                window.ff.toast('Could not determine credential token. Please refresh the page.', 'error', 3500);
                return;
            }

            var isRevealed = btn.data('revealed') === true;

            if (isRevealed) {
                // Hide & Wipe from display
                textSpan.text('••••••••');
                btn.data('revealed', false);
                btn.find('i').attr('data-lucide', 'eye');
                ffSecretCache.delete(cacheKey);
                if (ffSecretTimers.has(cacheKey)) {
                    clearTimeout(ffSecretTimers.get(cacheKey));
                    ffSecretTimers.delete(cacheKey);
                }
                window.ff.icons();
                return;
            }

            if (ffRevealLifetime > 0 && ffSecretCache.has(cacheKey)) {
                revealSecretInDom(btn, textSpan, ffSecretCache.get(cacheKey), cacheKey);
                return;
            }

            textSpan.text('•••');
            $.ajax({
                url: "{{ url('panel/passwords/reveal') }}/" + encodeURIComponent(revealToken),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    field_index: fieldIndex !== undefined && fieldIndex !== null ? fieldIndex : null
                },
                success: function(res) {
                    if (res.ok === 1 && res.secret !== undefined) {
                        if (ffRevealLifetime > 0) {
                            ffSecretCache.set(cacheKey, res.secret);
                        }
                        revealSecretInDom(btn, textSpan, res.secret, cacheKey);
                    } else {
                        textSpan.text('••••••••');
                        window.ff.toast(res.info || 'Could not decrypt secret.', 'error', 4000);
                    }
                },
                error: function(xhr) {
                    textSpan.text('••••••••');
                    if (xhr.status === 403 && xhr.responseJSON && xhr.responseJSON.code === 'REQUIRES_2FA') {
                        openRevealAuthModal(function() {
                            btn.trigger('click');
                        });
                        return;
                    }
                    var msg = xhr.responseJSON ? xhr.responseJSON.info || xhr.responseJSON.message : 'Error fetching secret.';
                    window.ff.toast(msg, 'error', 4000);
                }
            });
        });

        function revealSecretInDom(btn, textSpan, secret, cacheKey) {
            textSpan.text(secret || '(empty)');
            btn.data('revealed', true);
            btn.find('i').attr('data-lucide', 'eye-off');
            window.ff.icons();

            if (ffSecretTimers.has(cacheKey)) {
                clearTimeout(ffSecretTimers.get(cacheKey));
            }
            var timer = setTimeout(function() {
                textSpan.text('••••••••');
                btn.data('revealed', false);
                btn.find('i').attr('data-lucide', 'eye');
                ffSecretCache.delete(cacheKey);
                ffSecretTimers.delete(cacheKey);
                window.ff.icons();
            }, 20000);
            ffSecretTimers.set(cacheKey, timer);
        }

        function ffUniversalCopy(text, btnElement, successMsg) {
            var msg = successMsg || 'Copied to clipboard! 📋';
            var $btn = $(btnElement);

            function showSuccess() {
                if (window.ff && window.ff.toast) {
                    window.ff.toast(msg, 'success', 2500);
                }
                if ($btn && $btn.length) {
                    var $icon = $btn.find('i, svg').first();
                    $icon.attr('data-lucide', 'check');
                    if (window.lucide) {
                        window.lucide.createIcons({
                            root: $btn[0]
                        });
                    } else if (window.ff && window.ff.icons) {
                        window.ff.icons();
                    }
                    setTimeout(function() {
                        $icon.attr('data-lucide', 'copy');
                        if (window.lucide) {
                            window.lucide.createIcons({
                                root: $btn[0]
                            });
                        } else if (window.ff && window.ff.icons) {
                            window.ff.icons();
                        }
                    }, 1800);
                }
            }

            function fallbackCopy() {
                var textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.setAttribute('readonly', '');
                textarea.style.position = 'fixed';
                textarea.style.top = '0';
                textarea.style.left = '0';
                textarea.style.width = '2em';
                textarea.style.height = '2em';
                textarea.style.padding = '0';
                textarea.style.border = 'none';
                textarea.style.outline = 'none';
                textarea.style.boxShadow = 'none';
                textarea.style.background = 'transparent';
                textarea.style.opacity = '0.01';
                textarea.style.zIndex = '-1';
                document.body.appendChild(textarea);

                textarea.focus();
                textarea.select();
                textarea.setSelectionRange(0, textarea.value.length);

                var success = false;
                try {
                    success = document.execCommand('copy');
                } catch (e) {
                    success = false;
                }
                document.body.removeChild(textarea);

                if (success) {
                    showSuccess();
                } else {
                    if (window.ff && window.ff.toast) {
                        window.ff.toast('Could not copy automatically.', 'error', 3000);
                    }
                }
            }

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function() {
                    showSuccess();
                }).catch(function() {
                    fallbackCopy();
                });
            } else {
                fallbackCopy();
            }
        }

        // Copy plain text helper
        $(document).on('click', '.ff-copy-text-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var btn = $(this).closest('.ff-copy-text-btn');
            var text = btn.attr('data-copy') || btn.data('copy');
            if (text) {
                ffUniversalCopy(text, btn, 'Username copied to clipboard! 📋');
            }
        });

        // JIT Authenticated Copy handler
        $(document).on('click', '.ff-jit-copy-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var btn = $(this).closest('.ff-jit-copy-btn');
            var revealToken = btn.attr('data-reveal-token') || btn.data('reveal-token') || btn.attr('data-pw-id') || btn.data('pw-id') || btn.closest('.ff-row').find('.ff-secret-text').attr('data-reveal-token') || btn.closest('.ff-row').find('.ff-secret-text').data('reveal-token') || btn.closest('.ff-list-row').find('[data-reveal-token]').attr('data-reveal-token') || btn.closest('.ff-list-row').data('pw-row');
            var fieldIndex = btn.attr('data-field-index') !== undefined ? btn.attr('data-field-index') : btn.data('field-index');
            var cacheKey = String(revealToken) + '_' + (fieldIndex !== undefined && fieldIndex !== null ? fieldIndex : 'main');

            if (!revealToken || revealToken === 'undefined') {
                if (window.ff && window.ff.toast) {
                    window.ff.toast('Could not determine credential token. Please refresh the page.', 'error', 3500);
                }
                return;
            }

            if (ffSecretCache.has(cacheKey)) {
                ffUniversalCopy(ffSecretCache.get(cacheKey), btn, 'Password copied to clipboard! 📋');
                return;
            }

            // Fetch decrypted secret with vault passcode verification
            $.ajax({
                url: "{{ url('panel/passwords/reveal') }}/" + encodeURIComponent(revealToken),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    field_index: fieldIndex !== undefined && fieldIndex !== null ? fieldIndex : null
                },
                success: function(res) {
                    if (res.ok === 1 && res.secret !== undefined) {
                        ffSecretCache.set(cacheKey, res.secret);
                        ffUniversalCopy(res.secret, btn, 'Password copied to clipboard! 📋');
                    } else {
                        if (window.ff && window.ff.toast) {
                            window.ff.toast(res.info || 'Could not decrypt secret.', 'error', 4000);
                        }
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403 && xhr.responseJSON && xhr.responseJSON.code === 'REQUIRES_2FA') {
                        openRevealAuthModal(function() {
                            btn.trigger('click');
                        });
                        return;
                    }
                    var msg = xhr.responseJSON ? xhr.responseJSON.info || xhr.responseJSON.message : 'Error fetching secret.';
                    if (window.ff && window.ff.toast) {
                        window.ff.toast(msg, 'error', 4000);
                    }
                }
            });
        });

        const ffPasswordData = @json($detailPayload);

        // Open password detail modal
        $(document).on('click', '.ff-pw-open', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (window.ff && window.ff.closeAllMenus) {
                window.ff.closeAllMenus();
            }
            const token = $(this).data('reveal-token') || $(this).attr('data-reveal-token');
            if (token) {
                ffOpenPasswordDetail(token);
            }
        });

        function ffEscape(v) {
            return String(v == null ? '' : v).replace(/[&<>"']/g, function(c) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c];
            });
        }

        function ffOpenPasswordDetail(tokenOrId) {
            var pw = ffPasswordData.find(function(p) {
                return String(p.revealToken) === String(tokenOrId) || String(p.id) === String(tokenOrId);
            });
            if (!pw) return;

            var fieldsHtml = (pw.authFields || []).map(function(f) {
                return `
                    <div class="ff-field" style="margin-top:14px;">
                        <label class="ff-label">${ffEscape(f.label)}</label>
                        <div class="ff-row" style="gap:8px;">
                            <span class="ff-mono ff-grow ff-truncate ff-secret-text" data-reveal-token="${pw.revealToken}" data-field-index="${f.index}" style="font-size:14px; color:var(--ff-text);">••••••••</span>
                            <button type="button" class="ff-menu-btn ff-jit-reveal-btn" data-reveal-token="${pw.revealToken}" data-field-index="${f.index}" aria-label="Reveal" title="Reveal secret">
                                <i data-lucide="eye" class="w-[15px] h-[15px]"></i>
                            </button>
                            <button type="button" class="ff-menu-btn ff-jit-copy-btn" data-reveal-token="${pw.revealToken}" data-field-index="${f.index}" aria-label="Copy" title="Copy secret">
                                <i data-lucide="copy" class="w-[15px] h-[15px]"></i>
                            </button>
                        </div>
                    </div>`;
            }).join('');

            var notesHtml = pw.notes ?
                `<div class="ff-field" style="margin-top:14px;">
                        <label class="ff-label">Notes</label>
                        <p class="ff-hint" style="margin:0; line-height:1.55;">${ffEscape(pw.notes)}</p>
                     </div>` :
                '';

            var urlHtml = pw.url ?
                `<div class="ff-field" style="margin-top:14px;">
                        <label class="ff-label">Website</label>
                        <a href="${ffEscape(pw.url)}" target="_blank" rel="noopener noreferrer" class="ff-accent-text ff-truncate" style="font-size:13.5px; text-decoration:none;">${ffEscape(pw.url)}</a>
                     </div>` :
                '';

            var host = document.getElementById('pwDetailHost');
            host.hidden = false;
            host.innerHTML = `
                <div class="ff-modal-backdrop" onclick="if (event.target === this) ffClosePasswordDetail()">
                    <div class="ff-modal" style="width:440px;">
                        <div class="ff-row-between" style="flex-wrap:nowrap; align-items:flex-start; gap:12px;">
                            <div class="ff-row" style="gap:12px; min-width:0; flex:1;">
                                <span class="ff-tile-icon ff-tile-icon-sm" style="width:38px;height:38px;border-radius:10px; flex-shrink:0;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="10" rx="2"/>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                    </svg>
                                </span>
                                <div class="ff-min0" style="flex:1; min-width:0;">
                                    <div class="ff-modal-title" style="font-size:16.5px; word-break:break-word;">${ffEscape(pw.title)}</div>
                                    <div style="display:flex; align-items:center; gap:6px; margin-top:2px;">
                                        <span class="ff-modal-sub" style="word-break:break-word;">${ffEscape(pw.username || 'No username')}</span>
                                        ${pw.username ? `
                                        <button type="button" class="ff-menu-btn ff-copy-text-btn" data-copy="${ffEscape(pw.username)}" title="Copy username" style="width:22px; height:22px; padding:2px; flex-shrink:0;">
                                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                        </button>` : ''}
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="ff-modal-close" onclick="ffClosePasswordDetail()" aria-label="Close" style="background:none; border:none; color:var(--ff-text-soft); cursor:pointer; padding:4px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:-2px; margin-right:-4px;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </button>
                        </div>

                        <div class="ff-divider" style="margin:18px 0 0;"></div>

                        <div class="ff-field" style="margin-top:14px;">
                            <label class="ff-label">Password</label>
                            <div class="ff-row" style="gap:8px;">
                                <span class="ff-mono ff-grow ff-secret-text" data-reveal-token="${pw.revealToken}" style="font-size:14px; color:var(--ff-text);">••••••••</span>
                                <button type="button" class="ff-menu-btn ff-jit-reveal-btn" data-reveal-token="${pw.revealToken}" aria-label="Reveal password" title="Reveal password">
                                    <i data-lucide="eye" class="w-[15px] h-[15px]"></i>
                                </button>
                                <button type="button" class="ff-menu-btn ff-jit-copy-btn" data-reveal-token="${pw.revealToken}" aria-label="Copy password" title="Copy password">
                                    <i data-lucide="copy" class="w-[15px] h-[15px]"></i>
                                </button>
                            </div>
                        </div>

                        ${fieldsHtml}
                        ${urlHtml}
                        ${notesHtml}
                    </div>
                </div>`;
            window.ff.icons();
        }

        function ffClosePasswordDetail() {
            var host = document.getElementById('pwDetailHost');
            if (host) {
                host.hidden = true;
                host.innerHTML = '';
            }
        }

        // ------------------------------------------------- vault session timer
        (function() {
            var initialRemaining = parseInt({{ $remainingTime ?? 1800 }});
            var isImmediate = initialRemaining === 0;
            var remainingSeconds = initialRemaining;
            var timerElement = document.getElementById('timer-display') || document.getElementById('sessionTimer');
            var timerBadge = document.getElementById('session-timer') || document.getElementById('sessionTimerBadge');

            if (isImmediate) {
                if (timerElement) timerElement.textContent = 'Ask Always';
                if (timerBadge) {
                    timerBadge.style.background = 'rgba(239, 68, 68, 0.15)';
                    timerBadge.style.color = '#ef4444';
                    timerBadge.style.borderColor = 'rgba(239, 68, 68, 0.4)';
                    timerBadge.setAttribute('title', 'Vault configured to Ask Always on every visit');
                }
                return;
            }

            function paintBadge() {
                if (!timerBadge) return;
                if (remainingSeconds <= 60) {
                    timerBadge.style.background = 'rgba(239, 68, 68, 0.2)';
                    timerBadge.style.color = '#ef4444';
                    timerBadge.style.borderColor = '#ef4444';
                } else if (remainingSeconds <= 300) {
                    timerBadge.style.background = 'rgba(245, 158, 11, 0.15)';
                    timerBadge.style.color = '#f59e0b';
                    timerBadge.style.borderColor = 'rgba(245, 158, 11, 0.4)';
                } else {
                    timerBadge.style.background = '';
                    timerBadge.style.color = '';
                    timerBadge.style.borderColor = '';
                }
            }

            function formatTime(totalSecs) {
                var hours = Math.floor(totalSecs / 3600);
                var minutes = Math.floor((totalSecs % 3600) / 60);
                var seconds = totalSecs % 60;
                if (hours > 0) {
                    return String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
                }
                return String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            }

            function updateTimer() {
                if (remainingSeconds <= 0) {
                    clearInterval(timerInterval);
                    if (window.ff && window.ff.toast) {
                        window.ff.toast('Vault session expired. Locking vault…', 'error', 3000);
                    }
                    setTimeout(function() {
                        window.location.href = '{{ route('panel.hiddenPasswordsLogin') }}';
                    }, 600);
                    return;
                }

                if (timerElement) {
                    timerElement.textContent = formatTime(remainingSeconds);
                }
                paintBadge();
                remainingSeconds--;
            }

            var timerInterval = setInterval(updateTimer, 1000);
            updateTimer();

            // Click on timer badge to manually extend vault session
            if (timerBadge) {
                timerBadge.style.cursor = 'pointer';
                timerBadge.setAttribute('title', 'Click to extend vault session');
                timerBadge.addEventListener('click', function() {
                    fetch('{{ route('panel.extendHiddenPasswordsSession') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.ok === 1 || data.code === 200 || data.success) {
                            remainingSeconds = data.remaining_time || 1800;
                            updateTimer();
                            if (window.ff && window.ff.toast) {
                                window.ff.toast('Vault session extended!', 'success', 2000);
                            }
                        }
                    })
                    .catch(err => console.error('Extension error:', err));
                });
            }
        })();

        // ------------------------------------------------ Reveal 2FA Authentication Modal Logic
        let pendingRevealCallback = null;
        const revealAuthModal = document.getElementById('modal-reveal-auth');
        const formRevealAuth = document.getElementById('form-reveal-auth');
        const revealAuthInput = document.getElementById('revealAuthInput');
        const revealAuthLabel = document.getElementById('revealAuthLabel');
        const revealAuthMode = document.getElementById('revealAuthMode');
        const revealAuthSub = document.getElementById('reveal-auth-sub');
        const revealAuthError = document.getElementById('reveal-auth-error');
        const btnToggleReveal = document.getElementById('btnToggleRevealMode');
        const btnSendRevealEmail = document.getElementById('btnSendRevealEmailOtp');
        const revealEmailToast = document.getElementById('reveal-email-toast');
        const btnSubmitReveal = document.getElementById('btnSubmitRevealAuth');

        // Check for Biometric Fingerprint availability for Password Reveals
        if (window.FileFusionNative && window.FileFusionNative.biometrics) {
            window.FileFusionNative.biometrics.isAvailable().then(avail => {
                const bioCont = document.getElementById('biometricRevealContainer');
                if (bioCont && (avail || typeof window.FileFusionBiometrics !== 'undefined')) {
                    bioCont.style.display = 'block';
                }
            });

            const bioRevealBtn = document.getElementById('btnBiometricRevealUnlock');
            if (bioRevealBtn) {
                bioRevealBtn.onclick = async function() {
                    const success = await window.FileFusionNative.biometrics.unlockVault('reveal');
                    if (success) {
                        const cb = pendingRevealCallback;
                        closeRevealAuthModal();
                        if (typeof cb === 'function') cb();
                    }
                };
            }
        }

        function openRevealAuthModal(callback) {
            pendingRevealCallback = callback;
            revealAuthModal.hidden = false;
            revealAuthError.style.display = 'none';
            revealAuthInput.value = '';
            revealAuthInput.focus();
            window.ff.icons();

            // Auto trigger fingerprint prompt on native app
            if (window.FileFusionNative && (window.FileFusionNative.isNative || typeof window.FileFusionBiometrics !== 'undefined')) {
                setTimeout(async () => {
                    const success = await window.FileFusionNative.biometrics.unlockVault('reveal');
                    if (success) {
                        const cb = pendingRevealCallback;
                        closeRevealAuthModal();
                        if (typeof cb === 'function') cb();
                    }
                }, 300);
            }
        }

        function closeRevealAuthModal() {
            revealAuthModal.hidden = true;
            pendingRevealCallback = null;
        }

        document.querySelectorAll('.btn-close-reveal-auth').forEach(btn => {
            btn.addEventListener('click', closeRevealAuthModal);
        });

        if (revealAuthModal) {
            revealAuthModal.addEventListener('click', function(e) {
                if (e.target === revealAuthModal) closeRevealAuthModal();
            });
        }

        // Toggle TOTP vs Password
        if (btnToggleReveal) {
            btnToggleReveal.addEventListener('click', function(e) {
                e.preventDefault();
                let isTotp = revealAuthMode.value === 'totp';
                isTotp = !isTotp;
                revealAuthMode.value = isTotp ? 'totp' : 'password';

                if (isTotp) {
                    revealAuthLabel.textContent = 'Security Code (2FA / TOTP)';
                    revealAuthInput.type = 'text';
                    revealAuthInput.placeholder = '000000';
                    revealAuthInput.maxLength = 12;
                    revealAuthInput.style.letterSpacing = '0.35em';
                    revealAuthInput.style.fontSize = '22px';
                    revealAuthSub.textContent = 'Enter your 6-digit Authenticator code to reveal passwords.';
                    btnToggleReveal.textContent = '🔑 Use Account / Vault Password instead';
                } else {
                    revealAuthLabel.textContent = 'Account / Vault Password';
                    revealAuthInput.type = 'password';
                    revealAuthInput.placeholder = '••••••••';
                    revealAuthInput.maxLength = 64;
                    revealAuthInput.style.letterSpacing = '0.2em';
                    revealAuthInput.style.fontSize = '16px';
                    revealAuthSub.textContent = 'Enter your password to reveal confidential credentials.';
                    btnToggleReveal.textContent = '📱 Use 2FA Authenticator Code instead';
                }

                revealAuthInput.value = '';
                revealAuthInput.focus();
                revealAuthError.style.display = 'none';
            });
        }

        // Send Email OTP for Reveal
        if (btnSendRevealEmail) {
            btnSendRevealEmail.addEventListener('click', function(e) {
                e.preventDefault();
                btnSendRevealEmail.disabled = true;
                btnSendRevealEmail.textContent = 'Sending email...';

                fetch("{{ route('two-factor.email-otp') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ action: 'Password Vault Reveal' })
                })
                .then(r => r.json())
                .then(data => {
                    revealEmailToast.style.display = 'block';
                    revealEmailToast.textContent = data.message || 'Security code sent to your email!';
                    if (revealAuthMode.value !== 'totp' && btnToggleReveal) btnToggleReveal.click();
                    btnSendRevealEmail.textContent = '✉️ Code Sent';
                    setTimeout(() => { btnSendRevealEmail.disabled = false; btnSendRevealEmail.textContent = '✉️ Resend email code'; }, 30000);
                })
                .catch(err => {
                    btnSendRevealEmail.disabled = false;
                    btnSendRevealEmail.textContent = '✉️ Send code via email';
                    revealAuthError.textContent = 'Failed to send email: ' + err.message;
                    revealAuthError.style.display = 'block';
                });
            });
        }

        // Submit Reveal Authentication
        if (formRevealAuth) {
            formRevealAuth.addEventListener('submit', function(e) {
                e.preventDefault();
                const val = revealAuthInput.value.trim();
                if (!val) {
                    revealAuthError.textContent = 'Please enter your code or password.';
                    revealAuthError.style.display = 'block';
                    return;
                }

                btnSubmitReveal.disabled = true;
                btnSubmitReveal.textContent = 'Verifying...';
                revealAuthError.style.display = 'none';

                fetch("{{ route('panel.passwords.verifyRevealAuth') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        code: revealAuthMode.value === 'totp' ? val : '',
                        password: revealAuthMode.value !== 'totp' ? val : ''
                    })
                })
                .then(r => r.json())
                .then(data => {
                    btnSubmitReveal.disabled = false;
                    btnSubmitReveal.textContent = 'Verify & Unlock';

                    if (data.ok === 1) {
                        closeRevealAuthModal();
                        const lifetimeSec = data.reveal_lifetime !== undefined ? data.reveal_lifetime : {{ (int) $u->getPasswordRevealLifetime() }};
                        const unlockMsg = lifetimeSec === 0
                            ? 'Verified! Decrypting credential.'
                            : `Unlocked! Passwords will remain accessible for ${Math.round(lifetimeSec / 60)} minutes.`;
                        window.ff.toast(unlockMsg, 'success', 3500);
                        if (typeof pendingRevealCallback === 'function') {
                            pendingRevealCallback();
                        }
                    } else {
                        revealAuthError.textContent = data.info || 'Invalid verification code or password.';
                        revealAuthError.style.display = 'block';
                        revealAuthInput.value = '';
                        revealAuthInput.focus();
                    }
                })
                .catch(err => {
                    btnSubmitReveal.disabled = false;
                    btnSubmitReveal.textContent = 'Verify & Unlock';
                    revealAuthError.textContent = 'Network error: ' + err.message;
                    revealAuthError.style.display = 'block';
                });
            });
        }

        $(document).on('change', '#hiddenPwPerPageSelect', function() {
            const perPage = $(this).val();
            $.ajax({
                url: "{{ route('panel.settings.updatePerPage') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    per_page: perPage
                },
                complete: function() {
                    const url = new URL(window.location.href);
                    url.searchParams.set('per_page', perPage);
                    url.searchParams.set('page', '1');
                    window.location.href = url.toString();
                }
            });
        });

        // =========================================================================
        // PASSWORD SHARE MODAL HANDLERS (ZERO-KNOWLEDGE WITH AUTH & WARNING)
        // =========================================================================
        $(document).on('click', '.open-password-share-modal', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (typeof ffClosePasswordDetail === 'function') {
                ffClosePasswordDetail();
            }
            if (window.ff && window.ff.closeAllMenus) {
                window.ff.closeAllMenus();
            }
            $('#modal-reveal-auth').prop('hidden', true);

            const pwId = $(this).data('pw-id') || $(this).attr('data-pw-id');
            const title = $(this).data('title') || $(this).attr('data-title') || 'Credential';

            $('#sharePasswordIdInput').val(pwId);
            $('#sharePasswordModalSubtitle').text('Zero-knowledge encrypted share for "' + title + '"');
            $('#sharePasswordAccountPassInput').val('');
            $('#sharePasswordPasscodeInput').val('');
            $('#sharePasswordResultBox').hide();
            $('#executeCreateSharePasswordBtn').prop('disabled', false).text('Authorize & Generate Secret Link');

            $('#sharePasswordModal').css('display', 'flex');
            if (window.lucide) window.lucide.createIcons();
        });

        $(document).on('click', '.closeSharePasswordModalBtn', function() {
            $('#sharePasswordModal').css('display', 'none');
        });

        $(document).on('click', '#executeCreateSharePasswordBtn', function() {
            const pwId = $('#sharePasswordIdInput').val();
            const accountPass = $('#sharePasswordAccountPassInput').val();
            const expiresInMins = $('#sharePasswordExpirySelect').val();
            const passcode = $('#sharePasswordPasscodeInput').val();
            const btn = $(this);

            if (!accountPass) {
                if (window.ff && window.ff.toast) {
                    window.ff.toast('Please enter your account password to authorize sharing.', 'error', 3500);
                } else {
                    alert('Please enter your account password to authorize sharing.');
                }
                $('#sharePasswordAccountPassInput').focus();
                return;
            }

            btn.prop('disabled', true).text('Encrypting & Authorizing...');

            $.ajax({
                url: "{{ route('panel.share.password') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    password_id: pwId,
                    account_password: accountPass,
                    expires_in_minutes: expiresInMins || 1440,
                    password: passcode || null,
                    burn_after_reading: 1,
                    is_anonymous: 1
                },
                success: function(res) {
                    btn.prop('disabled', false).text('Authorize & Generate Secret Link');
                    if (res.ok && res.share) {
                        $('#sharePasswordResultUrl').val(res.share.public_url);
                        $('#sharePasswordResultBox').slideDown();
                        $('#sharePasswordAccountPassInput').val('');
                        if (window.ff && window.ff.toast) {
                            window.ff.toast('Zero-knowledge secret share link generated!', 'success');
                        }
                        if (window.lucide) window.lucide.createIcons();
                    } else {
                        if (window.ff && window.ff.toast) {
                            window.ff.toast(res.message || 'Failed to generate secret link.', 'error');
                        }
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Authorize & Generate Secret Link');
                    const msg = xhr.responseJSON?.message || 'Error generating secret link. Check your password.';
                    if (window.ff && window.ff.toast) {
                        window.ff.toast(msg, 'error', 4000);
                    } else {
                        alert(msg);
                    }
                }
            });
        });

        $(document).on('click', '#copySharePasswordResultBtn', function() {
            const url = $('#sharePasswordResultUrl').val();
            if (window.ff && typeof window.ff.copy === 'function') {
                window.ff.copy(url, 'Secret link copied to clipboard!');
            } else if (window.copyToClipboard) {
                window.copyToClipboard(url, 'Secret link copied to clipboard!');
            }
        });

        $('#hiddenPwSearch').on('keyup', function() {
            const term = $(this).val().toLowerCase().trim();
            $('.ff-list-row').each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(term) > -1);
            });
        });

        window.ff.icons();
    </script>
@endsection
