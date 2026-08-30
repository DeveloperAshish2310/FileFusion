@extends('layout.backend')

@push('title', 'System & Database Backups')

@section('content')
<div class="ff-page">
    <div class="ff-admin-header">
        <div>
            <h1 class="ff-h1" style="display: flex; align-items: center; gap: 10px;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                System &amp; Database Backups
            </h1>
            <p class="ff-sub">Generate full system archives (Database + Storage Files), offsite sync to FTP/SFTP, and retain local copies.</p>
        </div>
        <div class="ff-admin-actions">
            <button type="button" id="btn-toggle-remote-config" class="ff-btn" style="font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <span>⚙️</span> Configure FTP / SFTP Remote Storage
            </button>
        </div>
    </div>

    <!-- Alert / Status Container -->
    <div id="backup-alert-container" style="margin-bottom: 20px;"></div>

    @if(session('success'))
        <div class="ff-alert is-success" style="margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="ff-alert is-danger" style="margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif

    <!-- Metrics Stats Grid -->
    <div class="ff-admin-kpi-grid">
        <div class="ff-card" style="padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted);">Total Backups</span>
            <div style="font-size: 28px; font-weight: 800; color: #6366f1; margin-top: 4px;" id="stat-total-backups">{{ $stats['total_backups'] }}</div>
            <div style="font-size: 12.5px; color: var(--ff-muted); margin-top: 6px;">Archived pool size: <strong id="stat-pool-size">{{ $stats['total_backup_size_formatted'] }}</strong></div>
        </div>

        <div class="ff-card" style="padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted);">Database Size</span>
            <div style="font-size: 28px; font-weight: 800; color: #10b981; margin-top: 4px;">{{ $stats['database_size_formatted'] }}</div>
            <div style="font-size: 12.5px; color: var(--ff-muted); margin-top: 6px;">MySQL database records &amp; schema</div>
        </div>

        <div class="ff-card" style="padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted);">Last Backup Created</span>
            <div style="font-size: 22px; font-weight: 800; color: var(--ff-text); margin-top: 6px;" id="stat-last-time">{{ $stats['last_backup_time'] }}</div>
            <div style="font-size: 12.5px; color: var(--ff-muted); margin-top: 6px;" id="stat-last-date">{{ $stats['last_backup_date'] }}</div>
        </div>

        <div class="ff-card" style="padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted);">Offsite Sync Status</span>
            <div style="font-size: 14px; font-weight: 700; color: var(--ff-text); margin-top: 8px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span style="padding: 3px 8px; border-radius: 6px; font-size: 11.5px; font-weight: 700; background: {{ ($remoteConfigs['ftp']['enabled'] ?? false) ? 'rgba(16,185,129,0.15)' : 'rgba(255,255,255,0.06)' }}; color: {{ ($remoteConfigs['ftp']['enabled'] ?? false) ? '#10b981' : 'var(--ff-muted)' }};">
                    FTP: {{ ($remoteConfigs['ftp']['enabled'] ?? false) ? 'Active' : 'Off' }}
                </span>
                <span style="padding: 3px 8px; border-radius: 6px; font-size: 11.5px; font-weight: 700; background: {{ ($remoteConfigs['sftp']['enabled'] ?? false) ? 'rgba(16,185,129,0.15)' : 'rgba(255,255,255,0.06)' }}; color: {{ ($remoteConfigs['sftp']['enabled'] ?? false) ? '#10b981' : 'var(--ff-muted)' }};">
                    SFTP: {{ ($remoteConfigs['sftp']['enabled'] ?? false) ? 'Active' : 'Off' }}
                </span>
            </div>
            <div style="font-size: 12px; color: var(--ff-muted); margin-top: 6px;">
                Keep Local: <strong>{{ ($remoteConfigs['keep_local'] ?? true) ? 'Always Retained' : 'Remote Only' }}</strong>
            </div>
        </div>
    </div>

    <!-- Remote Storage Configuration Panel (Collapsible) -->
    <div id="panel-remote-config" class="ff-card" style="display: none; padding: 24px; margin-bottom: 28px; border-left: 4px solid #6366f1;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid var(--ff-border); padding-bottom: 12px;">
            <div>
                <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: var(--ff-text); display: flex; align-items: center; gap: 8px;">
                    <span>☁️</span> FTP &amp; SFTP Remote Offsite Storage Configuration
                </h3>
                <p style="margin: 4px 0 0 0; font-size: 12.5px; color: var(--ff-muted);">Configure external storage credentials to automatically replicate backups offsite while keeping local copies intact.</p>
            </div>
            <button type="button" id="btn-close-remote-config" class="ff-modal-close" aria-label="Close modal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <form action="{{ route('panel.admin.backups.config.remote') }}" method="POST">
            @csrf

            <!-- Local Retention & Auto Target -->
            <div style="background: var(--ff-bg2); padding: 16px; border-radius: 8px; margin-bottom: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; align-items: center;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="keep_local" value="1" {{ ($remoteConfigs['keep_local'] ?? true) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #6366f1;">
                    <div>
                        <div style="font-size: 13.5px; font-weight: 700; color: var(--ff-text);">Always Keep Local Archive</div>
                        <div style="font-size: 11.5px; color: var(--ff-muted);">Keep the generated backup file in local storage even after uploading to remote.</div>
                    </div>
                </label>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: var(--ff-text); margin-bottom: 6px;">Default Auto-Upload Destination</label>
                    <select name="auto_upload_target" class="ff-select" style="width: 100%; height: 38px;">
                        <option value="none" {{ ($remoteConfigs['auto_upload_target'] ?? '') === 'none' ? 'selected' : '' }}>Local Only (Manual Offsite Upload)</option>
                        <option value="ftp" {{ ($remoteConfigs['auto_upload_target'] ?? '') === 'ftp' ? 'selected' : '' }}>Auto-Replicate to FTP Server</option>
                        <option value="sftp" {{ ($remoteConfigs['auto_upload_target'] ?? '') === 'sftp' ? 'selected' : '' }}>Auto-Replicate to SFTP Server</option>
                        <option value="both" {{ ($remoteConfigs['auto_upload_target'] ?? '') === 'both' ? 'selected' : '' }}>Replicate to Both (FTP &amp; SFTP)</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 24px;">
                <!-- FTP Configuration Card -->
                <div style="background: var(--ff-bg2); padding: 20px; border-radius: 10px; border: 1px solid var(--ff-border);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                        <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--ff-text); display: flex; align-items: center; gap: 8px;">
                            <span>📡</span> FTP / FTPS Destination
                        </h4>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; cursor: pointer; color: var(--ff-text);">
                            <input type="checkbox" name="ftp_enabled" id="ftp_enabled" value="1" {{ ($remoteConfigs['ftp']['enabled'] ?? false) ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: #6366f1;">
                            Enable FTP
                        </label>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px;">
                            <div>
                                <label style="font-size: 12px; font-weight: 600; color: var(--ff-muted); display: block; margin-bottom: 4px;">FTP Host</label>
                                <input type="text" name="ftp_host" id="ftp_host" value="{{ $remoteConfigs['ftp']['host'] ?? '' }}" placeholder="ftp.yourserver.com" class="ff-input" style="width: 100%; height: 36px;">
                            </div>
                            <div>
                                <label style="font-size: 12px; font-weight: 600; color: var(--ff-muted); display: block; margin-bottom: 4px;">Port</label>
                                <input type="number" name="ftp_port" id="ftp_port" value="{{ $remoteConfigs['ftp']['port'] ?? 21 }}" class="ff-input" style="width: 100%; height: 36px;">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div>
                                <label style="font-size: 12px; font-weight: 600; color: var(--ff-muted); display: block; margin-bottom: 4px;">Username</label>
                                <input type="text" name="ftp_username" id="ftp_username" value="{{ $remoteConfigs['ftp']['username'] ?? '' }}" placeholder="ftp_user" class="ff-input" style="width: 100%; height: 36px;">
                            </div>
                            <div>
                                <label style="font-size: 12px; font-weight: 600; color: var(--ff-muted); display: block; margin-bottom: 4px;">Password</label>
                                <input type="password" name="ftp_password" id="ftp_password" placeholder="{{ !empty($remoteConfigs['ftp']['password']) ? '••••••••' : 'Password' }}" class="ff-input" style="width: 100%; height: 36px;">
                            </div>
                        </div>

                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: var(--ff-muted); display: block; margin-bottom: 4px;">Remote Storage Directory</label>
                            <input type="text" name="ftp_path" id="ftp_path" value="{{ $remoteConfigs['ftp']['path'] ?? '/backups/' }}" placeholder="/backups/" class="ff-input" style="width: 100%; height: 36px;">
                        </div>

                        <div style="display: flex; gap: 16px; margin-top: 4px;">
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; cursor: pointer; color: var(--ff-text);">
                                <input type="checkbox" name="ftp_ssl" id="ftp_ssl" value="1" {{ ($remoteConfigs['ftp']['ssl'] ?? false) ? 'checked' : '' }} style="width: 14px; height: 14px; accent-color: #6366f1;">
                                FTPS (SSL/TLS)
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; cursor: pointer; color: var(--ff-text);">
                                <input type="checkbox" name="ftp_passive" id="ftp_passive" value="1" {{ ($remoteConfigs['ftp']['passive'] ?? true) ? 'checked' : '' }} style="width: 14px; height: 14px; accent-color: #6366f1;">
                                Passive Mode
                            </label>
                        </div>

                        <button type="button" id="btn-test-ftp" class="ff-btn" style="margin-top: 6px; font-size: 12.5px; font-weight: 700; width: 100%; justify-content: center;">
                            <span>🧪</span> Test FTP Connection
                        </button>
                    </div>
                </div>

                <!-- SFTP Configuration Card -->
                <div style="background: var(--ff-bg2); padding: 20px; border-radius: 10px; border: 1px solid var(--ff-border);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                        <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--ff-text); display: flex; align-items: center; gap: 8px;">
                            <span>🛡️</span> SFTP (SSH) Destination
                        </h4>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; cursor: pointer; color: var(--ff-text);">
                            <input type="checkbox" name="sftp_enabled" id="sftp_enabled" value="1" {{ ($remoteConfigs['sftp']['enabled'] ?? false) ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: #6366f1;">
                            Enable SFTP
                        </label>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px;">
                            <div>
                                <label style="font-size: 12px; font-weight: 600; color: var(--ff-muted); display: block; margin-bottom: 4px;">SFTP Host</label>
                                <input type="text" name="sftp_host" id="sftp_host" value="{{ $remoteConfigs['sftp']['host'] ?? '' }}" placeholder="sftp.yourserver.com" class="ff-input" style="width: 100%; height: 36px;">
                            </div>
                            <div>
                                <label style="font-size: 12px; font-weight: 600; color: var(--ff-muted); display: block; margin-bottom: 4px;">Port</label>
                                <input type="number" name="sftp_port" id="sftp_port" value="{{ $remoteConfigs['sftp']['port'] ?? 22 }}" class="ff-input" style="width: 100%; height: 36px;">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div>
                                <label style="font-size: 12px; font-weight: 600; color: var(--ff-muted); display: block; margin-bottom: 4px;">Username</label>
                                <input type="text" name="sftp_username" id="sftp_username" value="{{ $remoteConfigs['sftp']['username'] ?? '' }}" placeholder="ssh_user" class="ff-input" style="width: 100%; height: 36px;">
                            </div>
                            <div>
                                <label style="font-size: 12px; font-weight: 600; color: var(--ff-muted); display: block; margin-bottom: 4px;">Password</label>
                                <input type="password" name="sftp_password" id="sftp_password" placeholder="{{ !empty($remoteConfigs['sftp']['password']) ? '••••••••' : 'Password' }}" class="ff-input" style="width: 100%; height: 36px;">
                            </div>
                        </div>

                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: var(--ff-muted); display: block; margin-bottom: 4px;">Remote Storage Directory</label>
                            <input type="text" name="sftp_path" id="sftp_path" value="{{ $remoteConfigs['sftp']['path'] ?? '/backups/' }}" placeholder="/backups/" class="ff-input" style="width: 100%; height: 36px;">
                        </div>

                        <button type="button" id="btn-test-sftp" class="ff-btn" style="margin-top: 6px; font-size: 12.5px; font-weight: 700; width: 100%; justify-content: center;">
                            <span>🧪</span> Test SFTP Connection
                        </button>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="ff-btn is-primary" style="padding: 10px 24px; font-size: 13.5px; font-weight: 700; background: #6366f1; border-color: #6366f1;">
                    Save Remote Storage Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Active Remote Destination Selector for 1-Click Backups -->
    <div class="ff-card" style="padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; background: var(--ff-bg2);">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 18px;">🎯</span>
            <div>
                <span style="font-size: 13px; font-weight: 700; color: var(--ff-text);">Target Destination for Next Backup:</span>
                <span style="font-size: 12px; color: var(--ff-muted); margin-left: 4px;">(Local copy is always safely preserved)</span>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <select id="select-active-remote-target" class="ff-select" style="height: 38px; font-weight: 600;">
                <option value="none">💾 Local Storage Only</option>
                <option value="ftp" {{ ($remoteConfigs['ftp']['enabled'] ?? false) ? '' : 'disabled' }}>📡 Upload to FTP Server (+ Local)</option>
                <option value="sftp" {{ ($remoteConfigs['sftp']['enabled'] ?? false) ? '' : 'disabled' }}>🛡️ Upload to SFTP Server (+ Local)</option>
                <option value="both" {{ (($remoteConfigs['ftp']['enabled'] ?? false) && ($remoteConfigs['sftp']['enabled'] ?? false)) ? '' : 'disabled' }}>🌐 Sync to Both FTP &amp; SFTP (+ Local)</option>
            </select>
        </div>
    </div>

    <!-- 1-Click Backup Triggers Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 28px;">
        <!-- Whole Site & Codebase Backup -->
        <div class="ff-card" style="padding: 24px; border-left: 4px solid #ec4899; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <h3 style="margin: 0; font-size: 15.5px; font-weight: 700; color: var(--ff-text); display: flex; align-items: center; gap: 8px;">
                        <span>🌐</span> Whole Site &amp; HTML
                    </h3>
                    <span style="background: rgba(236,72,153,0.12); color: #ec4899; border: 1px solid rgba(236,72,153,0.25); font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 10px;">
                        Complete Site
                    </span>
                </div>
                <p style="font-size: 13px; color: var(--ff-muted); margin: 0 0 16px 0; line-height: 1.5;">
                    Packages entire HTML templates, public folder, application codebase, uploaded storage files, and SQL database dump.
                </p>
            </div>
            <button type="button" class="ff-btn is-primary js-trigger-backup" data-type="codebase" style="width: 100%; height: 42px; font-weight: 700; justify-content: center; background: #ec4899; border-color: #ec4899;">
                <span class="js-btn-icon">⚡</span>
                <span class="js-btn-label">Backup Whole Site &amp; HTML</span>
            </button>
        </div>

        <!-- Full System Backup -->
        <div class="ff-card" style="padding: 24px; border-left: 4px solid #6366f1; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <h3 style="margin: 0; font-size: 15.5px; font-weight: 700; color: var(--ff-text); display: flex; align-items: center; gap: 8px;">
                        <span>📦</span> Full Storage &amp; DB
                    </h3>
                    <span style="background: rgba(99,102,241,0.12); color: #6366f1; border: 1px solid rgba(99,102,241,0.25); font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 10px;">
                        Recommended
                    </span>
                </div>
                <p style="font-size: 13px; color: var(--ff-muted); margin: 0 0 16px 0; line-height: 1.5;">
                    Packages the complete SQL database dump, all encrypted storage files, and system manifest into a single ZIP archive.
                </p>
            </div>
            <button type="button" class="ff-btn is-primary js-trigger-backup" data-type="full" style="width: 100%; height: 42px; font-weight: 700; justify-content: center; background: #6366f1; border-color: #6366f1;">
                <span class="js-btn-icon">⚡</span>
                <span class="js-btn-label">Create Storage &amp; DB</span>
            </button>
        </div>

        <!-- Database Only Backup -->
        <div class="ff-card" style="padding: 24px; border-left: 4px solid #10b981; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <h3 style="margin: 0; font-size: 15.5px; font-weight: 700; color: var(--ff-text); display: flex; align-items: center; gap: 8px;">
                        <span>🗄️</span> Database Only
                    </h3>
                    <span style="background: rgba(16,185,129,0.12); color: #10b981; border: 1px solid rgba(16,185,129,0.25); font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 10px;">
                        Fast &amp; Lightweight
                    </span>
                </div>
                <p style="font-size: 13px; color: var(--ff-muted); margin: 0 0 16px 0; line-height: 1.5;">
                    Dumps all database tables, encrypted passwords, categories, bookmarks, users, and activity logs into a SQL archive.
                </p>
            </div>
            <button type="button" class="ff-btn js-trigger-backup" data-type="database" style="width: 100%; height: 42px; font-weight: 700; justify-content: center; border-color: #10b981; color: #10b981;">
                <span class="js-btn-icon">🗄️</span>
                <span class="js-btn-label">Backup Database Only</span>
            </button>
        </div>

        <!-- Files Only Backup -->
        <div class="ff-card" style="padding: 24px; border-left: 4px solid #3b82f6; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <h3 style="margin: 0; font-size: 15.5px; font-weight: 700; color: var(--ff-text); display: flex; align-items: center; gap: 8px;">
                        <span>📁</span> Storage Files Only
                    </h3>
                    <span style="background: rgba(59,130,246,0.12); color: #3b82f6; border: 1px solid rgba(59,130,246,0.25); font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 10px;">
                        Payload Storage
                    </span>
                </div>
                <p style="font-size: 13px; color: var(--ff-muted); margin: 0 0 16px 0; line-height: 1.5;">
                    Archives all physical envelope-encrypted storage files and user avatar files without dumping the database.
                </p>
            </div>
            <button type="button" class="ff-btn js-trigger-backup" data-type="files" style="width: 100%; height: 42px; font-weight: 700; justify-content: center; border-color: #3b82f6; color: #3b82f6;">
                <span class="js-btn-icon">📁</span>
                <span class="js-btn-label">Backup Storage Files</span>
            </button>
        </div>
    </div>

    <!-- Backups History Table -->
    <div class="ff-card" style="padding: 0; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--ff-border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--ff-text);">Backup Archives Repository</h3>
                <p style="margin: 4px 0 0 0; font-size: 12.5px; color: var(--ff-muted);">All generated backup packages stored under <code>storage/app/backups/</code>.</p>
            </div>
            <span style="font-size: 12px; color: var(--ff-muted);">Auto-sorted: newest first</span>
        </div>

        <div class="ff-admin-table-wrap">
            <table class="ff-admin-table">
                <thead>
                    <tr style="background: var(--ff-bg2); border-bottom: 1px solid var(--ff-border); text-align: left;">
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--ff-muted); text-transform: uppercase;">Archive Filename</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--ff-muted); text-transform: uppercase;">Type</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--ff-muted); text-transform: uppercase;">Size</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--ff-muted); text-transform: uppercase;">Created</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--ff-muted); text-transform: uppercase;">SHA-256 Checksum</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--ff-muted); text-transform: uppercase; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="backups-tbody">
                    @forelse($backups as $b)
                        <tr id="row-backup-{{ md5($b['filename']) }}" style="border-bottom: 1px solid var(--ff-border);">
                            <td style="padding: 14px 16px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: {{ $b['type'] === 'codebase' ? 'rgba(236,72,153,0.15)' : ($b['type'] === 'full' ? 'rgba(99,102,241,0.15)' : ($b['type'] === 'database' ? 'rgba(16,185,129,0.15)' : 'rgba(59,130,246,0.15)')) }};">
                                        <span style="font-size: 16px;">{{ $b['type'] === 'codebase' ? '🌐' : ($b['type'] === 'full' ? '📦' : ($b['type'] === 'database' ? '🗄️' : '📁')) }}</span>
                                    </div>
                                    <div>
                                        <div style="font-size: 13.5px; font-weight: 700; color: var(--ff-text); font-family: monospace;">{{ $b['filename'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 14px 16px;">
                                @if($b['type'] === 'codebase')
                                    <span style="background: rgba(236,72,153,0.12); color: #ec4899; border: 1px solid rgba(236,72,153,0.25); font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 10px;">Whole Site &amp; HTML</span>
                                @elseif($b['type'] === 'full')
                                    <span style="background: rgba(99,102,241,0.12); color: #6366f1; border: 1px solid rgba(99,102,241,0.25); font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 10px;">Full System</span>
                                @elseif($b['type'] === 'database')
                                    <span style="background: rgba(16,185,129,0.12); color: #10b981; border: 1px solid rgba(16,185,129,0.25); font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 10px;">Database</span>
                                @else
                                    <span style="background: rgba(59,130,246,0.12); color: #3b82f6; border: 1px solid rgba(59,130,246,0.25); font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 10px;">Files Only</span>
                                @endif
                            </td>
                            <td style="padding: 14px 16px; font-size: 13px; font-weight: 700; color: var(--ff-text);">
                                {{ $b['size_formatted'] }}
                            </td>
                            <td style="padding: 14px 16px;">
                                <div style="font-size: 13px; font-weight: 600; color: var(--ff-text);">{{ $b['time_ago'] }}</div>
                                <div style="font-size: 11px; color: var(--ff-muted);">{{ $b['created_at'] }}</div>
                            </td>
                            <td style="padding: 14px 16px;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <code class="ff-mono" style="font-size: 11px; color: var(--ff-muted); background: var(--ff-bg2); padding: 2px 6px; border-radius: 4px; max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        {{ substr($b['checksum'], 0, 16) }}...
                                    </code>
                                    <button type="button" class="ff-btn is-sm js-copy-hash" data-hash="{{ $b['checksum'] }}" title="Copy Full SHA-256 Checksum" style="padding: 2px 6px; font-size: 11px;">
                                        📋
                                    </button>
                                </div>
                            </td>
                            <td style="padding: 14px 16px; text-align: right;">
                                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 6px;">
                                    <a href="{{ route('panel.admin.backups.download', $b['filename']) }}" class="ff-btn is-sm is-primary" style="font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                        <span>⬇️</span> Download
                                    </a>
                                    
                                    @if(!empty($remoteConfigs['ftp']['enabled']))
                                        <button type="button" class="ff-btn is-sm js-push-remote" data-driver="ftp" data-filename="{{ $b['filename'] }}" title="Push archive to FTP offsite storage" style="font-size: 11.5px; font-weight: 600;">
                                            📡 FTP
                                        </button>
                                    @endif

                                    @if(!empty($remoteConfigs['sftp']['enabled']))
                                        <button type="button" class="ff-btn is-sm js-push-remote" data-driver="sftp" data-filename="{{ $b['filename'] }}" title="Push archive to SFTP offsite storage" style="font-size: 11.5px; font-weight: 600;">
                                            🛡️ SFTP
                                        </button>
                                    @endif

                                    <button type="button" class="ff-btn is-sm js-delete-backup" data-filename="{{ $b['filename'] }}" style="font-size: 12px; color: #ef4444; border-color: rgba(239,68,68,0.3);">
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="empty-backups-row">
                            <td colspan="6" style="padding: 48px 20px; text-align: center;">
                                <div style="font-size: 40px; margin-bottom: 12px;">📦</div>
                                <h4 style="margin: 0 0 6px 0; font-size: 16px; font-weight: 700; color: var(--ff-text);">No Backup Archives Yet</h4>
                                <p style="font-size: 13px; color: var(--ff-muted); margin: 0 0 16px 0;">Create your first full system or database backup using the triggers above.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('push-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const alertBox = document.getElementById('backup-alert-container');
        const triggerBtns = document.querySelectorAll('.js-trigger-backup');
        const deleteBtns = document.querySelectorAll('.js-delete-backup');
        const copyBtns = document.querySelectorAll('.js-copy-hash');
        const panelRemoteConfig = document.getElementById('panel-remote-config');
        const btnToggleRemoteConfig = document.getElementById('btn-toggle-remote-config');
        const btnCloseRemoteConfig = document.getElementById('btn-close-remote-config');
        const selectActiveRemoteTarget = document.getElementById('select-active-remote-target');

        // Toggle Remote Storage Config Panel
        if (btnToggleRemoteConfig && panelRemoteConfig) {
            btnToggleRemoteConfig.addEventListener('click', () => {
                const isHidden = panelRemoteConfig.style.display === 'none';
                panelRemoteConfig.style.display = isHidden ? 'block' : 'none';
                if (isHidden) {
                    panelRemoteConfig.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        }
        if (btnCloseRemoteConfig && panelRemoteConfig) {
            btnCloseRemoteConfig.addEventListener('click', () => {
                panelRemoteConfig.style.display = 'none';
            });
        }

        // Copy Hash Handler
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.js-copy-hash');
            if (!btn) return;
            const hash = btn.getAttribute('data-hash');
            if (hash) {
                if (window.ff && typeof window.ff.copy === 'function') {
                    window.ff.copy(hash, 'SHA-256 Checksum copied!');
                } else if (window.copyToClipboard) {
                    window.copyToClipboard(hash, 'SHA-256 Checksum copied!');
                }
                const original = btn.innerHTML;
                btn.innerHTML = '✓';
                setTimeout(() => btn.innerHTML = original, 1500);
            }
        });

        // Trigger Backup Creation via AJAX
        triggerBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const type = this.getAttribute('data-type') || 'full';
                const remoteTarget = selectActiveRemoteTarget ? selectActiveRemoteTarget.value : 'none';
                const labelSpan = this.querySelector('.js-btn-label');
                const iconSpan = this.querySelector('.js-btn-icon');
                const originalLabel = labelSpan ? labelSpan.textContent : '';
                const originalIcon = iconSpan ? iconSpan.textContent : '';

                if (alertBox) {
                    alertBox.innerHTML = `
                        <div class="ff-alert" style="background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.3); color: var(--ff-text); padding: 14px; border-radius: 8px; display: flex; align-items: center; gap: 10px;">
                            <span style="animation: spin 1s linear infinite; display: inline-block;">⏳</span>
                            <div>
                                <strong>Generating ${type.toUpperCase()} Backup Package...</strong>
                                <div style="font-size: 12px; color: var(--ff-muted);">Archiving tables, building ZIP package, and syncing offsite targets. Please wait.</div>
                            </div>
                        </div>
                    `;
                }

                triggerBtns.forEach(b => b.disabled = true);
                if (labelSpan) labelSpan.textContent = 'Processing...';
                if (iconSpan) iconSpan.textContent = '⏳';

                fetch("{{ route('panel.admin.backups.create') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ type: type, remote_target: remoteTarget })
                })
                .then(res => res.json())
                .then(res => {
                    triggerBtns.forEach(b => b.disabled = false);
                    if (labelSpan) labelSpan.textContent = originalLabel;
                    if (iconSpan) iconSpan.textContent = originalIcon;

                    if (res.ok && res.backup) {
                        const b = res.backup;
                        if (alertBox) {
                            alertBox.innerHTML = `
                                <div class="ff-alert is-success" style="padding: 14px; border-radius: 8px; margin-bottom: 20px;">
                                    <strong>✓ Backup Created Successfully!</strong> ${res.message}
                                    <div style="margin-top: 6px; font-size: 12px;">
                                        File: <code>${b.filename}</code> • Size: <strong>${b.size_formatted}</strong> • Checksum: <code>${b.checksum.substr(0, 16)}...</code>
                                    </div>
                                </div>
                            `;
                        }

                        setTimeout(() => window.location.reload(), 1400);
                    } else {
                        if (alertBox) {
                            alertBox.innerHTML = `
                                <div class="ff-alert is-danger" style="margin-bottom: 20px;">
                                    <strong>⚠️ Backup Creation Failed:</strong> ${res.message || 'Unknown error occurred'}
                                </div>
                            `;
                        }
                    }
                })
                .catch(err => {
                    triggerBtns.forEach(b => b.disabled = false);
                    if (labelSpan) labelSpan.textContent = originalLabel;
                    if (iconSpan) iconSpan.textContent = originalIcon;

                    if (alertBox) {
                        alertBox.innerHTML = `
                            <div class="ff-alert is-danger" style="margin-bottom: 20px;">
                                <strong>⚠️ Request Error:</strong> ${err.message || 'Network error'}
                            </div>
                        `;
                    }
                });
            });
        });

        // Test FTP Connection
        const btnTestFtp = document.getElementById('btn-test-ftp');
        if (btnTestFtp) {
            btnTestFtp.addEventListener('click', function() {
                const orig = this.innerHTML;
                this.innerHTML = '<span>⏳</span> Testing FTP Connection...';
                this.disabled = true;

                fetch("{{ route('panel.admin.backups.testRemote') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        driver: 'ftp',
                        host: document.getElementById('ftp_host')?.value || '',
                        port: document.getElementById('ftp_port')?.value || 21,
                        username: document.getElementById('ftp_username')?.value || '',
                        password: document.getElementById('ftp_password')?.value || '',
                        path: document.getElementById('ftp_path')?.value || '/backups/',
                        ssl: document.getElementById('ftp_ssl')?.checked ? 1 : 0,
                        passive: document.getElementById('ftp_passive')?.checked ? 1 : 0,
                    })
                })
                .then(res => res.json())
                .then(res => {
                    btnTestFtp.disabled = false;
                    btnTestFtp.innerHTML = orig;
                    alert((res.ok ? '✓ ' : '⚠️ ') + res.message);
                })
                .catch(err => {
                    btnTestFtp.disabled = false;
                    btnTestFtp.innerHTML = orig;
                    alert('Connection test failed: ' + err.message);
                });
            });
        }

        // Test SFTP Connection
        const btnTestSftp = document.getElementById('btn-test-sftp');
        if (btnTestSftp) {
            btnTestSftp.addEventListener('click', function() {
                const orig = this.innerHTML;
                this.innerHTML = '<span>⏳</span> Testing SFTP Connection...';
                this.disabled = true;

                fetch("{{ route('panel.admin.backups.testRemote') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        driver: 'sftp',
                        host: document.getElementById('sftp_host')?.value || '',
                        port: document.getElementById('sftp_port')?.value || 22,
                        username: document.getElementById('sftp_username')?.value || '',
                        password: document.getElementById('sftp_password')?.value || '',
                        path: document.getElementById('sftp_path')?.value || '/backups/',
                    })
                })
                .then(res => res.json())
                .then(res => {
                    btnTestSftp.disabled = false;
                    btnTestSftp.innerHTML = orig;
                    alert((res.ok ? '✓ ' : '⚠️ ') + res.message);
                })
                .catch(err => {
                    btnTestSftp.disabled = false;
                    btnTestSftp.innerHTML = orig;
                    alert('Connection test failed: ' + err.message);
                });
            });
        }

        // Manual Push to Remote (FTP / SFTP)
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.js-push-remote');
            if (!btn) return;

            const driver = btn.getAttribute('data-driver');
            const filename = btn.getAttribute('data-filename');
            const orig = btn.innerHTML;

            if (!confirm(`Push archive "${filename}" to ${driver.toUpperCase()} offsite server?`)) {
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '⏳ Uploading...';

            fetch(`{{ url('/panel/admin/backups/upload-remote') }}/${encodeURIComponent(filename)}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ driver: driver })
            })
            .then(res => res.json())
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = orig;

                if (res.ok) {
                    if (alertBox) {
                        alertBox.innerHTML = `
                            <div class="ff-alert is-success" style="margin-bottom: 20px;">
                                <strong>✓ Offsite Replicated!</strong> ${res.message}
                            </div>
                        `;
                    }
                } else {
                    alert(res.message || 'Remote upload failed.');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = orig;
                alert('Upload error: ' + err.message);
            });
        });

        // Delete Backup Action
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.js-delete-backup');
            if (!btn) return;

            const filename = btn.getAttribute('data-filename');
            if (!confirm(`Are you sure you want to permanently delete backup archive: "${filename}"?`)) {
                return;
            }

            fetch(`{{ url('/panel/admin/backups/delete') }}/${encodeURIComponent(filename)}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(res => {
                if (res.ok) {
                    const row = btn.closest('tr');
                    if (row) row.remove();

                    if (alertBox) {
                        alertBox.innerHTML = `
                            <div class="ff-alert is-success" style="margin-bottom: 20px;">
                                ${res.message}
                            </div>
                        `;
                    }
                } else {
                    alert(res.message || 'Failed to delete backup.');
                }
            })
            .catch(err => alert('Delete error: ' + err.message));
        });
    });
</script>
<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
@endsection
