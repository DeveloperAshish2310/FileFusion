@php
    $isVaultUnlocked = session('vault_group_authenticated') || session('hidden_links_authenticated') || session('hidden_files_authenticated');
@endphp

{{-- ==================== IMPORT LINKS MODAL ==================== --}}
<div id="importLinksModal" class="ff-modal-overlay" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.7); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:16px;">
    <div class="ff-modal-card" style="background:var(--ff-card, #1e293b) !important; color:var(--ff-text, #f8fafc) !important; border:1px solid var(--ff-border, rgba(255,255,255,0.1)); border-radius:16px; width:100%; max-width:620px; max-height:92vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 24px 48px rgba(0,0,0,0.4);">
        <div style="padding:18px 24px; border-bottom:1px solid var(--ff-border, rgba(255,255,255,0.08)); display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:12px;">
                <span class="ff-section-icon" style="width:38px; height:38px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; background:color-mix(in srgb, var(--ff-accent) 15%, transparent); color:var(--ff-accent);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                </span>
                <div>
                    <div style="font-weight:700; font-size:16.5px; color:var(--ff-text, #f8fafc);">Import &amp; Update Links</div>
                    <div style="font-size:12px; color:var(--ff-muted, #94a3b8);">Import spreadsheets, update existing items, and move categories</div>
                </div>
            </div>
            <button type="button" class="ff-menu-btn closeImportLinksModalBtn" style="border:none; background:transparent; cursor:pointer; color:var(--ff-muted, #94a3b8); border-radius:8px; padding:4px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div style="padding:22px 24px; overflow-y:auto; flex:1;" id="importLinksModalBody">
            <div id="importLinksStep1">
                <div id="importLinksDropzone" style="border:2px dashed var(--ff-border, rgba(255,255,255,0.18)); border-radius:12px; padding:36px 20px; text-align:center; cursor:pointer; background:var(--ff-bg-2, rgba(255,255,255,0.02)); transition:all 0.2s ease;">
                    <input type="file" id="importLinksFileInput" accept=".xlsx, .xls, .csv" style="display:none;">
                    <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent,#E0392E)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 12px; display:block;">
                        <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="8" y1="13" x2="16" y2="13"/>
                        <line x1="8" y1="17" x2="16" y2="17"/>
                        <line x1="10" y1="9" x2="8" y2="9"/>
                    </svg>
                    <div style="font-weight:700; font-size:15px; color:var(--ff-text, #f8fafc);">Drag &amp; drop your Excel or CSV spreadsheet here</div>
                    <div style="font-size:13px; color:var(--ff-muted, #94a3b8); margin-top:4px;">or <span style="color:var(--ff-accent,#E0392E); font-weight:600;">browse files</span> from your computer</div>
                    <div style="font-size:11.5px; color:var(--ff-muted, #64748b); margin-top:10px;">Supports .xlsx, .xls, and .csv with title, url, category, starred &amp; hidden tags</div>
                </div>

                <div style="margin-top:18px; padding:14px 16px; border-radius:12px; background:var(--ff-bg-2, rgba(255,255,255,0.03)); border:1px solid var(--ff-border, rgba(255,255,255,0.08)); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                    <div>
                        <div style="font-size:12.5px; font-weight:700; color:var(--ff-text, #f8fafc);">Need a pre-formatted template?</div>
                        <div style="font-size:11.5px; color:var(--ff-muted, #94a3b8);">Includes sample ID, Category, Starred, and Hidden columns</div>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button type="button" class="ff-btn ff-btn-sm" id="downloadLinksSampleXlsxBtn" style="font-size:11.5px; padding:6px 12px; display:inline-flex; align-items:center; gap:5px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Excel (.xlsx)
                        </button>
                        <button type="button" class="ff-btn ff-btn-sm" id="downloadLinksSampleCsvBtn" style="font-size:11.5px; padding:6px 12px; display:inline-flex; align-items:center; gap:5px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            CSV (.csv)
                        </button>
                    </div>
                </div>
            </div>

            <div id="importLinksStep2" style="display:none;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid var(--ff-border, rgba(255,255,255,0.08));">
                    <div style="font-size:13px; font-weight:700; color:var(--ff-text, #f8fafc); display:flex; align-items:center; gap:8px;">
                        <span style="display:inline-flex; width:8px; height:8px; border-radius:50%; background:#10b981;"></span>
                        <span id="importLinksFileNameLabel">file.xlsx</span>
                        <span id="importLinksRowCountBadge" style="font-size:11px; padding:2px 8px; border-radius:12px; background:rgba(16,185,129,0.15); color:#10b981; font-weight:700;">0 rows</span>
                    </div>
                    <button type="button" id="changeImportLinksFileBtn" style="background:none; border:none; color:var(--ff-accent,#E0392E); font-size:12px; font-weight:600; cursor:pointer;">Choose another file</button>
                </div>

                <div style="font-size:12.5px; font-weight:700; margin-bottom:10px; color:var(--ff-text, #f8fafc); text-transform:uppercase; letter-spacing:0.5px;">Map Spreadsheet Columns:</div>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:18px;">
                    <div class="ff-field">
                        <label class="ff-label" style="font-size:12px; margin-bottom:4px; color:var(--ff-text, #f8fafc);">Title <span style="color:#ef4444;">*</span></label>
                        <select id="mapLinkTitle" class="ff-select map-field-select" style="width:100%;"></select>
                    </div>
                    <div class="ff-field">
                        <label class="ff-label" style="font-size:12px; margin-bottom:4px; color:var(--ff-text, #f8fafc);">URL <span style="color:#ef4444;">*</span></label>
                        <select id="mapLinkUrl" class="ff-select map-field-select" style="width:100%;"></select>
                    </div>
                    <div class="ff-field">
                        <label class="ff-label" style="font-size:12px; margin-bottom:4px; color:var(--ff-text, #f8fafc);">Category <span style="color:var(--ff-muted,#94a3b8); font-size:10.5px;">(Moves item)</span></label>
                        <select id="mapLinkCategory" class="ff-select map-field-select" style="width:100%;"></select>
                    </div>
                    <div class="ff-field">
                        <label class="ff-label" style="font-size:12px; margin-bottom:4px; color:var(--ff-text, #f8fafc);">Link ID <span style="color:var(--ff-muted,#94a3b8); font-size:10.5px;">(For Updates)</span></label>
                        <select id="mapLinkId" class="ff-select map-field-select" style="width:100%;"></select>
                    </div>
                    <div class="ff-field">
                        <label class="ff-label" style="font-size:12px; margin-bottom:4px; color:var(--ff-text, #f8fafc);">Description</label>
                        <select id="mapLinkDesc" class="ff-select map-field-select" style="width:100%;"></select>
                    </div>
                    <div class="ff-field">
                        <label class="ff-label" style="font-size:12px; margin-bottom:4px; color:var(--ff-text, #f8fafc);">Tags</label>
                        <select id="mapLinkTags" class="ff-select map-field-select" style="width:100%;"></select>
                    </div>
                    <div class="ff-field">
                        <label class="ff-label" style="font-size:12px; margin-bottom:4px; color:var(--ff-text, #f8fafc);">Starred <span style="color:var(--ff-muted,#94a3b8); font-size:10.5px;">(Yes/No)</span></label>
                        <select id="mapLinkStarred" class="ff-select map-field-select" style="width:100%;"></select>
                    </div>
                    <div class="ff-field">
                        <label class="ff-label" style="font-size:12px; margin-bottom:4px; color:var(--ff-text, #f8fafc);">Hidden / Vault <span style="color:var(--ff-muted,#94a3b8); font-size:10.5px;">(Yes/No)</span></label>
                        <select id="mapLinkHidden" class="ff-select map-field-select" style="width:100%;"></select>
                    </div>
                </div>

                <!-- Import Strategy Selection -->
                <div style="margin-bottom:16px; padding:14px 16px; border-radius:12px; background:var(--ff-bg-2, rgba(255,255,255,0.03)); border:1px solid var(--ff-border, rgba(255,255,255,0.08));">
                    <label class="ff-label" style="font-size:12px; font-weight:700; color:var(--ff-text, #f8fafc); margin-bottom:6px; display:block;">
                        Import &amp; Update Behavior:
                    </label>
                    <select id="importLinksStrategySelect" class="ff-select" style="width:100%; font-size:13px;">
                        <option value="update_or_insert" selected>🔄 Update Existing &amp; Insert New (Update fields &amp; move category)</option>
                        <option value="insert_all">➕ Always Insert as New Links (Allow duplicates)</option>
                        <option value="skip_existing">⏭️ Skip Existing URLs (Only insert new links)</option>
                    </select>
                    <div style="font-size:11.5px; color:var(--ff-muted, #94a3b8); margin-top:6px;">
                        If an ID or URL matches an existing bookmark, FileFusion updates its details and moves it to the target category.
                    </div>
                </div>

                <!-- Destination Selection -->
                <div style="margin-bottom:16px; padding:14px 16px; border-radius:12px; background:var(--ff-bg-2, rgba(255,255,255,0.03)); border:1px solid var(--ff-border, rgba(255,255,255,0.08));">
                    <label class="ff-label" style="font-size:12px; font-weight:700; color:var(--ff-text, #f8fafc); margin-bottom:6px; display:block;">
                        Destination Collection:
                    </label>
                    <select id="importLinksDestinationSelect" class="ff-select" style="width:100%; font-size:13px;">
                        <option value="auto" selected>🌐 Automatic (Follow spreadsheet 'Hidden' column)</option>
                        <option value="public">📂 Public Collection (Force all unhidden)</option>
                        <option value="hidden">🔐 Secret Vault (Force all into hidden vault)</option>
                    </select>
                </div>

                <!-- Screenshot Queue Option -->
                <div style="margin-bottom:16px; padding:12px 14px; border-radius:12px; background:var(--ff-bg-2, rgba(255,255,255,0.03)); border:1px solid var(--ff-border, rgba(255,255,255,0.08)); display:flex; align-items:center; justify-content:space-between; gap:12px;">
                    <div>
                        <div style="font-size:13px; font-weight:700; color:var(--ff-text, #f8fafc); display:flex; align-items:center; gap:6px;">
                            <span>📸 Capture Website Screenshots in Background (Queue)</span>
                        </div>
                        <div style="font-size:11.5px; color:var(--ff-muted, #94a3b8); margin-top:2px;">
                            Automatically generate desktop screenshot preview thumbnails for imported bookmarks in the background.
                        </div>
                    </div>
                    <label class="ff-switch" style="flex-shrink:0;">
                        <input type="checkbox" id="importCaptureScreenshotsToggle" checked>
                        <span class="ff-slider round"></span>
                    </label>
                </div>

                <div style="font-size:12px; font-weight:700; margin-bottom:8px; color:var(--ff-text, #f8fafc); text-transform:uppercase; letter-spacing:0.5px;">Preview of First 3 Records:</div>
                <div style="overflow-x:auto; border:1px solid var(--ff-border, rgba(255,255,255,0.1)); border-radius:10px; margin-bottom:10px; background:var(--ff-bg-2, rgba(255,255,255,0.02));">
                    <table style="width:100%; font-size:12px; text-align:left; border-collapse:collapse;" id="importLinksPreviewTable">
                        <thead>
                            <tr style="background:var(--ff-surface-subtle, rgba(0,0,0,0.15)); border-bottom:1px solid var(--ff-border, rgba(255,255,255,0.08));">
                                <th style="padding:8px 10px; color:var(--ff-text,#f8fafc);">Title</th>
                                <th style="padding:8px 10px; color:var(--ff-text,#f8fafc);">URL</th>
                                <th style="padding:8px 10px; color:var(--ff-text,#f8fafc);">Category</th>
                                <th style="padding:8px 10px; color:var(--ff-text,#f8fafc);">Starred</th>
                                <th style="padding:8px 10px; color:var(--ff-text,#f8fafc);">Hidden</th>
                            </tr>
                        </thead>
                        <tbody id="importLinksPreviewTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div style="padding:14px 24px; border-top:1px solid var(--ff-border, rgba(255,255,255,0.08)); background:var(--ff-bg-2, rgba(0,0,0,0.04)); display:flex; align-items:center; justify-content:flex-end; gap:10px;">
            <button type="button" class="ff-btn closeImportLinksModalBtn">Cancel</button>
            <button type="button" id="confirmImportLinksBtn" class="ff-btn ff-btn-primary" style="display:none;">Import Links Now</button>
        </div>
    </div>
