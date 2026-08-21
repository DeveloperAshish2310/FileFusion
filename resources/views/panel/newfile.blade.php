@extends('layout.backend')
@push('title', 'File Editor')

@section('content')
    {{-- High-Performance CDN Markdown, Sanitizer & Vector PDF Export Engine --}}
    <script src="https://cdn.jsdelivr.net/npm/marked@12.0.1/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.9/dist/purify.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

    <div class="ff-breadcrumb">
        <a href="{{ route('panel.dashboard') }}">Dashboard</a>
        <span>/</span>
        <a href="{{ route('panel.filelist') }}">Files</a>
        <span>/</span>
        <span class="is-current">Editor</span>
    </div>

    {{-- Page Header & Responsive Toolbar --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
        <div>
            <h1 class="ff-h1 ff-h1-sm" style="display: flex; align-items: center; gap: 10px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                File Editor
            </h1>
            <p class="ff-sub">Edit and preview code scripts, notes, and documents.</p>
        </div>

        {{-- Top Toolbar Actions --}}
        {{-- Top Toolbar Actions --}}
        <div class="ff-toolbar-group" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            {{-- Language Auto-Detection Badge --}}
            <div id="languageBadge" style="height: 36px; display: inline-flex; align-items: center; gap: 6px; padding: 0 12px; border-radius: 8px; background: rgba(99,102,241,0.15); color: #818cf8; font-weight: 700; font-size: 12px; border: 1px solid rgba(99,102,241,0.3); box-sizing: border-box;">
                <span id="languageBadgeText">Markdown</span>
            </div>

            {{-- Mode Switcher (Segmented Control - only shown for Markdown files) --}}
            <div id="viewModeButtonGroup" style="display: inline-flex; height: 36px; background: var(--ff-surface, #27272a); border-radius: 8px; padding: 3px; border: 1px solid var(--ff-border, #3f3f46); box-sizing: border-box; align-items: center; transition: all 0.2s ease;">
                <button type="button" class="ff-btn" id="btnModeEditor" onclick="setViewMode('editor')" style="height: 100%; padding: 0 12px; font-size: 12px; font-weight: 600; border-radius: 6px; background: transparent; color: var(--ff-muted, #a1a1aa); border: none; display: inline-flex; align-items: center;">
                    Editor
                </button>
                <button type="button" class="ff-btn" id="btnModeSplit" onclick="setViewMode('split')" style="height: 100%; padding: 0 12px; font-size: 12px; font-weight: 600; border-radius: 6px; background: var(--ff-bg-card, #18181b); color: #fff; border: none; display: inline-flex; align-items: center;">
                    Split View
                </button>
                <button type="button" class="ff-btn" id="btnModePreview" onclick="setViewMode('preview')" style="height: 100%; padding: 0 12px; font-size: 12px; font-weight: 600; border-radius: 6px; background: transparent; color: var(--ff-muted, #a1a1aa); border: none; display: inline-flex; align-items: center;">
                    Preview
                </button>
            </div>

            {{-- Theme Toggle Button --}}
            <button type="button" class="ff-btn" id="themeToggleBtn" onclick="toggleTheme()" style="height: 36px; padding: 0 14px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; background: var(--ff-surface, #27272a); color: var(--ff-text, #f1f5f9); border: 1px solid var(--ff-border, #3f3f46); border-radius: 8px; box-sizing: border-box; transition: all 0.2s ease;" title="Switch Preview Theme">
                <span id="themeLabel">Dark Theme</span>
            </button>

            {{-- PDF Export with Light & Dark Options --}}
            <div style="position: relative; display: inline-block;" id="pdfExportDropdownContainer">
                <button type="button" class="ff-btn" id="btnExportPdf" onclick="togglePdfDropdown(event)" style="height: 36px; padding: 0 14px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; color: var(--ff-text, #f1f5f9); border: 1px solid var(--ff-border, #3f3f46); background: var(--ff-surface, #27272a); border-radius: 8px; box-sizing: border-box; transition: all 0.2s ease;" title="Choose Light or Dark PDF Export">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    <span>Export PDF</span>
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div id="pdfMenuDropdown" style="display: none; position: absolute; right: 0; top: 42px; background: #18181b; border: 1px solid #27272a; border-radius: 8px; padding: 6px; z-index: 1000; min-width: 165px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
                    <button type="button" class="ff-btn pdf-dropdown-item" onclick="exportToPdf('dark'); closePdfDropdown();" style="width: 100%; text-align: left; justify-content: flex-start; gap: 8px; padding: 8px 12px; font-size: 12px; border: none; background: transparent; color: var(--ff-text, #f1f5f9); border-radius: 6px;">
                        <strong>Export Dark PDF</strong>
                    </button>
                    <button type="button" class="ff-btn pdf-dropdown-item" onclick="exportToPdf('light'); closePdfDropdown();" style="width: 100%; text-align: left; justify-content: flex-start; gap: 8px; padding: 8px 12px; font-size: 12px; border: none; background: transparent; color: var(--ff-text, #f1f5f9); border-radius: 6px;">
                        <strong>Export Light PDF</strong>
                    </button>
                </div>
            </div>
        </div>
    </div>


    {{-- Main Workspace Grid --}}
    <div class="ff-split" id="mainWorkspace">

        {{-- =============================== Editor & Live Preview Panel =============================== --}}
        <div class="ff-form-card" id="editorContainerCard" style="display: flex; flex-direction: column; gap: 16px;">

            @if (isset($file) && isset($content))
                <textarea id="serverInitialContent" style="display:none;" readonly>{{ $content }}</textarea>
                <input type="hidden" id="serverInitialFileId" value="{{ $file->id }}">
                <input type="hidden" id="serverInitialFileName" value="{{ $file->name }}">
                <input type="hidden" id="serverInitialCreatedAt" value="{{ $file->created_at }}">
                <input type="hidden" id="serverInitialUpdatedAt" value="{{ $file->updated_at }}">
            @endif

            {{-- File Details Header with Symmetrical Alignment --}}
            <div style="display: flex; flex-direction: column; gap: 6px; width: 100%;">
                <label class="ff-label" for="fileName" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--ff-muted, #a1a1aa); margin: 0;">File Name & Extension</label>
                
                <div class="ff-file-header-row" style="display: flex; gap: 10px; align-items: center; width: 100%;">
                    <input type="text" id="fileName" class="ff-input" value="{{ isset($file) ? $file->name : request('prefill_title', 'Untitled.md') }}" autocomplete="off" placeholder="notes.md, script.py, app.js, index.html..." style="flex: 1; min-width: 140px; font-weight: 600; height: 42px; box-sizing: border-box;">
                    
                    <div class="ff-file-btn-group" style="display: flex; gap: 8px; align-items: center; flex-shrink: 0;">
                        <button type="button" id="newFile" class="ff-btn" style="height: 42px; padding: 0 16px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; box-sizing: border-box; border-radius: 8px;">
                            + New
                        </button>
                        <button type="button" id="saveFile" class="ff-btn is-primary" style="height: 42px; padding: 0 18px; font-size: 13px; font-weight: 600; background: #6366f1; border-color: #6366f1; display: inline-flex; align-items: center; justify-content: center; box-sizing: border-box; border-radius: 8px;">
                            Save
                        </button>

                    </div>
                </div>
            </div>

            {{-- Split / Tab View Container --}}
            <div id="dualViewGrid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; min-height: 500px;">
                
                {{-- Editor Pane --}}
                <div id="editorPane" style="display: flex; flex-direction: column;">
                    <div id="editorHeaderLabel" style="font-size: 11px; font-weight: 700; color: var(--ff-muted, #a1a1aa); text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">MARKDOWN / CODE SOURCE</div>
                    <textarea id="editor" rows="22" class="ff-textarea is-mono" autocomplete="off" placeholder="Type Markdown (# Title, **bold**, `code`, etc.) or paste your code here..." style="flex: 1; min-height: 480px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 13.5px; line-height: 1.6; tab-size: 4; resize: vertical; background: #121214; border: 1px solid var(--ff-border, #27272a); border-radius: 8px; padding: 14px; box-sizing: border-box;">{{ isset($content) ? $content : request('prefill_content', '') }}</textarea>
                    <div class="ff-hint" style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px;">
                        <span id="wordCount">0 words · 0 characters</span>
                        <span id="autoSaveIndicator" style="color: #10b981;">● Auto-saved locally</span>
                    </div>
                </div>

                {{-- Live Rendered Preview Pane (Dark by default) --}}
                <div id="previewPane" class="theme-dark" style="display: flex; flex-direction: column; background: #18181b; border: 1px solid #27272a; border-radius: 10px; overflow: hidden; transition: all 0.2s ease;">
                    <div id="previewPaneHeader" style="padding: 10px 14px; background: #121214; border-bottom: 1px solid #27272a; font-size: 11px; font-weight: 700; color: #a1a1aa; display: flex; justify-content: space-between; align-items: center;">
                        <span id="previewHeaderLabel">LIVE MARKDOWN PREVIEW</span>
                        <span id="previewThemeBadge" style="font-size: 10px; color: #818cf8; background: rgba(99,102,241,0.15); padding: 2px 6px; border-radius: 4px;">🌙 DARK MODE</span>
                    </div>
                    <div id="liveMarkdownContent" class="ff-markdown-body is-dark" style="flex: 1; padding: 18px; overflow-y: auto; max-height: 520px; font-size: 14px; line-height: 1.7; color: #e4e4e7;">
                        <p style="color: #71717a; font-style: italic;">Live preview will appear here as you type...</p>
                    </div>
                </div>

            </div>

        </div>

        {{-- ================================ Aside (Drafts & Info) ================================ --}}
        <div class="ff-stack" id="asidePanel">
            
            {{-- Quick Document Inspector --}}
            <div class="ff-card" style="padding: 16px;">
                <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted, #a1a1aa); margin-bottom: 12px;">Document Inspector</div>
                <div style="display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--ff-muted, #a1a1aa);">Detected Format:</span>
                        <strong id="inspectorLang">Markdown</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--ff-muted, #a1a1aa);">Active Theme:</span>
                        <strong id="inspectorTheme" style="color: #fbbf24;">🌙 Dark (PDF will match)</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--ff-muted, #a1a1aa);">Lines Count:</span>
                        <strong id="inspectorLines">1</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--ff-muted, #a1a1aa);">Estimated Reading:</span>
                        <strong id="inspectorReadTime">< 1 min</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--ff-muted, #a1a1aa);">Cloud Encryption:</span>
                        <strong style="color: #10b981;">AES-256-GCM</strong>
                    </div>
                </div>
            </div>

            {{-- Drafts Manager --}}
            <div class="ff-card" style="padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h3 style="font-size: 14px; font-weight: 700; margin: 0;">Your Drafts</h3>
                    <span class="ff-hint" id="fileCount" style="font-size: 12px;">0 files</span>
                </div>
                <div id="fileList" class="ff-stack-xs" style="display: flex; flex-direction: column; gap: 8px; max-height: 300px; overflow-y: auto;"></div>
            </div>

        </div>

    </div>

@endsection

@section('push-script')
    <style>
        /* Responsive Layout Grid */
        #mainWorkspace {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 20px;
            align-items: start;
        }

        @media (max-width: 1024px) {
            #mainWorkspace {
                grid-template-columns: 1fr !important;
            }
            #asidePanel {
                order: 2;
            }
        }

        @media (max-width: 768px) {
            #dualViewGrid {
                grid-template-columns: 1fr !important;
            }
            #editor {
                min-height: 360px !important;
            }
            .ff-file-header-row {
                flex-direction: column !important;
                align-items: stretch !important;
            }
            .ff-file-btn-group {
                width: 100% !important;
                display: flex !important;
            }
            .ff-file-btn-group button {
                flex: 1 !important;
            }
            .ff-toolbar-group {
                width: 100% !important;
                justify-content: flex-start !important;
            }
        }

        /* =========================================================================
           MARKDOWN STYLES (DARK THEME - DEFAULT & LIGHT THEME)
           ========================================================================= */
        .ff-markdown-body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Noto Sans", Helvetica, Arial, sans-serif; transition: all 0.2s ease; }
        
        /* Dark Theme */
        .ff-markdown-body.is-dark { color: #e4e4e7; }
        .ff-markdown-body.is-dark h1 { font-size: 1.75rem; font-weight: 800; border-bottom: 1px solid #3f3f46; padding-bottom: 8px; margin: 16px 0 12px 0; color: #ffffff; line-height: 1.3; }
        .ff-markdown-body.is-dark h2 { font-size: 1.35rem; font-weight: 700; border-bottom: 1px solid #3f3f46; padding-bottom: 6px; margin: 14px 0 10px 0; color: #f4f4f5; line-height: 1.3; }
        .ff-markdown-body.is-dark h3 { font-size: 1.15rem; font-weight: 700; margin: 12px 0 8px 0; color: #e4e4e7; }
        .ff-markdown-body.is-dark h4 { font-size: 1.0rem; font-weight: 700; margin: 10px 0 6px 0; color: #d4d4d8; }
        .ff-markdown-body.is-dark p { margin-bottom: 12px; line-height: 1.65; }
        .ff-markdown-body.is-dark code { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; background: rgba(255,255,255,0.08); padding: 2px 6px; border-radius: 4px; font-size: 12.5px; color: #a5b4fc; }
        .ff-markdown-body.is-dark pre { background: #0c0c0e !important; border: 1px solid #27272a; padding: 14px; border-radius: 8px; overflow-x: auto; margin: 14px 0; }
        .ff-markdown-body.is-dark pre code { background: none; padding: 0; color: #e4e4e7; font-size: 13px; }
        .ff-markdown-body.is-dark blockquote { border-left: 4px solid #6366f1; padding: 6px 16px; margin: 12px 0; background: rgba(99,102,241,0.06); color: #cbd5e1; border-radius: 0 6px 6px 0; }
        .ff-markdown-body.is-dark ul, .ff-markdown-body.is-dark ol { padding-left: 24px; margin-bottom: 12px; }
        .ff-markdown-body.is-dark li { margin-bottom: 4px; }
        .ff-markdown-body.is-dark table { width: 100%; border-collapse: collapse; margin: 14px 0; }
        .ff-markdown-body.is-dark th, .ff-markdown-body.is-dark td { border: 1px solid #3f3f46; padding: 8px 12px; text-align: left; }
        .ff-markdown-body.is-dark th { background: rgba(255,255,255,0.06); font-weight: 700; color: #ffffff; }
        .ff-markdown-body.is-dark tr:nth-child(even) { background: rgba(255,255,255,0.02); }
        .ff-markdown-body.is-dark hr { border: none; border-top: 1px solid #3f3f46; margin: 20px 0; }
        .ff-markdown-body.is-dark a { color: #818cf8; text-decoration: underline; }
        .ff-markdown-body.is-dark img { max-width: 100%; border-radius: 8px; }

        /* Light Theme */
        .ff-markdown-body.is-light { color: #1f2937; }
        .ff-markdown-body.is-light h1 { font-size: 1.75rem; font-weight: 800; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; margin: 16px 0 12px 0; color: #111827; line-height: 1.3; }
        .ff-markdown-body.is-light h2 { font-size: 1.35rem; font-weight: 700; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; margin: 14px 0 10px 0; color: #1f2937; line-height: 1.3; }
        .ff-markdown-body.is-light h3 { font-size: 1.15rem; font-weight: 700; margin: 12px 0 8px 0; color: #374151; }
        .ff-markdown-body.is-light h4 { font-size: 1.0rem; font-weight: 700; margin: 10px 0 6px 0; color: #4b5563; }
        .ff-markdown-body.is-light p { margin-bottom: 12px; line-height: 1.65; color: #374151; }
        .ff-markdown-body.is-light code { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-size: 12.5px; color: #4338ca; }
        .ff-markdown-body.is-light pre { background: #f8fafc !important; border: 1px solid #e2e8f0; padding: 14px; border-radius: 8px; overflow-x: auto; margin: 14px 0; }
        .ff-markdown-body.is-light pre code { background: none; padding: 0; color: #0f172a; font-size: 13px; }
        .ff-markdown-body.is-light blockquote { border-left: 4px solid #6366f1; padding: 6px 16px; margin: 12px 0; background: #f5f3ff; color: #4338ca; border-radius: 0 6px 6px 0; }
        .ff-markdown-body.is-light ul, .ff-markdown-body.is-light ol { padding-left: 24px; margin-bottom: 12px; color: #374151; }
        .ff-markdown-body.is-light li { margin-bottom: 4px; }
        .ff-markdown-body.is-light table { width: 100%; border-collapse: collapse; margin: 14px 0; }
        .ff-markdown-body.is-light th, .ff-markdown-body.is-light td { border: 1px solid #e5e7eb; padding: 8px 12px; text-align: left; }
        .ff-markdown-body.is-light th { background: #f9fafb; font-weight: 700; color: #111827; }
        .ff-markdown-body.is-light tr:nth-child(even) { background: #f9fafb; }
        .ff-markdown-body.is-light hr { border: none; border-top: 1px solid #e5e7eb; margin: 20px 0; }
        .ff-markdown-body.is-light a { color: #4f46e5; text-decoration: underline; }
        .ff-markdown-body input[type="checkbox"] { margin-right: 6px; }

        /* Drafts List Item Theme Styles */
        .ff-draft-item {
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
            background: var(--ff-surface, #27272a);
            border: 1px solid var(--ff-border, #3f3f46);
            color: var(--ff-text, #f1f5f9);
        }
        .ff-draft-item:hover {
            background: var(--ff-surface-hover, #323238);
            border-color: var(--ff-border-hover, #52525b);
        }
        .ff-draft-item.is-active {
            border: 1.5px solid #6366f1 !important;
            background: rgba(99, 102, 241, 0.15) !important;
        }
        html[data-theme="light"] .ff-draft-item,
        [data-theme="light"] .ff-draft-item {
            background: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            color: #0f172a !important;
        }
        html[data-theme="light"] .ff-draft-item:hover,
        [data-theme="light"] .ff-draft-item:hover {
            background: #f1f5f9 !important;
        }
        html[data-theme="light"] .ff-draft-item.is-active,
        [data-theme="light"] .ff-draft-item.is-active {
            border: 1.5px solid #6366f1 !important;
            background: #eef2ff !important;
        }
        html[data-theme="dark"] .ff-draft-item,
        [data-theme="dark"] .ff-draft-item {
            background: #27272a !important;
            border: 1px solid #3f3f46 !important;
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] .ff-draft-item:hover,
        [data-theme="dark"] .ff-draft-item:hover {
            background: #323238 !important;
        }
        html[data-theme="dark"] .ff-draft-item.is-active,
        [data-theme="dark"] .ff-draft-item.is-active {
            border: 1.5px solid #6366f1 !important;
            background: rgba(99, 102, 241, 0.15) !important;
        }

        .ff-draft-title {
            font-weight: 600;
            font-size: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 140px;
            color: var(--ff-text, #f1f5f9);
        }
        html[data-theme="light"] .ff-draft-title,
        [data-theme="light"] .ff-draft-title {
            color: #0f172a !important;
        }
        html[data-theme="dark"] .ff-draft-title,
        [data-theme="dark"] .ff-draft-title {
            color: #f1f5f9 !important;
        }

        .ff-draft-date {
            font-size: 11px;
            color: var(--ff-muted, #a1a1aa);
        }
        html[data-theme="light"] .ff-draft-date,
        [data-theme="light"] .ff-draft-date {
            color: #64748b !important;
        }
        html[data-theme="dark"] .ff-draft-date,
        [data-theme="dark"] .ff-draft-date {
            color: #a1a1aa !important;
        }
    </style>

    <script>
        // =========================================================================
        // 1. THEME MANAGEMENT (DARK & LIGHT THEMES FOR EDITOR & PREVIEW)
        // =========================================================================
        let isSettingTheme = false;
        let currentTheme = 'dark';

        function toggleTheme() {
            const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
            if (window.ff && typeof window.ff.setTheme === 'function') {
                window.ff.setTheme(nextTheme);
            }
            setTheme(nextTheme);
        }

        function setTheme(theme) {
            if (isSettingTheme) return;
            isSettingTheme = true;

            try {
                currentTheme = theme;
                if (document.documentElement.dataset.theme !== theme) {
                    document.documentElement.dataset.theme = theme;
                }
                localStorage.setItem('theme', theme);

                const editor = document.getElementById('editor');
                const editorHeader = document.getElementById('editorHeaderLabel');
                const previewPane = document.getElementById('previewPane');
                const previewHeader = document.getElementById('previewPaneHeader');
                const content = document.getElementById('liveMarkdownContent');
                const toggleBtn = document.getElementById('themeToggleBtn');
                const themeLabel = document.getElementById('themeLabel');
                const themeBadge = document.getElementById('previewThemeBadge');
                const inspectorTheme = document.getElementById('inspectorTheme');
                const viewGroup = document.getElementById('viewModeButtonGroup');
                const exportBtn = document.getElementById('btnExportPdf');
                const pdfDropdown = document.getElementById('pdfMenuDropdown');

                if (theme === 'light') {
                    if (editor) {
                        editor.style.background = '#ffffff';
                        editor.style.color = '#0f172a';
                        editor.style.borderColor = '#cbd5e1';
                        editor.style.caretColor = '#2563eb';
                    }
                    if (editorHeader) {
                        editorHeader.style.color = '#64748b';
                    }
                    if (previewPane) {
                        previewPane.style.background = '#ffffff';
                        previewPane.style.borderColor = '#cbd5e1';
                    }
                    if (previewHeader) {
                        previewHeader.style.background = '#f8fafc';
                        previewHeader.style.borderColor = '#e2e8f0';
                        previewHeader.style.color = '#475569';
                    }
                    if (content) {
                        content.classList.remove('is-dark');
                        content.classList.add('is-light');
                    }
                    if (viewGroup) {
                        viewGroup.style.background = '#e2e8f0';
                        viewGroup.style.borderColor = '#cbd5e1';
                    }
                    if (toggleBtn) {
                        if (themeLabel) themeLabel.textContent = 'Light Theme';
                        toggleBtn.style.color = '#0f172a';
                        toggleBtn.style.background = '#ffffff';
                        toggleBtn.style.borderColor = '#cbd5e1';
                    }
                    if (exportBtn) {
                        exportBtn.style.color = '#0f172a';
                        exportBtn.style.background = '#ffffff';
                        exportBtn.style.borderColor = '#cbd5e1';
                    }
                    if (pdfDropdown) {
                        pdfDropdown.style.background = '#ffffff';
                        pdfDropdown.style.borderColor = '#e2e8f0';
                    }
                    if (themeBadge) {
                        themeBadge.textContent = 'LIGHT MODE';
                        themeBadge.style.color = '#2563eb';
                        themeBadge.style.background = '#eff6ff';
                    }
                    if (inspectorTheme) {
                        inspectorTheme.innerHTML = 'Light (PDF matches)';
                        inspectorTheme.style.color = '#2563eb';
                    }
                } else {
                    if (editor) {
                        editor.style.background = '#121214';
                        editor.style.color = '#f1f5f9';
                        editor.style.borderColor = '#27272a';
                        editor.style.caretColor = '#818cf8';
                    }
                    if (editorHeader) {
                        editorHeader.style.color = '#a1a1aa';
                    }
                    if (previewPane) {
                        previewPane.style.background = '#18181b';
                        previewPane.style.borderColor = '#27272a';
                    }
                    if (previewHeader) {
                        previewHeader.style.background = '#121214';
                        previewHeader.style.borderColor = '#27272a';
                        previewHeader.style.color = '#a1a1aa';
                    }
                    if (content) {
                        content.classList.remove('is-light');
                        content.classList.add('is-dark');
                    }
                    if (viewGroup) {
                        viewGroup.style.background = 'var(--ff-surface, #27272a)';
                        viewGroup.style.borderColor = 'var(--ff-border, #3f3f46)';
                    }
                    if (toggleBtn) {
                        if (themeLabel) themeLabel.textContent = 'Dark Theme';
                        toggleBtn.style.color = 'var(--ff-text, #f1f5f9)';
                        toggleBtn.style.background = 'var(--ff-surface, #27272a)';
                        toggleBtn.style.borderColor = 'var(--ff-border, #3f3f46)';
                    }
                    if (exportBtn) {
                        exportBtn.style.color = 'var(--ff-text, #f1f5f9)';
                        exportBtn.style.background = 'var(--ff-surface, #27272a)';
                        exportBtn.style.borderColor = 'var(--ff-border, #3f3f46)';
                    }
                    if (pdfDropdown) {
                        pdfDropdown.style.background = '#18181b';
                        pdfDropdown.style.borderColor = '#27272a';
                    }
                    if (themeBadge) {
                        themeBadge.textContent = 'DARK MODE';
                        themeBadge.style.color = '#818cf8';
                        themeBadge.style.background = 'rgba(99,102,241,0.15)';
                    }
                    if (inspectorTheme) {
                        inspectorTheme.innerHTML = 'Dark (PDF matches)';
                        inspectorTheme.style.color = '#818cf8';
                    }
                }

                setViewMode(currentViewMode);

                if (typeof window.ffUpdateDraftList === 'function') {
                    window.ffUpdateDraftList();
                }
            } finally {
                isSettingTheme = false;
            }
        }

        // =========================================================================
        // 2. LANGUAGE AUTO-DETECTION ENGINE
        // =========================================================================
        const LANGUAGE_DEFINITIONS = [
            { id: 'markdown',   name: 'Markdown',    icon: '', color: '#818cf8', ext: ['md', 'markdown'], regex: /(^#\s+|^##\s+|\[.*\]\(.*\)|```|\*\*.*\*\*|^- \[ \]|^- \[x\])/m },
            { id: 'python',     name: 'Python',      icon: '', color: '#38bdf8', ext: ['py', 'pyw'], regex: /^(import\s+\w+|from\s+\w+\s+import|def\s+\w+\(|class\s+\w+:|if\s+__name__\s*==)/m },
            { id: 'javascript', name: 'JavaScript',  icon: '', color: '#facc15', ext: ['js', 'mjs', 'cjs'], regex: /(const\s+\w+\s*=|let\s+\w+\s*=|function\s*\w*\(|console\.log\(|=>\s*{)/m },
            { id: 'typescript', name: 'TypeScript',  icon: '', color: '#3b82f6', ext: ['ts', 'tsx'], regex: /(interface\s+\w+|type\s+\w+\s*=|:\s*string|:\s*number|:\s*boolean)/m },
            { id: 'html',       name: 'HTML',        icon: '', color: '#f97316', ext: ['html', 'htm'], regex: /(<!DOCTYPE\s+html>|<html|<head|<body|<div|<script|<meta)/i },
            { id: 'css',        name: 'CSS',         icon: '', color: '#38bdf8', ext: ['css', 'scss', 'less'], regex: /(@media|@import|margin:\s*|padding:\s*|color:\s*|background:\s*|\{[\s\S]*?\})/m },
            { id: 'go',         name: 'Go',          icon: '', color: '#00add8', ext: ['go'], regex: /(package\s+\w+|func\s+\w+\(|import\s*\([\s\S]*?\)|type\s+\w+\s+struct)/m },
            { id: 'php',        name: 'PHP',         icon: '', color: '#a855f7', ext: ['php'], regex: /(<\?php|\$\w+\s*=|namespace\s+\w+|public\s+function)/m },
            { id: 'sql',        name: 'SQL',         icon: '', color: '#ec4899', ext: ['sql'], regex: /(SELECT\s+.*FROM|INSERT\s+INTO|UPDATE\s+\w+\s+SET|CREATE\s+TABLE|ALTER\s+TABLE)/i },
            { id: 'json',       name: 'JSON',        icon: '', color: '#eab308', ext: ['json'], regex: /^(\s*\{[\s\S]*\}|\s*\[[\s\S]*\])$/m },
            { id: 'shell',      name: 'Bash Script', icon: '', color: '#4ade80', ext: ['sh', 'bash', 'zsh'], regex: /(^#!\/bin\/(bash|sh)|echo\s+.*|\bif\s+\[.*\];\s*then)/m },
            { id: 'rust',       name: 'Rust',        icon: '', color: '#ea580c', ext: ['rs'], regex: /(fn\s+main\(\)|let\s+mut\s+|impl\s+\w+|pub\s+fn)/m },
        ];

        function detectLanguage(filename, content) {
            const ext = (filename.includes('.') ? filename.split('.').pop() : '').toLowerCase();
            
            if (ext) {
                const matchedByExt = LANGUAGE_DEFINITIONS.find(l => l.ext.includes(ext));
                if (matchedByExt) return matchedByExt;
            }

            if (content && content.trim().length > 0) {
                for (const lang of LANGUAGE_DEFINITIONS) {
                    if (lang.regex && lang.regex.test(content)) {
                        return lang;
                    }
                }
            }

            return { id: 'markdown', name: 'Markdown', icon: '', color: '#818cf8' };
        }

        // =========================================================================
        // 3. GFM MARKDOWN COMPILER
        // =========================================================================
        function escapeHtml(str) {
            return str.replace(/[&<>"']/g, function(m) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
            });
        }

        function parseMarkdownFallback(md) {
            if (!md || !md.trim()) return '<p style="color: #71717a; font-style: italic;">Nothing to preview yet — start typing on the left.</p>';
            let html = escapeHtml(md);
            html = html.replace(/```([a-zA-Z0-9_-]*)\n([\s\S]*?)```/g, '<pre><code>$2</code></pre>');
            html = html.replace(/`([^`]+)`/g, '<code>$1</code>');
            html = html.replace(/^### (.*$)/gim, '<h3>$1</h3>');
            html = html.replace(/^## (.*$)/gim, '<h2>$1</h2>');
            html = html.replace(/^# (.*$)/gim, '<h1>$1</h1>');
            html = html.replace(/^\> (.*$)/gim, '<blockquote>$1</blockquote>');
            html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
            html = html.replace(/\*([^*]+)\*/g, '<em>$1</em>');
            html = html.replace(/^- \[x\] (.*$)/gim, '<li><input type="checkbox" checked disabled> $1</li>');
            html = html.replace(/^- \[ \] (.*$)/gim, '<li><input type="checkbox" disabled> $1</li>');
            html = html.replace(/^- (.*$)/gim, '<li>$1</li>');
            html = html.replace(/\n\n+/g, '</p><p>');
            return '<p>' + html + '</p>';
        }

        function compileMarkdown(md) {
            if (!md || !md.trim()) {
                return '<p style="color: #71717a; font-style: italic;">Nothing to preview yet — start typing on the left.</p>';
            }

            try {
                if (typeof marked !== 'undefined' && typeof marked.parse === 'function') {
                    marked.setOptions({ gfm: true, breaks: true });
                    const raw = marked.parse(md);
                    if (typeof DOMPurify !== 'undefined' && typeof DOMPurify.sanitize === 'function') {
                        return DOMPurify.sanitize(raw);
                    }
                    return raw;
                }
            } catch (e) {
                console.warn('Marked error, using fallback:', e);
            }

            return parseMarkdownFallback(md);
        }

        // =========================================================================
        // 4. LIVE PREVIEW & VIEW MODE SWITCHER (CODE FULL-WIDTH / MD SPLIT)
        // =========================================================================
        let currentViewMode = 'split';

        function setViewMode(mode) {
            currentViewMode = mode;
            const editorPane = document.getElementById('editorPane');
            const previewPane = document.getElementById('previewPane');
            const dualViewGrid = document.getElementById('dualViewGrid');
            const btnEditor = document.getElementById('btnModeEditor');
            const btnSplit = document.getElementById('btnModeSplit');
            const btnPreview = document.getElementById('btnModePreview');
            const filename = document.getElementById('fileName').value;
            const isMd = filename.endsWith('.md') || filename.endsWith('.markdown') || !filename.includes('.');

            const isLight = currentTheme === 'light';
            const inactiveColor = isLight ? '#64748b' : 'var(--ff-muted, #a1a1aa)';
            const activeBg = isLight ? '#ffffff' : 'var(--ff-bg-card, #18181b)';
            const activeColor = isLight ? '#0f172a' : '#ffffff';
            const activeShadow = isLight ? '0 1px 3px rgba(0,0,0,0.1)' : 'none';

            [btnEditor, btnSplit, btnPreview].forEach(b => {
                if (b) {
                    b.style.background = 'transparent';
                    b.style.color = inactiveColor;
                    b.style.boxShadow = 'none';
                }
            });

            let activeBtn = null;
            if (!isMd || mode === 'editor') {
                activeBtn = btnEditor;
                dualViewGrid.style.gridTemplateColumns = '1fr';
                editorPane.style.display = 'flex';
                previewPane.style.display = 'none';
            } else if (mode === 'split') {
                activeBtn = btnSplit;
                dualViewGrid.style.gridTemplateColumns = '1fr 1fr';
                editorPane.style.display = 'flex';
                previewPane.style.display = 'flex';
            } else if (mode === 'preview') {
                activeBtn = btnPreview;
                dualViewGrid.style.gridTemplateColumns = '1fr';
                editorPane.style.display = 'none';
                previewPane.style.display = 'flex';
            }

            if (activeBtn) {
                activeBtn.style.background = activeBg;
                activeBtn.style.color = activeColor;
                activeBtn.style.boxShadow = activeShadow;
            }

            renderLivePreview();
        }

        function renderLivePreview() {
            const editor = document.getElementById('editor');
            const previewContent = document.getElementById('liveMarkdownContent');
            const filename = document.getElementById('fileName').value;
            const text = editor.value;

            const lang = detectLanguage(filename, text);
            
            const badgeEl = document.getElementById('languageBadge');
            const badgeText = document.getElementById('languageBadgeText');
            if (badgeText) {
                badgeText.textContent = lang.name;
            } else if (badgeEl) {
                badgeEl.textContent = lang.name;
            }

            if (badgeEl) {
                if (currentTheme === 'light') {
                    badgeEl.style.color = '#4338ca';
                    badgeEl.style.borderColor = '#c7d2fe';
                    badgeEl.style.background = '#eef2ff';
                } else {
                    badgeEl.style.color = lang.color || '#818cf8';
                    badgeEl.style.borderColor = (lang.color || '#818cf8') + '55';
                    badgeEl.style.background = (lang.color || '#818cf8') + '15';
                }
            }

            document.getElementById('inspectorLang').textContent = lang.name;

            const isMarkdown = (lang.id === 'markdown' || filename.endsWith('.md') || filename.endsWith('.markdown'));
            const viewModeGroup = document.getElementById('viewModeButtonGroup');
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            const editorHeaderLabel = document.getElementById('editorHeaderLabel');
            const dualViewGrid = document.getElementById('dualViewGrid');
            const editorPane = document.getElementById('editorPane');
            const previewPane = document.getElementById('previewPane');

            if (!isMarkdown) {
                // PURE CODE FILE (PHP, Python, JS, HTML, CSS, SQL, etc.)
                // Hide split-preview controls and hide markdown preview pane
                if (viewModeGroup) viewModeGroup.style.display = 'none';
                if (themeToggleBtn) themeToggleBtn.style.display = 'none';
                if (editorHeaderLabel) editorHeaderLabel.textContent = `${lang.name.toUpperCase()} SOURCE CODE`;


                dualViewGrid.style.gridTemplateColumns = '1fr';
                editorPane.style.display = 'flex';
                previewPane.style.display = 'none';
            } else {
                // MARKDOWN DOCUMENT (.md, .markdown)
                // Show split-view segmented controls and theme toggle
                if (viewModeGroup) viewModeGroup.style.display = 'inline-flex';
                if (themeToggleBtn) themeToggleBtn.style.display = 'inline-flex';
                if (editorHeaderLabel) editorHeaderLabel.textContent = '📝 MARKDOWN SOURCE';

                if (currentViewMode === 'split') {
                    dualViewGrid.style.gridTemplateColumns = '1fr 1fr';
                    editorPane.style.display = 'flex';
                    previewPane.style.display = 'flex';
                } else if (currentViewMode === 'preview') {
                    dualViewGrid.style.gridTemplateColumns = '1fr';
                    editorPane.style.display = 'none';
                    previewPane.style.display = 'flex';
                } else {
                    dualViewGrid.style.gridTemplateColumns = '1fr';
                    editorPane.style.display = 'flex';
                    previewPane.style.display = 'none';
                }

                previewContent.innerHTML = compileMarkdown(text);
                document.getElementById('previewHeaderLabel').textContent = 'LIVE MARKDOWN PREVIEW';
            }
        }

        // =========================================================================
        // 5. VECTOR PDF GENERATOR (LIGHT & DARK THEME OPTIONS)
        // =========================================================================
        function togglePdfDropdown(e) {
            e.stopPropagation();
            const menu = document.getElementById('pdfMenuDropdown');
            if (menu) {
                menu.style.display = (menu.style.display === 'none' || !menu.style.display) ? 'block' : 'none';
            }
        }

        function closePdfDropdown() {
            const menu = document.getElementById('pdfMenuDropdown');
            if (menu) menu.style.display = 'none';
        }

        document.addEventListener('click', (e) => {
            const container = document.getElementById('pdfExportDropdownContainer');
            if (container && !container.contains(e.target)) {
                closePdfDropdown();
            }
        });

        function exportToPdf(themeOption) {
            const targetTheme = themeOption || currentTheme || 'dark';
            const isDark = (targetTheme === 'dark');
            const rawFilename = document.getElementById('fileName').value.trim() || 'Document';
            const baseName = rawFilename.replace(/\.[^/.]+$/, "");
            const pdfFilename = baseName + (isDark ? '-dark.pdf' : '-light.pdf');

            const exportBtn = document.getElementById('btnExportPdf');
            const originalBtnHtml = exportBtn.innerHTML;
            exportBtn.disabled = true;
            exportBtn.innerHTML = `<span>⏳ Exporting ${targetTheme.toUpperCase()} PDF...</span>`;

            try {
                if (!window.jspdf || !window.jspdf.jsPDF) {
                    throw new Error('PDF generator library is still loading. Please try again in 1 second.');
                }

                const { jsPDF } = window.jspdf;
                const doc = new jsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: 'a4'
                });

                const margin = 20;
                const pageWidth = 210;
                const pageHeight = 297;
                const maxLineWidth = pageWidth - (margin * 2);
                let y = 25;

                const colors = isDark ? {
                    bg: [18, 18, 20],
                    title: [129, 140, 248],
                    subtitle: [156, 163, 175],
                    divider: [99, 102, 241],
                    h1: [255, 255, 255],
                    h2: [244, 244, 245],
                    h3: [228, 228, 231],
                    text: [212, 212, 216],
                    codeBg: [28, 28, 32],
                    codeBorder: [45, 45, 52],
                    codeText: [228, 228, 231],
                    quoteBar: [129, 140, 248],
                    quoteText: [199, 210, 254],
                    tableHeadBg: [39, 39, 42],
                    tableHeadText: [255, 255, 255],
                    tableRowBg: [24, 24, 27],
                    tableAltBg: [18, 18, 20],
                    tableText: [228, 228, 231],
                    tableBorder: [45, 45, 52]
                } : {
                    bg: [255, 255, 255],
                    title: [30, 27, 75],
                    subtitle: [107, 114, 128],
                    divider: [99, 102, 241],
                    h1: [17, 24, 39],
                    h2: [31, 41, 55],
                    h3: [55, 65, 81],
                    text: [55, 65, 81],
                    codeBg: [243, 244, 246],
                    codeBorder: [229, 231, 235],
                    codeText: [17, 24, 39],
                    quoteBar: [99, 102, 241],
                    quoteText: [79, 70, 229],
                    tableHeadBg: [243, 244, 246],
                    tableHeadText: [17, 24, 39],
                    tableRowBg: [255, 255, 255],
                    tableAltBg: [249, 250, 251],
                    tableText: [31, 41, 55],
                    tableBorder: [209, 213, 219]
                };

                function paintBackground() {
                    doc.setFillColor(colors.bg[0], colors.bg[1], colors.bg[2]);
                    doc.rect(0, 0, pageWidth, pageHeight, 'F');
                }

                paintBackground();

                doc.setFont("helvetica", "bold");
                doc.setFontSize(20);
                doc.setTextColor(colors.title[0], colors.title[1], colors.title[2]);
                doc.text(rawFilename, margin, y);
                y += 7;

                doc.setFont("helvetica", "normal");
                doc.setFontSize(9);
                doc.setTextColor(colors.subtitle[0], colors.subtitle[1], colors.subtitle[2]);
                doc.text(`Generated with File Fusion (${isDark ? 'Dark Edition' : 'Light Edition'}) • ${new Date().toLocaleDateString()}`, margin, y);
                y += 5;

                doc.setDrawColor(colors.divider[0], colors.divider[1], colors.divider[2]);
                doc.setLineWidth(0.7);
                doc.line(margin, y, pageWidth - margin, y);
                y += 10;

                const markdownText = document.getElementById('editor').value;
                const lines = markdownText.split('\n');

                let inCodeBlock = false;
                let codeBlockLines = [];
                let inTable = false;
                let tableRows = [];

                function checkPageBreak(neededHeight = 10) {
                    if (y + neededHeight > 275) {
                        doc.addPage();
                        paintBackground();
                        y = 20;
                    }
                }

                for (let i = 0; i < lines.length; i++) {
                    const line = lines[i];

                    if (line.trim().startsWith('```')) {
                        if (inCodeBlock) {
                            inCodeBlock = false;
                            doc.setFont("courier", "normal");
                            doc.setFontSize(9.5);
                            doc.setTextColor(colors.codeText[0], colors.codeText[1], colors.codeText[2]);

                            const codeText = codeBlockLines.join('\n');
                            const splitCode = doc.splitTextToSize(codeText, maxLineWidth - 8);
                            const blockHeight = (splitCode.length * 4.5) + 6;

                            checkPageBreak(blockHeight);

                            doc.setFillColor(colors.codeBg[0], colors.codeBg[1], colors.codeBg[2]);
                            doc.setDrawColor(colors.codeBorder[0], colors.codeBorder[1], colors.codeBorder[2]);
                            doc.rect(margin, y - 4, maxLineWidth, blockHeight, 'FD');

                            doc.text(splitCode, margin + 4, y + 1);
                            y += blockHeight + 6;
                            codeBlockLines = [];
                        } else {
                            inCodeBlock = true;
                            codeBlockLines = [];
                        }
                        continue;
                    }

                    if (inCodeBlock) {
                        codeBlockLines.push(line);
                        continue;
                    }

                    if (line.trim().startsWith('|') && line.trim().endsWith('|')) {
                        if (!inTable) inTable = true;
                        if (!line.includes('---')) {
                            const cols = line.split('|').slice(1, -1).map(c => c.trim());
                            tableRows.push(cols);
                        }
                        continue;
                    } else if (inTable) {
                        inTable = false;
                        if (tableRows.length > 0 && typeof doc.autoTable === 'function') {
                            const head = [tableRows[0]];
                            const body = tableRows.slice(1);
                            doc.autoTable({
                                head: head,
                                body: body,
                                startY: y,
                                margin: { left: margin, right: margin },
                                theme: 'grid',
                                styles: {
                                    fontSize: 9,
                                    cellPadding: 3,
                                    textColor: colors.tableText,
                                    fillColor: colors.tableRowBg,
                                    lineColor: colors.tableBorder
                                },
                                headStyles: {
                                    fillColor: colors.tableHeadBg,
                                    textColor: colors.tableHeadText,
                                    fontStyle: 'bold',
                                    lineColor: colors.tableBorder
                                },
                                alternateRowStyles: {
                                    fillColor: colors.tableAltBg
                                }
                            });
                            y = doc.lastAutoTable.finalY + 8;
                            tableRows = [];
                        }
                    }

                    const trimmed = line.trim();
                    if (!trimmed) {
                        y += 4;
                        continue;
                    }

                    if (trimmed.startsWith('# ')) {
                        checkPageBreak(14);
                        doc.setFont("helvetica", "bold");
                        doc.setFontSize(16);
                        doc.setTextColor(colors.h1[0], colors.h1[1], colors.h1[2]);
                        doc.text(trimmed.substring(2), margin, y);
                        y += 8;
                    } else if (trimmed.startsWith('## ')) {
                        checkPageBreak(12);
                        doc.setFont("helvetica", "bold");
                        doc.setFontSize(13.5);
                        doc.setTextColor(colors.h2[0], colors.h2[1], colors.h2[2]);
                        doc.text(trimmed.substring(3), margin, y);
                        y += 7;
                    } else if (trimmed.startsWith('### ')) {
                        checkPageBreak(10);
                        doc.setFont("helvetica", "bold");
                        doc.setFontSize(11.5);
                        doc.setTextColor(colors.h3[0], colors.h3[1], colors.h3[2]);
                        doc.text(trimmed.substring(4), margin, y);
                        y += 6;
                    } else if (trimmed.startsWith('> ')) {
                        checkPageBreak(10);
                        doc.setFont("helvetica", "italic");
                        doc.setFontSize(10);
                        doc.setTextColor(colors.quoteText[0], colors.quoteText[1], colors.quoteText[2]);
                        const qText = doc.splitTextToSize(trimmed.substring(2), maxLineWidth - 10);
                        const qHeight = qText.length * 4.5;

                        doc.setDrawColor(colors.quoteBar[0], colors.quoteBar[1], colors.quoteBar[2]);
                        doc.setLineWidth(1);
                        doc.line(margin, y - 3, margin, y + qHeight - 2);

                        doc.text(qText, margin + 4, y);
                        y += qHeight + 4;
                    } else if (trimmed.startsWith('- [ ] ') || trimmed.startsWith('- [x] ') || trimmed.startsWith('- ') || trimmed.startsWith('* ')) {
                        checkPageBreak(8);
                        doc.setFont("helvetica", "normal");
                        doc.setFontSize(10);
                        doc.setTextColor(colors.text[0], colors.text[1], colors.text[2]);

                        let prefix = '• ';
                        let content = trimmed;
                        if (trimmed.startsWith('- [x] ')) { prefix = '[X] '; content = trimmed.substring(6); }
                        else if (trimmed.startsWith('- [ ] ')) { prefix = '[ ] '; content = trimmed.substring(6); }
                        else if (trimmed.startsWith('- ') || trimmed.startsWith('* ')) { content = trimmed.substring(2); }

                        const lText = doc.splitTextToSize(prefix + content, maxLineWidth - 4);
                        doc.text(lText, margin + 2, y);
                        y += (lText.length * 4.8) + 2;
                    } else {
                        checkPageBreak(8);
                        doc.setFont("helvetica", "normal");
                        doc.setFontSize(10);
                        doc.setTextColor(colors.text[0], colors.text[1], colors.text[2]);

                        const cleanPara = trimmed.replace(/\*\*([^*]+)\*\*/g, '$1').replace(/\*([^*]+)\*/g, '$1').replace(/`([^`]+)`/g, '$1');
                        const pText = doc.splitTextToSize(cleanPara, maxLineWidth);
                        doc.text(pText, margin, y);
                        y += (pText.length * 4.8) + 3;
                    }
                }

                if (inTable && tableRows.length > 0 && typeof doc.autoTable === 'function') {
                    const head = [tableRows[0]];
                    const body = tableRows.slice(1);
                    doc.autoTable({
                        head: head,
                        body: body,
                        startY: y,
                        margin: { left: margin, right: margin },
                        theme: 'grid',
                        styles: {
                            fontSize: 9,
                            cellPadding: 3,
                            textColor: colors.tableText,
                            fillColor: colors.tableRowBg,
                            lineColor: colors.tableBorder
                        },
                        headStyles: {
                            fillColor: colors.tableHeadBg,
                            textColor: colors.tableHeadText,
                            fontStyle: 'bold',
                            lineColor: colors.tableBorder
                        },
                        alternateRowStyles: {
                            fillColor: colors.tableAltBg
                        }
                    });
                }

                doc.save(pdfFilename);

                exportBtn.disabled = false;
                exportBtn.innerHTML = `<span>✅ ${isDark ? 'Dark' : 'Light'} PDF Downloaded!</span>`;
                setTimeout(() => {
                    exportBtn.innerHTML = originalBtnHtml;
                }, 2500);

            } catch (err) {
                console.error('jsPDF generation error:', err);
                exportBtn.disabled = false;
                exportBtn.innerHTML = originalBtnHtml;
                alert('PDF generation error: ' + err.message);
            }
        }

        // =========================================================================
        // 6. UNIVERSAL TOAST NOTIFICATION SYSTEM (AUTO-DISMISS)
        // =========================================================================
        function showToast(message, type = 'success', duration = 3000) {
            let container = document.getElementById('ffToastContainer');
            if (!container) {
                container = document.createElement('div');
                container.id = 'ffToastContainer';
                container.style.position = 'fixed';
                container.style.top = '24px';
                container.style.right = '24px';
                container.style.zIndex = '999999';
                container.style.display = 'flex';
                container.style.flexDirection = 'column';
                container.style.gap = '10px';
                container.style.pointerEvents = 'none';
                container.style.maxWidth = '400px';
                container.style.width = 'calc(100% - 48px)';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.style.pointerEvents = 'auto';
            toast.style.padding = '12px 18px';
            toast.style.borderRadius = '10px';
            toast.style.background = 'rgba(24, 24, 27, 0.95)';
            toast.style.backdropFilter = 'blur(16px)';
            toast.style.border = '1px solid rgba(255, 255, 255, 0.12)';
            toast.style.boxShadow = '0 12px 30px rgba(0, 0, 0, 0.6)';
            toast.style.display = 'flex';
            toast.style.alignItems = 'center';
            toast.style.gap = '12px';
            toast.style.color = '#fff';
            toast.style.fontSize = '13px';
            toast.style.fontWeight = '500';
            toast.style.transform = 'translateX(120%)';
            toast.style.opacity = '0';
            toast.style.transition = 'all 0.35s cubic-bezier(0.16, 1, 0.3, 1)';

            let icon = '✅';
            let borderColor = '#10b981';

            if (type === 'error') {
                icon = '❌';
                borderColor = '#ef4444';
                toast.style.background = 'rgba(40, 15, 15, 0.95)';
            } else if (type === 'warning') {
                icon = '⚠️';
                borderColor = '#f59e0b';
            } else if (type === 'info') {
                icon = 'ℹ️';
                borderColor = '#6366f1';
            }

            toast.style.borderLeft = `4px solid ${borderColor}`;

            toast.innerHTML = `
                <span style="font-size: 18px; flex-shrink: 0;">${icon}</span>
                <div style="flex: 1; line-height: 1.4;">${escapeHtml(message)}</div>
            `;

            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.style.transform = 'translateX(0)';
                toast.style.opacity = '1';
            });

            setTimeout(() => {
                toast.style.transform = 'translateX(120%)';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 400);
            }, duration);
        }

        // =========================================================================
        // 7. WORKSPACE LOGIC & CLOUD STORAGE SYNC
        // =========================================================================
        document.addEventListener('DOMContentLoaded', () => {
            window.ff.icons();

            let files = JSON.parse(localStorage.getItem('textFiles')) || [];
            // Clean up any stale corrupted raw ciphertext from local storage universally
            files = files.filter(f => f && f.content && !String(f.content).includes('FF_ENC'));
            localStorage.setItem('textFiles', JSON.stringify(files));

            let currentFile = null;
            let lastFileName = localStorage.getItem('lastFileName') || null;

            const editor = document.getElementById('editor');
            const fileName = document.getElementById('fileName');
            const fileList = document.getElementById('fileList');
            const wordCountEl = document.getElementById('wordCount');
            const fileCountEl = document.getElementById('fileCount');
            const inspectorLines = document.getElementById('inspectorLines');
            const inspectorReadTime = document.getElementById('inspectorReadTime');

            const serverFileIdEl = document.getElementById('serverInitialFileId');
            let serverFileId = serverFileIdEl ? serverFileIdEl.value : null;

            if (serverFileId) {
                const serverName = document.getElementById('serverInitialFileName').value;
                const serverContent = document.getElementById('serverInitialContent') ? document.getElementById('serverInitialContent').value : editor.value;

                // Remove any existing entry matching this ID or name
                files = files.filter(f => String(f.id) !== String(serverFileId) && f.name !== serverName);

                const serverFile = {
                    id: serverFileId,
                    name: serverName,
                    content: serverContent,
                    createdAt: document.getElementById('serverInitialCreatedAt').value,
                    updatedAt: document.getElementById('serverInitialUpdatedAt').value
                };

                files.unshift(serverFile);
                localStorage.setItem('textFiles', JSON.stringify(files));
                currentFile = serverFile;

                editor.value = serverContent;
                fileName.value = serverName;
            }

            function createFile(name = 'Untitled.md', content = '') {
                const file = {
                    id: Date.now().toString(),
                    name,
                    content,
                    createdAt: new Date(),
                    updatedAt: new Date()
                };
                files.push(file);
                saveFiles(false);
                return file;
            }

            function saveFiles(isExplicit = false) {
                localStorage.setItem('textFiles', JSON.stringify(files));
                updateFileList();

                $.ajax({
                    type: "POST",
                    url: "{{ route('panel.createfile') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        files: files,
                        lastFileName: lastFileName
                    },
                    success: function(response) {
                        const autoSaveEl = document.getElementById('autoSaveIndicator');
                        if (autoSaveEl) autoSaveEl.textContent = '● Synced to Cloud Storage';

                        // Synchronize server DB IDs back to client drafts
                        if (response.synced_files && Array.isArray(response.synced_files)) {
                            response.synced_files.forEach(synced => {
                                const target = files.find(f => f.id === synced.client_id || f.name === synced.name);
                                if (target) {
                                    target.id = synced.db_id;
                                    if (currentFile && (currentFile.id === synced.client_id || currentFile.name === synced.name)) {
                                        currentFile.id = synced.db_id;
                                    }
                                }
                            });
                            localStorage.setItem('textFiles', JSON.stringify(files));
                        }

                        if (isExplicit) {
                            showToast('File saved and encrypted successfully!', 'success', 2500);
                        }
                    },
                    error: function(xhr) {
                        const autoSaveEl = document.getElementById('autoSaveIndicator');
                        if (autoSaveEl) {
                            autoSaveEl.textContent = '● Save failed (Quota / Offline)';
                            autoSaveEl.style.color = '#ef4444';
                        }

                        const errorMsg = xhr.responseJSON && xhr.responseJSON.info 
                            ? xhr.responseJSON.info 
                            : 'Unable to save to cloud. Saved locally.';
                        
                        showToast(errorMsg, 'error', 4500);
                    }
                });
            }

            function saveLastFileName(name) {
                lastFileName = name;
                localStorage.setItem('lastFileName', lastFileName);
            }

            function updateFileList() {
                fileList.innerHTML = '';
                fileCountEl.textContent = `${files.length} file${files.length === 1 ? '' : 's'}`;

                if (files.length === 0) {
                    fileList.innerHTML = '<p class="ff-hint" style="margin:0; font-size:12px;">No drafts yet.</p>';
                    return;
                }

                const isLight = (currentTheme === 'light');

                files.forEach(file => {
                    const row = document.createElement('div');
                    const isActive = (currentFile && String(currentFile.id) === String(file.id));
                    row.className = `ff-draft-item ${isLight ? 'is-light' : 'is-dark'} ${isActive ? 'is-active' : ''}`;

                    const lang = detectLanguage(file.name, file.content);

                    row.innerHTML = `
                        <div style="display:flex; align-items:center; gap:8px; min-width:0;">
                            <span style="font-size:16px;">${lang.icon}</span>
                            <div style="min-width:0;">
                                <div class="ff-draft-title">${escapeHtml(file.name)}</div>
                                <div class="ff-draft-date">${new Date(file.updatedAt).toLocaleDateString()}</div>
                            </div>
                        </div>
                        <button type="button" class="ff-menu-btn delete-draft" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:14px; padding:2px 4px; border-radius:4px;" title="Delete draft">
                            🗑️
                        </button>
                    `;

                    row.querySelector('.delete-draft').addEventListener('click', (e) => {
                        e.stopPropagation();
                        deleteFile(file.id);
                    });

                    row.addEventListener('click', () => openFile(file.id));
                    fileList.appendChild(row);
                });
            }

            window.ffUpdateDraftList = updateFileList;

            function openFile(id) {
                currentFile = files.find(f => String(f.id) === String(id));
                if (currentFile) {
                    if (serverFileId && String(serverFileId) === String(id) && document.getElementById('serverInitialContent')) {
                        currentFile.content = document.getElementById('serverInitialContent').value;
                    }
                    editor.value = currentFile.content;
                    fileName.value = currentFile.name;
                    const isMd = currentFile.name.endsWith('.md') || currentFile.name.endsWith('.markdown') || !currentFile.name.includes('.');
                    setViewMode(isMd ? 'split' : 'editor');
                    updateStats();
                    saveLastFileName(currentFile.name);
                    updateFileList();
                }
            }

            async function deleteFile(id) {
                const fileToDelete = files.find(f => f.id === id);
                if (!fileToDelete) return;

                const confirmed = await window.ff.confirm({
                    title: '🗑️ Delete Draft',
                    message: `Delete draft "${fileToDelete.name}"?`,
                    confirmText: 'Delete Draft',
                    isDanger: true
                });

                if (!confirmed) return;

                files = files.filter(f => f.id !== id);
                if (currentFile && currentFile.id === id) {
                    currentFile = files[0] || null;
                    if (currentFile) {
                        openFile(currentFile.id);
                    } else {
                        editor.value = '';
                        fileName.value = 'Untitled.md';
                        setViewMode('split');
                        updateStats();
                    }
                }
                saveFiles(false);
                window.ff.toast('Draft deleted.', 'info', 2000);
            }

            function updateStats() {
                const text = editor.value;
                const words = text.trim() ? text.trim().split(/\s+/).length : 0;
                const chars = text.length;
                const lines = text.split('\n').length;
                const readMinutes = Math.max(1, Math.ceil(words / 200));

                wordCountEl.textContent = `${words} words · ${chars} characters`;
                inspectorLines.textContent = lines;
                inspectorReadTime.textContent = words > 10 ? `${readMinutes} min read` : '< 1 min';

                renderLivePreview();
            }

            editor.addEventListener('input', () => {
                if (currentFile) {
                    currentFile.content = editor.value;
                    currentFile.updatedAt = new Date();
                    saveFiles();
                }
                updateStats();
            });

            editor.addEventListener('keyup', updateStats);

            fileName.addEventListener('input', () => {
                const isMd = fileName.value.endsWith('.md') || fileName.value.endsWith('.markdown') || !fileName.value.includes('.');
                if (!isMd) {
                    setViewMode('editor');
                }
                updateStats();
            });

            fileName.addEventListener('change', () => {
                if (currentFile) {
                    currentFile.name = fileName.value;
                    currentFile.updatedAt = new Date();
                    saveLastFileName(currentFile.name);
                    saveFiles();
                }
            });

            document.getElementById('newFile').addEventListener('click', () => {
                currentFile = createFile('Untitled.md', '');
                editor.value = '';
                fileName.value = 'Untitled.md';
                saveLastFileName('Untitled.md');
                setViewMode('split');
                updateStats();
                updateFileList();
            });

            const isMac = (navigator.platform && navigator.platform.toUpperCase().indexOf('MAC') >= 0) || (navigator.userAgent && navigator.userAgent.toUpperCase().indexOf('MAC') >= 0);
            const shortcutKeyText = isMac ? '⌘S' : 'Ctrl+S';

            document.getElementById('saveFile').title = `Save file to cloud (${shortcutKeyText})`;

            function performSave() {
                const saveBtn = document.getElementById('saveFile');
                const originalBtnHtml = saveBtn.innerHTML;

                if (currentFile) {
                    currentFile.content = editor.value;
                    currentFile.name = fileName.value;
                    currentFile.updatedAt = new Date();
                    saveFiles(true);
                } else {
                    currentFile = createFile(fileName.value, editor.value);
                    saveFiles(true);
                }

                saveBtn.innerHTML = `<span>✅ Saved</span>`;
                const autoSaveEl = document.getElementById('autoSaveIndicator');
                if (autoSaveEl) {
                    autoSaveEl.textContent = `● Saved (${shortcutKeyText})`;
                    autoSaveEl.style.color = '#10b981';
                }

                setTimeout(() => {
                    saveBtn.innerHTML = originalBtnHtml;
                }, 1800);
            }

            document.getElementById('saveFile').addEventListener('click', performSave);

            // Global Keybinding: Ctrl + S (Windows/Linux) & Command + S (macOS ⌘S)
            window.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && (e.key.toLowerCase() === 's' || e.code === 'KeyS')) {
                    e.preventDefault(); // Intercept browser native 'Save Page As' dialog
                    performSave();
                }
            });

            if (serverFileId) {
                openFile(serverFileId);
            } else {
                if (files.length === 0) {
                    currentFile = createFile('Welcome.md', '# Welcome to Intelligent Markdown Editor 🚀\n\nType **Markdown**, Python, JavaScript, HTML, Go, or SQL on the left — and watch it **render live in real time** on the right!\n\n### Features:\n- [x] **Live Split-Screen Rendering**\n- [x] **Light & Dark Theme Switcher**\n- [x] **Theme-Matching Vector PDF Export**\n- [x] **AES-256-GCM Encrypted Cloud Storage**\n\n| Feature | Status | Theme Match |\n| :--- | :--- | :--- |\n| Dark Preview | 🌙 Default | ✅ Active |\n| Light Preview | ☀️ Supported | ✅ Active |\n| PDF Export | 📄 Vector | 🎨 Matches Active Theme |\n\n> "Security and developer productivity in one seamless platform."\n\n```python\n# Example Python Snippet\ndef calculate_sha256(data):\n    import hashlib\n    return hashlib.sha256(data.encode()).hexdigest()\n```\n');
                } else if (lastFileName) {
                    const lastFile = files.find(f => f.name === lastFileName);
                    openFile(lastFile ? lastFile.id : files[0].id);
                } else {
                    openFile(files[0].id);
                }
            }

            const initialTheme = document.documentElement.dataset.theme || localStorage.getItem('theme') || 'dark';
            setTheme(initialTheme);

            // Synchronize with top navbar theme toggle automatically
            const themeObserver = new MutationObserver(() => {
                if (isSettingTheme) return;
                const currentGlobalTheme = document.documentElement.dataset.theme || 'dark';
                if (currentGlobalTheme !== currentTheme) {
                    setTheme(currentGlobalTheme);
                }
            });
            themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

            window.addEventListener('ff:theme-change', (e) => {
                if (isSettingTheme) return;
                const currentGlobalTheme = e.detail?.theme || document.documentElement.dataset.theme || 'dark';
                if (currentGlobalTheme !== currentTheme) {
                    setTheme(currentGlobalTheme);
                }
            });

            updateStats();
            updateFileList();
        });
    </script>
@endsection
