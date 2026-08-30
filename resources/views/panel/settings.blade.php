@extends('layout.backend')
@push('title', 'Settings')

@section('content')
    @php
        $u = auth()->user();
        $pct = $u->getStorageUsagePercentage();
        $barState = $pct > 90 ? 'is-danger' : ($pct > 75 ? 'is-warn' : '');
        $displayName = $u->nickname ?? $u->name ?? $u->username ?? 'Account';
        $initial = function_exists('getInitials') ? getInitials($displayName) : strtoupper(substr($displayName, 0, 1));
    @endphp

    <h1 class="ff-h1 ff-h1-sm">Settings</h1>
    <p class="ff-sub">Manage your account settings and preferences.</p>

    @if ($errors->any())
        <div class="ff-alert is-error">
            <i data-lucide="alert-circle" class="w-4 h-4"></i>
            <div>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="ff-split-settings">
        <div class="ff-stack">

            {{-- ============================= Encrypted Profile Avatar ============================= --}}
            <div class="ff-form-card" id="avatar-section">
                <div class="ff-section-head" style="justify-content: space-between; align-items: flex-start;">
                    <div style="display: flex; gap: 12px;">
                        <span class="ff-section-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </span>
                        <div>
                            <div class="ff-section-title">Profile Picture</div>
                            <div class="ff-section-sub">Encrypted avatar stored securely at rest</div>
                        </div>
                    </div>
                    <span style="font-size: 11px; background: rgba(99, 102, 241, 0.12); color: #6366f1; border: 1px solid rgba(99, 102, 241, 0.25); padding: 3px 9px; border-radius: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        AES-256-GCM Encrypted
                    </span>
                </div>

                <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap; margin-top: 14px;">
                    <div style="position: relative; width: 84px; height: 84px; border-radius: 50%; overflow: hidden; border: 2px solid var(--ff-border); background: var(--ff-bg-2); flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                        <img src="{{ route('panel.user.avatar', $u->id) }}?v={{ time() }}" id="avatar-preview-img" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>

                    <div style="flex: 1; min-width: 220px; display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <form action="{{ route('panel.settings.avatar') }}" method="POST" enctype="multipart/form-data" id="form-upload-avatar" style="margin: 0; display: inline;">
                                @csrf
                                <input type="file" name="avatar" id="avatar-file-input" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif" style="display: none;">
                                
                                <button type="button" class="ff-btn ff-btn-primary ff-btn-sm" onclick="document.getElementById('avatar-file-input').click();">
                                    <i data-lucide="upload" class="w-3.5 h-3.5" style="margin-right: 4px;"></i> Upload New Photo
                                </button>
                            </form>

                            @if ($u->hasAvatar())
                                <form action="{{ route('panel.settings.removeAvatar') }}" method="POST" style="margin: 0; display: inline;">
                                    @csrf
                                    <button type="submit" class="ff-btn ff-btn-danger ff-btn-sm" onclick="return confirm('Remove your profile picture?');">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5" style="margin-right: 4px;"></i> Remove
                                    </button>
                                </form>
                            @endif
                        </div>
                        <span class="ff-hint" style="font-size: 12px; margin: 0;">JPG, PNG, WEBP or GIF up to 5MB. Images are automatically encrypted before saving to disk.</span>
                    </div>
                </div>
            </div>

            {{-- ============================= Account info ============================= --}}
            <form action="{{ route('panel.settings.update') }}" method="POST" class="ff-form-card" id="profile-section">
                @csrf

                <div class="ff-section-head">
                    <span class="ff-section-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </span>
                    <div>
                        <div class="ff-section-title">Account Info &amp; Profile</div>
                        <div class="ff-section-sub">Your basic profile details &amp; personal preferences</div>
                    </div>
                </div>

                <div class="ff-field">
                    <label class="ff-label" for="name">Name</label>
                    <input type="text" name="name" id="name" class="ff-input" required
                        placeholder="Enter your name" value="{{ old('name', $u->name ?? '') }}">
                </div>

                <div class="ff-field">
                    <label class="ff-label" for="email">Email</label>
                    <input type="email" name="email" id="email" class="ff-input" disabled readonly
                        value="{{ $u->email ?? '' }}">
                    <span class="ff-hint">Email cannot be changed</span>
                </div>

                <div class="ff-field">
                    <label class="ff-label" for="items_per_page">Items Visible Per Page</label>
                    <input type="number" name="items_per_page" id="items_per_page" class="ff-input" min="1" max="200"
                        placeholder="Default: {{ \App\Models\LandingPageSetting::get('sys_items_per_page', '12') }}"
                        value="{{ old('items_per_page', $u->items_per_page ?? '') }}">
                    <span class="ff-hint">Number of bookmarks, files, or cards shown per page. Leave blank to use system default ({{ \App\Models\LandingPageSetting::get('sys_items_per_page', '12') }}).</span>
                </div>

                <div class="ff-divider"></div>
                <div class="ff-section-head" id="vault-password-section">
                    <span class="ff-section-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="10" rx="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </span>
                    <div>
                        <div class="ff-section-title">Vault Password</div>
                        <div class="ff-section-sub">Protects your hidden files &amp; links</div>
                    </div>
                </div>

                <div class="ff-split-even">
                    <div class="ff-field">
                        <label class="ff-label" for="vault_password">New Vault Password</label>
                        <input type="password" name="vault_password" id="vault_password" class="ff-input"
                            placeholder="Enter new vault password" autocomplete="new-password">
                    </div>
                    <div class="ff-field">
                        <label class="ff-label" for="vault_password_confirmation">Confirm Vault Password</label>
                        <input type="password" name="vault_password_confirmation" id="vault_password_confirmation"
                            class="ff-input" placeholder="Confirm vault password" autocomplete="new-password">
                    </div>
                </div>
                <div class="ff-hint" style="margin-top:-8px;">Leave blank to keep your current vault password</div>

                <div class="ff-divider"></div>
                <div class="ff-form-actions">
                    <a href="{{ route('panel.dashboard') }}" class="ff-btn">Cancel</a>
                    <button type="submit" class="ff-btn ff-btn-primary">Update Settings</button>
                </div>
            </form>

            {{-- ============================ Change password ============================ --}}
            <form action="{{ route('panel.settings.update.password') }}" method="POST" class="ff-form-card" id="change-password-section">
                @csrf

                <div class="ff-section-head">
                    <span class="ff-section-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="10" rx="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            <line x1="12" y1="15" x2="12" y2="17" />
                        </svg>
                    </span>
                    <div>
                        <div class="ff-section-title">Change Password</div>
                        <div class="ff-section-sub">Update your login password</div>
                    </div>
                </div>

                <div class="ff-field">
                    <label class="ff-label" for="current_password">Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="ff-input" required
                        placeholder="Enter current password" autocomplete="current-password">
                </div>

                <div class="ff-split-even">
                    <div class="ff-field">
                        <label class="ff-label" for="new_password">New Password</label>
                        <input type="password" name="new_password" id="new_password" class="ff-input" required
                            placeholder="Min 8 characters" autocomplete="new-password">
                    </div>
                    <div class="ff-field">
                        <label class="ff-label" for="new_password_confirmation">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                            class="ff-input" required placeholder="Confirm new password" autocomplete="new-password">
                    </div>
                </div>

                <div class="ff-divider"></div>
                <div class="ff-form-actions">
                    <a href="{{ route('panel.dashboard') }}" class="ff-btn">Cancel</a>
                    <button type="submit" class="ff-btn ff-btn-primary">Change Password</button>
                </div>
            </form>

            {{-- ============================ Vault & Passcode Settings ============================ --}}
            <form action="{{ route('panel.settings.update') }}" method="POST" class="ff-form-card" id="vault-password-section">
                @csrf

                <div class="ff-section-head">
                    <span class="ff-section-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </span>
                    <div>
                        <div class="ff-section-title">Vault Security &amp; Session Lifetimes</div>
                        <div class="ff-section-sub">Configure timeout duration for Hidden Files, Links, Passwords, and Vault Passcode</div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                    <div class="ff-field">
                        <label class="ff-label" for="hidden_files_session_lifetime">📁 Hidden Files Timeout</label>
                        <select name="hidden_files_session_lifetime" id="hidden_files_session_lifetime" class="ff-select">
                            <option value="0" {{ $u->getHiddenFilesSessionLifetime() === 0 ? 'selected' : '' }}>⚡ Immediate (Ask Always)</option>
                            <option value="120" {{ $u->getHiddenFilesSessionLifetime() == 120 ? 'selected' : '' }}>⏱️ 2 Minutes (Test)</option>
                            <option value="300" {{ $u->getHiddenFilesSessionLifetime() == 300 ? 'selected' : '' }}>5 Minutes</option>
                            <option value="900" {{ $u->getHiddenFilesSessionLifetime() == 900 ? 'selected' : '' }}>15 Minutes</option>
                            <option value="1800" {{ $u->getHiddenFilesSessionLifetime() == 1800 ? 'selected' : '' }}>30 Minutes (Recommended)</option>
                            <option value="3600" {{ $u->getHiddenFilesSessionLifetime() == 3600 ? 'selected' : '' }}>1 Hour</option>
                            <option value="7200" {{ $u->getHiddenFilesSessionLifetime() == 7200 ? 'selected' : '' }}>2 Hours</option>
                            <option value="14400" {{ $u->getHiddenFilesSessionLifetime() == 14400 ? 'selected' : '' }}>4 Hours</option>
                            <option value="28800" {{ $u->getHiddenFilesSessionLifetime() == 28800 ? 'selected' : '' }}>8 Hours</option>
                            <option value="86400" {{ $u->getHiddenFilesSessionLifetime() == 86400 ? 'selected' : '' }}>24 Hours (1 Day)</option>
                        </select>
                        <span class="ff-hint">Session duration for Hidden Files before auto-locking.</span>
                    </div>

                    <div class="ff-field">
                        <label class="ff-label" for="hidden_links_session_lifetime">🔗 Hidden Links Timeout</label>
                        <select name="hidden_links_session_lifetime" id="hidden_links_session_lifetime" class="ff-select">
                            <option value="0" {{ $u->getHiddenLinksSessionLifetime() === 0 ? 'selected' : '' }}>⚡ Immediate (Ask Always)</option>
                            <option value="120" {{ $u->getHiddenLinksSessionLifetime() == 120 ? 'selected' : '' }}>⏱️ 2 Minutes (Test)</option>
                            <option value="300" {{ $u->getHiddenLinksSessionLifetime() == 300 ? 'selected' : '' }}>5 Minutes</option>
                            <option value="900" {{ $u->getHiddenLinksSessionLifetime() == 900 ? 'selected' : '' }}>15 Minutes</option>
                            <option value="1800" {{ $u->getHiddenLinksSessionLifetime() == 1800 ? 'selected' : '' }}>30 Minutes (Recommended)</option>
                            <option value="3600" {{ $u->getHiddenLinksSessionLifetime() == 3600 ? 'selected' : '' }}>1 Hour</option>
                            <option value="7200" {{ $u->getHiddenLinksSessionLifetime() == 7200 ? 'selected' : '' }}>2 Hours</option>
                            <option value="14400" {{ $u->getHiddenLinksSessionLifetime() == 14400 ? 'selected' : '' }}>4 Hours</option>
                            <option value="28800" {{ $u->getHiddenLinksSessionLifetime() == 28800 ? 'selected' : '' }}>8 Hours</option>
                            <option value="86400" {{ $u->getHiddenLinksSessionLifetime() == 86400 ? 'selected' : '' }}>24 Hours (1 Day)</option>
                        </select>
                        <span class="ff-hint">Session duration for Hidden Links before auto-locking.</span>
                    </div>

                    <div class="ff-field">
                        <label class="ff-label" for="hidden_passwords_session_lifetime">🔑 Password Vault Timeout</label>
                        <select name="hidden_passwords_session_lifetime" id="hidden_passwords_session_lifetime" class="ff-select">
                            <option value="0" {{ $u->getHiddenPasswordsSessionLifetime() === 0 ? 'selected' : '' }}>⚡ Immediate (Ask Always)</option>
                            <option value="120" {{ $u->getHiddenPasswordsSessionLifetime() == 120 ? 'selected' : '' }}>⏱️ 2 Minutes (Test)</option>
                            <option value="300" {{ $u->getHiddenPasswordsSessionLifetime() == 300 ? 'selected' : '' }}>5 Minutes</option>
                            <option value="900" {{ $u->getHiddenPasswordsSessionLifetime() == 900 ? 'selected' : '' }}>15 Minutes</option>
                            <option value="1800" {{ $u->getHiddenPasswordsSessionLifetime() == 1800 ? 'selected' : '' }}>30 Minutes (Recommended)</option>
                            <option value="3600" {{ $u->getHiddenPasswordsSessionLifetime() == 3600 ? 'selected' : '' }}>1 Hour</option>
                            <option value="7200" {{ $u->getHiddenPasswordsSessionLifetime() == 7200 ? 'selected' : '' }}>2 Hours</option>
                            <option value="14400" {{ $u->getHiddenPasswordsSessionLifetime() == 14400 ? 'selected' : '' }}>4 Hours</option>
                            <option value="28800" {{ $u->getHiddenPasswordsSessionLifetime() == 28800 ? 'selected' : '' }}>8 Hours</option>
                            <option value="86400" {{ $u->getHiddenPasswordsSessionLifetime() == 86400 ? 'selected' : '' }}>24 Hours (1 Day)</option>
                        </select>
                        <span class="ff-hint">Session duration for Password Vault before auto-locking.</span>
                    </div>

                    <div class="ff-field">
                        <label class="ff-label" for="password_reveal_lifetime">👁️ Password Reveal Secret Lifetime</label>
                        <select name="password_reveal_lifetime" id="password_reveal_lifetime" class="ff-select">
                            <option value="0" {{ $u->getPasswordRevealLifetime() === 0 ? 'selected' : '' }}>⚡ Immediate (Ask Always)</option>
                            <option value="300" {{ $u->getPasswordRevealLifetime() == 300 ? 'selected' : '' }}>5 Minutes</option>
                            <option value="900" {{ $u->getPasswordRevealLifetime() == 900 ? 'selected' : '' }}>15 Minutes (Recommended)</option>
                            <option value="1800" {{ $u->getPasswordRevealLifetime() == 1800 ? 'selected' : '' }}>30 Minutes</option>
                            <option value="3600" {{ $u->getPasswordRevealLifetime() == 3600 ? 'selected' : '' }}>1 Hour</option>
                            <option value="7200" {{ $u->getPasswordRevealLifetime() == 7200 ? 'selected' : '' }}>2 Hours</option>
                        </select>
                        <span class="ff-hint">How long decrypted password credentials stay viewable after verifying authentication.</span>
                    </div>
                </div>

                <div class="ff-divider"></div>

                <div style="margin: 16px 0; padding: 14px 16px; border-radius: 10px; background: var(--ff-surface-subtle, rgba(0,0,0,0.02)); border: 1px solid var(--ff-border, #e2e8f0);">
                    <input type="hidden" name="vault_biometric_enabled_present" value="1">
                    <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; user-select: none;">
                        <input type="checkbox" name="vault_biometric_enabled" value="1" {{ $u->isVaultBiometricEnabled() ? 'checked' : '' }} style="width: 18px; height: 18px; margin-top: 3px; accent-color: var(--ff-accent);">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <span style="font-weight: 600; font-size: 14px; color: var(--ff-text, #0f172a);">
                                    👆 Use Fingerprint / Biometrics for Vault Unlock
                                </span>
                                <span style="font-size: 11px; background: rgba(16, 185, 129, 0.12); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.25); padding: 2px 8px; border-radius: 10px; font-weight: 600;">
                                    Native Mobile Only
                                </span>
                            </div>
                            <p style="font-size: 12.5px; color: var(--ff-text-2, var(--ff-muted, #64748b)); margin-top: 4px; line-height: 1.45; margin-bottom: 0;">
                                When enabled, your enrolled Android fingerprint sensor will unlock Hidden Files, Links, Passwords, Categories, and Secret Reveals instantly on the mobile app without typing passcodes. This feature operates exclusively inside the native application with hardware biometric security.
                            </p>
                        </div>
                    </label>
                </div>

                <div class="ff-split-even">
                    <div class="ff-field">
                        <label class="ff-label" for="vault_password">New Vault Passcode (Optional)</label>
                        <input type="password" name="vault_password" id="vault_password" class="ff-input"
                            placeholder="Leave blank to keep unchanged" autocomplete="new-password">
                        <span class="ff-hint">Dedicated PIN or passcode for unlocking hidden sections.</span>
                    </div>
                    <div class="ff-field">
                        <label class="ff-label" for="vault_password_confirmation">Confirm Vault Passcode</label>
                        <input type="password" name="vault_password_confirmation" id="vault_password_confirmation"
                            class="ff-input" placeholder="Confirm new passcode" autocomplete="new-password">
                    </div>
                </div>

                <div class="ff-divider"></div>
                <div class="ff-form-actions">
                    <button type="submit" class="ff-btn ff-btn-primary">Save Vault Settings</button>
                </div>
            </form>

            {{-- ======================= Two-Factor Authentication (2FA) ======================= --}}
            <div class="ff-form-card" id="two-factor-section">
                <div class="ff-section-head" style="justify-content: space-between; align-items: flex-start;">
                    <div style="display: flex; gap: 12px;">
                        <span class="ff-section-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        <div>
                            <div class="ff-section-title">Two-Factor Authentication (2FA / TOTP)</div>
                            <div class="ff-section-sub">Add an extra layer of security with Google Authenticator &amp; Email OTP</div>
                        </div>
                    </div>

                    @if ($u->hasTwoFactorEnabled())
                        <span class="ff-badge-status is-active" style="background: var(--ff-icon-bg); color: var(--ff-accent); border: 1px solid var(--ff-accent);">
                            <span class="ff-dot-live" style="background: var(--ff-accent);"></span>
                            2FA Active
                        </span>
                    @else
                        <span class="ff-badge-status" style="color: var(--ff-text-soft);">
                            Disabled
                        </span>
                    @endif
                </div>

                <!-- 2FA Alert Notice Box -->
                <div id="alert-2fa-container" style="display: none; margin: 12px 0;"></div>

                @if (!$u->hasTwoFactorEnabled())
                    {{-- 2FA Disabled State --}}
                    <div style="background: var(--ff-bg-2); padding: 18px; border-radius: 12px; border: 1px solid var(--ff-border); margin: 16px 0;">
                        <p style="font-size: 13.5px; color: var(--ff-text); margin: 0 0 14px 0; line-height: 1.6;">
                            Protect your FileFusion workspace and private vaults from unauthorized access. When enabled, signing in or accessing protected areas will require a 6-digit one-time passcode from your <strong>Google Authenticator</strong> app or <strong>Email OTP</strong>.
                        </p>
                        <button type="button" id="btn-open-2fa-setup" class="ff-btn ff-btn-primary">
                            <i data-lucide="shield-check" class="w-4 h-4" style="margin-right: 6px;"></i> Set Up Two-Factor Authentication
                        </button>
                    </div>
                @else
                    {{-- 2FA Enabled State & Preferences --}}
                    <form action="{{ route('panel.2fa.preferences') }}" method="POST" id="form-2fa-preferences" style="margin-top: 16px;">
                        @csrf

                        <div class="ff-field">
                            <label class="ff-label" style="font-weight: 600;">Primary Verification Method</label>
                            <select name="two_factor_type" class="ff-input">
                                <option value="authenticator" {{ ($u->two_factor_type ?? '') === 'authenticator' ? 'selected' : '' }}>
                                    📱 Authenticator App (Google Authenticator, Authy, Microsoft Authenticator)
                                </option>
                                <option value="email" {{ ($u->two_factor_type ?? '') === 'email' ? 'selected' : '' }}>
                                    ✉️ Email Security Code (Instant One-Time Passcode)
                                </option>
                                <option value="both" {{ ($u->two_factor_type ?? '') === 'both' ? 'selected' : '' }}>
                                    🔐 Both (Accept either Authenticator or Email Passcode)
                                </option>
                            </select>
                            <span class="ff-hint">Choose how you want to receive or generate one-time security codes.</span>
                        </div>

                        <div style="margin: 20px 0 16px 0;">
                            <label class="ff-label" style="font-weight: 600; margin-bottom: 8px; display: block;">Enforce Two-Factor Authentication For</label>
                            <div style="display: flex; flex-direction: column; gap: 10px; background: var(--ff-bg-2); padding: 16px; border-radius: 12px; border: 1px solid var(--ff-border);">
                                <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                                    <input type="checkbox" name="enforce_login" value="1" {{ $u->two_factor_enforce_login ? 'checked' : '' }} class="ff-checkbox">
                                    <div>
                                        <div style="font-size: 13.5px; font-weight: 600; color: var(--ff-text);">Account Login</div>
                                        <div style="font-size: 12px; color: var(--ff-text-2);">Prompt for 2FA every time you sign in to FileFusion.</div>
                                    </div>
                                </label>

                                <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                                    <input type="checkbox" name="enforce_vault" value="1" {{ $u->two_factor_enforce_vault ? 'checked' : '' }} class="ff-checkbox">
                                    <div>
                                        <div style="font-size: 13.5px; font-weight: 600; color: var(--ff-text);">Hidden Vault Unlock</div>
                                        <div style="font-size: 12px; color: var(--ff-text-2);">Require 2FA verification when unlocking Hidden Files, Links, and Passwords.</div>
                                    </div>
                                </label>

                                <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                                    <input type="checkbox" name="enforce_password_reveal" value="1" {{ $u->two_factor_enforce_password_reveal ? 'checked' : '' }} class="ff-checkbox">
                                    <div>
                                        <div style="font-size: 13.5px; font-weight: 600; color: var(--ff-text);">Password Vault JIT Reveal</div>
                                        <div style="font-size: 12px; color: var(--ff-text-2);">Require 2FA verification before decrypting raw passwords and secret fields.</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div style="margin: 20px 0 16px 0;">
                            <label class="ff-label" style="font-weight: 600; margin-bottom: 8px; display: block;">Session Timeout &amp; Re-Authentication Lifetimes</label>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; background: var(--ff-bg-2); padding: 16px; border-radius: 12px; border: 1px solid var(--ff-border);">
                                <div>
                                    <label class="ff-label" style="font-size: 12px; font-weight: 700;">🔒 Hidden Vaults Group Lifetime</label>
                                    <select name="vault_session_lifetime" class="ff-select" style="width: 100%; margin-top: 4px;">
                                        <option value="900" {{ $u->getVaultSessionLifetime() == 900 ? 'selected' : '' }}>15 Minutes</option>
                                        <option value="1800" {{ $u->getVaultSessionLifetime() == 1800 ? 'selected' : '' }}>30 Minutes (Recommended)</option>
                                        <option value="3600" {{ $u->getVaultSessionLifetime() == 3600 ? 'selected' : '' }}>1 Hour</option>
                                        <option value="7200" {{ $u->getVaultSessionLifetime() == 7200 ? 'selected' : '' }}>2 Hours</option>
                                        <option value="14400" {{ $u->getVaultSessionLifetime() == 14400 ? 'selected' : '' }}>4 Hours</option>
                                        <option value="28800" {{ $u->getVaultSessionLifetime() == 28800 ? 'selected' : '' }}>8 Hours</option>
                                        <option value="86400" {{ $u->getVaultSessionLifetime() == 86400 ? 'selected' : '' }}>24 Hours (1 Day)</option>
                                    </select>
                                    <span class="ff-hint" style="font-size: 11px; margin-top: 4px; display: block;">Unlocking any hidden vault grants access to all vaults (Files, Links, Passwords) for this duration.</span>
                                </div>

                                <div>
                                    <label class="ff-label" style="font-size: 12px; font-weight: 700;">🔑 Password Reveal Secret Lifetime</label>
                                    <select name="password_reveal_lifetime" class="ff-select" style="width: 100%; margin-top: 4px;">
                                        <option value="0" {{ $u->getPasswordRevealLifetime() === 0 ? 'selected' : '' }}>⚡ Immediate (Prompt Every Time)</option>
                                        <option value="300" {{ $u->getPasswordRevealLifetime() == 300 ? 'selected' : '' }}>5 Minutes</option>
                                        <option value="900" {{ $u->getPasswordRevealLifetime() == 900 ? 'selected' : '' }}>15 Minutes (Recommended)</option>
                                        <option value="1800" {{ $u->getPasswordRevealLifetime() == 1800 ? 'selected' : '' }}>30 Minutes</option>
                                        <option value="3600" {{ $u->getPasswordRevealLifetime() == 3600 ? 'selected' : '' }}>1 Hour</option>
                                        <option value="7200" {{ $u->getPasswordRevealLifetime() == 7200 ? 'selected' : '' }}>2 Hours</option>
                                    </select>
                                    <span class="ff-hint" style="font-size: 11px; margin-top: 4px; display: block;">How long decrypted credentials stay viewable after verifying authentication.</span>
                                </div>
                            </div>
                        </div>

                        <div class="ff-divider"></div>

                        <div class="ff-row-between" style="flex-wrap: wrap; gap: 10px;">
                            <div class="ff-row" style="gap: 10px; flex-wrap: wrap;">
                                <button type="button" id="btn-view-recovery-codes" class="ff-btn ff-btn-sm">
                                    <i data-lucide="key" class="w-3.5 h-3.5" style="margin-right: 4px;"></i> Backup Codes
                                </button>
                                <button type="button" id="btn-open-disable-2fa-modal" class="ff-btn ff-btn-danger ff-btn-sm">
                                    Disable 2FA
                                </button>
                            </div>
                            <button type="submit" class="ff-btn ff-btn-primary">
                                Save 2FA Settings
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            {{-- ============================== Recent Security Activity ============================== --}}
            <div class="ff-form-card">
                <div class="ff-section-head" style="flex-wrap: wrap; gap: 10px;">
                    <span class="ff-section-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            <polyline points="9 12 11 14 15 10"></polyline>
                        </svg>
                    </span>
                    <div style="flex: 1; min-width: 200px;">
                        <div class="ff-section-title">Security &amp; Login Activity</div>
                        <div class="ff-section-sub">Recent sign-ins, IP addresses, devices, and vault access on your account</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <label class="ff-checkbox-label" style="font-size: 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; user-select: none;" title="Automatically poll for new events every 15 seconds">
                            <input type="checkbox" id="user-activity-autorefresh-toggle" class="ff-checkbox" style="width: 14px; height: 14px;">
                            <span>Auto-refresh</span>
                            <span id="user-activity-live-indicator" style="display: none; width: 8px; height: 8px; border-radius: 50%; background: #10b981; box-shadow: 0 0 8px #10b981;"></span>
                        </label>
                        <button type="button" id="btn-refresh-user-activity" class="ff-btn ff-btn-sm" style="height: 32px; display: inline-flex; align-items: center; gap: 5px;">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5" id="user-activity-refresh-icon"></i> <span>Refresh</span>
                        </button>
                    </div>
                </div>

                <div id="user-security-activity-list" style="display: flex; flex-direction: column; gap: 8px; margin-top: 6px; max-height: 380px; overflow-y: auto; scrollbar-width: thin; padding-right: 4px;">
                    <div style="padding: 16px; text-align: center; color: var(--ff-text-soft); font-size: 13px;">
                        Loading recent security events...
                    </div>
                </div>
            </div>

            {{-- ============================== Appearance ============================== --}}
            <div class="ff-form-card">
                <div class="ff-section-head">
                    <span class="ff-section-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="13.5" cy="6.5" r=".5" />
                            <circle cx="17.5" cy="10.5" r=".5" />
                            <circle cx="8.5" cy="7.5" r=".5" />
                            <circle cx="6.5" cy="12.5" r=".5" />
                            <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.9 0 1.5-.7 1.5-1.5 0-.4-.2-.8-.4-1.1-.2-.3-.4-.7-.4-1.1 0-.8.7-1.5 1.5-1.5H16c3.3 0 6-2.7 6-6 0-4.4-4-8-10-8z" />
                        </svg>
                    </span>
                    <div>
                        <div class="ff-section-title">Appearance</div>
                        <div class="ff-section-sub">Theme, accent colour and text size for your workspace</div>
                    </div>
                </div>

                <div class="ff-toggle-row">
                    <div>
                        <div class="ff-toggle-label">Dark mode</div>
                        <div class="ff-toggle-sub">Saved on this device</div>
                    </div>
                    <label class="ff-switch">
                        <input type="checkbox" id="darkModeToggle">
                    </label>
                </div>

                <div class="ff-field">
                    <label class="ff-label">Accent Colour</label>
                    <div class="ff-row" style="gap:10px; flex-wrap:wrap;">
                        @foreach (['terracotta' => ['#d97757', '#b8542e'], 'emerald' => ['#2f9e6e', '#1d7350'], 'teal' => ['#1e9ca8', '#146e77'], 'indigo' => ['#5b6fcf', '#3e4fa3']] as $key => $pair)
                            <button type="button" class="ff-swatch" data-accent="{{ $key }}"
                                aria-label="{{ ucfirst($key) }} accent"
                                style="background:linear-gradient(135deg, {{ $pair[0] }}, {{ $pair[1] }});"></button>
                        @endforeach
                    </div>
                </div>

                <div class="ff-field">
                    <div class="ff-row-between">
                        <label class="ff-label" for="fontScale">Font Size</label>
                        <span class="ff-accent-text" style="font-size:13px; font-weight:600;"
                            id="fontScaleLabel">100%</span>
                    </div>
                    <input type="range" min="0.85" max="1.3" step="0.05" value="1" id="fontScale"
                        class="ff-range">
                </div>
            </div>

            {{-- ============================== Developer & API Tokens ============================== --}}
            <div class="ff-form-card" id="developer-api-tokens-card">
                <div class="ff-section-head">
                    <span class="ff-section-icon" style="background: {{ $u->canUseApi() ? 'rgba(99, 102, 241, 0.15)' : 'rgba(239, 68, 68, 0.12)' }}; color: {{ $u->canUseApi() ? '#6366f1' : '#ef4444' }};">
                        @if ($u->canUseApi())
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="16 18 22 12 16 6" />
                                <polyline points="8 6 2 12 8 18" />
                            </svg>
                        @else
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        @endif
                    </span>
                    <div>
                        <div class="ff-section-title">Developer &amp; API Tokens</div>
                        <div class="ff-section-sub">Personal Access Tokens for REST APIs, CLI automation, and AI Assistants (Cursor, Claude Desktop, Antigravity MCP)</div>
                    </div>
                </div>

                @if ($u->canUseApi())
                    <div class="ff-row-between" style="margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <div style="font-size: 13.5px; font-weight: 600; color: var(--ff-text);">Personal Access Tokens</div>
                            <div class="ff-hint" style="margin-top: 2px;">Tokens grant programmatic access according to selected ability scopes</div>
                        </div>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            @if ($u->isAdmin())
                                <a href="{{ route('api.tester') }}" target="_blank" class="ff-btn ff-btn-secondary ff-btn-sm" style="display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                                    Live API Playground
                                </a>
                            @endif
                            <button type="button" id="btn-open-create-token" class="ff-btn ff-btn-primary ff-btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                Generate New Token
                            </button>
                        </div>
                    </div>

                    <div id="api-tokens-table-wrap">
                        @if (isset($apiTokens) && $apiTokens->count() > 0)
                            <div style="overflow-x: auto; border: 1px solid var(--ff-border); border-radius: 10px;">
                                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                                    <thead>
                                        <tr style="background: var(--ff-surface-subtle); border-bottom: 1px solid var(--ff-border); color: var(--ff-text-2); font-weight: 600;">
                                            <th style="padding: 10px 14px;">Token Name</th>
                                            <th style="padding: 10px 14px;">Identifier</th>
                                            <th style="padding: 10px 14px;">Scopes</th>
                                            <th style="padding: 10px 14px;">Last Used</th>
                                            <th style="padding: 10px 14px;">Expires</th>
                                            <th style="padding: 10px 14px; text-align: right;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($apiTokens as $token)
                                            <tr style="border-bottom: 1px solid var(--ff-border); color: var(--ff-text);" id="token-row-{{ $token->id }}">
                                                <td style="padding: 12px 14px; font-weight: 600;">
                                                    {{ $token->name }}
                                                </td>
                                                <td style="padding: 12px 14px;">
                                                    <code style="background: var(--ff-input); padding: 2px 6px; border-radius: 4px; font-size: 12px; color: var(--ff-text-2);">{{ $token->token_id }}</code>
                                                </td>
                                                <td style="padding: 12px 14px;">
                                                    <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                                        @if (in_array('*', $token->abilities ?? []))
                                                            <span style="font-size: 10.5px; font-weight: 700; background: rgba(99, 102, 241, 0.15); color: #6366f1; padding: 2px 7px; border-radius: 10px;">Full Access (*)</span>
                                                        @else
                                                            @foreach (array_slice($token->abilities ?? [], 0, 3) as $ab)
                                                                <span style="font-size: 10.5px; font-weight: 600; background: var(--ff-input); color: var(--ff-text-2); padding: 2px 6px; border-radius: 8px; border: 1px solid var(--ff-border);">{{ $ab }}</span>
                                                            @endforeach
                                                            @if (count($token->abilities ?? []) > 3)
                                                                <span style="font-size: 10.5px; color: var(--ff-text-muted);">+{{ count($token->abilities) - 3 }} more</span>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </td>
                                                <td style="padding: 12px 14px; color: var(--ff-text-2); font-size: 12px;">
                                                    {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never' }}
                                                </td>
                                                <td style="padding: 12px 14px; font-size: 12px;">
                                                    @if ($token->isExpired())
                                                        <span style="color: var(--ff-danger); font-weight: 600;">Expired</span>
                                                    @elseif ($token->expires_at)
                                                        <span style="color: var(--ff-text-2);">{{ $token->expires_at->format('M d, Y') }}</span>
                                                    @else
                                                        <span style="color: var(--ff-text-muted);">Never</span>
                                                    @endif
                                                </td>
                                                <td style="padding: 12px 14px; text-align: right;">
                                                    <button type="button" class="ff-btn ff-btn-danger ff-btn-sm btn-revoke-token" data-token-id="{{ $token->id }}" data-token-name="{{ $token->name }}" style="padding: 3px 8px; font-size: 11.5px;">
                                                        Revoke
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div style="text-align: center; padding: 28px 16px; border: 1px dashed var(--ff-border); border-radius: 10px; color: var(--ff-text-muted);">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 6px; opacity: 0.7;">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                <div style="font-size: 13.5px; font-weight: 600; color: var(--ff-text-2);">No API Tokens Generated</div>
                                <div class="ff-hint" style="margin-top: 2px;">Create a personal access token to connect CLI tools, mobile scripts, or AI assistants.</div>
                            </div>
                        @endif
                    </div>

                    {{-- MCP Quick-start hint --}}
                    <div style="margin-top: 14px; padding: 12px 14px; background: var(--ff-surface-subtle); border: 1px solid var(--ff-border); border-radius: 8px; font-size: 12px; color: var(--ff-text-2); display: flex; align-items: flex-start; gap: 10px;">
                        <span style="color: #6366f1; font-size: 14px;">💡</span>
                        <div>
                            <strong style="color: var(--ff-text);">Connecting AI via Model Context Protocol (MCP)?</strong>
                            Generate a token with your desired scopes, then configure your AI editor (Claude Desktop, Cursor, Antigravity) with <code style="background: var(--ff-input); padding: 1px 4px; border-radius: 4px;">FILEFUSION_API_TOKEN</code>.
                        </div>
                    </div>
                @else
                    {{-- Locked state: Admin approval required --}}
                    <div style="padding: 24px 20px; text-align: center; border: 1px dashed var(--ff-border); border-radius: 10px; background: var(--ff-surface-subtle);">
                        <div style="width: 44px; height: 44px; margin: 0 auto 12px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); display: flex; align-items: center; justify-content: center; color: #ef4444;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                        <div style="font-size: 15px; font-weight: 700; color: var(--ff-text); margin-bottom: 6px;">API Access Restricted</div>
                        <p style="font-size: 13px; color: var(--ff-text-2); max-width: 460px; margin: 0 auto 14px; line-height: 1.5;">
                            Personal API token generation and programmatic API access require approval from an administrator for your account.
                        </p>
                        <div style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: var(--ff-text-muted); background: var(--ff-input); padding: 6px 14px; border-radius: 20px; border: 1px solid var(--ff-border);">
                            <span>🛡️ Contact your workspace administrator to request API &amp; Token access</span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ============================== Danger zone ============================== --}}
            <div class="ff-danger-card">
                <div class="ff-section-head">
                    <span class="ff-danger-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                            <line x1="12" y1="9" x2="12" y2="13" />
                            <line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg>
                    </span>
                    <div>
                        <div class="ff-section-title">Danger Zone</div>
                        <div class="ff-section-sub">Irreversible actions — proceed with care</div>
                    </div>
                </div>
                <div class="ff-row-between" style="padding-top:2px;">
                    <div>
                        <div style="font-size:14px; font-weight:600; color:var(--ff-text);">Clean Storage</div>
                        <div class="ff-hint" style="margin-top:2px;">Find and permanently remove orphaned files</div>
                    </div>
                    <a href="{{ route('panel.cleanStoragePage') }}" class="ff-btn ff-btn-danger">Clean Storage</a>
                </div>
            </div>
        </div>

        {{-- ================================= Aside ================================= --}}
        <div class="ff-stack ff-hide-mobile" style="position:sticky; top:0;">
            <div class="ff-form-card">
                <div style="display:flex; flex-direction:column; align-items:center; text-align:center; gap:10px; padding:6px 0 14px;">
                    @if ($u->hasAvatar())
                        <img src="{{ route('panel.user.avatar', $u->id) }}?v={{ time() }}" class="ff-avatar ff-avatar-lg" style="object-fit: cover; border-radius: 50%;" id="aside-avatar-img">
                    @else
                        <span class="ff-avatar ff-avatar-lg" id="aside-avatar-initial">{{ $initial }}</span>
                    @endif
                    <div>
                        <div style="font-family:var(--ff-font-display); font-size:15.5px; font-weight:700; color:var(--ff-text);">
                            {{ $displayName }}
                        </div>
                        <div class="ff-hint">{{ $u->email }}</div>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: center; gap: 6px; flex-wrap: wrap;">
                        <span class="ff-plan-badge">{{ $u->isPro() ? ($u->isSuperAdmin() ? 'Super Admin' : ($u->isAdmin() ? 'Admin' : ($u->isManager() ? 'Manager' : 'Pro Plan'))) : 'Free Plan' }}</span>
                        <span style="display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:700; padding:2px 8px; border-radius:12px; letter-spacing:0.3px; text-transform:uppercase; {{ $u->isSuperAdmin() ? 'background:rgba(99,102,241,0.16); color:#818cf8; border:1px solid rgba(99,102,241,0.35);' : ($u->isAdmin() ? 'background:rgba(16,185,129,0.16); color:#10b981; border:1px solid rgba(16,185,129,0.35);' : ($u->isManager() ? 'background:rgba(245,158,11,0.16); color:#f59e0b; border:1px solid rgba(245,158,11,0.35);' : 'background:var(--ff-input); color:var(--ff-text-2); border:1px solid var(--ff-border);')) }}">
                            @if ($u->isSuperAdmin())
                                🛡️ Super Admin
                            @elseif ($u->isAdmin())
                                ⚡ Admin
                            @elseif ($u->isManager())
                                💼 Manager
                            @elseif ($u->isPro())
                                ⭐ Pro User
                            @else
                                👤 {{ $u->getRoleDisplayName() }}
                            @endif
                        </span>
                    </div>
                </div>

                <div class="ff-divider"></div>

                <div style="padding-top:14px;">
                    <div style="font-size:13px; font-weight:600; color:var(--ff-text-2); margin-bottom:10px;">Storage
                    </div>
                    <div class="ff-progress">
                        <div class="ff-progress-bar {{ $barState }}" id="aside-storage-bar" style="width: {{ min($pct, 100) }}%"></div>
                    </div>
                    <div class="ff-meter-legend">
                        <span id="aside-storage-used">{{ $u->getStorageUsedFormatted() }} used</span>
                        <span>{{ $u->getStorageQuotaFormatted() }} total</span>
                    </div>
                </div>
            </div>

            <div class="ff-form-card">
                <div class="ff-section-title">Need more space?</div>
                <div class="ff-section-sub" style="margin:6px 0 14px;">
                    Free up room by emptying the Trash Can or running the storage cleaner.
                </div>
                <a href="{{ route('panel.trashview', 'files') }}" class="ff-btn ff-btn-primary ff-btn-block">Open Trash
                    Can</a>
            </div>
        </div>
    </div>

    {{-- ==================== MODAL: 2FA Setup Wizard ==================== --}}
    <div id="modal-2fa-setup" class="ff-modal-backdrop" hidden>
        <div class="ff-modal" style="width: 480px; max-width: 95%;">
            <div class="ff-row-between" style="margin-bottom: 18px;">
                <div>
                    <div class="ff-modal-title">🔐 Set Up Two-Factor Authentication</div>
                    <div class="ff-modal-sub">Scan the QR code with Google Authenticator or Authy</div>
                </div>
                <button type="button" class="ff-modal-close btn-close-2fa-modal" aria-label="Close modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div id="setup-2fa-loading" style="text-align: center; padding: 40px 0;">
                <div class="ff-sub">Generating security keys...</div>
            </div>

            <div id="setup-2fa-content" style="display: none;">
                <div style="text-align: center; margin-bottom: 18px;">
                    <div style="background: #ffffff; padding: 12px; border-radius: 12px; border: 1px solid var(--ff-border); display: inline-block; box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
                        <img id="qr-code-image" src="" alt="2FA QR Code" style="width: 170px; height: 170px; display: block;">
                    </div>
                    <p class="ff-hint" style="margin: 10px 0 4px 0;">
                        Scan this QR code with <strong>Google Authenticator</strong>, <strong>Authy</strong>, or your password manager.
                    </p>
                </div>

                <div style="background: var(--ff-bg-2); padding: 12px 14px; border-radius: 10px; border: 1px solid var(--ff-border); display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <div>
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--ff-text-soft);">Manual Setup Key</div>
                        <code id="manual-secret-key" class="ff-mono" style="font-size: 14px; font-weight: 700; color: var(--ff-accent); letter-spacing: 2px;"></code>
                    </div>
                    <button type="button" id="btn-copy-secret" class="ff-btn ff-btn-sm">Copy</button>
                </div>

                <div class="ff-field" style="margin-bottom: 16px;">
                    <label class="ff-label" style="font-size: 12px; font-weight: 600;">Preferred Method</label>
                    <select id="setup-2fa-type" class="ff-input">
                        <option value="authenticator">📱 Authenticator App (Recommended)</option>
                        <option value="email">✉️ Email Security Code (Email OTP)</option>
                        <option value="both">🔐 Both (Accept either Authenticator or Email OTP)</option>
                    </select>
                </div>

                <div class="ff-field" style="margin-bottom: 20px;">
                    <label class="ff-label" style="font-size: 12px; font-weight: 600;">Enter 6-Digit Code from App to Confirm</label>
                    <input type="text" id="setup-verify-code" class="ff-input ff-mono" placeholder="000000" maxlength="6" style="text-align: center; font-size: 22px; font-weight: 700; letter-spacing: 8px;" autocomplete="off">
                    <div id="setup-error-msg" class="ff-hint" style="color: var(--ff-danger); display: none; margin-top: 6px;"></div>
                </div>

                <div class="ff-row" style="gap: 10px; justify-content: flex-end;">
                    <button type="button" class="ff-btn btn-close-2fa-modal">Cancel</button>
                    <button type="button" id="btn-confirm-enable-2fa" class="ff-btn ff-btn-primary">
                        Verify &amp; Activate 2FA
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== MODAL: 2FA Recovery Codes ==================== --}}
    <div id="modal-2fa-recovery-codes" class="ff-modal-backdrop" hidden>
        <div class="ff-modal" style="width: 520px; max-width: 95%;">
            <div class="ff-row-between" style="margin-bottom: 16px;">
                <div>
                    <div class="ff-modal-title">🔑 Emergency Backup Codes</div>
                    <div class="ff-modal-sub" id="recovery-codes-summary">One-time codes for account recovery</div>
                </div>
                <button type="button" class="ff-modal-close btn-close-recovery-modal" aria-label="Close modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <p style="font-size: 13px; color: var(--ff-text-2); margin: 0 0 14px 0; line-height: 1.5;">
                Save these one-time recovery codes in a safe place. You can use each code once to log in if you lose access to your authenticator device. Used codes cannot be reused.
            </p>

            <div id="recovery-codes-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; background: var(--ff-bg-2); padding: 14px; border-radius: 12px; border: 1px solid var(--ff-border); margin-bottom: 18px;">
                <!-- Filled via JS -->
            </div>

            <div class="ff-row-between" style="flex-wrap: wrap; gap: 10px;">
                <div class="ff-row" style="gap: 8px; flex-wrap: wrap;">
                    <button type="button" id="btn-copy-recovery-codes" class="ff-btn ff-btn-sm">📋 Copy Active Codes</button>
                    <button type="button" id="btn-download-recovery-codes" class="ff-btn ff-btn-sm">⬇️ Download .txt</button>
                    <button type="button" id="btn-regenerate-modal-codes" class="ff-btn ff-btn-sm" style="color: var(--ff-danger);">🔄 Generate New</button>
                </div>
                <button type="button" class="ff-btn ff-btn-primary btn-close-recovery-modal">Done</button>
            </div>
        </div>
    </div>

    {{-- ==================== MODAL: Disable 2FA ==================== --}}
    <div id="modal-2fa-disable" class="ff-modal-backdrop" hidden>
        <div class="ff-modal" style="width: 420px; max-width: 95%;">
            <div class="ff-row-between" style="margin-bottom: 16px;">
                <div>
                    <div class="ff-modal-title" style="color: var(--ff-danger);">Disable Two-Factor Authentication</div>
                    <div class="ff-modal-sub">Confirm with your account password</div>
                </div>
                <button type="button" class="ff-modal-close btn-close-disable-modal" aria-label="Close modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <p style="font-size: 13px; color: var(--ff-text-2); margin: 0 0 16px 0; line-height: 1.5;">
                Disabling 2FA reduces your account security. Please enter your account password to confirm.
            </p>

            <div class="ff-field" style="margin-bottom: 20px;">
                <label class="ff-label" for="disable_2fa_password">Account Password</label>
                <input type="password" id="disable_2fa_password" class="ff-input" placeholder="Enter your password">
                <div id="disable-error-msg" class="ff-hint" style="color: var(--ff-danger); display: none; margin-top: 6px;"></div>
            </div>

            <div class="ff-row" style="gap: 10px; justify-content: flex-end;">
                <button type="button" class="ff-btn btn-close-disable-modal">Cancel</button>
                <button type="button" id="btn-confirm-disable-2fa" class="ff-btn ff-btn-danger">
                    Confirm &amp; Disable 2FA
                </button>
            </div>
        </div>
    </div>

    {{-- ==================== MODAL: Avatar Crop & Preview ==================== --}}
    <div id="modal-avatar-cropper" class="ff-modal-backdrop" hidden>
        <div class="ff-modal" style="width: 540px; max-width: 95%;">
            <div class="ff-row-between" style="margin-bottom: 16px;">
                <div>
                    <div class="ff-modal-title" style="display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: var(--ff-accent);"><path d="M6 2v14a2 2 0 0 0 2 2h14"/><path d="M18 22V8a2 2 0 0 0-2-2H2"/></svg>
                        Crop &amp; Preview Profile Photo
                    </div>
                    <div class="ff-modal-sub">Drag to position, scroll/zoom, and frame your avatar</div>
                </div>
                <button type="button" class="ff-modal-close btn-close-cropper-modal" aria-label="Close modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Cropper Workspace Area -->
            <div style="display: flex; flex-direction: column; align-items: center; gap: 14px; margin-bottom: 18px;">
                <div style="position: relative; width: 320px; height: 320px; max-width: 100%; aspect-ratio: 1/1; background: #000; border-radius: 12px; overflow: hidden; box-shadow: inset 0 0 20px rgba(0,0,0,0.6); user-select: none; touch-action: none; cursor: grab;" id="crop-viewport">
                    <canvas id="crop-canvas" width="320" height="320" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: block;"></canvas>
                    
                    <!-- Circular Mask Overlay -->
                    <div style="position: absolute; inset: 0; pointer-events: none; border-radius: 50%; box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.65); border: 2px dashed rgba(255, 255, 255, 0.85);"></div>
                </div>

                <!-- Controls & Live Circular Preview Row -->
                <div style="width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; background: var(--ff-bg-2); padding: 12px 16px; border-radius: 10px; border: 1px solid var(--ff-border);">
                    <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 220px;">
                        <span style="font-size: 12px; font-weight: 600; color: var(--ff-text-soft);">Zoom:</span>
                        <input type="range" id="crop-zoom-slider" min="1" max="4" step="0.02" value="1" style="flex: 1; height: 6px; cursor: pointer;">
                        <button type="button" id="btn-crop-rotate" class="ff-btn ff-btn-sm" title="Rotate 90°" style="padding: 6px 10px;">
                            <i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i>
                        </button>
                        <button type="button" id="btn-crop-reset" class="ff-btn ff-btn-sm" title="Reset Frame" style="padding: 6px 10px; font-size: 11.5px;">
                            Reset
                        </button>
                    </div>

                    <!-- Live preview circle -->
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 11px; font-weight: 600; color: var(--ff-muted);">Preview:</span>
                        <div style="width: 44px; height: 44px; border-radius: 50%; overflow: hidden; border: 2px solid var(--ff-accent); box-shadow: 0 2px 8px rgba(0,0,0,0.15); background: #000;">
                            <canvas id="crop-preview-canvas" width="44" height="44" style="width: 100%; height: 100%; display: block;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Encryption Notice -->
                <div style="width: 100%; font-size: 12px; color: var(--ff-text-soft); display: flex; align-items: center; gap: 6px; justify-content: center;">
                    <i data-lucide="shield-check" class="w-4 h-4" style="color: #10b981;"></i>
                    <span>Photo is encrypted using <strong>AES-256-GCM</strong> envelope encryption on upload.</span>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="ff-row" style="gap: 10px; justify-content: flex-end;">
                <button type="button" class="ff-btn btn-close-cropper-modal">Cancel</button>
                <button type="button" id="btn-save-cropped-avatar" class="ff-btn ff-btn-primary">
                    <i data-lucide="check" class="w-4 h-4" style="margin-right: 4px;"></i>
                    Crop &amp; Upload
                </button>
            </div>
        </div>
    </div>

    {{-- ==================== MODAL: Generate API Token ==================== --}}
    <div id="modal-create-api-token" class="ff-modal-backdrop" hidden>
        <div class="ff-modal" style="width: 580px; max-width: 95%;">
            <div class="ff-row-between" style="margin-bottom: 16px;">
                <div>
                    <div class="ff-modal-title" style="display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: #6366f1;"><polyline points="16 18 22 12 16 6" /><polyline points="8 6 2 12 8 18" /></svg>
                        Generate Personal API Token
                    </div>
                    <div class="ff-modal-sub">Create a token for REST APIs, CLI, or AI Assistants (MCP)</div>
                </div>
                <button type="button" class="ff-modal-close btn-close-token-modal" aria-label="Close modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="form-create-api-token">
                @csrf
                <div class="ff-field" style="margin-bottom: 14px;">
                    <label class="ff-label" for="token_name">Token Name / Purpose <span style="color: var(--ff-danger);">*</span></label>
                    <input type="text" id="token_name" name="name" class="ff-input" placeholder="e.g. Claude Desktop MCP, Backup Script, CLI Tool" required>
                </div>

                <div class="ff-field" style="margin-bottom: 16px;">
                    <label class="ff-label" for="token_expires_in">Expiration</label>
                    <select id="token_expires_in" name="expires_in" class="ff-select">
                        <option value="never" selected>No Expiration (Never)</option>
                        <option value="30">30 Days</option>
                        <option value="90">90 Days</option>
                        <option value="365">1 Year</option>
                        <option value="7">7 Days (Testing)</option>
                    </select>
                </div>

                <div style="margin-bottom: 18px;">
                    <div class="ff-row-between" style="margin-bottom: 8px;">
                        <label class="ff-label" style="margin: 0;">Ability Scopes &amp; Permissions</label>
                        <button type="button" id="btn-toggle-all-scopes" class="ff-hint" style="background: none; border: none; cursor: pointer; color: var(--ff-accent); font-weight: 600;">Select All</button>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 12px; max-height: 240px; overflow-y: auto; padding: 12px; border: 1px solid var(--ff-border); border-radius: 8px; background: var(--ff-surface-subtle);">
                        {{-- Files --}}
                        <div>
                            <div style="font-size: 11.5px; font-weight: 700; color: var(--ff-text-2); text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">📁 File Management</div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 8px;">
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; cursor: pointer;">
                                    <input type="checkbox" name="abilities[]" value="files:read" class="token-ability-chk" checked>
                                    <span><code>files:read</code> (List &amp; Download)</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; cursor: pointer;">
                                    <input type="checkbox" name="abilities[]" value="files:write" class="token-ability-chk" checked>
                                    <span><code>files:write</code> (Upload &amp; Edit)</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; cursor: pointer;">
                                    <input type="checkbox" name="abilities[]" value="files:delete" class="token-ability-chk">
                                    <span><code>files:delete</code> (Trash &amp; Purge)</span>
                                </label>
                            </div>
                        </div>

                        {{-- Links --}}
                        <div>
                            <div style="font-size: 11.5px; font-weight: 700; color: var(--ff-text-2); text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">🔗 Bookmarks &amp; Links</div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 8px;">
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; cursor: pointer;">
                                    <input type="checkbox" name="abilities[]" value="links:read" class="token-ability-chk" checked>
                                    <span><code>links:read</code> (View Bookmarks)</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; cursor: pointer;">
                                    <input type="checkbox" name="abilities[]" value="links:write" class="token-ability-chk" checked>
                                    <span><code>links:write</code> (Add &amp; Edit)</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; cursor: pointer;">
                                    <input type="checkbox" name="abilities[]" value="links:delete" class="token-ability-chk">
                                    <span><code>links:delete</code> (Delete)</span>
                                </label>
                            </div>
                        </div>

                        {{-- Categories --}}
                        <div>
                            <div style="font-size: 11.5px; font-weight: 700; color: var(--ff-text-2); text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">🏷️ Categories &amp; Tags</div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 8px;">
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; cursor: pointer;">
                                    <input type="checkbox" name="abilities[]" value="categories:read" class="token-ability-chk" checked>
                                    <span><code>categories:read</code> (View Categories)</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; cursor: pointer;">
                                    <input type="checkbox" name="abilities[]" value="categories:write" class="token-ability-chk" checked>
                                    <span><code>categories:write</code> (Add &amp; Edit)</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; cursor: pointer;">
                                    <input type="checkbox" name="abilities[]" value="categories:delete" class="token-ability-chk">
                                    <span><code>categories:delete</code> (Delete)</span>
                                </label>
                            </div>
                        </div>

                        {{-- To-Dos --}}
                        <div>
                            <div style="font-size: 11.5px; font-weight: 700; color: var(--ff-text-2); text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">✅ To-Dos &amp; Tasks</div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 8px;">
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; cursor: pointer;">
                                    <input type="checkbox" name="abilities[]" value="todos:read" class="token-ability-chk" checked>
                                    <span><code>todos:read</code> (View Tasks)</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; cursor: pointer;">
                                    <input type="checkbox" name="abilities[]" value="todos:write" class="token-ability-chk" checked>
                                    <span><code>todos:write</code> (Add &amp; Update)</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; cursor: pointer;">
                                    <input type="checkbox" name="abilities[]" value="todos:delete" class="token-ability-chk">
                                    <span><code>todos:delete</code> (Delete)</span>
                                </label>
                            </div>
                        </div>

                        {{-- Passwords --}}
                        <div>
                            <div style="font-size: 11.5px; font-weight: 700; color: var(--ff-text-2); text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">🔑 Password Vault</div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 8px;">
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; cursor: pointer;">
                                    <input type="checkbox" name="abilities[]" value="vault:read" class="token-ability-chk" checked>
                                    <span><code>vault:read</code> (List Masked)</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; cursor: pointer;">
                                    <input type="checkbox" name="abilities[]" value="vault:reveal" class="token-ability-chk">
                                    <span style="color: #f59e0b; font-weight: 600;"><code>vault:reveal</code> (JIT Decrypt)</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; cursor: pointer;">
                                    <input type="checkbox" name="abilities[]" value="vault:write" class="token-ability-chk">
                                    <span><code>vault:write</code> (Store &amp; Edit)</span>
                                </label>
                            </div>
                        </div>

                        @if ($u->isAdmin())
                            {{-- Admin --}}
                            <div>
                                <div style="font-size: 11.5px; font-weight: 700; color: #818cf8; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">🛡️ Admin Operations</div>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 8px;">
                                    <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; cursor: pointer;">
                                        <input type="checkbox" name="abilities[]" value="admin:users" class="token-ability-chk">
                                        <span><code>admin:users</code> (User Manager)</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; cursor: pointer;">
                                        <input type="checkbox" name="abilities[]" value="admin:system" class="token-ability-chk">
                                        <span><code>admin:system</code> (Health &amp; Metrics)</span>
                                    </label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="ff-row" style="gap: 10px; justify-content: flex-end;">
                    <button type="button" class="ff-btn btn-close-token-modal">Cancel</button>
                    <button type="submit" id="btn-submit-create-token" class="ff-btn ff-btn-primary">
                        Generate Token
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== MODAL: One-Time Token Reveal ==================== --}}
    <div id="modal-token-revealed" class="ff-modal-backdrop" hidden>
        <div class="ff-modal" style="width: 600px; max-width: 95%;">
            <div class="ff-row-between" style="margin-bottom: 14px;">
                <div>
                    <div class="ff-modal-title" style="display: flex; align-items: center; gap: 8px; color: #10b981;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        API Token Generated
                    </div>
                    <div class="ff-modal-sub">Make sure to copy your personal token now. It will not be shown again.</div>
                </div>
                <button type="button" class="ff-modal-close btn-close-token-revealed" aria-label="Close modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 8px; padding: 10px 14px; margin-bottom: 14px; font-size: 12.5px; color: var(--ff-text); display: flex; align-items: flex-start; gap: 8px;">
                <span style="color: #f59e0b; font-size: 14px;">⚠️</span>
                <div>
                    <strong>Important:</strong> For security reasons, this secret key is never stored in plaintext and cannot be recovered.
                </div>
            </div>

            <div class="ff-field" style="margin-bottom: 16px;">
                <label class="ff-label">Personal Access Token</label>
                <div style="display: flex; gap: 8px;">
                    <input type="text" id="revealed-token-input" class="ff-input" readonly style="font-family: monospace; font-size: 12.5px; background: var(--ff-input); font-weight: 600;">
                    <button type="button" id="btn-copy-revealed-token" class="ff-btn ff-btn-primary" style="white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;">
                        <span>📋 Copy</span>
                    </button>
                </div>
            </div>

            {{-- MCP Config Snippet --}}
            <div style="border: 1px solid var(--ff-border); border-radius: 8px; padding: 12px 14px; background: var(--ff-surface-subtle); margin-bottom: 16px;">
                <div class="ff-row-between" style="margin-bottom: 6px;">
                    <span style="font-size: 11.5px; font-weight: 700; color: var(--ff-text-2); text-transform: uppercase;">🤖 AI Assistant Config (MCP Snippet)</span>
                    <button type="button" id="btn-copy-mcp-config" class="ff-hint" style="background: none; border: none; cursor: pointer; color: var(--ff-accent); font-weight: 600;">Copy JSON</button>
                </div>
                <pre id="mcp-config-snippet" style="margin: 0; padding: 10px; background: var(--ff-bg); border-radius: 6px; font-size: 11.5px; color: var(--ff-text); overflow-x: auto; font-family: monospace;"></pre>
            </div>

            <div class="ff-row" style="justify-content: flex-end;">
                <button type="button" class="ff-btn ff-btn-primary btn-close-token-revealed">
                    I Have Saved My Token
                </button>
            </div>
        </div>
    </div>
