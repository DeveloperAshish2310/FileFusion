@extends('layout.backend')

@push('title', 'Email System & Template CMS')

@section('content')
<div class="ff-page">
    <!-- Page Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 class="ff-h1" style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                Email System & Template CMS
            </h1>
            <p class="ff-sub" style="margin: 0;">Configure dynamic SMTP server credentials and customize notification email templates across all 6 event types.</p>
        </div>
    </div>

    <!-- Alert Notices -->
    @if(session('success'))
        <div class="ff-alert is-success" style="margin-bottom: 20px;">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="ff-alert is-danger" style="margin-bottom: 20px; align-items: flex-start; padding: 16px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-top: 2px; shrink: 0;">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <div style="margin-left: 10px; width: 100%;">
                <div style="font-weight: 800; font-size: 14px; color: #ef4444; margin-bottom: 6px;">SMTP Email Dispatch Failed</div>
                <div style="white-space: pre-wrap; font-family: inherit; font-size: 13px; line-height: 1.5; color: var(--ff-text);">{{ session('error') }}</div>
            </div>
        </div>
    @endif

    <!-- Dynamic AJAX Alert Container -->
    <div id="ajax-test-alert-container" style="margin-bottom: 20px;"></div>

    <!-- Main Navigation Tabs -->

    <div style="display: flex; gap: 10px; margin-bottom: 24px; border-bottom: 1px solid var(--ff-border); padding-bottom: 10px; overflow-x: auto;">
        <button type="button" class="email-tab-btn active" data-target="tab-smtp" style="padding: 10px 20px; font-size: 14px; font-weight: 700; border-radius: 8px; border: 1px solid transparent; cursor: pointer; white-space: nowrap; transition: all 0.2s;">
            SMTP Credentials & Server
        </button>
        <button type="button" class="email-tab-btn" data-target="tab-templates" style="padding: 10px 20px; font-size: 14px; font-weight: 700; border-radius: 8px; border: 1px solid transparent; cursor: pointer; white-space: nowrap; transition: all 0.2s;">
            Notification Email Templates
        </button>
    </div>

    <!-- Main Editor Form -->
    <form action="{{ route('panel.admin.emailSettings.update') }}" method="POST">
        @csrf

        <!-- SMTP SETTINGS TAB -->
        <div id="tab-smtp" class="email-tab-panel">
            <div class="ff-card" style="padding: 24px; margin-bottom: 24px;">
                <h3 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 700; color: var(--ff-text); border-bottom: 1px solid var(--ff-border); padding-bottom: 12px;">
                    SMTP Transport & Mailer Configuration
                </h3>

                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Mail Driver / Transport</label>
                        <select name="mail_mailer" class="ff-select" style="width: 100%; height: 42px;">
                            <option value="smtp" {{ ($settings['mail_mailer'] ?? '') === 'smtp' ? 'selected' : '' }}>SMTP Server (Recommended)</option>
                            <option value="log" {{ ($settings['mail_mailer'] ?? '') === 'log' ? 'selected' : '' }}>Log Driver (Testing / Local Storage Logs)</option>
                            <option value="sendmail" {{ ($settings['mail_mailer'] ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">SMTP Hostname</label>
                        <input type="text" name="mail_host" value="{{ $settings['mail_host'] ?? 'smtp.gmail.com' }}" placeholder="e.g. smtp.gmail.com or smtp.mailtrap.io" class="ff-input" style="width: 100%; height: 42px;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">SMTP Port</label>
                        <input type="text" name="mail_port" value="{{ $settings['mail_port'] ?? '587' }}" placeholder="587 or 465" class="ff-input" style="width: 100%; height: 42px;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">Encryption Protocol</label>
                        <select name="mail_encryption" class="ff-select" style="width: 100%; height: 42px;">
                            <option value="tls" {{ ($settings['mail_encryption'] ?? '') === 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                            <option value="ssl" {{ ($settings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                            <option value="none" {{ ($settings['mail_encryption'] ?? '') === 'none' ? 'selected' : '' }}>None (Unencrypted)</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">SMTP Username</label>
                        <input type="text" name="mail_username" value="{{ $settings['mail_username'] ?? '' }}" placeholder="your-email@gmail.com" class="ff-input" style="width: 100%; height: 42px;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">SMTP Password / App Password</label>
                        <input type="password" name="mail_password" value="{{ $settings['mail_password'] ?? '' }}" placeholder="••••••••••••••••" class="ff-input" style="width: 100%; height: 42px;">
                    </div>
                </div>

                <h4 style="margin: 20px 0 16px 0; font-size: 15px; font-weight: 700; color: var(--ff-text); border-top: 1px solid var(--ff-border); padding-top: 16px;">
                    📧 Sender Identity
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">From Email Address</label>
                        <input type="email" name="mail_from_address" value="{{ $settings['mail_from_address'] ?? 'noreply@filefusion.io' }}" class="ff-input" style="width: 100%; height: 42px;" required>
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px; margin-bottom: 6px;">From Sender Name</label>
                        <input type="text" name="mail_from_name" value="{{ $settings['mail_from_name'] ?? 'FileFusion Workspace' }}" class="ff-input" style="width: 100%; height: 42px;" required>
                    </div>
                </div>
            </div>

            <!-- SMTP Tester Card -->
            <div class="ff-card" style="padding: 24px; margin-bottom: 24px; background: var(--ff-bg2);">
                <h4 style="margin: 0 0 10px 0; font-size: 15px; font-weight: 700; color: var(--ff-text);">
                    🧪 Live SMTP Connection Test
                </h4>
                <p style="margin: 0 0 16px 0; font-size: 12.5px; color: var(--ff-muted);">Send a test email using the currently configured SMTP server to verify credentials and connectivity.</p>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <input type="email" id="test_email_target" name="test_email_target" value="{{ Auth::user()->email }}" class="ff-input" style="width: 300px; height: 40px;" placeholder="Enter target test email...">
                    <button type="button" id="btn-trigger-smtp-test" class="ff-btn" style="height: 40px; background: #6366f1; color: #fff; font-weight: 700;">
                        Send Test Email
                    </button>
                </div>
            </div>
        </div>

        <!-- TEMPLATES TAB SECTION -->
        <div id="tab-templates" class="email-tab-panel" style="display: none;">
            
            <!-- Template Event Sub-Tabs -->
            <div style="display: flex; gap: 6px; overflow-x: auto; padding-bottom: 10px; margin-bottom: 20px; border-bottom: 1px solid var(--ff-border);">
                <button type="button" class="tpl-sub-btn active" data-target="tpl-shared" style="padding: 8px 14px; font-size: 12.5px; font-weight: 700; border-radius: 6px; border: 1px solid transparent; cursor: pointer; white-space: nowrap;">
                    1. 📁 File Shared
                </button>
                <button type="button" class="tpl-sub-btn" data-target="tpl-deleted" style="padding: 8px 14px; font-size: 12.5px; font-weight: 700; border-radius: 6px; border: 1px solid transparent; cursor: pointer; white-space: nowrap;">
                    2. ⚠️ File Deleted Report
                </button>
                <button type="button" class="tpl-sub-btn" data-target="tpl-account" style="padding: 8px 14px; font-size: 12.5px; font-weight: 700; border-radius: 6px; border: 1px solid transparent; cursor: pointer; white-space: nowrap;">
                    3. 🎉 Account Created
                </button>
                <button type="button" class="tpl-sub-btn" data-target="tpl-totp" style="padding: 8px 14px; font-size: 12.5px; font-weight: 700; border-radius: 6px; border: 1px solid transparent; cursor: pointer; white-space: nowrap;">
                    4. 🔐 TOTP 2FA Code
                </button>
                <button type="button" class="tpl-sub-btn" data-target="tpl-forgot" style="padding: 8px 14px; font-size: 12.5px; font-weight: 700; border-radius: 6px; border: 1px solid transparent; cursor: pointer; white-space: nowrap;">
                    5. 🔑 Forgot Password
                </button>
                <button type="button" class="tpl-sub-btn" data-target="tpl-alerts" style="padding: 8px 14px; font-size: 12.5px; font-weight: 700; border-radius: 6px; border: 1px solid transparent; cursor: pointer; white-space: nowrap;">
                    6. ⚡ Storage & Trash Alerts
                </button>
            </div>

            <!-- TPL 1: FILE SHARED -->
            <div id="tpl-shared" class="tpl-panel">
                <div class="ff-card" style="padding: 24px; margin-bottom: 24px;">
                    <h3 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 700; color: var(--ff-text);">1. 📁 File Shared Notification Email Template</h3>
                    <div style="background: rgba(99,102,241,0.08); border-left: 3px solid #6366f1; padding: 12px 16px; margin-bottom: 18px; border-radius: 6px; font-size: 12.5px; color: var(--ff-text);">
                        <strong>Available Variables:</strong> <code>{recipient_name}</code>, <code>{owner_name}</code>, <code>{file_name}</code>, <code>{download_link}</code>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); margin-bottom: 6px;">Email Subject Line</label>
                        <input type="text" name="email_tpl_file_shared_subject" value="{{ $settings['email_tpl_file_shared_subject'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); margin-bottom: 6px;">HTML Email Body</label>
                        <textarea name="email_tpl_file_shared_body" rows="6" class="ff-input" style="width: 100%; line-height: 1.5; font-family: monospace; font-size: 13px;">{{ $settings['email_tpl_file_shared_body'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <!-- TPL 2: FILE DELETED -->
            <div id="tpl-deleted" class="tpl-panel" style="display: none;">
                <div class="ff-card" style="padding: 24px; margin-bottom: 24px;">
                    <h3 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 700; color: var(--ff-text);">2. ⚠️ File Removal Report (Not For Self-Delete)</h3>
                    <div style="background: rgba(99,102,241,0.08); border-left: 3px solid #6366f1; padding: 12px 16px; margin-bottom: 18px; border-radius: 6px; font-size: 12.5px; color: var(--ff-text);">
                        <strong>Available Variables:</strong> <code>{user_name}</code>, <code>{file_name}</code>, <code>{deleted_by}</code>, <code>{reason}</code>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); margin-bottom: 6px;">Email Subject Line</label>
                        <input type="text" name="email_tpl_file_deleted_subject" value="{{ $settings['email_tpl_file_deleted_subject'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); margin-bottom: 6px;">HTML Email Body</label>
                        <textarea name="email_tpl_file_deleted_body" rows="6" class="ff-input" style="width: 100%; line-height: 1.5; font-family: monospace; font-size: 13px;">{{ $settings['email_tpl_file_deleted_body'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <!-- TPL 3: ACCOUNT CREATED -->
            <div id="tpl-account" class="tpl-panel" style="display: none;">
                <div class="ff-card" style="padding: 24px; margin-bottom: 24px;">
                    <h3 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 700; color: var(--ff-text);">3. 🎉 Account Created Successfully Email</h3>
                    <div style="background: rgba(99,102,241,0.08); border-left: 3px solid #6366f1; padding: 12px 16px; margin-bottom: 18px; border-radius: 6px; font-size: 12.5px; color: var(--ff-text);">
                        <strong>Available Variables:</strong> <code>{user_name}</code>, <code>{user_email}</code>, <code>{login_link}</code>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); margin-bottom: 6px;">Email Subject Line</label>
                        <input type="text" name="email_tpl_account_created_subject" value="{{ $settings['email_tpl_account_created_subject'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); margin-bottom: 6px;">HTML Email Body</label>
                        <textarea name="email_tpl_account_created_body" rows="6" class="ff-input" style="width: 100%; line-height: 1.5; font-family: monospace; font-size: 13px;">{{ $settings['email_tpl_account_created_body'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <!-- TPL 4: TOTP 2FA -->
            <div id="tpl-totp" class="tpl-panel" style="display: none;">
                <div class="ff-card" style="padding: 24px; margin-bottom: 24px;">
                    <h3 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 700; color: var(--ff-text);">4. 🔐 TOTP 2FA Security Code Email</h3>
                    <div style="background: rgba(99,102,241,0.08); border-left: 3px solid #6366f1; padding: 12px 16px; margin-bottom: 18px; border-radius: 6px; font-size: 12.5px; color: var(--ff-text);">
                        <strong>Available Variables:</strong> <code>{user_name}</code>, <code>{totp_code}</code>, <code>{expires_minutes}</code>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); margin-bottom: 6px;">Email Subject Line</label>
                        <input type="text" name="email_tpl_totp_subject" value="{{ $settings['email_tpl_totp_subject'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); margin-bottom: 6px;">HTML Email Body</label>
                        <textarea name="email_tpl_totp_body" rows="6" class="ff-input" style="width: 100%; line-height: 1.5; font-family: monospace; font-size: 13px;">{{ $settings['email_tpl_totp_body'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <!-- TPL 5: FORGOT PASSWORD -->
            <div id="tpl-forgot" class="tpl-panel" style="display: none;">
                <div class="ff-card" style="padding: 24px; margin-bottom: 24px;">
                    <h3 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 700; color: var(--ff-text);">5. 🔑 Forgot Password Reset Link Email</h3>
                    <div style="background: rgba(99,102,241,0.08); border-left: 3px solid #6366f1; padding: 12px 16px; margin-bottom: 18px; border-radius: 6px; font-size: 12.5px; color: var(--ff-text);">
                        <strong>Available Variables:</strong> <code>{user_name}</code>, <code>{reset_link}</code>, <code>{expires_hours}</code>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); margin-bottom: 6px;">Email Subject Line</label>
                        <input type="text" name="email_tpl_forgot_pass_subject" value="{{ $settings['email_tpl_forgot_pass_subject'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); margin-bottom: 6px;">HTML Email Body</label>
                        <textarea name="email_tpl_forgot_pass_body" rows="6" class="ff-input" style="width: 100%; line-height: 1.5; font-family: monospace; font-size: 13px;">{{ $settings['email_tpl_forgot_pass_body'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <!-- TPL 6: ALERTS -->
            <div id="tpl-alerts" class="tpl-panel" style="display: none;">
                <div class="ff-card" style="padding: 24px; margin-bottom: 24px;">
                    <h3 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 700; color: var(--ff-text);">6.A ⚠️ Storage Quota Alert Email (75%, 90%, 100%)</h3>
                    <div style="background: rgba(99,102,241,0.08); border-left: 3px solid #6366f1; padding: 12px 16px; margin-bottom: 18px; border-radius: 6px; font-size: 12.5px; color: var(--ff-text);">
                        <strong>Available Variables:</strong> <code>{user_name}</code>, <code>{used_storage}</code>, <code>{quota_storage}</code>, <code>{percentage}</code>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); margin-bottom: 6px;">Email Subject Line</label>
                        <input type="text" name="email_tpl_storage_alert_subject" value="{{ $settings['email_tpl_storage_alert_subject'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div style="margin-bottom: 24px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); margin-bottom: 6px;">HTML Email Body</label>
                        <textarea name="email_tpl_storage_alert_body" rows="5" class="ff-input" style="width: 100%; line-height: 1.5; font-family: monospace; font-size: 13px;">{{ $settings['email_tpl_storage_alert_body'] ?? '' }}</textarea>
                    </div>

                    <h3 style="margin: 20px 0 16px 0; font-size: 16px; font-weight: 700; color: var(--ff-text); border-top: 1px solid var(--ff-border); padding-top: 16px;">6.B ⏰ Trash Retention Expiration Warning Email</h3>
                    <div style="background: rgba(99,102,241,0.08); border-left: 3px solid #6366f1; padding: 12px 16px; margin-bottom: 18px; border-radius: 6px; font-size: 12.5px; color: var(--ff-text);">
                        <strong>Available Variables:</strong> <code>{user_name}</code>, <code>{file_count}</code>, <code>{deletion_date}</code>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); margin-bottom: 6px;">Email Subject Line</label>
                        <input type="text" name="email_tpl_trash_expiring_subject" value="{{ $settings['email_tpl_trash_expiring_subject'] ?? '' }}" class="ff-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); margin-bottom: 6px;">HTML Email Body</label>
                        <textarea name="email_tpl_trash_expiring_body" rows="5" class="ff-input" style="width: 100%; line-height: 1.5; font-family: monospace; font-size: 13px;">{{ $settings['email_tpl_trash_expiring_body'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

        </div>

        <!-- Sticky Floating Save Bar -->
        <div style="position: sticky; bottom: 20px; z-index: 99; background: var(--ff-surface); border: 1px solid var(--ff-border); border-radius: 12px; padding: 14px 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: space-between; margin-top: 24px;">
            <span style="font-size: 13px; color: var(--ff-muted);">
                <span style="color: #10b981; font-weight: 700;">● Live Mail System</span> Email templates and SMTP settings update dynamically.
            </span>
            <button type="submit" class="ff-btn is-primary" style="padding: 10px 24px; font-size: 14px; font-weight: 700; background: #6366f1; border-color: #6366f1;">
                Save Email & SMTP Settings
            </button>
        </div>
    </form>

    <!-- Hidden Form for SMTP Test Trigger -->
    <form id="smtp-test-form" action="{{ route('panel.admin.emailSettings.test') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="test_email" id="hidden_test_email">
    </form>
</div>
@endsection

@section('push-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Main Tab Switching
        const emailTabBtns = document.querySelectorAll('.email-tab-btn');
        const emailTabPanels = document.querySelectorAll('.email-tab-panel');

        function updateMainTabs() {
            emailTabBtns.forEach(btn => {
                const isActive = btn.classList.contains('active');
                if (isActive) {
                    btn.style.background = '#6366f1';
                    btn.style.color = '#ffffff';
                    btn.style.borderColor = '#6366f1';
                } else {
                    btn.style.background = 'var(--ff-bg2)';
                    btn.style.color = 'var(--ff-text)';
                    btn.style.borderColor = 'var(--ff-border)';
                }
            });
        }
        updateMainTabs();

        emailTabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                emailTabBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                updateMainTabs();

                emailTabPanels.forEach(panel => {
                    panel.style.display = (panel.id === targetId) ? 'block' : 'none';
                });
            });
        });

        // Template Sub-Tab Switching
        const tplSubBtns = document.querySelectorAll('.tpl-sub-btn');
        const tplPanels = document.querySelectorAll('.tpl-panel');

        function updateSubTabs() {
            tplSubBtns.forEach(btn => {
                const isActive = btn.classList.contains('active');
                if (isActive) {
                    btn.style.background = '#6366f1';
                    btn.style.color = '#ffffff';
                } else {
                    btn.style.background = 'var(--ff-bg2)';
                    btn.style.color = 'var(--ff-text)';
                }
            });
        }
        updateSubTabs();

        tplSubBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                tplSubBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                updateSubTabs();

                tplPanels.forEach(panel => {
                    panel.style.display = (panel.id === targetId) ? 'block' : 'none';
                });
            });
        });

        // SMTP Connection Test Trigger with live AJAX fetch payload
        const btnTest = document.getElementById('btn-trigger-smtp-test');
        const testInput = document.getElementById('test_email_target');
        const alertContainer = document.getElementById('ajax-test-alert-container');

        if (btnTest && testInput) {
            btnTest.addEventListener('click', function(e) {
                e.preventDefault();
                const email = testInput.value.trim();
                if (!email) {
                    alert('Please enter a target email address for the test.');
                    return;
                }

                btnTest.disabled = true;
                btnTest.innerHTML = 'Connecting & Sending Test Email...';

                if (alertContainer) {
                    alertContainer.innerHTML = '';
                }

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('test_email', email);

                const smtpFields = ['mail_mailer', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name'];
                smtpFields.forEach(fieldName => {
                    const srcElem = document.querySelector(`[name="${fieldName}"]`);
                    if (srcElem && srcElem.value !== '') {
                        formData.append(fieldName, srcElem.value);
                    }
                });

                fetch('{{ route("panel.admin.emailSettings.test") }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    btnTest.disabled = false;
                    btnTest.innerHTML = 'Send Test Email';

                    if (alertContainer) {
                        if (data.ok) {
                            alertContainer.innerHTML = `
                                <div class="ff-alert is-success" style="margin-bottom: 20px; padding: 16px;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="shrink: 0;">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                        <polyline points="22 4 12 14.01 9 11.01"/>
                                    </svg>
                                    <div style="margin-left: 10px;">
                                        <div style="font-weight: 800; font-size: 14px; color: #10b981;">SMTP Email Dispatched Successfully</div>

                                        <div style="font-size: 13px; margin-top: 4px; color: var(--ff-text);">${data.message}</div>
                                    </div>
                                </div>
                            `;
                        } else {
                            alertContainer.innerHTML = `
                                <div class="ff-alert is-danger" style="margin-bottom: 20px; align-items: flex-start; padding: 16px;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-top: 2px; shrink: 0;">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="12" y1="8" x2="12" y2="12"/>
                                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                                    </svg>
                                    <div style="margin-left: 10px; width: 100%;">
                                        <div style="font-weight: 800; font-size: 14px; color: #ef4444; margin-bottom: 6px;">SMTP Email Dispatch Failed</div>
                                        <div style="white-space: pre-wrap; font-family: inherit; font-size: 13px; line-height: 1.5; color: var(--ff-text);">${data.message}</div>
                                    </div>
                                </div>
                            `;
                        }
                        alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                })
                .catch(err => {
                    btnTest.disabled = false;
                    btnTest.innerHTML = 'Send Test Email';
                    if (alertContainer) {
                        alertContainer.innerHTML = `
                            <div class="ff-alert is-danger" style="margin-bottom: 20px; padding: 16px;">
                                <div style="font-weight: 800; font-size: 14px; color: #ef4444;">Network Error</div>
                                <div style="font-size: 13px; margin-top: 4px; color: var(--ff-text);">${err.message || 'Failed to dispatch test email request.'}</div>
                            </div>
                        `;
                    }
                });
            });
        }


    });
</script>
@endsection
