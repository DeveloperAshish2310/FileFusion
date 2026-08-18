@extends('layout.backend')
@push('title', 'Links')

@section('content')
    @php
        $activeCategory = request('category', '');
        $starredActive = request('starred') == '1';
        $currentPerPage = \App\Helpers\SettingHelper::getItemsPerPage(12);
    @endphp

    <div class="ff-row-between" style="align-items:flex-start; margin-bottom:22px;">
        <div>
            <h1 class="ff-h1">Links</h1>
            <p class="ff-sub" style="margin-bottom:0;">Your complete collection of saved links</p>
        </div>
        <div class="ff-row" style="gap:8px;">
            <button type="button" class="ff-btn" id="openImportLinksModalBtn" style="display:inline-flex; align-items:center; gap:6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                Import
            </button>
            <button type="button" class="ff-btn" id="openExportLinksModalBtn" style="display:inline-flex; align-items:center; gap:6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export
            </button>
            <a href="{{ route('panel.addlinkview') }}" class="ff-btn ff-btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                New Link
            </a>
        </div>
    </div>

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

    <div class="ff-toolbar">
        <label class="ff-input-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="search" id="search-input" placeholder="Search by title or URL… (Ctrl+K)"
                value="{{ request('search') }}" autocomplete="off">
        </label>

        <div class="ff-viewtoggle" id="linkViewToggle">
            <button type="button" class="ff-viewtoggle-btn" data-view="grid" aria-label="Grid view">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1.5" />
                    <rect x="14" y="3" width="7" height="7" rx="1.5" />
                    <rect x="3" y="14" width="7" height="7" rx="1.5" />
                    <rect x="14" y="14" width="7" height="7" rx="1.5" />
                </svg>
            </button>
            <button type="button" class="ff-viewtoggle-btn" data-view="list" aria-label="List view">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="4" y1="6" x2="20" y2="6" />
                    <line x1="4" y1="12" x2="20" y2="12" />
                    <line x1="4" y1="18" x2="20" y2="18" />
                </svg>
            </button>
        </div>
    </div>

    <div class="ff-row-between" style="margin-bottom:16px; align-items:center; gap:12px; flex-wrap:nowrap;">
        <div class="ff-chip-row-single" id="linkFilters" style="margin-bottom:0; flex:1; min-width:0;">
            <button type="button" class="ff-chip {{ $activeCategory === '' ? 'is-active' : '' }}"
                data-category="">All</button>
            @foreach ($categories as $category)
                @php $cid = encrypt($category->id); @endphp
                <button type="button" class="ff-chip {{ $activeCategory === $cid ? 'is-active' : '' }}"
                    data-category="{{ $cid }}">{{ $category->title }}</button>
            @endforeach

            <button type="button" id="starred-filter" class="ff-chip {{ $starredActive ? 'is-active' : '' }}"
                data-active="{{ $starredActive ? 'true' : 'false' }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="{{ $starredActive ? 'currentColor' : 'none' }}"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                </svg>
                Starred
            </button>
        </div>

        <div style="display:inline-flex; align-items:center; gap:6px; background:var(--ff-surface); border:1px solid var(--ff-border); padding:4px 10px; border-radius:10px; flex-shrink:0;">
            <span style="font-size:12px; color:var(--ff-muted); font-weight:600;">Show:</span>
            <select id="perPageSelect" class="ff-select" style="border:none; background:transparent; padding:2px 4px; font-size:12px; font-weight:600; cursor:pointer; color:var(--ff-text); outline:none;" title="Items visible per page">
                <option value="12" {{ $currentPerPage == 12 ? 'selected' : '' }}>12 / page</option>
                <option value="24" {{ $currentPerPage == 24 ? 'selected' : '' }}>24 / page</option>
                <option value="48" {{ $currentPerPage == 48 ? 'selected' : '' }}>48 / page</option>
                <option value="96" {{ $currentPerPage == 96 ? 'selected' : '' }}>96 / page</option>
            </select>
        </div>
    </div>

    @if (!empty($tags) && count($tags) > 0)
        <div style="margin-top:2px; margin-bottom:24px;">
            <div style="font-size:11.5px; font-weight:700; color:var(--ff-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">Tags:</div>
            <div class="ff-tags-container" id="linkTagFilters" style="margin-top:0; margin-bottom:0; display:flex; align-items:flex-start; gap:8px;">
                <div class="ff-tags-chips-wrapper" style="display:flex; align-items:center; gap:8px 6px; flex-wrap:wrap; flex:1;">
                    <button type="button" class="ff-chip ff-chip-sm {{ request('tag') === '' || !request()->has('tag') ? 'is-active' : '' }}" data-tag="" style="font-size:11.5px; padding:2px 10px; border-radius:20px;">All Tags</button>
                    @foreach ($tags as $tag)
                        <button type="button" class="ff-chip ff-chip-sm {{ strtolower(request('tag')) === strtolower($tag) ? 'is-active' : '' }}" data-tag="{{ strtolower($tag) }}" style="font-size:11.5px; padding:2px 10px; border-radius:20px;">#{{ $tag }}</button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ---------------------------- Bulk action bar ---------------------------- --}}
    <div id="bulkActionBar" class="ff-bulkbar" hidden>
        <label class="ff-bulkbar-label">
            <input type="checkbox" id="selectAllCheckbox" class="ff-checkbox">
            Select all — <span id="selectedCount">0</span> selected
        </label>
        <div class="ff-row" style="gap:8px;">
            <button type="button" id="bulkStarBtn" class="ff-btn ff-btn-sm">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                </svg>
                Star
            </button>
            <button type="button" id="bulkHideBtn" class="ff-btn ff-btn-sm">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.7 18.7 0 0 1 5.06-5.94" />
                    <line x1="1" y1="1" x2="23" y2="23" />
                </svg>
                Hide Selected
            </button>
            <button type="button" id="bulkDeleteBtn" class="ff-btn ff-btn-danger ff-btn-sm">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                </svg>
                Delete Selected
            </button>
        </div>
    </div>

    <div id="links-container">
        @include('panel.ajax.links_card_load')
    </div>

    <div id="importLinksModal" class="ff-modal-overlay" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.65); align-items:center; justify-content:center; padding:16px;">
        <div class="ff-modal-card" style="background:var(--ff-card, #ffffff) !important; color:var(--ff-text, #0f172a) !important; border:1px solid var(--ff-border, #e2e8f0); border-radius:14px; width:100%; max-width:680px; max-height:90vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.3);">
            <div style="padding:18px 24px; border-bottom:1px solid var(--ff-border, #e2e8f0); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="ff-section-icon" style="width:34px; height:34px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; background:rgba(224,57,46,0.1); color:var(--ff-accent,#E0392E);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                    </span>
                    <div>
                        <div style="font-weight:700; font-size:16px; color:var(--ff-text, #0f172a);">Import Links from Excel / CSV</div>
                        <div style="font-size:12px; color:var(--ff-text-2, var(--ff-muted, #64748b));">Map your spreadsheet columns and import bookmarks in bulk</div>
                    </div>
                </div>
                <button type="button" class="ff-menu-btn closeImportLinksModalBtn" style="border:none; background:transparent; cursor:pointer; color:var(--ff-muted, #64748b);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <div style="padding:24px; overflow-y:auto; flex:1;" id="importLinksModalBody">
                <div id="importLinksStep1">
                    <div id="importLinksDropzone" style="border:2px dashed var(--ff-border, #cbd5e1); border-radius:10px; padding:36px 20px; text-align:center; cursor:pointer; background:var(--ff-bg-2, rgba(0,0,0,0.02)); transition:all 0.2s ease;">
                        <input type="file" id="importLinksFileInput" accept=".xlsx, .xls, .csv" style="display:none;">
                        <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent,#E0392E)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 12px; display:block;">
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="8" y1="13" x2="16" y2="13"/>
                            <line x1="8" y1="17" x2="16" y2="17"/>
                            <line x1="10" y1="9" x2="8" y2="9"/>
                        </svg>
                        <div style="font-weight:600; font-size:15px; color:var(--ff-text, #0f172a);">Drag &amp; drop your Excel or CSV file here</div>
                        <div style="font-size:13px; color:var(--ff-text-2, var(--ff-muted, #64748b)); margin-top:4px;">or <span style="color:var(--ff-accent,#E0392E); font-weight:600;">browse files</span> from your computer</div>
                        <div style="font-size:11px; color:var(--ff-muted, #94a3b8); margin-top:10px;">Supports .xlsx, .xls, and .csv files (Chrome, Firefox, Bitwarden, Pocket exports)</div>
                    </div>

                    <div style="margin-top:16px; padding:12px 16px; border-radius:8px; background:var(--ff-bg-2, rgba(0,0,0,0.03)); border:1px solid var(--ff-border, #e2e8f0); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                        <div>
                            <div style="font-size:12px; font-weight:600; color:var(--ff-text, #0f172a);">Need a starter template?</div>
                            <div style="font-size:11px; color:var(--ff-text-2, var(--ff-muted, #64748b));">Download pre-formatted template with sample columns</div>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button type="button" class="ff-btn ff-btn-sm" id="downloadLinksSampleXlsxBtn" style="font-size:11px; padding:4px 10px; display:inline-flex; align-items:center; gap:4px;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                Excel (.xlsx)
                            </button>
                            <button type="button" class="ff-btn ff-btn-sm" id="downloadLinksSampleCsvBtn" style="font-size:11px; padding:4px 10px; display:inline-flex; align-items:center; gap:4px;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                CSV (.csv)
                            </button>
                        </div>
                    </div>
                </div>

                <div id="importLinksStep2" style="display:none;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; padding-bottom:10px; border-bottom:1px solid var(--ff-border, #e2e8f0);">
                        <div style="font-size:13px; font-weight:600; color:var(--ff-text, #0f172a); display:flex; align-items:center; gap:8px;">
                            <span style="display:inline-flex; width:8px; height:8px; border-radius:50%; background:#10b981;"></span>
                            <span id="importLinksFileNameLabel">file.xlsx</span>
                            <span id="importLinksRowCountBadge" style="font-size:11px; padding:2px 8px; border-radius:12px; background:rgba(16,185,129,0.15); color:#10b981; font-weight:600;">0 rows</span>
                        </div>
                        <button type="button" id="changeImportLinksFileBtn" style="background:none; border:none; color:var(--ff-accent,#E0392E); font-size:12px; font-weight:600; cursor:pointer;">Choose another file</button>
                    </div>

                    <div style="font-size:13px; font-weight:600; margin-bottom:10px; color:var(--ff-text, #0f172a);">Map Spreadsheet Columns to Link Fields:</div>
                    
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:18px;">
                        <div class="ff-field">
                            <label class="ff-label" style="font-size:12px; margin-bottom:4px;">Title <span style="color:#ef4444;">*</span></label>
                            <select id="mapLinkTitle" class="ff-select map-field-select" style="width:100%;"></select>
                        </div>
                        <div class="ff-field">
                            <label class="ff-label" style="font-size:12px; margin-bottom:4px;">URL <span style="color:#ef4444;">*</span></label>
                            <select id="mapLinkUrl" class="ff-select map-field-select" style="width:100%;"></select>
                        </div>
                        <div class="ff-field">
                            <label class="ff-label" style="font-size:12px; margin-bottom:4px;">Description</label>
                            <select id="mapLinkDesc" class="ff-select map-field-select" style="width:100%;"></select>
                        </div>
                        <div class="ff-field">
                            <label class="ff-label" style="font-size:12px; margin-bottom:4px;">Tags</label>
                            <select id="mapLinkTags" class="ff-select map-field-select" style="width:100%;"></select>
                        </div>
                        <div class="ff-field" style="grid-column: span 2;">
                            <label class="ff-label" style="font-size:12px; margin-bottom:4px;">Category</label>
                            <select id="mapLinkCategory" class="ff-select map-field-select" style="width:100%;"></select>
                        </div>
                    </div>

                    <div style="font-size:12px; font-weight:600; margin-bottom:6px; color:var(--ff-text-2, var(--ff-muted, #64748b));">Preview of First 3 Records:</div>
                    <div style="overflow-x:auto; border:1px solid var(--ff-border, #e2e8f0); border-radius:8px; margin-bottom:10px;">
                        <table style="width:100%; font-size:12px; text-align:left; border-collapse:collapse;" id="importLinksPreviewTable">
                            <thead>
                                <tr style="background:var(--ff-bg-2, rgba(0,0,0,0.03)); border-bottom:1px solid var(--ff-border, #e2e8f0);">
                                    <th style="padding:8px 10px;">Title</th>
                                    <th style="padding:8px 10px;">URL</th>
                                    <th style="padding:8px 10px;">Description</th>
                                    <th style="padding:8px 10px;">Tags</th>
                                    <th style="padding:8px 10px;">Category</th>
                                </tr>
                            </thead>
                            <tbody id="importLinksPreviewTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div style="padding:14px 24px; border-top:1px solid var(--ff-border, #e2e8f0); background:var(--ff-bg-2, rgba(0,0,0,0.02)); display:flex; align-items:center; justify-content:flex-end; gap:10px;">
                <button type="button" class="ff-btn closeImportLinksModalBtn">Cancel</button>
                <button type="button" id="confirmImportLinksBtn" class="ff-btn ff-btn-primary" style="display:none;">Import Links Now</button>
            </div>
        </div>
    </div>

    <div id="exportLinksModal" class="ff-modal-overlay" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.65); align-items:center; justify-content:center; padding:16px;">
        <div class="ff-modal-card" style="background:var(--ff-card, #ffffff) !important; color:var(--ff-text, #0f172a) !important; border:1px solid var(--ff-border, #e2e8f0); border-radius:14px; width:100%; max-width:500px; max-height:90vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.3);">
            <div style="padding:18px 24px; border-bottom:1px solid var(--ff-border, #e2e8f0); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="ff-section-icon" style="width:34px; height:34px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; background:rgba(224,57,46,0.1); color:var(--ff-accent,#E0392E);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                    </span>
                    <div>
                        <div style="font-weight:700; font-size:16px; color:var(--ff-text, #0f172a);">Export Links Collection</div>
                        <div style="font-size:12px; color:var(--ff-text-2, var(--ff-muted, #64748b));">Choose custom fields and file format</div>
                    </div>
                </div>
                <button type="button" class="ff-menu-btn closeExportLinksModalBtn" style="border:none; background:transparent; cursor:pointer; color:var(--ff-muted, #64748b);">
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
                        <button type="button" id="toggleAllExportLinkFields" style="background:none; border:none; color:var(--ff-accent,#E0392E); font-size:12px; font-weight:600; cursor:pointer;">Select All</button>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                            <input type="checkbox" name="exportLinkFields[]" value="title" checked> Title
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                            <input type="checkbox" name="exportLinkFields[]" value="url" checked> URL
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                            <input type="checkbox" name="exportLinkFields[]" value="category" checked> Category
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                            <input type="checkbox" name="exportLinkFields[]" value="description" checked> Description
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                            <input type="checkbox" name="exportLinkFields[]" value="tags" checked> Tags
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                            <input type="checkbox" name="exportLinkFields[]" value="created_at" checked> Created Date
                        </label>
                    </div>
                </div>

                <div class="ff-divider" style="margin:16px 0;"></div>

                <div>
                    <div style="font-size:13px; font-weight:600; margin-bottom:10px; color:var(--ff-text-main, #0f172a);">File Format:</div>
                    <div style="display:flex; gap:12px;">
                        <label style="flex:1; border:1px solid var(--ff-border, #cbd5e1); border-radius:8px; padding:12px; display:flex; align-items:center; gap:10px; cursor:pointer; background:var(--ff-surface, #fff);">
                            <input type="radio" name="exportLinkFormat" value="xlsx" checked>
                            <div>
                                <div style="font-weight:600; font-size:13px; color:var(--ff-text-main, #0f172a);">Excel (.xlsx)</div>
                                <div style="font-size:11px; color:var(--ff-text-muted, #64748b);">Spreadsheet</div>
                            </div>
                        </label>
                        <label style="flex:1; border:1px solid var(--ff-border, #cbd5e1); border-radius:8px; padding:12px; display:flex; align-items:center; gap:10px; cursor:pointer; background:var(--ff-surface, #fff);">
                            <input type="radio" name="exportLinkFormat" value="csv">
                            <div>
                                <div style="font-weight:600; font-size:13px; color:var(--ff-text-main, #0f172a);">CSV (.csv)</div>
                                <div style="font-size:11px; color:var(--ff-text-muted, #64748b);">Comma-separated</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div style="padding:14px 24px; border-top:1px solid var(--ff-border, #e2e8f0); background:var(--ff-surface-subtle, rgba(0,0,0,0.02)); display:flex; align-items:center; justify-content:flex-end; gap:10px;">
                <button type="button" class="ff-btn closeExportLinksModalBtn">Cancel</button>
                <button type="button" id="executeExportLinksBtn" class="ff-btn ff-btn-primary">Download File</button>
            </div>
        </div>
    </div>
@endsection

@section('push-script')
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script>
        // ------------------------------------------------------------- view mode
        function applyLinkView(mode) {
            localStorage.setItem('ff-link-view', mode);
            document.querySelectorAll('[data-ff-view-target="links"]').forEach(function(el) {
                el.setAttribute('data-ff-view', mode);
            });
            document.querySelectorAll('#linkViewToggle .ff-viewtoggle-btn').forEach(function(btn) {
                btn.classList.toggle('is-active', btn.dataset.view === mode);
            });
        }

        document.getElementById('linkViewToggle').addEventListener('click', function(e) {
            var btn = e.target.closest('.ff-viewtoggle-btn');
            if (btn) applyLinkView(btn.dataset.view);
        });

        // -------------------------------------------------------------- filters
        let searchTimeout;

        function applyFilters() {
            const search = $('#search-input').val();
            const category = $('#linkFilters .ff-chip.is-active[data-category]').data('category') || '';
            const tag = $('#linkTagFilters .ff-chip.is-active[data-tag]').data('tag') || '';
            const starred = $('#starred-filter').data('active') === 'true' ? '1' : '';

            const url = new URL(window.location.href);
            url.searchParams.set('search', search);
            url.searchParams.set('category', category);
            url.searchParams.set('tag', tag);
            url.searchParams.set('starred', starred);
            if (!search) url.searchParams.delete('search');
            if (!category) url.searchParams.delete('category');
            if (!tag) url.searchParams.delete('tag');
            if (!starred) url.searchParams.delete('starred');
            window.history.pushState({}, '', url);

            $('#links-container').css('opacity', 0.5);

            $.ajax({
                url: '{{ route('panel.linklist') }}',
                type: 'GET',
                data: {
                    search: search,
                    category: category,
                    tag: tag,
                    starred: starred
                },
                success: function(response) {
                    $('#links-container').html(response).css('opacity', 1);
                    afterLinksRender();
                },
                error: function(xhr) {
                    console.error('Filter error:', xhr);
                    $('#links-container').css('opacity', 1);
                }
            });
        }

        $('#search-input').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 400);
        });

        $('#linkFilters').on('click', '.ff-chip[data-category]', function() {
            $('#linkFilters .ff-chip[data-category]').removeClass('is-active');
            $(this).addClass('is-active');
            applyFilters();
        });

        $(document).on('click', '#linkTagFilters .ff-chip[data-tag]', function() {
            $('#linkTagFilters .ff-chip[data-tag]').removeClass('is-active');
            $(this).addClass('is-active');
            applyFilters();
        });

        $('#starred-filter').on('click', function() {
            const isActive = $(this).data('active') === 'true';
            $(this).data('active', isActive ? 'false' : 'true');
            $(this).toggleClass('is-active', !isActive);
            $(this).find('svg').attr('fill', !isActive ? 'currentColor' : 'none');
            applyFilters();
        });

        $(document).on('click', '#links-container .ff-pagination a', function(e) {
            e.preventDefault();
            $.get($(this).attr('href'), function(response) {
                $('#links-container').html(response);
                afterLinksRender();
            });
        });

        // --------------------------------------------------- row-level actions
        function afterLinksRender() {
            applyLinkView(localStorage.getItem('ff-link-view') || 'grid');
            updateBulkBarLinks();
            window.ff.icons();
        }

        $(document).on('click', '.toggle-star', function(e) {
            e.preventDefault();
            const linkId = this.getAttribute('data-link-id');

            fetch('{{ route('panel.toggleStarLink') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        link_id: linkId
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    document.querySelectorAll('.toggle-star[data-link-id="' + linkId + '"]').forEach(function(btn) {
                        btn.classList.toggle('is-starred', data.is_starred);
                        btn.setAttribute('title', data.is_starred ? 'Unstar' : 'Star');
                        var icon = btn.querySelector('svg');
                        if (icon) icon.setAttribute('fill', data.is_starred ? 'currentColor' : 'none');
                    });
                })
                .catch(error => console.error('Error:', error));
        });

        $(document).on('click', '.toggle-hide', async function(e) {
            e.preventDefault();
            const linkId = this.getAttribute('data-link-id');
            const confirmed = await window.ff.confirm({
                title: '👁️ Hide Link',
                message: 'Move this link to your private hidden vault?',
                confirmText: 'Hide Link',
                isDanger: false
            });
            if (!confirmed) return;

            fetch('{{ route('panel.toggleHideLink') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        link_id: linkId
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.ff.toast('Link moved to hidden vault.', 'success', 2000);
                        applyFilters();
                    } else {
                        window.ff.toast(data.message || 'Failed to update link visibility.', 'error', 4000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.ff.toast('An error occurred. Please try again.', 'error', 4000);
                });
        });

        $(document).on('click', '.recapture-screenshot-btn', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (window.ff && typeof window.ff.closeAllMenus === 'function') {
                window.ff.closeAllMenus();
            }
            const linkId = this.getAttribute('data-link-id');
            const btn = this;
            
            const confirmed = await window.ff.confirm({
                title: '📸 Recapture Screenshot',
                message: 'Capture a fresh desktop screenshot thumbnail for this website link?',
                confirmText: 'Capture Screenshot',
                isDanger: false
            });
            if (!confirmed) return;

            window.ff.toast('Capturing website screenshot in background...', 'info', 4000);

            fetch('{{ route('panel.links.recapture_screenshot') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    linkId: linkId
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.code === 200) {
                    window.ff.toast('Screenshot updated successfully!', 'success', 3000);
                    applyFilters();
                } else {
                    window.ff.toast(data.info || 'Unable to capture screenshot.', 'error', 4500);
                }
            })
            .catch(err => {
                console.error(err);
                window.ff.toast('Failed to capture screenshot.', 'error', 4000);
            });
        });

        // ---------------------------------------------------------- bulk actions
        function updateBulkBarLinks() {
            var total = $('.link-checkbox').length;
            var checked = $('.link-checkbox:checked').length;
            document.getElementById('bulkActionBar').hidden = total === 0;
            $('#selectedCount').text(checked);
            $('#selectAllCheckbox').prop('checked', checked > 0 && checked === total);
        }

        $(document).on('change', '.link-checkbox', function() {
            var id = $(this).data('id');
            $('.link-checkbox[data-id="' + id + '"]').prop('checked', this.checked);
            updateBulkBarLinks();
        });

        $(document).on('change', '#selectAllCheckbox', function() {
            $('.link-checkbox').prop('checked', $(this).is(':checked'));
            updateBulkBarLinks();
        });

        async function executeBulkActionLinks(action) {
            var selectedIds = [];
            $('[data-ff-view="grid"] .link-checkbox:checked, [data-ff-view="list"] .link-checkbox:checked').each(
                function() {
                    var id = $(this).data('id');
                    if (selectedIds.indexOf(id) === -1) selectedIds.push(id);
                });

            if (selectedIds.length === 0) {
                window.ff.toast('Please select at least one link.', 'info', 2500);
                return;
            }

            const title = action === 'delete' ? '🗑️ Delete Links' : (action === 'hide' ? '👁️ Hide Links' : '⭐ Star Links');
            const confirmMsg = action === 'delete' ?
                `Delete ${selectedIds.length} selected link(s)?` :
                (action === 'hide' ? `Hide ${selectedIds.length} selected link(s)? They will move to your private vault.` : `Star ${selectedIds.length} selected link(s)?`);

            if (action !== 'star') {
                const confirmed = await window.ff.confirm({
                    title: title,
                    message: confirmMsg,
                    confirmText: action === 'delete' ? 'Delete Links' : 'Hide Links',
                    isDanger: action === 'delete'
                });
                if (!confirmed) return;
            }

            $.ajax({
                type: 'POST',
                url: "{{ route('panel.bulkActionLinks') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    action: action,
                    ids: selectedIds
                },
                success: function(res) {
                    if (res.success) {
                        const msg = action === 'delete' ? 'Links deleted.' : (action === 'hide' ? 'Links hidden.' : 'Links updated.');
                        window.ff.toast(msg, 'success', 2000);
                        applyFilters();
                    } else {
                        window.ff.toast(res.message || 'Action could not be completed.', 'error', 4000);
                    }
                },
                error: function() {
                    window.ff.toast('An error occurred. Please try again.', 'error', 4000);
                }
            });
        }

        $(document).on('click', '#bulkStarBtn', function(e) {
            e.preventDefault();
            executeBulkActionLinks('star');
        });
        $(document).on('click', '#bulkHideBtn', function(e) {
            e.preventDefault();
            executeBulkActionLinks('hide');
        });
        $(document).on('click', '#bulkDeleteBtn', function(e) {
            e.preventDefault();
            executeBulkActionLinks('delete');
        });

        // ---------------------------------------------------------- single delete
        $(document).on('click', '.ff-link-delete-btn', async function(e) {
            e.preventDefault();
            const href = $(this).data('url') || $(this).attr('href');
            const title = $(this).data('title') || 'this link';
            const confirmed = await window.ff.confirm({
                title: '🗑️ Delete Link',
                message: `Move link "${title}" to the trash?`,
                confirmText: 'Move to Trash',
                isDanger: true
            });
            if (confirmed) {
                window.location.href = href;
            }
        });

        // =========================================================================
        // IMPORT LINKS WITH CUSTOM FIELD MAPPING
        // =========================================================================
        let parsedLinkRows = [];
        let linkFileColumns = [];

        const importLinksModal = document.getElementById('importLinksModal');
        const exportLinksModal = document.getElementById('exportLinksModal');

        $('#openImportLinksModalBtn').on('click', function() {
            resetImportLinksModal();
            importLinksModal.style.display = 'flex';
        });
        $('.closeImportLinksModalBtn').on('click', function() {
            importLinksModal.style.display = 'none';
        });

        $('#openExportLinksModalBtn').on('click', function() {
            exportLinksModal.style.display = 'flex';
        });
        $('.closeExportLinksModalBtn').on('click', function() {
            exportLinksModal.style.display = 'none';
        });

        const dropzone = document.getElementById('importLinksDropzone');
        const fileInput = document.getElementById('importLinksFileInput');

        dropzone.addEventListener('click', () => fileInput.click());
        dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.style.borderColor = 'var(--ff-accent, #E0392E)'; });
        dropzone.addEventListener('dragleave', () => { dropzone.style.borderColor = 'var(--ff-border, #cbd5e1)'; });
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.style.borderColor = 'var(--ff-border, #cbd5e1)';
            if (e.dataTransfer.files.length) handleLinksFile(e.dataTransfer.files[0]);
        });
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length) handleLinksFile(e.target.files[0]);
        });

        $('#changeImportLinksFileBtn').on('click', function() {
            resetImportLinksModal();
            fileInput.click();
        });

        function resetImportLinksModal() {
            parsedLinkRows = [];
            linkFileColumns = [];
            fileInput.value = '';
            $('#importLinksStep1').show();
            $('#importLinksStep2').hide();
            $('#confirmImportLinksBtn').hide();
        }

        function handleLinksFile(file) {
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

        function populateLinkMappingDropdowns() {
            const selectIds = ['mapLinkTitle', 'mapLinkUrl', 'mapLinkDesc', 'mapLinkTags', 'mapLinkCategory'];
            
            selectIds.forEach(id => {
                const select = $(`#${id}`);
                select.empty();
                const isRequired = id === 'mapLinkTitle' || id === 'mapLinkUrl';
                select.append(`<option value="">${isRequired ? '-- Select Column --' : '-- None (Skip) --'}</option>`);

                linkFileColumns.forEach(col => {
                    select.append(`<option value="${escapeHtml(col)}">${escapeHtml(col)}</option>`);
                });
            });

            linkFileColumns.forEach(col => {
                const lower = col.toLowerCase().trim();
                if (['title', 'name', 'label', 'bookmark', 'headline'].some(k => lower.includes(k))) {
                    if (!$('#mapLinkTitle').val()) $('#mapLinkTitle').val(col);
                }
                if (['url', 'link', 'href', 'website', 'address'].some(k => lower.includes(k))) {
                    if (!$('#mapLinkUrl').val()) $('#mapLinkUrl').val(col);
                }
                if (['description', 'desc', 'notes', 'summary'].some(k => lower.includes(k))) {
                    if (!$('#mapLinkDesc').val()) $('#mapLinkDesc').val(col);
                }
                if (['tag', 'tags', 'labels', 'keywords'].some(k => lower.includes(k))) {
                    if (!$('#mapLinkTags').val()) $('#mapLinkTags').val(col);
                }
                if (['category', 'folder', 'collection', 'group'].some(k => lower.includes(k))) {
                    if (!$('#mapLinkCategory').val()) $('#mapLinkCategory').val(col);
                }
            });
        }

        $('.map-field-select').on('change', function() {
            updateLinkPreviewTable();
        });

        function updateLinkPreviewTable() {
            const tbody = $('#importLinksPreviewTableBody');
            tbody.empty();

            const titleCol = $('#mapLinkTitle').val();
            const urlCol = $('#mapLinkUrl').val();
            const descCol = $('#mapLinkDesc').val();
            const tagsCol = $('#mapLinkTags').val();
            const catCol = $('#mapLinkCategory').val();

            const sample = parsedLinkRows.slice(0, 3);
            sample.forEach(row => {
                tbody.append(`
                    <tr style="border-bottom:1px solid var(--ff-border, #e2e8f0);">
                        <td style="padding:6px 10px; font-weight:600;">${escapeHtml(titleCol ? row[titleCol] : '-')}</td>
                        <td style="padding:6px 10px; color:var(--ff-accent,#E0392E);">${escapeHtml(urlCol ? row[urlCol] : '-')}</td>
                        <td style="padding:6px 10px; color:var(--ff-text-muted,#64748b);">${escapeHtml(descCol ? row[descCol] : '-')}</td>
                        <td style="padding:6px 10px;">${escapeHtml(tagsCol ? row[tagsCol] : '-')}</td>
                        <td style="padding:6px 10px;">${escapeHtml(catCol ? row[catCol] : '-')}</td>
                    </tr>
                `);
            });
        }

        $('#confirmImportLinksBtn').on('click', function() {
            const titleCol = $('#mapLinkTitle').val();
            const urlCol = $('#mapLinkUrl').val();

            if (!titleCol || !urlCol) {
                window.ff.toast('Please map both Title and URL columns before importing.', 'error');
                return;
            }

            const descCol = $('#mapLinkDesc').val();
            const tagsCol = $('#mapLinkTags').val();
            const catCol = $('#mapLinkCategory').val();

            const mappedItems = parsedLinkRows.map(row => ({
                title: String(row[titleCol] || '').trim(),
                url: String(row[urlCol] || '').trim(),
                description: descCol ? String(row[descCol] || '').trim() : '',
                tags: tagsCol ? String(row[tagsCol] || '').trim() : '',
                category: catCol ? String(row[catCol] || '').trim() : '',
            })).filter(item => item.title && item.url);

            if (mappedItems.length === 0) {
                window.ff.toast('No valid rows found with both Title and URL.', 'error');
                return;
            }

            const btn = $(this);
            btn.prop('disabled', true).text('Importing…');

            $.ajax({
                type: 'POST',
                url: "{{ route('panel.links.import') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    items: mappedItems
                },
                success: function(res) {
                    if (res.success) {
                        window.ff.toast(res.message, 'success', 3000);
                        importLinksModal.style.display = 'none';
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        window.ff.toast(res.message || 'Import failed', 'error');
                        btn.prop('disabled', false).text('Import Links Now');
                    }
                },
                error: function(xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Import failed';
                    window.ff.toast(msg, 'error');
                    btn.prop('disabled', false).text('Import Links Now');
                }
            });
        });

        // =========================================================================
        // EXPORT LINKS WITH CUSTOM FIELD SELECTION
        // =========================================================================
        $('#toggleAllExportLinkFields').on('click', function() {
            const checkboxes = $('input[name="exportLinkFields[]"]');
            const allChecked = checkboxes.filter(':checked').length === checkboxes.length;
            checkboxes.prop('checked', !allChecked);
            $(this).text(allChecked ? 'Select All' : 'Deselect All');
        });

        $('#executeExportLinksBtn').on('click', function() {
            const selected = $('input[name="exportLinkFields[]"]:checked').map(function() {
                return $(this).val();
            }).get();

            if (selected.length === 0) {
                window.ff.toast('Please select at least one field to export.', 'error');
                return;
            }

            const format = $('input[name="exportLinkFormat"]:checked').val() || 'xlsx';
            const btn = $(this);
            btn.prop('disabled', true).text('Exporting…');

            $.ajax({
                type: 'POST',
                url: "{{ route('panel.links.export') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    fields: selected,
                    format: format
                },
                success: function(res) {
                    btn.prop('disabled', false).text('Download File');
                    exportLinksModal.style.display = 'none';

                    if (format === 'xlsx') {
                        if (!res.rows || res.rows.length === 0) {
                            window.ff.toast('No links to export.', 'info');
                            return;
                        }
                        const ws = XLSX.utils.json_to_sheet(res.rows);
                        const wb = XLSX.utils.book_new();
                        XLSX.utils.book_append_sheet(wb, ws, "Links");
                        XLSX.writeFile(wb, `${res.filename || 'Links_Export'}.xlsx`);
                        window.ff.toast('Excel file downloaded successfully!', 'success');
                    } else {
                        const blob = new Blob(["\uFEFF" + res], { type: 'text/csv;charset=utf-8;' });
                        const link = document.createElement('a');
                        link.href = URL.createObjectURL(blob);
                        link.download = `Links_Export_${new Date().toISOString().slice(0,10)}.csv`;
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
        // DOWNLOAD SAMPLE LINKS TEMPLATE (.xlsx / .csv)
        // =========================================================================
        const sampleLinksData = [
            { "Title": "Google Search", "URL": "https://google.com", "Category": "Search Engines", "Description": "Official Google search engine", "Tags": "search, google, web" },
            { "Title": "GitHub Hub", "URL": "https://github.com", "Category": "Development", "Description": "Source code hosting & collaboration", "Tags": "dev, git, code" },
            { "Title": "Laravel Docs", "URL": "https://laravel.com/docs", "Category": "Frameworks", "Description": "The PHP Framework for Web Artisans", "Tags": "php, backend, laravel" }
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

        // =========================================================================
        // ITEMS PER PAGE SELECTOR
        // =========================================================================
        $(document).on('change', '#perPageSelect', function() {
            const perPage = $(this).val();
            // Save to persistent user datastorage
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

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, function(c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }
    </script>
@endsection