</div>

{{-- ==================== EXPORT LINKS MODAL ==================== --}}
<div id="exportLinksModal" class="ff-modal-overlay" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.7); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:16px;">
    <div class="ff-modal-card" style="background:var(--ff-card, #1e293b) !important; color:var(--ff-text, #f8fafc) !important; border:1px solid var(--ff-border, rgba(255,255,255,0.1)); border-radius:16px; width:100%; max-width:540px; max-height:90vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 24px 48px rgba(0,0,0,0.4);">
        <div style="padding:18px 24px; border-bottom:1px solid var(--ff-border, rgba(255,255,255,0.08)); display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:12px;">
                <span class="ff-section-icon" style="width:38px; height:38px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; background:color-mix(in srgb, var(--ff-accent) 15%, transparent); color:var(--ff-accent);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                </span>
                <div>
                    <div style="font-weight:700; font-size:16.5px; color:var(--ff-text, #f8fafc);">Export Links Collection</div>
                    <div style="font-size:12px; color:var(--ff-muted, #94a3b8);">Choose fields, vault access, and file format</div>
                </div>
            </div>
            <button type="button" class="ff-menu-btn closeExportLinksModalBtn" style="border:none; background:transparent; cursor:pointer; color:var(--ff-muted, #94a3b8); border-radius:8px; padding:4px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div style="padding:22px 24px; overflow-y:auto; flex:1;">
            <!-- Field Selection -->
            <div style="margin-bottom:20px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                    <span style="font-size:13px; font-weight:700; color:var(--ff-text, #f8fafc); text-transform:uppercase; letter-spacing:0.5px;">Select Fields to Include:</span>
                    <button type="button" id="toggleAllExportLinkFields" style="background:none; border:none; color:var(--ff-accent,#E0392E); font-size:12.5px; font-weight:600; cursor:pointer;">Select All</button>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px 14px;">
                    <label style="display:flex; align-items:center; gap:9px; font-size:13px; color:var(--ff-text, #f8fafc); cursor:pointer; font-weight:500;">
                        <input type="checkbox" name="exportLinkFields[]" value="id" checked style="accent-color:var(--ff-accent); width:16px; height:16px;"> 
                        <span>ID <span style="font-size:11px; color:var(--ff-muted, #94a3b8);">(For Update/Sync)</span></span>
                    </label>
                    <label style="display:flex; align-items:center; gap:9px; font-size:13px; color:var(--ff-text, #f8fafc); cursor:pointer; font-weight:500;">
                        <input type="checkbox" name="exportLinkFields[]" value="title" checked style="accent-color:var(--ff-accent); width:16px; height:16px;"> Title
                    </label>
                    <label style="display:flex; align-items:center; gap:9px; font-size:13px; color:var(--ff-text, #f8fafc); cursor:pointer; font-weight:500;">
                        <input type="checkbox" name="exportLinkFields[]" value="url" checked style="accent-color:var(--ff-accent); width:16px; height:16px;"> URL
                    </label>
                    <label style="display:flex; align-items:center; gap:9px; font-size:13px; color:var(--ff-text, #f8fafc); cursor:pointer; font-weight:500;">
                        <input type="checkbox" name="exportLinkFields[]" value="category" checked style="accent-color:var(--ff-accent); width:16px; height:16px;"> Category
                    </label>
                    <label style="display:flex; align-items:center; gap:9px; font-size:13px; color:var(--ff-text, #f8fafc); cursor:pointer; font-weight:500;">
                        <input type="checkbox" name="exportLinkFields[]" value="description" checked style="accent-color:var(--ff-accent); width:16px; height:16px;"> Description
                    </label>
                    <label style="display:flex; align-items:center; gap:9px; font-size:13px; color:var(--ff-text, #f8fafc); cursor:pointer; font-weight:500;">
                        <input type="checkbox" name="exportLinkFields[]" value="tags" checked style="accent-color:var(--ff-accent); width:16px; height:16px;"> Tags
                    </label>
                    <label style="display:flex; align-items:center; gap:9px; font-size:13px; color:var(--ff-text, #f8fafc); cursor:pointer; font-weight:500;">
                        <input type="checkbox" name="exportLinkFields[]" value="is_starred" checked style="accent-color:var(--ff-accent); width:16px; height:16px;"> Starred (Favorite)
                    </label>
                    <label style="display:flex; align-items:center; gap:9px; font-size:13px; color:var(--ff-text, #f8fafc); cursor:pointer; font-weight:500;">
                        <input type="checkbox" name="exportLinkFields[]" value="is_hidden" checked style="accent-color:var(--ff-accent); width:16px; height:16px;"> Hidden (Vault)
                    </label>
                    <label style="display:flex; align-items:center; gap:9px; font-size:13px; color:var(--ff-text, #f8fafc); cursor:pointer; font-weight:500; grid-column:span 2;">
                        <input type="checkbox" name="exportLinkFields[]" value="created_at" checked style="accent-color:var(--ff-accent); width:16px; height:16px;"> Created Date &amp; Time
                    </label>
                </div>
            </div>

            <!-- Hidden Vault Links Option -->
            <div style="margin-bottom:20px; padding:14px 16px; border-radius:12px; background:var(--ff-bg-2, rgba(255,255,255,0.03)); border:1px solid var(--ff-border, rgba(255,255,255,0.08));">
                <label style="display:flex; align-items:center; justify-content:space-between; cursor:pointer; margin-bottom:0;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="display:inline-flex; width:32px; height:32px; border-radius:8px; align-items:center; justify-content:center; background:rgba(239,68,68,0.15); color:#ef4444; flex-shrink:0;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </span>
                        <div>
                            <div style="font-weight:700; font-size:13px; color:var(--ff-text, #f8fafc);">Include Private / Hidden Vault Links</div>
                            <div style="font-size:11.5px; color:var(--ff-muted, #94a3b8);">Export secret bookmarks protected with password</div>
                        </div>
                    </div>
                    <input type="checkbox" id="exportIncludeHiddenLinksToggle" style="accent-color:var(--ff-accent); width:18px; height:18px; cursor:pointer;">
                </label>

                <div id="exportVaultPassSection" style="display:none; margin-top:12px; padding-top:12px; border-top:1px dashed var(--ff-border, rgba(255,255,255,0.12));">
                    @if ($isVaultUnlocked)
                        <div style="font-size:12px; color:#10b981; font-weight:600; display:flex; align-items:center; gap:6px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Vault unlocked in active session (ready to export)</span>
                        </div>
                    @else
                        <label class="ff-label" style="font-size:12px; color:var(--ff-text, #f8fafc); margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                            <span>Vault Passcode or 2FA Code</span>
                            <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="password" id="exportVaultPassInput" class="ff-input" placeholder="Enter vault passcode to decrypt..." autocomplete="new-password" data-lpignore="true" style="width:100%;">
                    @endif
                </div>
            </div>

            <!-- Format Selection -->
            <div>
                <div style="font-size:13px; font-weight:700; margin-bottom:10px; color:var(--ff-text, #f8fafc); text-transform:uppercase; letter-spacing:0.5px;">File Format:</div>
                <div style="display:flex; gap:12px;" class="ff-export-formats">
                    <label class="ff-export-format-card is-active" id="exportFormatXlsxCard" style="flex:1; border:1.5px solid var(--ff-accent); border-radius:12px; padding:14px; display:flex; align-items:center; gap:12px; cursor:pointer; background:color-mix(in srgb, var(--ff-accent) 10%, var(--ff-card)); transition:all 0.2s;">
                        <input type="radio" name="exportLinkFormat" value="xlsx" checked style="accent-color:var(--ff-accent); width:16px; height:16px;">
                        <div>
                            <div style="font-weight:700; font-size:13.5px; color:var(--ff-text, #f8fafc);">Excel (.xlsx)</div>
                            <div style="font-size:11.5px; color:var(--ff-muted, #94a3b8); margin-top:2px;">Formatted Spreadsheet</div>
                        </div>
                    </label>
                    <label class="ff-export-format-card" id="exportFormatCsvCard" style="flex:1; border:1.5px solid var(--ff-border, rgba(255,255,255,0.12)); border-radius:12px; padding:14px; display:flex; align-items:center; gap:12px; cursor:pointer; background:var(--ff-bg-2, rgba(255,255,255,0.03)); transition:all 0.2s;">
                        <input type="radio" name="exportLinkFormat" value="csv" style="accent-color:var(--ff-accent); width:16px; height:16px;">
                        <div>
                            <div style="font-weight:700; font-size:13.5px; color:var(--ff-text, #f8fafc);">CSV (.csv)</div>
                            <div style="font-size:11.5px; color:var(--ff-muted, #94a3b8); margin-top:2px;">Comma-Separated Text</div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div style="padding:14px 24px; border-top:1px solid var(--ff-border, rgba(255,255,255,0.08)); background:var(--ff-bg-2, rgba(0,0,0,0.04)); display:flex; align-items:center; justify-content:flex-end; gap:10px;">
            <button type="button" class="ff-btn closeExportLinksModalBtn">Cancel</button>
            <button type="button" id="executeExportLinksBtn" class="ff-btn ff-btn-primary" style="display:inline-flex; align-items:center; gap:6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                Download File
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    let parsedLinkRows = [];
    let linkFileColumns = [];

    const importLinksModal = document.getElementById('importLinksModal');
    const exportLinksModal = document.getElementById('exportLinksModal');

    $(document).on('click', '#openImportLinksModalBtn', function(e) {
        e.preventDefault();
        resetImportLinksModal();
        if (importLinksModal) importLinksModal.style.display = 'flex';
    });
    $(document).on('click', '.closeImportLinksModalBtn', function(e) {
        e.preventDefault();
        if (importLinksModal) importLinksModal.style.display = 'none';
    });

    $(document).on('click', '#openExportLinksModalBtn', function(e) {
        e.preventDefault();
        if (exportLinksModal) exportLinksModal.style.display = 'flex';
    });
    $(document).on('click', '.closeExportLinksModalBtn', function(e) {
        e.preventDefault();
        if (exportLinksModal) exportLinksModal.style.display = 'none';
    });

    // Format radio card toggling
    $(document).on('change', 'input[name="exportLinkFormat"]', function() {
        const format = $(this).val();
        if (format === 'xlsx') {
            $('#exportFormatXlsxCard').addClass('is-active').css({
                'border-color': 'var(--ff-accent)',
                'background': 'color-mix(in srgb, var(--ff-accent) 10%, var(--ff-card))'
            });
            $('#exportFormatCsvCard').removeClass('is-active').css({
                'border-color': 'var(--ff-border, rgba(255,255,255,0.12))',
                'background': 'var(--ff-bg-2, rgba(255,255,255,0.03))'
            });
        } else {
            $('#exportFormatCsvCard').addClass('is-active').css({
                'border-color': 'var(--ff-accent)',
                'background': 'color-mix(in srgb, var(--ff-accent) 10%, var(--ff-card))'
            });
            $('#exportFormatXlsxCard').removeClass('is-active').css({
                'border-color': 'var(--ff-border, rgba(255,255,255,0.12))',
                'background': 'var(--ff-bg-2, rgba(255,255,255,0.03))'
            });
        }
    });

    // Hidden Links toggle in Export
    $(document).on('change', '#exportIncludeHiddenLinksToggle', function() {
        if ($(this).is(':checked')) {
            $('#exportVaultPassSection').slideDown(200);
        } else {
            $('#exportVaultPassSection').slideUp(200);
        }
    });

    const dropzone = document.getElementById('importLinksDropzone');
    const fileInput = document.getElementById('importLinksFileInput');

    if (dropzone && fileInput) {
        dropzone.addEventListener('click', () => fileInput.click());
        dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.style.borderColor = 'var(--ff-accent, #E0392E)'; });
        dropzone.addEventListener('dragleave', () => { dropzone.style.borderColor = 'var(--ff-border, rgba(255,255,255,0.18))'; });
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.style.borderColor = 'var(--ff-border, rgba(255,255,255,0.18))';
            if (e.dataTransfer.files.length) handleLinksFile(e.dataTransfer.files[0]);
        });
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length) handleLinksFile(e.target.files[0]);
        });
    }

    $(document).on('click', '#changeImportLinksFileBtn', function() {
        resetImportLinksModal();
        if (fileInput) fileInput.click();
    });

    function resetImportLinksModal() {
        parsedLinkRows = [];
        linkFileColumns = [];
        if (fileInput) fileInput.value = '';
        $('#importLinksStep1').show();
        $('#importLinksStep2').hide();
        $('#confirmImportLinksBtn').hide();
    }

    function handleLinksFile(file) {
        if (typeof XLSX === 'undefined') {
            window.ff.toast('Spreadsheet parser is still loading. Please try again.', 'error');
            return;
        }
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

                parsedLinkRows = json;
                linkFileColumns = Object.keys(json[0] || {});

                $('#importLinksFileNameLabel').text(file.name);
                $('#importLinksRowCountBadge').text(`${json.length} rows found`);

                populateLinkMappingDropdowns();
                updateLinkPreviewTable();

                $('#importLinksStep1').hide();
                $('#importLinksStep2').show();
                $('#confirmImportLinksBtn').show();
            } catch (err) {
                window.ff.toast('Failed to parse spreadsheet: ' + err.message, 'error');
            }
        };
        reader.readAsArrayBuffer(file);
    }

    function escapeHtmlSafe(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function populateLinkMappingDropdowns() {
        const selectIds = [
            'mapLinkId', 'mapLinkTitle', 'mapLinkUrl', 'mapLinkCategory', 
            'mapLinkDesc', 'mapLinkTags', 'mapLinkStarred', 'mapLinkHidden'
        ];
        
        selectIds.forEach(id => {
            const select = $(`#${id}`);
            select.empty();
            const isRequired = id === 'mapLinkTitle' || id === 'mapLinkUrl';
            select.append(`<option value="">${isRequired ? '-- Select Column --' : '-- None (Skip) --'}</option>`);

            linkFileColumns.forEach(col => {
                select.append(`<option value="${escapeHtmlSafe(col)}">${escapeHtmlSafe(col)}</option>`);
            });
        });

        linkFileColumns.forEach(col => {
            const lower = col.toLowerCase().trim();
            if (['id', 'link_id', 'link id', 'key'].some(k => lower === k || lower.startsWith(k + '_'))) {
                if (!$('#mapLinkId').val()) $('#mapLinkId').val(col);
            }
            if (['title', 'name', 'label', 'bookmark', 'headline'].some(k => lower.includes(k))) {
                if (!$('#mapLinkTitle').val()) $('#mapLinkTitle').val(col);
            }
            if (['url', 'link', 'href', 'website', 'address'].some(k => lower.includes(k)) && !lower.includes('title')) {
                if (!$('#mapLinkUrl').val()) $('#mapLinkUrl').val(col);
            }
            if (['category', 'folder', 'collection', 'group'].some(k => lower.includes(k))) {
                if (!$('#mapLinkCategory').val()) $('#mapLinkCategory').val(col);
            }
            if (['description', 'desc', 'notes', 'summary'].some(k => lower.includes(k))) {
                if (!$('#mapLinkDesc').val()) $('#mapLinkDesc').val(col);
            }
            if (['tag', 'tags', 'labels', 'keywords'].some(k => lower.includes(k))) {
                if (!$('#mapLinkTags').val()) $('#mapLinkTags').val(col);
            }
            if (['star', 'starred', 'favorite', 'fav'].some(k => lower.includes(k))) {
                if (!$('#mapLinkStarred').val()) $('#mapLinkStarred').val(col);
            }
            if (['hidden', 'vault', 'private', 'secret'].some(k => lower.includes(k))) {
                if (!$('#mapLinkHidden').val()) $('#mapLinkHidden').val(col);
            }
        });
    }

    $(document).on('change', '.map-field-select', function() {
        updateLinkPreviewTable();
    });

    function updateLinkPreviewTable() {
        const tbody = $('#importLinksPreviewTableBody');
        tbody.empty();

        const titleCol = $('#mapLinkTitle').val();
        const urlCol = $('#mapLinkUrl').val();
        const catCol = $('#mapLinkCategory').val();
        const starCol = $('#mapLinkStarred').val();
        const hiddenCol = $('#mapLinkHidden').val();

        const sample = parsedLinkRows.slice(0, 3);
        sample.forEach(row => {
            const titleVal = titleCol ? row[titleCol] : '-';
            const urlVal = urlCol ? row[urlCol] : '-';
            const catVal = catCol ? row[catCol] : 'Uncategorized';
            const starVal = starCol ? (['1','true','yes','y'].includes(String(row[starCol]).toLowerCase()) ? '⭐ Yes' : 'No') : '-';
            const hiddenVal = hiddenCol ? (['1','true','yes','y'].includes(String(row[hiddenCol]).toLowerCase()) ? '🔒 Yes' : 'No') : '-';

            tbody.append(`
                <tr style="border-bottom:1px solid var(--ff-border, rgba(255,255,255,0.08));">
                    <td style="padding:8px 10px; font-weight:600; color:var(--ff-text,#f8fafc);">${escapeHtmlSafe(titleVal)}</td>
                    <td style="padding:8px 10px; color:var(--ff-accent,#E0392E); max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${escapeHtmlSafe(urlVal)}</td>
                    <td style="padding:8px 10px; color:var(--ff-muted,#94a3b8);">${escapeHtmlSafe(catVal)}</td>
                    <td style="padding:8px 10px; color:var(--ff-text,#f8fafc);">${escapeHtmlSafe(starVal)}</td>
                    <td style="padding:8px 10px; color:var(--ff-text,#f8fafc);">${escapeHtmlSafe(hiddenVal)}</td>
                </tr>
            `);
        });
    }

    $(document).on('click', '#confirmImportLinksBtn', function() {
        const titleCol = $('#mapLinkTitle').val();
        const urlCol = $('#mapLinkUrl').val();

        if (!titleCol || !urlCol) {
            window.ff.toast('Please map both Title and URL columns before importing.', 'error');
            return;
        }

        const idCol = $('#mapLinkId').val();
        const descCol = $('#mapLinkDesc').val();
        const tagsCol = $('#mapLinkTags').val();
        const catCol = $('#mapLinkCategory').val();
        const starCol = $('#mapLinkStarred').val();
        const hiddenCol = $('#mapLinkHidden').val();

        const mappedItems = parsedLinkRows.map(row => ({
            id: idCol ? String(row[idCol] || '').trim() : '',
            title: String(row[titleCol] || '').trim(),
            url: String(row[urlCol] || '').trim(),
            description: descCol ? String(row[descCol] || '').trim() : '',
            tags: tagsCol ? String(row[tagsCol] || '').trim() : '',
            category: catCol ? String(row[catCol] || '').trim() : '',
            is_starred: starCol ? String(row[starCol] || '').trim() : '',
            is_hidden: hiddenCol ? String(row[hiddenCol] || '').trim() : '',
        })).filter(item => item.title && item.url);

        if (mappedItems.length === 0) {
            window.ff.toast('No valid rows found with both Title and URL.', 'error');
            return;
        }

        const strategy = $('#importLinksStrategySelect').val() || 'update_or_insert';
        const destination = $('#importLinksDestinationSelect').val() || 'auto';
        const captureScreenshots = $('#importCaptureScreenshotsToggle').is(':checked') ? 1 : 0;

        const btn = $(this);
        btn.prop('disabled', true).text('Processing Import…');

        $.ajax({
            type: 'POST',
            url: "{{ route('panel.links.import') }}",
            data: {
                _token: "{{ csrf_token() }}",
                items: mappedItems,
                strategy: strategy,
                default_destination: destination,
                capture_screenshots: captureScreenshots
            },
            success: function(res) {
                if (res.success) {
                    window.ff.toast(res.message, 'success', 3500);
                    if (importLinksModal) importLinksModal.style.display = 'none';
                    setTimeout(() => window.location.reload(), 900);
                } else {
                    window.ff.toast(res.message || 'Import failed', 'error');
                    btn.prop('disabled', false).text('Import Links Now');
                }
            },
            error: function(xhr) {
                const res = xhr.responseJSON;
                const msg = (res && res.message) ? res.message : 'Import failed. Please check your data.';
                window.ff.toast(msg, 'error', 4500);
                btn.prop('disabled', false).text('Import Links Now');
            }
        });
    });

    $(document).on('click', '#toggleAllExportLinkFields', function() {
        const checkboxes = $('input[name="exportLinkFields[]"]');
        const allChecked = checkboxes.filter(':checked').length === checkboxes.length;
        checkboxes.prop('checked', !allChecked);
        $(this).text(allChecked ? 'Select All' : 'Deselect All');
    });

    $(document).on('click', '#executeExportLinksBtn', function() {
        const selected = $('input[name="exportLinkFields[]"]:checked').map(function() {
            return $(this).val();
        }).get();

        if (selected.length === 0) {
            window.ff.toast('Please select at least one field to export.', 'error');
            return;
        }

        const format = $('input[name="exportLinkFormat"]:checked').val() || 'xlsx';
        const includeHidden = $('#exportIncludeHiddenLinksToggle').is(':checked') ? 1 : 0;
        const vaultPass = $('#exportVaultPassInput').val() || '';

        const btn = $(this);
        btn.prop('disabled', true).text('Generating Export…');

        $.ajax({
            type: 'POST',
            url: "{{ route('panel.links.export') }}",
            data: {
                _token: "{{ csrf_token() }}",
                fields: selected,
                format: format,
                include_hidden: includeHidden,
                vault_pass: vaultPass
            },
            success: function(res) {
                btn.prop('disabled', false).html(`
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg> Download File
                `);
                if (exportLinksModal) exportLinksModal.style.display = 'none';

                if (format === 'xlsx') {
                    if (!res.rows || res.rows.length === 0) {
                        window.ff.toast('No links found to export.', 'info');
                        return;
                    }
                    const ws = XLSX.utils.json_to_sheet(res.rows);
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, "Links");
                    XLSX.writeFile(wb, `${res.filename || 'Links_Export'}.xlsx`);
                    window.ff.toast('Excel spreadsheet downloaded successfully!', 'success');
                } else {
                    const blob = new Blob(["\uFEFF" + res], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = `Links_Export_${new Date().toISOString().slice(0,10)}.csv`;
                    link.click();
                    window.ff.toast('CSV file downloaded successfully!', 'success');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg> Download File
                `);
                const res = xhr.responseJSON;
                const msg = (res && res.message) ? res.message : 'Export failed. Please check your permissions.';
                window.ff.toast(msg, 'error', 4500);
                if (res && res.require_auth) {
                    $('#exportVaultPassSection').slideDown(200);
                    $('#exportVaultPassInput').focus();
                }
            }
        });
    });

    const sampleLinksData = [
        { "ID": "", "Title": "Google Search", "URL": "https://google.com", "Category": "Search Engines", "Description": "Official Google search engine", "Tags": "search, google, web", "Starred": "No", "Hidden": "No" },
        { "ID": "", "Title": "GitHub Hub", "URL": "https://github.com", "Category": "Development", "Description": "Source code hosting & collaboration", "Tags": "dev, git, code", "Starred": "Yes", "Hidden": "No" },
        { "ID": "", "Title": "Secret Staging Server", "URL": "https://staging.internal.net", "Category": "Internal Vault", "Description": "Confidential staging infrastructure", "Tags": "confidential, dev", "Starred": "No", "Hidden": "Yes" }
    ];

    $(document).on('click', '#downloadLinksSampleXlsxBtn', function(e) {
        e.preventDefault();
        const ws = XLSX.utils.json_to_sheet(sampleLinksData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Links_Template");
        XLSX.writeFile(wb, "Links_Import_Template.xlsx");
        window.ff.toast('Excel template downloaded!', 'success', 2000);
    });

    $(document).on('click', '#downloadLinksSampleCsvBtn', function(e) {
        e.preventDefault();
        const ws = XLSX.utils.json_to_sheet(sampleLinksData);
        const csv = XLSX.utils.sheet_to_csv(ws);
        const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = "Links_Import_Template.csv";
        link.click();
        window.ff.toast('CSV template downloaded!', 'success', 2000);
    });
})();
</script>
