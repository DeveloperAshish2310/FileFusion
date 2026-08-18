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
            <input type="search" id="hiddenPwSearch" placeholder="Search hidden passwords..." autocomplete="off">
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

            <div class="ff-list-row">
                <div class="ff-list-main">
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
                </div>

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

                @include('panel.ajax.partials.password_menu', ['pw' => $pw])
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

    {{-- Reveal JIT 2FA Challenge Modal --}}
    @php
        $u = auth()->user();
        $has2fa = $u ? $u->hasTwoFactorEnabled() : false;
        $isTotpOn = $has2fa && $u->requiresTwoFactorFor('password_reveal');
    @endphp
    <div id="modal-reveal-auth" class="ff-modal-backdrop" hidden>
        <div class="ff-modal" style="max-width: 420px; width: 100%;">
            <div class="ff-row-between" style="margin-bottom: 14px;">
                <div>
                    <div class="ff-modal-title">🔐 Confidential Password Reveal</div>
                    <div class="ff-modal-sub" id="reveal-auth-sub">
                        {{ $isTotpOn ? 'Enter your 6-digit Authenticator code to reveal passwords.' : 'Enter your password to reveal confidential credentials.' }}
                    </div>
                </div>
                <button type="button" class="ff-modal-close btn-close-reveal-auth" aria-label="Close modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div id="reveal-email-toast" style="display: none; padding: 8px 12px; border-radius: 8px; font-size: 12px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #10b981; margin-bottom: 12px; text-align: center;"></div>

            <form id="form-reveal-auth" class="ff-stack-sm">
                @csrf
                <input type="hidden" id="revealAuthMode" value="{{ $isTotpOn ? 'totp' : 'password' }}">

                <div class="ff-field">
                    <label class="ff-label" id="revealAuthLabel" style="font-weight: 600;">
                        {{ $isTotpOn ? 'Security Code (2FA / TOTP)' : 'Account / Vault Password' }}
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
        const ffSecretCache = new Map();
        const ffSecretTimers = new Map();

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

        // ------------------------------------------------ JIT Reveal Handler
        $(document).on('click', '.ff-jit-reveal-btn', function(e) {
            e.preventDefault();
            var btn = $(this);
            var revealToken = btn.data('reveal-token') || btn.data('pw-id') || btn.closest('.ff-row').find('.ff-secret-text').data('reveal-token') || btn.closest('.ff-row').find('.ff-secret-text').data('pw-id');
            var fieldIndex = btn.data('field-index');
            var wrap = btn.closest('.ff-row');
            var textSpan = wrap.find('.ff-secret-text').first();
            var cacheKey = String(revealToken) + '_' + (fieldIndex !== undefined ? fieldIndex : 'main');

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

            if (ffSecretCache.has(cacheKey)) {
                revealSecretInDom(btn, textSpan, ffSecretCache.get(cacheKey), cacheKey);
                return;
            }

            textSpan.text('•••');
            $.ajax({
                url: "{{ url('panel/passwords/reveal') }}/" + encodeURIComponent(revealToken),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    field_index: fieldIndex
                },
                success: function(res) {
                    if (res.ok === 1 && res.secret !== undefined) {
                        ffSecretCache.set(cacheKey, res.secret);
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

        // The "View" menu item triggers JIT reveal
        $(document).on('click', '.ff-pw-open', function(e) {
            e.preventDefault();
            var row = $('[data-pw-row="' + $(this).data('pw-id') + '"]');
            (row.length ? row : $(this).closest('.ff-list-row')).find('.ff-jit-reveal-btn').first().trigger('click');
        });

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

        function openRevealAuthModal(callback) {
            pendingRevealCallback = callback;
            revealAuthModal.hidden = false;
            revealAuthError.style.display = 'none';
            revealAuthInput.value = '';
            revealAuthInput.focus();
            window.ff.icons();
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
                        window.ff.toast('Unlocked! Passwords will remain accessible for 15 minutes.', 'success', 3500);
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
