@extends('layout.backend')
@push('title', 'Passwords')

@section('content')
    <div class="ff-row-between" style="align-items:flex-start; margin-bottom:22px; flex-wrap:wrap; gap:14px;">
        <div>
            <h1 class="ff-h1">Passwords</h1>
            <p class="ff-sub" style="margin-bottom:0;">Store login credentials and API keys securely</p>
        </div>
        <div class="ff-row" style="gap:8px; flex-wrap:wrap; align-items:center;">
            <button type="button" class="ff-btn" id="openImportPasswordsModalBtn" style="display:inline-flex; align-items:center; gap:6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                Import
            </button>
            <button type="button" class="ff-btn" id="openExportPasswordsModalBtn" style="display:inline-flex; align-items:center; gap:6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export
            </button>
            <a href="{{ route('panel.addpasswordview') }}" class="ff-btn ff-btn-primary" style="display:inline-flex; align-items:center; gap:6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                Add Password
            </a>
        </div>
    </div>

    @php
        $currentPerPage = \App\Helpers\SettingHelper::getItemsPerPage(12);
    @endphp

    <div class="ff-toolbar" style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:20px; flex-wrap:wrap;">
        <form action="{{ route('panel.passwords') }}" method="GET" class="ff-input-icon" style="flex:1; min-width:240px; margin:0;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="search" name="q" placeholder="Search passwords..." value="{{ $search ?? '' }}"
                autocomplete="off">
        </form>

        <div style="display:inline-flex; align-items:center; gap:6px; background:var(--ff-surface); border:1px solid var(--ff-border); padding:4px 10px; border-radius:10px; flex-shrink:0;">
            <span style="font-size:12px; color:var(--ff-muted); font-weight:600;">Show:</span>
            <select id="pwPerPageSelect" class="ff-select" style="border:none; background:transparent; padding:2px 4px; font-size:12px; font-weight:600; cursor:pointer; color:var(--ff-text); outline:none;" title="Items visible per page">
                <option value="12" {{ $currentPerPage == 12 ? 'selected' : '' }}>12 / page</option>
                <option value="24" {{ $currentPerPage == 24 ? 'selected' : '' }}>24 / page</option>
                <option value="48" {{ $currentPerPage == 48 ? 'selected' : '' }}>48 / page</option>
                <option value="96" {{ $currentPerPage == 96 ? 'selected' : '' }}>96 / page</option>
            </select>
        </div>
    </div>

    <div class="ff-list">
        <div class="ff-list-head ff-hide-mobile">
            <span class="ff-list-col ff-grow">Title / Account</span>
            <span class="ff-list-col" style="width:200px;">Password</span>
            <span class="ff-list-col" style="width:230px;">Key Fields</span>
            <span class="ff-list-col" style="width:40px;"></span>
        </div>

        @forelse ($passwords as $pw)
            @php
                $primary = $pw->auth_fields[0] ?? null;
                $extra = max(0, count($pw->auth_fields) - 1);
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
                    <span class="ff-min0">
                        <span class="ff-list-title" style="display:block;">{{ $pw->title }}</span>
                        <span class="ff-list-subtitle" style="display:block;">{{ $pw->username }}</span>
                    </span>
                </button>

                <span class="ff-list-cell ff-hide-mobile ff-row" style="width:200px; gap:8px;">
                    <span class="ff-mono ff-secret-text" data-reveal-token="{{ $pw->reveal_token }}" style="font-size:13px;">••••••••</span>
                    <button type="button" class="ff-menu-btn ff-jit-reveal-btn" data-reveal-token="{{ $pw->reveal_token }}"
                        style="width:24px; height:24px;" aria-label="Reveal password">
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
                        @if ($extra > 0)
                            <button type="button" class="ff-badge-type ff-pw-open"
                                style="border:none; cursor:pointer; flex-shrink:0;"
                                data-reveal-token="{{ $pw->reveal_token }}">+{{ $extra }} more</button>
                        @endif
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
                        <rect x="3" y="11" width="18" height="10" rx="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                </span>
                <div class="ff-section-title" style="font-size:17px;">No credentials yet</div>
                <p class="ff-empty-text" style="max-width:380px; margin:0;">
                    Save logins, API keys and tokens so they live alongside your files and links.
                </p>
                <a href="{{ route('panel.addpasswordview') }}" class="ff-btn ff-btn-primary" style="margin-top:6px;">
                    Add First Credential
                </a>
            </div>
        @endforelse
    </div>

    {{-- Hidden forms backing the state-changing menu items --}}
    @foreach ($passwords as $pw)
        <form id="pw-delete-{{ $pw->reveal_token }}" action="{{ route('panel.deletepassword', encrypt($pw->id)) }}" method="POST" hidden>@csrf</form>
        <form id="pw-hide-{{ $pw->reveal_token }}" action="{{ route('panel.toggleHidePassword') }}" method="POST" hidden>
            @csrf
            <input type="hidden" name="password_id" value="{{ encrypt($pw->id) }}">
        </form>
    @endforeach

    {{-- ============================== Detail modal ============================== --}}
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
           {{-- ==================== PASSWORDS IMPORT MODAL ==================== --}}
    <div id="importPasswordsModal" class="ff-modal-overlay" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.65); align-items:center; justify-content:center; padding:16px;">
        <div class="ff-modal-card" style="background:var(--ff-card, #ffffff) !important; color:var(--ff-text, #0f172a) !important; border:1px solid var(--ff-border, #e2e8f0); border-radius:14px; width:100%; max-width:680px; max-height:90vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.3);">
            <div style="padding:18px 24px; border-bottom:1px solid var(--ff-border, #e2e8f0); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="ff-section-icon" style="width:34px; height:34px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; background:var(--ff-icon-bg, color-mix(in srgb, var(--ff-accent) 15%, transparent)); color:var(--ff-accent);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                    </span>
                    <div>
                        <div style="font-weight:700; font-size:16px; color:var(--ff-text, #0f172a);">Import Passwords from Excel / CSV</div>
                        <div style="font-size:12px; color:var(--ff-text-2, var(--ff-muted, #64748b));">Chrome, Bitwarden, 1Password, LastPass, or custom spreadsheets</div>
                    </div>
                </div>
                <button type="button" class="ff-menu-btn closeImportPasswordsModalBtn" style="border:none; background:transparent; cursor:pointer; color:var(--ff-muted, #64748b);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <div style="padding:24px; overflow-y:auto; flex:1;" id="importPasswordsModalBody">
                <div id="importPasswordsStep1">
                    <div id="importPasswordsDropzone" style="border:2px dashed var(--ff-border, #cbd5e1); border-radius:10px; padding:36px 20px; text-align:center; cursor:pointer; background:var(--ff-bg-2, rgba(0,0,0,0.02)); transition:all 0.2s ease;">
                        <input type="file" id="importPasswordsFileInput" accept=".xlsx, .xls, .csv" style="display:none;">
                        <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent,#E0392E)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 12px; display:block;">
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="8" y1="13" x2="16" y2="13"/>
                            <line x1="8" y1="17" x2="16" y2="17"/>
                            <line x1="10" y1="9" x2="8" y2="9"/>
                        </svg>
                        <div style="font-weight:600; font-size:15px; color:var(--ff-text, #0f172a);">Drag &amp; drop your Excel or CSV file here</div>
                        <div style="font-size:13px; color:var(--ff-text-2, var(--ff-muted, #64748b)); margin-top:4px;">or <span style="color:var(--ff-accent,#E0392E); font-weight:600;">browse files</span> from your computer</div>
                        <div style="font-size:11px; color:var(--ff-muted, #94a3b8); margin-top:10px;">Supports .xlsx, .xls, and .csv files from Chrome, Bitwarden, LastPass, 1Password, etc.</div>
                    </div>

                    <div style="margin-top:16px; padding:12px 16px; border-radius:8px; background:var(--ff-bg-2, rgba(0,0,0,0.03)); border:1px solid var(--ff-border, #e2e8f0); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                        <div>
                            <div style="font-size:12px; font-weight:600; color:var(--ff-text, #0f172a);">Need a starter template?</div>
                            <div style="font-size:11px; color:var(--ff-text-2, var(--ff-muted, #64748b));">Download pre-formatted template with sample columns</div>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button type="button" class="ff-btn ff-btn-sm" id="downloadPasswordsSampleXlsxBtn" style="font-size:11px; padding:4px 10px; display:inline-flex; align-items:center; gap:4px;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                Excel (.xlsx)
                            </button>
                            <button type="button" class="ff-btn ff-btn-sm" id="downloadPasswordsSampleCsvBtn" style="font-size:11px; padding:4px 10px; display:inline-flex; align-items:center; gap:4px;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                CSV (.csv)
                            </button>
                        </div>
                    </div>
                </div>

                <div id="importPasswordsStep2" style="display:none;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; padding-bottom:10px; border-bottom:1px solid var(--ff-border, #e2e8f0);">
                        <div style="font-size:13px; font-weight:600; color:var(--ff-text, #0f172a); display:flex; align-items:center; gap:8px;">
                            <span style="display:inline-flex; width:8px; height:8px; border-radius:50%; background:#10b981;"></span>
                            <span id="importPasswordsFileNameLabel">file.xlsx</span>
                            <span id="importPasswordsRowCountBadge" style="font-size:11px; padding:2px 8px; border-radius:12px; background:rgba(16,185,129,0.15); color:#10b981; font-weight:600;">0 rows</span>
                        </div>
                        <button type="button" id="changeImportPasswordsFileBtn" style="background:none; border:none; color:var(--ff-accent,#E0392E); font-size:12px; font-weight:600; cursor:pointer;">Choose another file</button>
                    </div>

                    <div style="font-size:13px; font-weight:600; margin-bottom:10px; color:var(--ff-text, #0f172a);">Map Spreadsheet Columns to Password Fields:</div>
                    
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:18px;">
                        <div class="ff-field">
                            <label class="ff-label" style="font-size:12px; margin-bottom:4px;">Title / Service <span style="color:#ef4444;">*</span></label>
                            <select id="mapPwTitle" class="ff-select map-pw-select" style="width:100%;"></select>
                        </div>
                        <div class="ff-field">
                            <label class="ff-label" style="font-size:12px; margin-bottom:4px;">Username / Email</label>
                            <select id="mapPwUsername" class="ff-select map-pw-select" style="width:100%;"></select>
                        </div>
                        <div class="ff-field">
                            <label class="ff-label" style="font-size:12px; margin-bottom:4px;">Password</label>
                            <select id="mapPwPassword" class="ff-select map-pw-select" style="width:100%;"></select>
                        </div>
                        <div class="ff-field">
                            <label class="ff-label" style="font-size:12px; margin-bottom:4px;">URL / Website</label>
                            <select id="mapPwUrl" class="ff-select map-pw-select" style="width:100%;"></select>
                        </div>
                        <div class="ff-field" style="grid-column: span 2;">
                            <label class="ff-label" style="font-size:12px; margin-bottom:4px;">Notes / Comments</label>
                            <select id="mapPwNotes" class="ff-select map-pw-select" style="width:100%;"></select>
                        </div>
                    </div>

                    <div style="font-size:12px; font-weight:600; margin-bottom:6px; color:var(--ff-text-2, var(--ff-muted, #64748b));">Preview of First 3 Records:</div>
                    <div style="overflow-x:auto; border:1px solid var(--ff-border, #e2e8f0); border-radius:8px; margin-bottom:10px;">
                        <table style="width:100%; font-size:12px; text-align:left; border-collapse:collapse;" id="importPasswordsPreviewTable">
                            <thead>
                                <tr style="background:var(--ff-bg-2, rgba(0,0,0,0.03)); border-bottom:1px solid var(--ff-border, #e2e8f0);">
                                    <th style="padding:8px 10px;">Title</th>
                                    <th style="padding:8px 10px;">Username</th>
                                    <th style="padding:8px 10px;">Password</th>
                                    <th style="padding:8px 10px;">URL</th>
                                    <th style="padding:8px 10px;">Notes</th>
                                </tr>
                            </thead>
                            <tbody id="importPasswordsPreviewTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div style="padding:14px 24px; border-top:1px solid var(--ff-border, #e2e8f0); background:var(--ff-bg-2, rgba(0,0,0,0.02)); display:flex; align-items:center; justify-content:flex-end; gap:10px;">
                <button type="button" class="ff-btn closeImportPasswordsModalBtn">Cancel</button>
                <button type="button" id="confirmImportPasswordsBtn" class="ff-btn ff-btn-primary" style="display:none;">Import Passwords Now</button>
            </div>
        </div>
    </div>

    {{-- ==================== PASSWORDS EXPORT MODAL ==================== --}}
    <div id="exportPasswordsModal" class="ff-modal-overlay" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.65); align-items:center; justify-content:center; padding:16px;">
        <div class="ff-modal-card" style="background:var(--ff-card, #ffffff) !important; color:var(--ff-text, #0f172a) !important; border:1px solid var(--ff-border, #e2e8f0); border-radius:14px; width:100%; max-width:500px; max-height:90vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.3);">
            <div style="padding:18px 24px; border-bottom:1px solid var(--ff-border, #e2e8f0); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="ff-section-icon" style="width:34px; height:34px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; background:var(--ff-icon-bg, color-mix(in srgb, var(--ff-accent) 15%, transparent)); color:var(--ff-accent);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                    </span>
                    <div>
                        <div style="font-weight:700; font-size:16px; color:var(--ff-text, #0f172a);">Export Password Vault</div>
                        <div style="font-size:12px; color:var(--ff-text-2, var(--ff-muted, #64748b));">Select custom fields and file format</div>
                    </div>
                </div>
                <button type="button" class="ff-menu-btn closeExportPasswordsModalBtn" style="border:none; background:transparent; cursor:pointer; color:var(--ff-muted, #64748b);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <div style="padding:22px 24px; overflow-y:auto; flex:1;">
                <div style="margin-bottom:18px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                        <span style="font-size:13px; font-weight:600; color:var(--ff-text-main, #0f172a);">Select Fields to Include:</span>
                        <button type="button" id="toggleAllExportPwFields" style="background:none; border:none; color:var(--ff-accent,#E0392E); font-size:12px; font-weight:600; cursor:pointer;">Select All</button>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                            <input type="checkbox" name="exportPwFields[]" value="title" checked> Title
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                            <input type="checkbox" name="exportPwFields[]" value="username" checked> Username/Email
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                            <input type="checkbox" name="exportPwFields[]" value="password" checked> Password
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                            <input type="checkbox" name="exportPwFields[]" value="url" checked> URL / Website
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                            <input type="checkbox" name="exportPwFields[]" value="notes" checked> Notes
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                            <input type="checkbox" name="exportPwFields[]" value="created_at" checked> Created Date
                        </label>
                    </div>
                </div>

                <div class="ff-alert is-warning" style="margin-bottom:18px; font-size:12px; padding:10px 14px; border-radius:8px; background:rgba(234,179,8,0.1); border:1px solid rgba(234,179,8,0.3); color:#ca8a04;">
                    <strong>Security Notice:</strong> Exported files contain decrypted credentials. Store exported files in a secure location and delete them when finished.
                </div>

                <div>
                    <div style="font-size:13px; font-weight:600; margin-bottom:10px; color:var(--ff-text-main, #0f172a);">File Format:</div>
                    <div style="display:flex; gap:12px;">
                        <label style="flex:1; border:1px solid var(--ff-border, #cbd5e1); border-radius:8px; padding:12px; display:flex; align-items:center; gap:10px; cursor:pointer; background:var(--ff-surface, #fff);">
                            <input type="radio" name="exportPwFormat" value="xlsx" checked>
                            <div>
                                <div style="font-weight:600; font-size:13px; color:var(--ff-text-main, #0f172a);">Excel (.xlsx)</div>
                                <div style="font-size:11px; color:var(--ff-text-muted, #64748b);">Spreadsheet</div>
                            </div>
                        </label>
                        <label style="flex:1; border:1px solid var(--ff-border, #cbd5e1); border-radius:8px; padding:12px; display:flex; align-items:center; gap:10px; cursor:pointer; background:var(--ff-surface, #fff);">
                            <input type="radio" name="exportPwFormat" value="csv">
                            <div>
                                <div style="font-weight:600; font-size:13px; color:var(--ff-text-main, #0f172a);">CSV (.csv)</div>
                                <div style="font-size:11px; color:var(--ff-text-muted, #64748b);">Comma-separated</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div style="padding:14px 24px; border-top:1px solid var(--ff-border, #e2e8f0); background:var(--ff-surface-subtle, rgba(0,0,0,0.02)); display:flex; align-items:center; justify-content:flex-end; gap:10px;">
                <button type="button" class="ff-btn closeExportPasswordsModalBtn">Cancel</button>
                <button type="button" id="executeExportPasswordsBtn" class="ff-btn ff-btn-primary">Download File</button>
            </div>
        </div>
    </div>

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
                        <label class="ff-label" style="font-size:12px; margin-bottom:4px;">Expiration Window</label>
                        <select id="sharePasswordExpirySelect" class="ff-select" style="width:100%;">
                            <option value="60">1 Hour</option>
                            <option value="1440" selected>24 Hours</option>
                            <option value="10080">7 Days</option>
                        </select>
                    </div>

                    <div class="ff-field">
                        <label class="ff-label" style="font-size:12px; margin-bottom:4px;">Recipient PIN (Optional)</label>
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
@endsection

@section('push-script')
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script>
        const ffPasswordData = @json($detailPayload);

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

            // Check if already in active memory cache (only if lifetime > 0)
            if (ffRevealLifetime > 0 && ffSecretCache.has(cacheKey)) {
                revealSecretInDom(btn, textSpan, ffSecretCache.get(cacheKey), cacheKey);
                return;
            }

            // Perform authenticated JIT fetch
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
                        // Open 2FA Challenge Modal
                        openRevealAuthModal(function() {
                            // Retry reveal on successful verification
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

            // Set 20-second automatic security wipe timer
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

            // Fetch decrypted secret with vault passcode / 2FA verification
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

        // ------------------------------------------------ Detail Modal with JIT
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

                        <div class="ff-divider" style="margin:18px 0 0;"></div>
                        <div class="ff-form-actions" style="margin-top:16px;">
                            <button type="button" class="ff-btn" onclick="ffClosePasswordDetail()">Close</button>
                            <button type="button" class="ff-btn open-password-share-modal" data-pw-id="${pw.id}" data-title="${ffEscape(pw.title)}" style="display:inline-flex; align-items:center; gap:6px;">
                                <i data-lucide="share-2" class="w-4 h-4"></i> Share Secret
                            </button>
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

        // =========================================================================
        // IMPORT PASSWORDS WITH CUSTOM FIELD MAPPING
        // =========================================================================
        let parsedPwRows = [];
        let pwFileColumns = [];

        const importPwModal = document.getElementById('importPasswordsModal');
        const exportPwModal = document.getElementById('exportPasswordsModal');

        $('#openImportPasswordsModalBtn').on('click', function() {
            resetImportPwModal();
            importPwModal.style.display = 'flex';
        });
        $('.closeImportPasswordsModalBtn').on('click', function() {
            importPwModal.style.display = 'none';
        });

        $('#openExportPasswordsModalBtn').on('click', function() {
            exportPwModal.style.display = 'flex';
        });
        $('.closeExportPasswordsModalBtn').on('click', function() {
            exportPwModal.style.display = 'none';
        });

        const pwDropzone = document.getElementById('importPasswordsDropzone');
        const pwFileInput = document.getElementById('importPasswordsFileInput');

        pwDropzone.addEventListener('click', () => pwFileInput.click());
        pwDropzone.addEventListener('dragover', (e) => { e.preventDefault(); pwDropzone.style.borderColor = 'var(--ff-accent, #E0392E)'; });
        pwDropzone.addEventListener('dragleave', () => { pwDropzone.style.borderColor = 'var(--ff-border, #cbd5e1)'; });
        pwDropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            pwDropzone.style.borderColor = 'var(--ff-border, #cbd5e1)';
            if (e.dataTransfer.files.length) handlePwFile(e.dataTransfer.files[0]);
        });
        pwFileInput.addEventListener('change', (e) => {
            if (e.target.files.length) handlePwFile(e.target.files[0]);
        });

        $('#changeImportPasswordsFileBtn').on('click', function() {
            resetImportPwModal();
            pwFileInput.click();
        });

        function resetImportPwModal() {
            parsedPwRows = [];
            pwFileColumns = [];
            pwFileInput.value = '';
            $('#importPasswordsStep1').show();
            $('#importPasswordsStep2').hide();
            $('#confirmImportPasswordsBtn').hide();
        }

        function handlePwFile(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const firstSheet = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[firstSheet];
                    const json = XLSX.utils.sheet_to_json(worksheet, { defval: '' });

                    if (!json || json.length === 0) {
                        window.ff.toast('The selected file has no data rows.', 'error');
                        return;
                    }

                    parsedPwRows = json;
                    pwFileColumns = Object.keys(json[0] || {});

                    $('#importPasswordsFileNameLabel').text(file.name);
                    $('#importPasswordsRowCountBadge').text(`${json.length} rows found`);

                    populatePwMappingDropdowns();
                    updatePwPreviewTable();

                    $('#importPasswordsStep1').hide();
                    $('#importPasswordsStep2').show();
                    $('#confirmImportPasswordsBtn').show();
                } catch (err) {
                    window.ff.toast('Failed to parse spreadsheet: ' + err.message, 'error');
                }
            };
            reader.readAsArrayBuffer(file);
        }

        function populatePwMappingDropdowns() {
            const selectIds = ['mapPwTitle', 'mapPwUsername', 'mapPwPassword', 'mapPwUrl', 'mapPwNotes'];
            
            selectIds.forEach(id => {
                const select = $(`#${id}`);
                select.empty();
                const isRequired = id === 'mapPwTitle';
                select.append(`<option value="">${isRequired ? '-- Select Column --' : '-- None (Skip) --'}</option>`);

                pwFileColumns.forEach(col => {
                    select.append(`<option value="${escapeHtml(col)}">${escapeHtml(col)}</option>`);
                });
            });

            // Smart Auto-Matching for Passwords (Chrome, Bitwarden, 1Password, LastPass)
            pwFileColumns.forEach(col => {
                const lower = col.toLowerCase().trim();
                if (['title', 'name', 'account', 'service', 'login_uri'].some(k => lower.includes(k))) {
                    if (!$('#mapPwTitle').val()) $('#mapPwTitle').val(col);
                }
                if (['username', 'user', 'email', 'login', 'account_name'].some(k => lower.includes(k))) {
                    if (!$('#mapPwUsername').val()) $('#mapPwUsername').val(col);
                }
                if (['password', 'pass', 'pwd', 'secret'].some(k => lower.includes(k))) {
                    if (!$('#mapPwPassword').val()) $('#mapPwPassword').val(col);
                }
                if (['url', 'website', 'login_url', 'uri', 'link'].some(k => lower.includes(k))) {
                    if (!$('#mapPwUrl').val()) $('#mapPwUrl').val(col);
                }
                if (['notes', 'comments', 'extra', 'description'].some(k => lower.includes(k))) {
                    if (!$('#mapPwNotes').val()) $('#mapPwNotes').val(col);
                }
            });
        }

        $('.map-pw-select').on('change', function() {
            updatePwPreviewTable();
        });

        function updatePwPreviewTable() {
            const tbody = $('#importPasswordsPreviewTableBody');
            tbody.empty();

            const titleCol = $('#mapPwTitle').val();
            const userCol = $('#mapPwUsername').val();
            const passCol = $('#mapPwPassword').val();
            const urlCol = $('#mapPwUrl').val();
            const notesCol = $('#mapPwNotes').val();

            const sample = parsedPwRows.slice(0, 3);
            sample.forEach(row => {
                tbody.append(`
                    <tr style="border-bottom:1px solid var(--ff-border, #e2e8f0);">
                        <td style="padding:6px 10px; font-weight:600;">${escapeHtml(titleCol ? row[titleCol] : '-')}</td>
                        <td style="padding:6px 10px;">${escapeHtml(userCol ? row[userCol] : '-')}</td>
                        <td style="padding:6px 10px; font-family:monospace;">${passCol && row[passCol] ? '••••••••' : '-'}</td>
                        <td style="padding:6px 10px; color:var(--ff-accent,#E0392E);">${escapeHtml(urlCol ? row[urlCol] : '-')}</td>
                        <td style="padding:6px 10px; color:var(--ff-text-muted,#64748b);">${escapeHtml(notesCol ? row[notesCol] : '-')}</td>
                    </tr>
                `);
            });
        }

        $('#confirmImportPasswordsBtn').on('click', function() {
            const titleCol = $('#mapPwTitle').val();

            if (!titleCol) {
                window.ff.toast('Please map the Title column before importing.', 'error');
                return;
            }

            const userCol = $('#mapPwUsername').val();
            const passCol = $('#mapPwPassword').val();
            const urlCol = $('#mapPwUrl').val();
            const notesCol = $('#mapPwNotes').val();

            const mappedItems = parsedPwRows.map(row => ({
                title: String(row[titleCol] || '').trim(),
                username: userCol ? String(row[userCol] || '').trim() : '',
                password: passCol ? String(row[passCol] || '').trim() : '',
                url: urlCol ? String(row[urlCol] || '').trim() : '',
                notes: notesCol ? String(row[notesCol] || '').trim() : '',
            })).filter(item => item.title);

            if (mappedItems.length === 0) {
                window.ff.toast('No valid rows found with a Title.', 'error');
                return;
            }

            const btn = $(this);
            btn.prop('disabled', true).text('Importing…');

            $.ajax({
                type: 'POST',
                url: "{{ route('panel.passwords.import') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    items: mappedItems
                },
                success: function(res) {
                    if (res.success) {
                        window.ff.toast(res.message, 'success', 3000);
                        importPwModal.style.display = 'none';
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        window.ff.toast(res.message || 'Import failed', 'error');
                        btn.prop('disabled', false).text('Import Passwords Now');
                    }
                },
                error: function(xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Import failed';
                    window.ff.toast(msg, 'error');
                    btn.prop('disabled', false).text('Import Passwords Now');
                }
            });
        });

        // =========================================================================
        // EXPORT PASSWORDS WITH CUSTOM FIELD SELECTION
        // =========================================================================
        $('#toggleAllExportPwFields').on('click', function() {
            const checkboxes = $('input[name="exportPwFields[]"]');
            const allChecked = checkboxes.filter(':checked').length === checkboxes.length;
            checkboxes.prop('checked', !allChecked);
            $(this).text(allChecked ? 'Select All' : 'Deselect All');
        });

        $('#executeExportPasswordsBtn').on('click', function() {
            const selected = $('input[name="exportPwFields[]"]:checked').map(function() {
                return $(this).val();
            }).get();

            if (selected.length === 0) {
                window.ff.toast('Please select at least one field to export.', 'error');
                return;
            }

            const format = $('input[name="exportPwFormat"]:checked').val() || 'xlsx';
            const btn = $(this);
            btn.prop('disabled', true).text('Exporting…');

            $.ajax({
                type: 'POST',
                url: "{{ route('panel.passwords.export') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    fields: selected,
                    format: format
                },
                success: function(res) {
                    btn.prop('disabled', false).text('Download File');
                    exportPwModal.style.display = 'none';

                    if (format === 'xlsx') {
                        if (!res.rows || res.rows.length === 0) {
                            window.ff.toast('No passwords to export.', 'info');
                            return;
                        }
                        const ws = XLSX.utils.json_to_sheet(res.rows);
                        const wb = XLSX.utils.book_new();
                        XLSX.utils.book_append_sheet(wb, ws, "Passwords");
                        XLSX.writeFile(wb, `${res.filename || 'Passwords_Vault_Export'}.xlsx`);
                        window.ff.toast('Excel file downloaded successfully!', 'success');
                    } else {
                        // Direct CSV download
                        const blob = new Blob(["\uFEFF" + res], { type: 'text/csv;charset=utf-8;' });
                        const link = document.createElement('a');
                        link.href = URL.createObjectURL(blob);
                        link.download = `Passwords_Vault_Export_${new Date().toISOString().slice(0,10)}.csv`;
                        link.click();
                        window.ff.toast('CSV file downloaded successfully!', 'success');
                    }
                },
                error: function() {
                    btn.prop('disabled', false).text('Download File');
                    window.ff.toast('Export failed. Please try again.', 'error');
                }
            });
        });

        // =========================================================================
        // DOWNLOAD SAMPLE PASSWORDS TEMPLATE (.xlsx / .csv)
        // =========================================================================
        const samplePwData = [
            { "Title": "Google Account", "Username": "user@gmail.com", "Password": "ExamplePassword123!", "URL": "https://accounts.google.com", "Notes": "Primary email login" },
            { "Title": "GitHub Developer", "Username": "dev_user", "Password": "SuperSecretPass#2026", "URL": "https://github.com/login", "Notes": "Developer account" },
            { "Title": "AWS Cloud Console", "Username": "admin@aws.com", "Password": "CloudMasterKey987$", "URL": "https://console.aws.amazon.com", "Notes": "Root administrator" }
        ];

        $(document).on('click', '#downloadPasswordsSampleXlsxBtn', function(e) {
            e.preventDefault();
            const ws = XLSX.utils.json_to_sheet(samplePwData);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Passwords_Template");
            XLSX.writeFile(wb, "Passwords_Import_Template.xlsx");
            window.ff.toast('Excel template downloaded!', 'success', 2000);
        });

        $(document).on('click', '#downloadPasswordsSampleCsvBtn', function(e) {
            e.preventDefault();
            const ws = XLSX.utils.json_to_sheet(samplePwData);
            const csv = XLSX.utils.sheet_to_csv(ws);
            const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = "Passwords_Import_Template.csv";
            link.click();
            window.ff.toast('CSV template downloaded!', 'success', 2000);
        });

        // =========================================================================
        // PASSWORD SHARE MODAL HANDLERS (ZERO-KNOWLEDGE WITH AUTH & WARNING)
        // =========================================================================
        $(document).on('click', '.open-password-share-modal', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Automatically close detail modal and menus so only the Share Secret modal is shown
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
            navigator.clipboard.writeText(url);
            if (window.ff && window.ff.toast) {
                window.ff.toast('Secret link copied to clipboard!', 'success', 2000);
            }
        });

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, function(c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }
    </script>
@endsection