@endsection

@section('push-script')
    <script>
        (function() {
            // ------------------------------------------------------------ dark mode
            var darkToggle = document.getElementById('darkModeToggle');
            var topbarToggle = document.getElementById('ffThemeToggle');

            function paintThemeIcon() {
                var dark = window.ff.theme() === 'dark';
                if (topbarToggle) {
                    topbarToggle.querySelector('.ff-icon-sun').style.display = dark ? '' : 'none';
                    topbarToggle.querySelector('.ff-icon-moon').style.display = dark ? 'none' : '';
                }
            }

            if (darkToggle) {
                darkToggle.checked = window.ff.theme() === 'dark';

                darkToggle.addEventListener('change', function() {
                    window.ff.setTheme(this.checked ? 'dark' : 'light');
                    paintThemeIcon();
                });

                document.addEventListener('ff:theme-change', function() {
                    darkToggle.checked = window.ff.theme() === 'dark';
                });
            }

            // --------------------------------------------------------- accent colour
            function paintSwatches() {
                var current = window.ff.accent();
                document.querySelectorAll('.ff-swatch').forEach(function(sw) {
                    sw.classList.toggle('is-active', sw.dataset.accent === current);
                });
            }

            document.querySelectorAll('.ff-swatch').forEach(function(sw) {
                sw.addEventListener('click', function() {
                    window.ff.setAccent(sw.dataset.accent);
                    paintSwatches();
                });
            });
            paintSwatches();

            // ------------------------------------------------------------ font scale
            var range = document.getElementById('fontScale');
            var label = document.getElementById('fontScaleLabel');
            if (range && label) {
                var stored = parseFloat(localStorage.getItem('ff-font-scale') || '1');
                range.value = stored;
                label.textContent = Math.round(stored * 100) + '%';

                range.addEventListener('input', function() {
                    window.ff.setFontScale(this.value);
                    label.textContent = Math.round(parseFloat(this.value) * 100) + '%';
                });
            }

            // ============================================================ 2FA Handlers
            var setupModal = document.getElementById('modal-2fa-setup');
            var recoveryModal = document.getElementById('modal-2fa-recovery-codes');
            var disableModal = document.getElementById('modal-2fa-disable');
            var activeRecoveryCodes = [];

            // Open Setup Wizard
            var btnOpenSetup = document.getElementById('btn-open-2fa-setup');
            if (btnOpenSetup) {
                btnOpenSetup.addEventListener('click', function() {
                    setupModal.hidden = false;
                    document.getElementById('setup-2fa-loading').style.display = 'block';
                    document.getElementById('setup-2fa-content').style.display = 'none';
                    document.getElementById('setup-error-msg').style.display = 'none';
                    document.getElementById('setup-verify-code').value = '';

                    fetch("{{ route('panel.2fa.setup') }}", {
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.ok) {
                            document.getElementById('manual-secret-key').textContent = data.secret;
                            var qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' + encodeURIComponent(data.qr_uri);
                            document.getElementById('qr-code-image').src = qrUrl;
                            document.getElementById('setup-2fa-loading').style.display = 'none';
                            document.getElementById('setup-2fa-content').style.display = 'block';
                            document.getElementById('setup-verify-code').focus();
                        }
                    })
                    .catch(err => {
                        alert('Error loading 2FA setup details: ' + err.message);
                    });
                });
            }

            // Copy manual secret key
            var btnCopySecret = document.getElementById('btn-copy-secret');
            if (btnCopySecret) {
                btnCopySecret.addEventListener('click', function() {
                    var secret = document.getElementById('manual-secret-key').textContent;
                    if (window.ff && typeof window.ff.copy === 'function') {
                        window.ff.copy(secret, 'Manual setup key copied!');
                    } else if (window.copyToClipboard) {
                        window.copyToClipboard(secret, 'Manual setup key copied!');
                    }
                    btnCopySecret.textContent = 'Copied!';
                    setTimeout(() => btnCopySecret.textContent = 'Copy', 2000);
                });
            }

            // Confirm & Enable 2FA
            var btnConfirmEnable = document.getElementById('btn-confirm-enable-2fa');
            if (btnConfirmEnable) {
                btnConfirmEnable.addEventListener('click', function() {
                    var code = document.getElementById('setup-verify-code').value.trim();
                    var type = document.getElementById('setup-2fa-type').value;
                    var errorMsg = document.getElementById('setup-error-msg');

                    if (!code || code.length < 6) {
                        errorMsg.textContent = 'Please enter the 6-digit code from your Authenticator app.';
                        errorMsg.style.display = 'block';
                        return;
                    }

                    btnConfirmEnable.disabled = true;
                    btnConfirmEnable.textContent = 'Verifying...';
                    errorMsg.style.display = 'none';

                    fetch("{{ route('panel.2fa.enable') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ code: code, type: type })
                    })
                    .then(r => r.json())
                    .then(data => {
                        btnConfirmEnable.disabled = false;
                        btnConfirmEnable.textContent = 'Verify & Activate 2FA';

                        if (data.ok) {
                            setupModal.hidden = true;
                            activeRecoveryCodes = data.recovery_codes || [];
                            renderRecoveryCodes(activeRecoveryCodes);
                            recoveryModal.hidden = false;
                        } else {
                            errorMsg.textContent = data.message || 'Invalid verification code.';
                            errorMsg.style.display = 'block';
                        }
                    })
                    .catch(err => {
                        btnConfirmEnable.disabled = false;
                        btnConfirmEnable.textContent = 'Verify & Activate 2FA';
                        errorMsg.textContent = 'Error: ' + err.message;
                        errorMsg.style.display = 'block';
                    });
                });
            }

            // Render recovery codes in modal with Active vs Used status badges
            function renderRecoveryCodes(data) {
                var grid = document.getElementById('recovery-codes-grid');
                var summary = document.getElementById('recovery-codes-summary');
                grid.innerHTML = '';

                var codesList = Array.isArray(data) ? data : (data.codes || data.recovery_codes || []);
                var activeCount = data.active_count !== undefined ? data.active_count : codesList.filter(c => !c.is_used).length;
                var usedCount = data.used_count !== undefined ? data.used_count : codesList.filter(c => c.is_used).length;
                var total = data.total || codesList.length;

                if (summary) {
                    summary.innerHTML = `<strong style="color: var(--ff-accent);">${activeCount} Active</strong> • <span style="color: var(--ff-text-soft);">${usedCount} Used</span> (${total} Total)`;
                }

                codesList.forEach(function(item) {
                    var codeStr = typeof item === 'string' ? item : item.code;
                    var isUsed = typeof item === 'object' && item.is_used;
                    var usedAt = typeof item === 'object' && item.used_at_formatted ? item.used_at_formatted : '';

                    var card = document.createElement('div');
                    card.style.cssText = 'padding: 10px 12px; border-radius: 8px; border: 1px solid var(--ff-border); display: flex; justify-content: space-between; align-items: center; background: var(--ff-card); font-family: var(--ff-font-mono);';

                    if (isUsed) {
                        card.style.opacity = '0.6';
                        card.innerHTML = `
                            <span style="font-size: 13.5px; font-weight: 700; color: var(--ff-text-soft); text-decoration: line-through;">${codeStr}</span>
                            <span style="font-size: 10.5px; font-weight: 700; color: var(--ff-danger); background: var(--ff-danger-soft); padding: 2px 6px; border-radius: 4px;">
                                Used ${usedAt ? '• ' + usedAt : ''}
                            </span>
                        `;
                    } else {
                        card.innerHTML = `
                            <span style="font-size: 13.5px; font-weight: 700; color: var(--ff-accent);">${codeStr}</span>
                            <span style="font-size: 10.5px; font-weight: 700; color: #10b981; background: rgba(16, 185, 129, 0.12); padding: 2px 6px; border-radius: 4px;">
                                Active
                            </span>
                        `;
                    }
                    grid.appendChild(card);
                });
            }

            // View Recovery Codes (GET)
            var btnViewRecovery = document.getElementById('btn-view-recovery-codes');
            if (btnViewRecovery) {
                btnViewRecovery.addEventListener('click', function() {
                    btnViewRecovery.disabled = true;
                    btnViewRecovery.textContent = 'Loading...';

                    fetch("{{ route('panel.2fa.recoveryCodes.get') }}", {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        btnViewRecovery.disabled = false;
                        btnViewRecovery.innerHTML = '<i data-lucide="key" class="w-3.5 h-3.5" style="margin-right: 4px;"></i> Backup Codes';
                        window.ff.icons();

                        if (data.ok) {
                            activeRecoveryCodes = data.data ? data.data.codes : (data.recovery_codes || []);
                            renderRecoveryCodes(data.data || data);
                            recoveryModal.hidden = false;
                        } else {
                            alert(data.message || 'Failed to load recovery codes.');
                        }
                    })
                    .catch(err => {
                        btnViewRecovery.disabled = false;
                        btnViewRecovery.innerHTML = '<i data-lucide="key" class="w-3.5 h-3.5" style="margin-right: 4px;"></i> Backup Codes';
                        alert('Error loading recovery codes: ' + err.message);
                    });
                });
            }

            // Regenerate All Codes button in modal
            var btnRegenModal = document.getElementById('btn-regenerate-modal-codes');
            if (btnRegenModal) {
                btnRegenModal.addEventListener('click', function() {
                    if (confirm('Generate 8 brand new recovery codes? Any previous active codes will become invalid immediately.')) {
                        btnRegenModal.disabled = true;
                        btnRegenModal.textContent = 'Generating...';

                        fetch("{{ route('panel.2fa.recoveryCodes') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            btnRegenModal.disabled = false;
                            btnRegenModal.textContent = '🔄 Generate New';
                            if (data.ok) {
                                activeRecoveryCodes = data.data ? data.data.codes : (data.recovery_codes || []);
                                renderRecoveryCodes(data.data || data);
                                window.ff.toast('New recovery backup codes generated!', 'success');
                            } else {
                                alert(data.message || 'Failed to generate recovery codes.');
                            }
                        })
                        .catch(err => {
                            btnRegenModal.disabled = false;
                            btnRegenModal.textContent = '🔄 Generate New';
                            alert('Error: ' + err.message);
                        });
                    }
                });
            }

            // Copy active recovery codes
            var btnCopyRecovery = document.getElementById('btn-copy-recovery-codes');
            if (btnCopyRecovery) {
                btnCopyRecovery.addEventListener('click', function() {
                    var codesToCopy = activeRecoveryCodes
                        .filter(c => typeof c === 'string' || !c.is_used)
                        .map(c => typeof c === 'string' ? c : c.code);

                    var text = codesToCopy.join('\n');
                    if (window.ff && typeof window.ff.copy === 'function') {
                        window.ff.copy(text, 'Recovery codes copied!');
                    } else if (window.copyToClipboard) {
                        window.copyToClipboard(text, 'Recovery codes copied!');
                    }
                    btnCopyRecovery.textContent = 'Copied!';
                    setTimeout(() => btnCopyRecovery.textContent = '📋 Copy Active Codes', 2000);
                });
            }

            // Download recovery codes as text file
            var btnDownloadRecovery = document.getElementById('btn-download-recovery-codes');
            if (btnDownloadRecovery) {
                btnDownloadRecovery.addEventListener('click', function() {
                    var lines = activeRecoveryCodes.map(function(c) {
                        var code = typeof c === 'string' ? c : c.code;
                        var isUsed = typeof c === 'object' && c.is_used;
                        return code + (isUsed ? ' [USED]' : ' [ACTIVE]');
                    });

                    var content = "FileFusion 2FA Backup Recovery Codes\nAccount: {{ $u->email }}\nGenerated: " + new Date().toISOString() + "\n\n" + lines.join('\n');
                    var blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
                    var a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = 'filefusion-recovery-codes.txt';
                    a.click();
                });
            }

            // Disable 2FA Modal Open
            var btnOpenDisable = document.getElementById('btn-open-disable-2fa-modal');
            if (btnOpenDisable) {
                btnOpenDisable.addEventListener('click', function() {
                    disableModal.hidden = false;
                    document.getElementById('disable_2fa_password').value = '';
                    document.getElementById('disable-error-msg').style.display = 'none';
                    document.getElementById('disable_2fa_password').focus();
                });
            }

            // Confirm Disable 2FA
            var btnConfirmDisable = document.getElementById('btn-confirm-disable-2fa');
            if (btnConfirmDisable) {
                btnConfirmDisable.addEventListener('click', function() {
                    var pw = document.getElementById('disable_2fa_password').value;
                    var errorMsg = document.getElementById('disable-error-msg');

                    if (!pw) {
                        errorMsg.textContent = 'Password is required.';
                        errorMsg.style.display = 'block';
                        return;
                    }

                    btnConfirmDisable.disabled = true;
                    btnConfirmDisable.textContent = 'Disabling...';

                    fetch("{{ route('panel.2fa.disable') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ password: pw })
                    })
                    .then(r => r.json())
                    .then(data => {
                        btnConfirmDisable.disabled = false;
                        btnConfirmDisable.textContent = 'Confirm & Disable 2FA';

                        if (data.ok) {
                            disableModal.hidden = true;
                            window.location.reload();
                        } else {
                            errorMsg.textContent = data.message || 'Incorrect password.';
                            errorMsg.style.display = 'block';
                        }
                    })
                    .catch(err => {
                        btnConfirmDisable.disabled = false;
                        btnConfirmDisable.textContent = 'Confirm & Disable 2FA';
                        errorMsg.textContent = 'Error: ' + err.message;
                        errorMsg.style.display = 'block';
                    });
                });
            }

            // Modal Close triggers
            document.querySelectorAll('.btn-close-2fa-modal').forEach(function(btn) {
                btn.addEventListener('click', function() { setupModal.hidden = true; });
            });
            document.querySelectorAll('.btn-close-recovery-modal').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    recoveryModal.hidden = true;
                    window.location.reload();
                });
            });
            document.querySelectorAll('.btn-close-disable-modal').forEach(function(btn) {
                btn.addEventListener('click', function() { disableModal.hidden = true; });
            });

            // =========================================================================
            // USER RECENT SECURITY & ACTIVITY LOGS (WITH AUTO-REFRESH & MAX-HEIGHT)
            // =========================================================================
            var userActivityTimer = null;
            var isFetchingActivity = false;

            function escapeHtml(str) {
                if (!str) return '';
                return String(str).replace(/[&<>"']/g, function(m) {
                    return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m];
                });
            }

            function loadUserSecurityActivity(isSilent) {
                var container = document.getElementById('user-security-activity-list');
                if (!container || isFetchingActivity) return;

                isFetchingActivity = true;
                var refreshIcon = document.getElementById('user-activity-refresh-icon');
                if (refreshIcon) refreshIcon.style.animation = 'ff-spin 0.7s linear infinite';

                fetch("{{ route('panel.user.activityLogs') }}", {
                    headers: { 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    isFetchingActivity = false;
                    if (refreshIcon) refreshIcon.style.animation = '';

                    if (!data.ok || !data.logs || data.logs.length === 0) {
                        container.innerHTML = '<div style="padding: 16px; text-align: center; color: var(--ff-text-soft); font-size: 13px;">No recent security activity found.</div>';
                        return;
                    }

                    var html = '';
                    data.logs.forEach(function(log) {
                        var badgeStyle = log.status === 'danger' ? 'color: var(--ff-danger); background: var(--ff-danger-soft); border: 1px solid var(--ff-danger-border);' :
                                        (log.status === 'warning' ? 'color: #f59e0b; background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.25);' :
                                        (log.status === 'success' ? 'color: #10b981; background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.25);' : 'color: var(--ff-text-2); background: var(--ff-card); border: 1px solid var(--ff-border);'));

                        html += `
                            <div class="ff-activity-card" style="padding: 10px 12px; background: var(--ff-bg-2); border: 1px solid var(--ff-border); border-radius: 8px; display: flex; flex-direction: column; gap: 6px; font-size: 12.5px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                                    <span style="font-size: 10.5px; font-weight: 700; font-family: var(--ff-font-mono); padding: 2px 7px; border-radius: 5px; ${badgeStyle}">
                                        ${escapeHtml(log.action)}
                                    </span>
                                    <div style="font-size: 11px; color: var(--ff-text-soft); font-weight: 500; white-space: nowrap;">
                                        ${escapeHtml(log.time_ago)} · <span style="font-size: 10.5px;">${escapeHtml(log.date_formatted)}</span>
                                    </div>
                                </div>
                                <div style="font-weight: 600; color: var(--ff-text); font-size: 12.5px; line-height: 1.45; word-break: break-word;">
                                    ${escapeHtml(log.description)}
                                </div>
                                <div style="font-size: 11px; color: var(--ff-text-soft); display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <span>🌐 ${escapeHtml(log.ip_address || '—')}</span>
                                    <span>&bull;</span>
                                    <span>💻 ${escapeHtml(log.device)} (${escapeHtml(log.os || 'OS')}, ${escapeHtml(log.browser || 'Browser')})</span>
                                </div>
                            </div>
                        `;
                    });

                    container.innerHTML = html;
                })
                .catch(err => {
                    isFetchingActivity = false;
                    if (refreshIcon) refreshIcon.style.animation = '';
                    if (!isSilent) {
                        container.innerHTML = '<div style="padding: 12px; text-align: center; color: var(--ff-danger); font-size: 12px;">Failed to load security logs: ' + err.message + '</div>';
                    }
                });
            }

            var btnRefreshUserActivity = document.getElementById('btn-refresh-user-activity');
            if (btnRefreshUserActivity) {
                btnRefreshUserActivity.addEventListener('click', function() {
                    loadUserSecurityActivity(false);
                });
            }

            var autoRefreshToggle = document.getElementById('user-activity-autorefresh-toggle');
            var liveDot = document.getElementById('user-activity-live-indicator');

            function setActivityAutoRefresh(enabled) {
                if (userActivityTimer) {
                    clearInterval(userActivityTimer);
                    userActivityTimer = null;
                }
                if (enabled) {
                    if (liveDot) liveDot.style.display = 'inline-block';
                    userActivityTimer = setInterval(function() {
                        loadUserSecurityActivity(true);
                    }, 15000);
                } else {
                    if (liveDot) liveDot.style.display = 'none';
                }
            }

            if (autoRefreshToggle) {
                var savedAutoRefresh = localStorage.getItem('ff-user-activity-autorefresh');
                if (savedAutoRefresh === 'true') {
                    autoRefreshToggle.checked = true;
                    setActivityAutoRefresh(true);
                }

                autoRefreshToggle.addEventListener('change', function() {
                    var isChecked = autoRefreshToggle.checked;
                    localStorage.setItem('ff-user-activity-autorefresh', isChecked ? 'true' : 'false');
                    setActivityAutoRefresh(isChecked);
                    if (isChecked) {
                        window.ff.toast('Security activity auto-refresh enabled (15s).', 'info', 2000);
                        loadUserSecurityActivity(true);
                    } else {
                        window.ff.toast('Security activity auto-refresh disabled.', 'info', 2000);
                    }
                });
            }

            // Initial load
            loadUserSecurityActivity(false);

            // Close modal when clicking outside on backdrop
            document.querySelectorAll('.ff-modal-backdrop').forEach(function(backdrop) {
                backdrop.addEventListener('click', function(e) {
                    if (e.target === backdrop) {
                        backdrop.hidden = true;
                        if (backdrop === recoveryModal) {
                            window.location.reload();
                        }
                    }
                });
            });

            // ------------------------------------------------ Avatar Cropper & Preview
            var cropperModal = document.getElementById('modal-avatar-cropper');
            var cropCanvas = document.getElementById('crop-canvas');
            var previewCanvas = document.getElementById('crop-preview-canvas');
            var cropViewport = document.getElementById('crop-viewport');
            var zoomSlider = document.getElementById('crop-zoom-slider');
            var btnRotate = document.getElementById('btn-crop-rotate');
            var btnReset = document.getElementById('btn-crop-reset');
            var btnSaveCrop = document.getElementById('btn-save-cropped-avatar');
            var avatarFileInput = document.getElementById('avatar-file-input');

            var cropCtx = cropCanvas ? cropCanvas.getContext('2d') : null;
            var previewCtx = previewCanvas ? previewCanvas.getContext('2d') : null;

            var cropImg = new Image();
            var cropScale = 1;
            var baseScale = 1;
            var cropRotation = 0;
            var cropPosX = 0;
            var cropPosY = 0;
            var isDragging = false;
            var startMouseX = 0;
            var startMouseY = 0;
            var startPosX = 0;
            var startPosY = 0;

            function resetCropper() {
                if (!cropImg.width || !cropImg.height) return;
                var cw = cropCanvas.width;
                var ch = cropCanvas.height;

                var scaleX = cw / cropImg.width;
                var scaleY = ch / cropImg.height;
                baseScale = Math.max(scaleX, scaleY);
                cropScale = baseScale;
                cropRotation = 0;
                cropPosX = cw / 2;
                cropPosY = ch / 2;

                if (zoomSlider) {
                    zoomSlider.min = (baseScale * 0.8).toFixed(3);
                    zoomSlider.max = (baseScale * 4).toFixed(3);
                    zoomSlider.value = baseScale.toFixed(3);
                    zoomSlider.step = (baseScale * 0.02).toFixed(4);
                }
            }

            function drawCropCanvas() {
                if (!cropCtx || !cropImg.src) return;
                var cw = cropCanvas.width;
                var ch = cropCanvas.height;

                cropCtx.clearRect(0, 0, cw, ch);
                cropCtx.save();

                cropCtx.translate(cropPosX, cropPosY);
                cropCtx.rotate((cropRotation * Math.PI) / 180);
                cropCtx.scale(cropScale, cropScale);

                cropCtx.drawImage(cropImg, -cropImg.width / 2, -cropImg.height / 2);
                cropCtx.restore();

                if (previewCtx) {
                    previewCtx.clearRect(0, 0, 44, 44);
                    previewCtx.drawImage(cropCanvas, 0, 0, cw, ch, 0, 0, 44, 44);
                }
            }

            if (avatarFileInput) {
                avatarFileInput.addEventListener('change', function(e) {
                    if (!this.files || !this.files[0]) return;
                    var file = this.files[0];
                    if (!file.type.match(/^image\//)) {
                        alert('Please select a valid image file (JPG, PNG, WEBP, GIF).');
                        return;
                    }

                    var reader = new FileReader();
                    reader.onload = function(evt) {
                        cropImg.onload = function() {
                            resetCropper();
                            if (cropperModal) cropperModal.hidden = false;
                            drawCropCanvas();
                        };
                        cropImg.src = evt.target.result;
                    };
                    reader.readAsDataURL(file);
                });
            }

            if (cropViewport) {
                cropViewport.addEventListener('mousedown', function(e) {
                    isDragging = true;
                    startMouseX = e.clientX;
                    startMouseY = e.clientY;
                    startPosX = cropPosX;
                    startPosY = cropPosY;
                    cropViewport.style.cursor = 'grabbing';
                });

                window.addEventListener('mousemove', function(e) {
                    if (!isDragging) return;
                    var dx = e.clientX - startMouseX;
                    var dy = e.clientY - startMouseY;
                    cropPosX = startPosX + dx;
                    cropPosY = startPosY + dy;
                    drawCropCanvas();
                });

                window.addEventListener('mouseup', function() {
                    if (isDragging) {
                        isDragging = false;
                        if (cropViewport) cropViewport.style.cursor = 'grab';
                    }
                });

                cropViewport.addEventListener('touchstart', function(e) {
                    if (e.touches.length === 1) {
                        isDragging = true;
                        startMouseX = e.touches[0].clientX;
                        startMouseY = e.touches[0].clientY;
                        startPosX = cropPosX;
                        startPosY = cropPosY;
                    }
                }, { passive: true });

                cropViewport.addEventListener('touchmove', function(e) {
                    if (!isDragging || e.touches.length !== 1) return;
                    var dx = e.touches[0].clientX - startMouseX;
                    var dy = e.touches[0].clientY - startMouseY;
                    cropPosX = startPosX + dx;
                    cropPosY = startPosY + dy;
                    drawCropCanvas();
                }, { passive: true });

                cropViewport.addEventListener('touchend', function() {
                    isDragging = false;
                });

                cropViewport.addEventListener('wheel', function(e) {
                    e.preventDefault();
                    var delta = e.deltaY < 0 ? 1.08 : 0.92;
                    var newScale = cropScale * delta;
                    var minS = baseScale * 0.5;
                    var maxS = baseScale * 5;
                    if (newScale >= minS && newScale <= maxS) {
                        cropScale = newScale;
                        if (zoomSlider) zoomSlider.value = cropScale;
                        drawCropCanvas();
                    }
                }, { passive: false });
            }

            if (zoomSlider) {
                zoomSlider.addEventListener('input', function() {
                    cropScale = parseFloat(this.value);
                    drawCropCanvas();
                });
            }

            if (btnRotate) {
                btnRotate.addEventListener('click', function() {
                    cropRotation = (cropRotation + 90) % 360;
                    drawCropCanvas();
                });
            }

            if (btnReset) {
                btnReset.addEventListener('click', function() {
                    resetCropper();
                    drawCropCanvas();
                });
            }

            document.querySelectorAll('.btn-close-cropper-modal').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    if (cropperModal) cropperModal.hidden = true;
                    if (avatarFileInput) avatarFileInput.value = '';
                });
            });

            if (btnSaveCrop) {
                btnSaveCrop.addEventListener('click', function() {
                    btnSaveCrop.disabled = true;
                    btnSaveCrop.textContent = 'Encrypting & Uploading...';

                    var exportCanvas = document.createElement('canvas');
                    exportCanvas.width = 512;
                    exportCanvas.height = 512;
                    var expCtx = exportCanvas.getContext('2d');

                    var ratio = 512 / cropCanvas.width;
                    expCtx.scale(ratio, ratio);
                    expCtx.drawImage(cropCanvas, 0, 0);

                    exportCanvas.toBlob(function(blob) {
                        if (!blob) {
                            alert('Failed to process image crop.');
                            btnSaveCrop.disabled = false;
                            btnSaveCrop.textContent = 'Crop & Upload';
                            return;
                        }

                        var formData = new FormData();
                        formData.append('avatar', blob, 'avatar.jpg');

                        fetch("{{ route('panel.settings.avatar') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(r => r.json())
                        .then(data => {
                            btnSaveCrop.disabled = false;
                            btnSaveCrop.innerHTML = '<i data-lucide="check" class="w-4 h-4" style="margin-right: 4px;"></i> Crop &amp; Upload';
                            window.ff.icons();

                            if (data.ok) {
                                if (cropperModal) cropperModal.hidden = true;
                                if (avatarFileInput) avatarFileInput.value = '';

                                var previewImg = document.getElementById('avatar-preview-img');
                                if (previewImg) {
                                    previewImg.src = data.avatar_url;
                                }

                                var asideAvatarImg = document.getElementById('aside-avatar-img');
                                var asideAvatarInitial = document.getElementById('aside-avatar-initial');
                                if (asideAvatarImg) {
                                    asideAvatarImg.src = data.avatar_url;
                                } else if (asideAvatarInitial) {
                                    var newAsideImg = document.createElement('img');
                                    newAsideImg.src = data.avatar_url;
                                    newAsideImg.id = 'aside-avatar-img';
                                    newAsideImg.className = 'ff-avatar ff-avatar-lg';
                                    newAsideImg.style.cssText = 'object-fit:cover; border-radius:50%;';
                                    asideAvatarInitial.parentNode.replaceChild(newAsideImg, asideAvatarInitial);
                                }

                                if (data.storage_used_formatted) {
                                    var storageUsedEl = document.getElementById('aside-storage-used');
                                    if (storageUsedEl) storageUsedEl.textContent = data.storage_used_formatted + ' used';
                                    var storageBarEl = document.getElementById('aside-storage-bar');
                                    if (storageBarEl && data.storage_percentage !== undefined) {
                                        storageBarEl.style.width = Math.min(data.storage_percentage, 100) + '%';
                                    }
                                }

                                var topbarAvatars = document.querySelectorAll('.ff-topbar .ff-avatar, .ff-topbar img.ff-avatar');
                                topbarAvatars.forEach(function(el) {
                                    if (el.tagName.toLowerCase() === 'img') {
                                        el.src = data.avatar_url;
                                    } else {
                                        var newImg = document.createElement('img');
                                        newImg.src = data.avatar_url;
                                        newImg.className = 'ff-avatar';
                                        newImg.style.cssText = 'object-fit:cover; border-radius:50%;';
                                        el.parentNode.replaceChild(newImg, el);
                                    }
                                });

                                window.ff.toast(data.message || 'Profile picture encrypted & updated!', 'success');
                            } else {
                                alert(data.message || 'Failed to upload encrypted avatar.');
                            }
                        })
                        .catch(err => {
                            btnSaveCrop.disabled = false;
                            btnSaveCrop.textContent = 'Crop & Upload';
                            alert('Error uploading avatar: ' + err.message);
                        });
                    }, 'image/jpeg', 0.92);
                });
            }

            // Auto-open 2FA setup wizard if navigated with ?setup_2fa=1 or session flag
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('setup_2fa') === '1' || @json(session('setup_2fa_active', false))) {
                var btnOpen2fa = document.getElementById('btn-open-2fa-setup');
                if (btnOpen2fa) {
                    setTimeout(function() {
                        btnOpen2fa.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        btnOpen2fa.click();
                    }, 350);
                }
            }

            // ------------------------------------------------------------ API Tokens & MCP Management
            var modalCreateToken = document.getElementById('modal-create-api-token');
            var modalTokenRevealed = document.getElementById('modal-token-revealed');
            var btnOpenCreateToken = document.getElementById('btn-open-create-token');
            var formCreateToken = document.getElementById('form-create-api-token');
            var btnSubmitCreateToken = document.getElementById('btn-submit-create-token');
            var btnToggleAllScopes = document.getElementById('btn-toggle-all-scopes');
            var revealedTokenInput = document.getElementById('revealed-token-input');
            var mcpConfigSnippet = document.getElementById('mcp-config-snippet');

            if (btnOpenCreateToken && modalCreateToken) {
                btnOpenCreateToken.addEventListener('click', function() {
                    modalCreateToken.hidden = false;
                    var nameInput = document.getElementById('token_name');
                    if (nameInput) setTimeout(function() { nameInput.focus(); }, 100);
                });
            }

            document.querySelectorAll('.btn-close-token-modal').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    if (modalCreateToken) modalCreateToken.hidden = true;
                });
            });

            document.querySelectorAll('.btn-close-token-revealed').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    if (modalTokenRevealed) modalTokenRevealed.hidden = true;
                    window.location.reload();
                });
            });

            if (btnToggleAllScopes) {
                btnToggleAllScopes.addEventListener('click', function() {
                    var checkboxes = document.querySelectorAll('.token-ability-chk');
                    var allChecked = Array.from(checkboxes).every(function(chk) { return chk.checked; });
                    checkboxes.forEach(function(chk) { chk.checked = !allChecked; });
                    btnToggleAllScopes.textContent = allChecked ? 'Select All' : 'Deselect All';
                });
            }

            if (formCreateToken) {
                formCreateToken.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (!btnSubmitCreateToken) return;

                    var name = (document.getElementById('token_name') || {}).value || '';
                    if (!name.trim()) {
                        alert('Please enter a token name.');
                        return;
                    }

                    var selectedAbilities = [];
                    document.querySelectorAll('.token-ability-chk:checked').forEach(function(chk) {
                        selectedAbilities.push(chk.value);
                    });

                    var expiresIn = (document.getElementById('token_expires_in') || {}).value || 'never';

                    btnSubmitCreateToken.disabled = true;
                    btnSubmitCreateToken.textContent = 'Generating...';

                    fetch('{{ route('panel.developer.tokens.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            name: name,
                            abilities: selectedAbilities,
                            expires_in: expiresIn
                        })
                    })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        btnSubmitCreateToken.disabled = false;
                        btnSubmitCreateToken.textContent = 'Generate Token';

                        if (data.success && data.plain_token) {
                            if (modalCreateToken) modalCreateToken.hidden = true;
                            formCreateToken.reset();

                            if (revealedTokenInput) {
                                revealedTokenInput.value = data.plain_token;
                            }

                            if (mcpConfigSnippet) {
                                var appUrl = window.location.origin;
                                var snippet = JSON.stringify({
                                    "mcpServers": {
                                        "filefusion": {
                                            "command": "npx",
                                            "args": ["-y", "filefusion-mcp-server"],
                                            "env": {
                                                "FILEFUSION_URL": appUrl,
                                                "FILEFUSION_API_TOKEN": data.plain_token
                                            }
                                        }
                                    }
                                }, null, 2);
                                mcpConfigSnippet.textContent = snippet;
                            }

                            if (modalTokenRevealed) {
                                modalTokenRevealed.hidden = false;
                            }
                        } else {
                            alert(data.message || 'Failed to generate token.');
                        }
                    })
                    .catch(function(err) {
                        btnSubmitCreateToken.disabled = false;
                        btnSubmitCreateToken.textContent = 'Generate Token';
                        alert('Error generating token: ' + err.message);
                    });
                });
            }

            var btnCopyRevealedToken = document.getElementById('btn-copy-revealed-token');
            if (btnCopyRevealedToken && revealedTokenInput) {
                btnCopyRevealedToken.addEventListener('click', function() {
                    if (window.ff && typeof window.ff.copy === 'function') {
                        window.ff.copy(revealedTokenInput.value, 'Token copied to clipboard!');
                    } else if (window.copyToClipboard) {
                        window.copyToClipboard(revealedTokenInput.value, 'Token copied to clipboard!');
                    }
                });
            }

            var btnCopyMcpConfig = document.getElementById('btn-copy-mcp-config');
            if (btnCopyMcpConfig && mcpConfigSnippet) {
                btnCopyMcpConfig.addEventListener('click', function() {
                    if (window.ff && typeof window.ff.copy === 'function') {
                        window.ff.copy(mcpConfigSnippet.textContent, 'MCP JSON config copied to clipboard!');
                    } else if (window.copyToClipboard) {
                        window.copyToClipboard(mcpConfigSnippet.textContent, 'MCP JSON config copied to clipboard!');
                    }
                });
            }

            // Revoke Token Handler
            document.querySelectorAll('.btn-revoke-token').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var tokenId = this.getAttribute('data-token-id');
                    var tokenName = this.getAttribute('data-token-name');
                    if (!confirm('Are you sure you want to revoke the token "' + tokenName + '"? Any applications or MCP servers using this token will be disconnected immediately.')) {
                        return;
                    }

                    var row = document.getElementById('token-row-' + tokenId);
                    btn.disabled = true;
                    btn.textContent = 'Revoking...';

                    fetch('{{ url('/panel/developer/tokens') }}/' + tokenId, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data.success) {
                            if (row) row.remove();
                            window.ff.toast('Token revoked successfully.', 'success');
                        } else {
                            btn.disabled = false;
                            btn.textContent = 'Revoke';
                            alert(data.message || 'Failed to revoke token.');
                        }
                    })
                    .catch(function(err) {
                        btn.disabled = false;
                        btn.textContent = 'Revoke';
                        alert('Error revoking token: ' + err.message);
                    });
                });
            });

            window.ff.icons();
        })();
    </script>
@endsection
