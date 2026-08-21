@extends('layout.backend')

@push('title', 'Global System Settings')

@section('content')
<div class="ff-page">
    <div class="ff-admin-header">
        <div>
            <h1 class="ff-h1" style="display: flex; align-items: center; gap: 10px;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                Global System Settings & Security
            </h1>
            <p class="ff-sub">Configure platform defaults, file upload limits, support email, and maintenance toggles.</p>
        </div>
    </div>

    <!-- Dynamic Storage Cleaner Alert Container -->
    <div id="cleaner-alert-container" style="margin-bottom: 20px;"></div>

    <form action="{{ route('panel.admin.settings.update') }}" method="POST">
        @csrf

        <div class="ff-card" style="padding: 24px; margin-bottom: 24px;">
            <h3 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 700; color: var(--ff-text); border-bottom: 1px solid var(--ff-border); padding-bottom: 12px;">General Platform Identity</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: var(--ff-text); margin-bottom: 6px;">Application Name</label>
                    <input type="text" name="app_name" value="{{ $settings['app_name'] ?? 'FileFusion' }}" class="ff-input" style="width: 100%; height: 42px;" required>
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: var(--ff-text); margin-bottom: 6px;">Support / Admin Contact Email</label>
                    <input type="email" name="support_email" value="{{ $settings['support_email'] ?? 'hello@filefusion.io' }}" class="ff-input" style="width: 100%; height: 42px;" required>
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: var(--ff-text); margin-bottom: 6px;">Default Items Visible Per Page</label>
                    <input type="number" step="1" min="1" max="200" name="items_per_page" value="{{ $settings['items_per_page'] ?? '12' }}" class="ff-input" style="width: 100%; height: 42px;" required>
                    <span style="font-size: 11.5px; color: var(--ff-muted); margin-top: 4px; display: block;">Default pagination density across bookmarks, files, categories, and credentials.</span>
                </div>
            </div>
        </div>

        <div class="ff-card" style="padding: 24px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--ff-border); padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--ff-text);">Group &amp; Role Default Storage Quotas</h3>
                    <p style="margin: 4px 0 0 0; font-size: 12.5px; color: var(--ff-muted);">Configure default storage limits for each user group/role from this point onwards.</p>
                </div>
                <span style="background: rgba(99,102,241,0.12); color: #6366f1; border: 1px solid rgba(99,102,241,0.25); font-weight: 700; font-size: 11.5px; padding: 4px 10px; border-radius: 12px;">
                    Role-Based Storage Allocations
                </span>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 16px;">
                @foreach($roleQuotas as $rKey => $rData)
                    <div style="background: var(--ff-bg-2); border: 1px solid var(--ff-border); border-radius: 10px; padding: 16px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                            <span style="font-size: 13.5px; font-weight: 700; color: var(--ff-text);">{{ $rData['label'] }}</span>
                            <code class="ff-mono" style="font-size: 11px; color: var(--ff-accent); background: var(--ff-surface); padding: 2px 6px; border-radius: 4px;">{{ $rKey }}</code>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="number" step="any" min="0.01" name="quota_role_{{ $rKey }}" value="{{ $rData['quota_gb'] }}" class="ff-input" style="flex: 1; height: 38px;" required>
                            <span style="font-size: 13px; font-weight: 700; color: var(--ff-text);">GB</span>
                        </div>
                        <label style="display: flex; align-items: center; gap: 8px; margin-top: 10px; cursor: pointer; font-size: 11.5px; color: var(--ff-muted);">
                            <input type="checkbox" name="sync_existing_role_{{ $rKey }}" value="1" style="width: 14px; height: 14px; accent-color: #6366f1;">
                            <span>Also sync existing {{ $rData['label'] }}s</span>
                        </label>
                    </div>
                @endforeach
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; padding-top: 16px; border-top: 1px dashed var(--ff-border);">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: var(--ff-text); margin-bottom: 6px;">Global Default Storage Quota (GB)</label>
                    <input type="number" step="any" min="0.01" name="default_quota_gb" value="{{ $settings['default_quota_gb'] ?? '25' }}" class="ff-input" style="width: 100%; height: 42px;" required>
                    <span style="font-size: 11.5px; color: var(--ff-muted); margin-top: 4px; display: block;">Fallback quota assigned when no specific role is matched.</span>
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: var(--ff-text); margin-bottom: 6px;">Max Single Upload File Size (MB)</label>
                    <input type="number" step="any" min="1" name="max_upload_mb" value="{{ $settings['max_upload_mb'] ?? '500' }}" class="ff-input" style="width: 100%; height: 42px;" required>
                    <span style="font-size: 11.5px; color: var(--ff-muted); margin-top: 4px; display: block;">Upload size limit enforced on individual file uploads.</span>
                </div>
            </div>
        </div>

        <div class="ff-card" style="padding: 24px; margin-bottom: 24px;">
            <h3 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 700; color: var(--ff-text); border-bottom: 1px solid var(--ff-border); padding-bottom: 12px;">System Access & Maintenance</h3>
            
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                    <input type="checkbox" name="registration_open" value="1" {{ ($settings['registration_open'] ?? '1') == '1' ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #6366f1;">
                    <div>
                        <div style="font-size: 14px; font-weight: 600; color: var(--ff-text);">Allow Public New User Registration</div>
                        <div style="font-size: 12px; color: var(--ff-muted);">If disabled, new users cannot register accounts on the public website.</div>
                    </div>
                </label>

                <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                    <input type="checkbox" name="maintenance_mode" value="1" {{ ($settings['maintenance_mode'] ?? '0') == '1' ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #ef4444;">
                    <div>
                        <div style="font-size: 14px; font-weight: 600; color: #ef4444;">Enable System Maintenance Mode</div>
                        <div style="font-size: 12px; color: var(--ff-muted);">Display maintenance notice to non-admin users.</div>
                    </div>
                </label>
            </div>
        </div>

        <div class="ff-card" style="padding: 24px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid var(--ff-border); padding-bottom: 12px; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--ff-text);">Storage & System Maintenance</h3>
                    <p style="margin: 4px 0 0 0; font-size: 12.5px; color: var(--ff-muted);">Purge orphan files, clear soft-deleted trash, remove expired share links, and reset application cache.</p>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button type="button" data-target="all" class="js-clean-btn ff-btn is-primary" style="font-size: 13px; font-weight: 700;">
                        Purge All Storage & Cache
                    </button>
                    <a href="{{ route('panel.admin.cleanStoragePage') }}" class="ff-btn" style="font-size: 13px; font-weight: 700;">
                        Interactive Cleaner Tool →
                    </a>
                </div>
            </div>

            <!-- Selective Trash Purge Checkbox Controls -->
            <div style="margin: 16px 0 20px 0; padding: 16px; background: var(--ff-bg2); border: 1px solid var(--ff-border); border-radius: 10px;">
                <div style="font-weight: 700; font-size: 13.5px; color: var(--ff-text); margin-bottom: 4px;">
                    Trash Purge Policy Controls
                </div>
                <p style="font-size: 12px; color: var(--ff-muted); margin: 0 0 10px 0;">
                    Trash storage is <strong>never deleted automatically</strong>. Check the specific options below to authorize targeted trash purging:
                </p>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 12.5px; font-weight: 600; color: var(--ff-text);">
                        <input type="checkbox" id="chk_admin_trash_full" value="storage_full" style="width: 16px; height: 16px; accent-color: var(--ff-accent);">
                        <span>Purge Trash for Users Exceeding Quota (≥90% Storage Used)</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 12.5px; font-weight: 600; color: var(--ff-text);">
                        <input type="checkbox" id="chk_admin_trash_non_admin" value="non_admin" style="width: 16px; height: 16px; accent-color: var(--ff-accent);">
                        <span>Purge Trash for Standard Non-Admin User Accounts</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 12.5px; font-weight: 600; color: var(--ff-text);">
                        <input type="checkbox" id="chk_admin_trash_all" value="all_manual" style="width: 16px; height: 16px; accent-color: var(--ff-accent);">
                        <span>Purge All Soft-Deleted Trash Records (Manual Admin Override)</span>
                    </label>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                <a href="{{ route('panel.admin.cleanStorage', ['target' => 'screenshots']) }}" data-target="screenshots" class="js-clean-btn ff-card" style="background: var(--ff-bg2); padding: 16px; text-decoration: none; display: flex; flex-direction: column; justify-content: space-between; gap: 12px; cursor: pointer;">
                    <div>
                        <div style="font-size: 14px; font-weight: 700; color: var(--ff-text); margin-bottom: 4px;">Orphan Screenshots</div>
                        <div style="font-size: 12px; color: var(--ff-muted);">Remove thumbnail files not linked to active bookmarks.</div>
                    </div>
                    <span style="font-size: 12px; font-weight: 700; color: var(--ff-accent);">Purge Screenshots →</span>
                </a>

                <a href="{{ route('panel.admin.cleanStorage', ['target' => 'trashed']) }}" data-target="trashed" class="js-clean-btn ff-card" style="background: var(--ff-bg2); padding: 16px; text-decoration: none; display: flex; flex-direction: column; justify-content: space-between; gap: 12px; cursor: pointer;">
                    <div>
                        <div style="font-size: 14px; font-weight: 700; color: var(--ff-text); margin-bottom: 4px;">Soft-Deleted Trash</div>
                        <div style="font-size: 12px; color: var(--ff-muted);">Delete trashed files physically from disk and reclaim storage.</div>
                    </div>
                    <span style="font-size: 12px; font-weight: 700; color: var(--ff-accent);">Purge Trashed Files →</span>
                </a>

                <a href="{{ route('panel.admin.cleanStorage', ['target' => 'shares']) }}" data-target="shares" class="js-clean-btn ff-card" style="background: var(--ff-bg2); padding: 16px; text-decoration: none; display: flex; flex-direction: column; justify-content: space-between; gap: 12px; cursor: pointer;">
                    <div>
                        <div style="font-size: 14px; font-weight: 700; color: var(--ff-text); margin-bottom: 4px;">Expired Share Links</div>
                        <div style="font-size: 12px; color: var(--ff-muted);">Remove expired sharing records from database.</div>
                    </div>
                    <span style="font-size: 12px; font-weight: 700; color: var(--ff-accent);">Clean Expired Shares →</span>
                </a>

                <a href="{{ route('panel.admin.cleanStorage', ['target' => 'cache']) }}" data-target="cache" class="js-clean-btn ff-card" style="background: var(--ff-bg2); padding: 16px; text-decoration: none; display: flex; flex-direction: column; justify-content: space-between; gap: 12px; cursor: pointer;">
                    <div>
                        <div style="font-size: 14px; font-weight: 700; color: var(--ff-text); margin-bottom: 4px;">Application & View Cache</div>
                        <div style="font-size: 12px; color: var(--ff-muted);">Reset compiled Blade templates and system cache.</div>
                    </div>
                    <span style="font-size: 12px; font-weight: 700; color: var(--ff-accent);">Clear Cache →</span>
                </a>
            </div>
        </div>


        <!-- Master Encryption Key Rotation & Leak Recovery Panel -->
        <div class="ff-card" style="padding: 24px; margin-bottom: 24px; border-left: 4px solid #6366f1;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--ff-border); padding-bottom: 12px; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--ff-text); display: flex; align-items: center; gap: 8px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        Master Encryption Keys & Security Rotation
                    </h3>
                    <p style="margin: 4px 0 0 0; font-size: 12.5px; color: var(--ff-muted);">
                        Rotate compromised or expired encryption keys across the database and physical files with zero downtime and multi-key fallback.
                    </p>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button type="button" id="btn-dry-run-keys" class="ff-btn" style="font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                        <span>🧪</span> Pre-Flight Dry Run
                    </button>
                    <button type="button" id="btn-open-rotate-modal" class="ff-btn is-primary" style="font-size: 13px; font-weight: 700; background: #6366f1; border-color: #6366f1; display: flex; align-items: center; gap: 6px;">
                        <span>🔐</span> Rotate Keys & Re-Encrypt
                    </button>
                </div>
            </div>

            <!-- Dynamic Key Rotation Status / Alert Box -->
            <div id="key-rotation-alert-container"></div>

            <!-- Active Key Fingerprints -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 16px;">
                <div style="background: var(--ff-bg2); padding: 14px; border-radius: 8px; border: 1px solid var(--ff-border);">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); margin-bottom: 4px;">Database Key (APP_KEY)</div>
                    <div style="font-family: monospace; font-size: 13px; font-weight: 700; color: var(--ff-text);" id="lbl-app-key-fingerprint">
                        {{ $keyStatus['app_key_fingerprint'] ?? 'N/A' }}
                    </div>
                    <div style="font-size: 11px; color: var(--ff-muted); margin-top: 4px;">
                        Previous Key Chain: <strong>{{ $keyStatus['app_previous_keys_count'] ?? 0 }} key(s)</strong>
                    </div>
                </div>

                <div style="background: var(--ff-bg2); padding: 14px; border-radius: 8px; border: 1px solid var(--ff-border);">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); margin-bottom: 4px;">File Envelope Key (FILE_KEY)</div>
                    <div style="font-family: monospace; font-size: 13px; font-weight: 700; color: var(--ff-text);" id="lbl-file-key-fingerprint">
                        {{ $keyStatus['file_key_fingerprint'] ?? 'N/A' }}
                    </div>
                    <div style="font-size: 11px; color: var(--ff-muted); margin-top: 4px;">
                        Version: <strong>{{ $keyStatus['file_key_version'] ?? 'v1' }}</strong> • Fallback Chain: <strong>{{ $keyStatus['file_previous_keys_count'] ?? 0 }} key(s)</strong>
                    </div>
                </div>

                <div style="background: var(--ff-bg2); padding: 14px; border-radius: 8px; border: 1px solid var(--ff-border);">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted); margin-bottom: 4px;">Cryptographic Engine</div>
                    <div style="font-size: 13px; font-weight: 700; color: #10b981; display: flex; align-items: center; gap: 6px;">
                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></span>
                        AES-256-GCM Envelope
                    </div>
                    <div style="font-size: 11px; color: var(--ff-muted); margin-top: 4px;">
                        CLI Command: <code>php artisan security:rotate-keys</code>
                    </div>
                </div>
            </div>

            <div style="font-size: 12px; color: var(--ff-muted); line-height: 1.5;">
                ℹ️ <strong>Zero Data Loss Guarantee:</strong> Physical files use envelope encryption. Rotating the Master Key updates only the 60-byte key header per file in microseconds, keeping multi-gigabyte payloads intact and safe.
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end;">
            <button type="submit" class="ff-btn is-primary" style="padding: 10px 24px; font-size: 14px; font-weight: 700;">
                Save System Settings
            </button>
        </div>
    </form>

    <!-- Key Rotation Modal -->
    <div id="modal-key-rotation" style="display: none; position: fixed; inset: 0; z-index: 10050; background: rgba(0,0,0,0.65); align-items: center; justify-content: center; padding: 20px;">
        <div class="ff-card" style="width: 100%; max-width: 520px; padding: 24px; background: var(--ff-card, #ffffff) !important; color: var(--ff-text) !important; border: 1px solid var(--ff-border); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); border-radius: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--ff-border); padding-bottom: 12px;">
                <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: var(--ff-text); display: flex; align-items: center; gap: 8px;">
                    <span>🔐</span> Rotate Master Encryption Keys
                </h3>
                <button type="button" id="btn-close-rotate-modal" class="ff-modal-close" aria-label="Close modal">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <p style="font-size: 13px; color: var(--ff-muted); margin-bottom: 20px; line-height: 1.5;">
                This operation generates new 256-bit cryptographically secure keys, re-encrypts all database records (<span style="color: var(--ff-text); font-weight: 600;">files, categories, links, passwords</span>), updates all physical file envelope headers, and synchronizes your <span style="font-family: monospace;">.env</span> configuration.
            </p>

            <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 20px; background: var(--ff-bg2); padding: 16px; border-radius: 8px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13.5px; font-weight: 600; color: var(--ff-text);">
                    <input type="checkbox" id="chk_rot_app" checked style="width: 18px; height: 18px; accent-color: #6366f1;">
                    Rotate Database Master Key (<code>APP_KEY</code>)
                </label>

                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13.5px; font-weight: 600; color: var(--ff-text);">
                    <input type="checkbox" id="chk_rot_file" checked style="width: 18px; height: 18px; accent-color: #6366f1;">
                    Rotate Storage File Envelope Key (<code>FILE_ENCRYPTION_KEY</code>)
                </label>

                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13.5px; font-weight: 600; color: var(--ff-text);">
                    <input type="checkbox" id="chk_rot_fallback" checked style="width: 18px; height: 18px; accent-color: #10b981;">
                    Keep Old Keys in Fallback Chain (Recommended for Zero Downtime)
                </label>
            </div>

            <!-- Type Confirmation to Proceed -->
            <div style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 8px; padding: 14px; margin-bottom: 20px;">
                <div style="font-size: 12.5px; font-weight: 700; color: #ef4444; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Safety Confirmation Required
                </div>
                <p style="font-size: 12px; color: var(--ff-text); margin: 0 0 10px 0; line-height: 1.4;">
                    To prevent accidental rotation, please type <strong style="color: #ef4444; font-family: monospace; background: var(--ff-surface); padding: 2px 6px; border-radius: 4px; border: 1px solid rgba(239, 68, 68, 0.3);">ROTATE</strong> in the box below to unlock the button:
                </p>
                <input type="text" id="input-confirm-rotate-phrase" placeholder="Type ROTATE to confirm" autocomplete="off" class="ff-input" style="width: 100%; height: 38px; font-family: monospace; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" id="btn-cancel-rotate-modal" class="ff-btn" style="font-size: 13px;">Cancel</button>
                <button type="button" id="btn-confirm-execute-rotation" class="ff-btn is-primary" disabled style="font-size: 13px; font-weight: 700; background: #6366f1; border-color: #6366f1; opacity: 0.45; cursor: not-allowed;">
                    Confirm &amp; Execute Rotation
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('push-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const alertBox = document.getElementById('cleaner-alert-container');
        const cleanBtns = document.querySelectorAll('.js-clean-btn');

        cleanBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const target = this.getAttribute('data-target') || 'all';

                if (!confirm(`Run Storage & System Cleaner target: '${target}'?`)) {
                    return;
                }

                const isFull = document.getElementById('chk_admin_trash_full')?.checked;
                const isNonAdmin = document.getElementById('chk_admin_trash_non_admin')?.checked;
                const isAll = document.getElementById('chk_admin_trash_all')?.checked;

                let trashFilter = '';
                if (isAll) {
                    trashFilter = 'all_manual';
                } else if (isFull && isNonAdmin) {
                    trashFilter = 'both';
                } else if (isFull) {
                    trashFilter = 'storage_full';
                } else if (isNonAdmin) {
                    trashFilter = 'non_admin';
                }

                if (alertBox) {
                    alertBox.innerHTML = `
                        <div class="ff-alert" style="margin-bottom: 20px; background: rgba(99,102,241,0.1); border-color: var(--ff-border);">
                            <span style="font-weight: 700; color: var(--ff-text);">Cleaning Storage & Cache...</span> Scanning target '${target}'
                        </div>
                    `;
                    alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }


                fetch(`{{ route('panel.admin.cleanStorage') }}?target=${target}&trash_filter=${trashFilter}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })

                .then(res => res.json())
                .then(data => {
                    if (alertBox && data.ok) {
                        let summaryListHtml = '';
                        if (data.summary && data.summary.length > 0) {
                            summaryListHtml = data.summary.map(item => `<li style="margin-bottom: 4px;">${item}</li>`).join('');
                        }

                        alertBox.innerHTML = `
                            <div class="ff-card" style="margin-bottom: 24px; padding: 20px; border-left: 4px solid #10b981; background: var(--ff-surface);">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                    <h4 style="margin: 0; font-size: 16px; font-weight: 800; color: #10b981; display: flex; align-items: center; gap: 8px;">
                                        🎉 Storage Cleanup Completed Successfully
                                    </h4>
                                    <span style="background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 700;">
                                        Freed ${data.freed_space_human_readable}
                                    </span>
                                </div>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 16px; background: var(--ff-bg2); padding: 12px; border-radius: 8px;">
                                    <div>
                                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted);">Total Space Recovered</div>
                                        <div style="font-size: 18px; font-weight: 800; color: #10b981;">${data.freed_space_human_readable}</div>
                                    </div>
                                    <div>
                                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted);">Files Deleted / Purged</div>
                                        <div style="font-size: 18px; font-weight: 800; color: var(--ff-text);">${data.deleted_files_count} File(s)</div>
                                    </div>
                                </div>
                                <div style="font-size: 13px; color: var(--ff-text); font-weight: 600; margin-bottom: 6px;">Detailed Action Breakdown:</div>
                                <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: var(--ff-muted); line-height: 1.6;">
                                    ${summaryListHtml}
                                </ul>
                            </div>
                        `;
                        alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                })
                .catch(err => {
                    if (alertBox) {
                        alertBox.innerHTML = `
                            <div class="ff-alert is-danger" style="margin-bottom: 20px;">
                                Failed to execute storage cleanup: ${err.message || 'Network error'}
                            </div>
                        `;
                    }
                });
            });
        });

        // --- Master Encryption Key Rotation JS Handlers ---
        const rotAlertBox = document.getElementById('key-rotation-alert-container');
        const btnDryRun = document.getElementById('btn-dry-run-keys');
        const btnOpenRotModal = document.getElementById('btn-open-rotate-modal');
        const rotModal = document.getElementById('modal-key-rotation');
        const btnCloseRotModal = document.getElementById('btn-close-rotate-modal');
        const btnCancelRotModal = document.getElementById('btn-cancel-rotate-modal');
        const btnConfirmExecuteRot = document.getElementById('btn-confirm-execute-rotation');
        const inputConfirmPhrase = document.getElementById('input-confirm-rotate-phrase');

        function updateConfirmButtonState() {
            if (!inputConfirmPhrase || !btnConfirmExecuteRot) return;
            const val = inputConfirmPhrase.value.trim().toUpperCase();
            if (val === 'ROTATE' || val === 'ROTATE KEYS') {
                btnConfirmExecuteRot.disabled = false;
                btnConfirmExecuteRot.style.opacity = '1';
                btnConfirmExecuteRot.style.cursor = 'pointer';
            } else {
                btnConfirmExecuteRot.disabled = true;
                btnConfirmExecuteRot.style.opacity = '0.45';
                btnConfirmExecuteRot.style.cursor = 'not-allowed';
            }
        }

        if (inputConfirmPhrase) {
            inputConfirmPhrase.addEventListener('input', updateConfirmButtonState);
            inputConfirmPhrase.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (!btnConfirmExecuteRot.disabled) {
                        btnConfirmExecuteRot.click();
                    }
                }
            });
        }

        // Modal Open / Close
        if (btnOpenRotModal && rotModal) {
            btnOpenRotModal.addEventListener('click', () => {
                if (inputConfirmPhrase) inputConfirmPhrase.value = '';
                updateConfirmButtonState();
                rotModal.style.display = 'flex';
                if (inputConfirmPhrase) setTimeout(() => inputConfirmPhrase.focus(), 100);
            });
        }
        if (btnCloseRotModal && rotModal) {
            btnCloseRotModal.addEventListener('click', () => {
                rotModal.style.display = 'none';
                if (inputConfirmPhrase) inputConfirmPhrase.value = '';
                updateConfirmButtonState();
            });
        }
        if (btnCancelRotModal && rotModal) {
            btnCancelRotModal.addEventListener('click', () => {
                rotModal.style.display = 'none';
                if (inputConfirmPhrase) inputConfirmPhrase.value = '';
                updateConfirmButtonState();
            });
        }

        // Dry Run Action
        if (btnDryRun) {
            btnDryRun.addEventListener('click', function() {
                if (rotAlertBox) {
                    rotAlertBox.innerHTML = `
                        <div class="ff-alert" style="margin-bottom: 16px; background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.3); color: var(--ff-text); padding: 14px; border-radius: 8px; display: flex; align-items: center; gap: 10px;">
                            <span style="animation: spin 1s linear infinite; display: inline-block;">⏳</span>
                            <strong>Running Pre-Flight Dry Run...</strong> Testing decryption of all database records and storage files.
                        </div>
                    `;
                }

                fetch("{{ route('panel.admin.keyRotation.dryRun') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(res => {
                    if (!rotAlertBox) return;
                    if (res.ok && res.data) {
                        const s = res.data.stats || {};
                        rotAlertBox.innerHTML = `
                            <div class="ff-card" style="margin-bottom: 20px; padding: 18px; border-left: 4px solid #10b981; background: var(--ff-surface);">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                                    <h4 style="margin: 0; font-size: 15px; font-weight: 800; color: #10b981; display: flex; align-items: center; gap: 8px;">
                                        ✓ Pre-Flight Dry Run Succeeded (100% Decryptable)
                                    </h4>
                                    <span style="background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 700;">
                                        All Keys Healthy
                                    </span>
                                </div>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px; font-size: 12px; background: var(--ff-bg2); padding: 12px; border-radius: 8px;">
                                    <div><span style="color: var(--ff-muted);">Files (DB):</span> <strong>${s.files_decryptable || 0} / ${s.files_total || 0}</strong></div>
                                    <div><span style="color: var(--ff-muted);">Categories:</span> <strong>${s.categories_decryptable || 0} / ${s.categories_total || 0}</strong></div>
                                    <div><span style="color: var(--ff-muted);">Links:</span> <strong>${s.links_decryptable || 0} / ${s.links_total || 0}</strong></div>
                                    <div><span style="color: var(--ff-muted);">Passwords:</span> <strong>${s.passwords_decryptable || 0} / ${s.passwords_total || 0}</strong></div>
                                    <div><span style="color: var(--ff-muted);">Disk Files:</span> <strong>${s.disk_files_decryptable || 0} / ${s.disk_files_encrypted || 0}</strong></div>
                                </div>
                            </div>
                        `;
                    } else {
                        const errs = (res.data?.errors || [res.message || 'Unknown error']).map(e => `<li>${e}</li>`).join('');
                        rotAlertBox.innerHTML = `
                            <div class="ff-alert is-danger" style="margin-bottom: 20px; padding: 16px;">
                                <strong>⚠️ Pre-Flight Check Warnings:</strong>
                                <ul style="margin: 8px 0 0 0; padding-left: 20px;">${errs}</ul>
                            </div>
                        `;
                    }
                })
                .catch(err => {
                    if (rotAlertBox) {
                        rotAlertBox.innerHTML = `<div class="ff-alert is-danger" style="margin-bottom: 20px;">Dry run request failed: ${err.message}</div>`;
                    }
                });
            });
        }

        // Live Rotation Action
        if (btnConfirmExecuteRot) {
            btnConfirmExecuteRot.addEventListener('click', function() {
                const phraseVal = inputConfirmPhrase ? inputConfirmPhrase.value.trim().toUpperCase() : '';
                if (phraseVal !== 'ROTATE' && phraseVal !== 'ROTATE KEYS') {
                    alert('Please type "ROTATE" in the confirmation box to proceed.');
                    return;
                }

                const rotApp = document.getElementById('chk_rot_app')?.checked ?? true;
                const rotFile = document.getElementById('chk_rot_file')?.checked ?? true;
                const rotFallback = document.getElementById('chk_rot_fallback')?.checked ?? true;

                if (!rotApp && !rotFile) {
                    alert('Please select at least one encryption key to rotate.');
                    return;
                }

                if (rotModal) rotModal.style.display = 'none';

                if (rotAlertBox) {
                    rotAlertBox.innerHTML = `
                        <div class="ff-alert" style="margin-bottom: 16px; background: rgba(99,102,241,0.15); border: 1px solid #6366f1; color: var(--ff-text); padding: 16px; border-radius: 8px;">
                            <div style="display: flex; align-items: center; gap: 10px; font-weight: 700; margin-bottom: 4px;">
                                <span style="animation: spin 1s linear infinite; display: inline-block;">⚡</span>
                                Executing Live Key Rotation & Re-Encryption...
                            </div>
                            <div style="font-size: 12px; color: var(--ff-muted);">
                                Generating 256-bit keys, re-encrypting database rows, updating file envelope headers, and synchronizing .env.
                            </div>
                        </div>
                    `;
                    rotAlertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                fetch("{{ route('panel.admin.keyRotation.execute') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        rotate_app_key: rotApp,
                        rotate_file_key: rotFile,
                        keep_previous: rotFallback
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if (!rotAlertBox) return;
                    if (res.ok && res.data) {
                        const d = res.data;
                        const dbStats = d.db_reencrypted?.stats || {};
                        const fStats = d.files_reencrypted?.stats || {};
                        const newKeys = d.new_keys || {};

                        // Update fingerprint labels
                        if (document.getElementById('lbl-app-key-fingerprint') && newKeys.app_key) {
                            document.getElementById('lbl-app-key-fingerprint').innerText = newKeys.app_key.substring(0, 14) + '...';
                        }
                        if (document.getElementById('lbl-file-key-fingerprint') && newKeys.file_key) {
                            document.getElementById('lbl-file-key-fingerprint').innerText = newKeys.file_key.substring(0, 14) + '...';
                        }

                        rotAlertBox.innerHTML = `
                            <div class="ff-card" style="margin-bottom: 20px; padding: 20px; border-left: 4px solid #10b981; background: var(--ff-surface);">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                    <h4 style="margin: 0; font-size: 16px; font-weight: 800; color: #10b981; display: flex; align-items: center; gap: 8px;">
                                        🎉 Master Encryption Key Rotation Completed Successfully!
                                    </h4>
                                    <span style="background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700;">
                                        Zero Data Loss
                                    </span>
                                </div>
                                <div style="font-size: 13px; color: var(--ff-text); margin-bottom: 12px;">
                                    All selected encryption keys have been regenerated and all database records and storage envelope headers re-encrypted without downtime.
                                </div>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; background: var(--ff-bg2); padding: 12px; border-radius: 8px; font-size: 12.5px;">
                                    <div><strong>Files DB Re-encrypted:</strong> ${dbStats.files_updated ?? 0} rows</div>
                                    <div><strong>Links DB Re-encrypted:</strong> ${dbStats.links_updated ?? 0} rows</div>
                                    <div><strong>Categories DB Re-encrypted:</strong> ${dbStats.categories_updated ?? 0} rows</div>
                                    <div><strong>Passwords DB Re-encrypted:</strong> ${dbStats.passwords_updated ?? 0} rows</div>
                                    <div><strong>Disk Files Re-keyed:</strong> ${fStats.encrypted_re_keyed ?? 0} files</div>
                                    <div><strong>Active Key Version:</strong> ${newKeys.file_key_version ?? 'v1'}</div>
                                </div>
                            </div>
                        `;
                    } else {
                        rotAlertBox.innerHTML = `
                            <div class="ff-alert is-danger" style="margin-bottom: 20px; padding: 16px;">
                                <strong>✗ Key Rotation Failed:</strong> ${res.message || 'Unknown error during rotation'}
                            </div>
                        `;
                    }
                })
                .catch(err => {
                    if (rotAlertBox) {
                        rotAlertBox.innerHTML = `<div class="ff-alert is-danger" style="margin-bottom: 20px;">Execution failed: ${err.message}</div>`;
                    }
                });
            });
        }
    });
</script>
@endsection

