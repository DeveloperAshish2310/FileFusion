<style>
    @media (max-width: 640px) {
        #previewModalBackdrop {
            padding: 8px !important;
        }
        .ff-preview-card {
            max-height: 96vh !important;
            border-radius: 12px !important;
        }
        .ff-preview-header {
            padding: 10px 12px !important;
            gap: 8px !important;
        }
        .ff-preview-title {
            font-size: 13.5px !important;
            max-width: 170px !important;
        }
        .ff-preview-sub {
            font-size: 11px !important;
        }
        .ff-preview-body {
            padding: 10px !important;
            min-height: 260px !important;
        }
        .ff-preview-download-text {
            display: none !important;
        }
    }
</style>

<div class="ff-modal-backdrop" id="previewModalBackdrop" style="position: fixed; inset: 0; background: rgba(0, 0, 0, 0.75); z-index: 10000; display: flex; align-items: center; justify-content: center; padding: 20px; animation: ffFadeIn 0.2s ease;">
    <div class="ff-card ff-preview-card" style="width: 100%; max-width: 960px; max-height: 90vh; display: flex; flex-direction: column; background: var(--ff-card, #ffffff) !important; color: var(--ff-text, #0f172a) !important; border-radius: 16px; border: 1px solid var(--ff-border, #e2e8f0); overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
        
        {{-- Modal Header --}}
        <div class="ff-preview-header" style="display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; border-bottom: 1px solid var(--ff-border, #e2e8f0); background: var(--ff-bg-2, #f8fafc); gap: 12px;">
            <div style="display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1;">
                <span class="ff-tile-icon" style="flex-shrink: 0; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; background: rgba(99, 102, 241, 0.15); color: #6366f1; border-radius: 8px; font-size: 16px;">
                    @if ($category === 'image') 🖼️
                    @elseif ($category === 'video') 🎬
                    @elseif ($category === 'audio') 🎵
                    @elseif ($category === 'pdf') 📄
                    @elseif ($category === 'spreadsheet') 📊
                    @elseif ($category === 'code') 💻
                    @else 📁
                    @endif
                </span>
                <div style="min-width: 0; flex: 1;">
                    <div class="ff-preview-title" style="font-weight: 700; font-size: 14.5px; color: var(--ff-text, #0f172a); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $file->name }}
                    </div>
                    <div class="ff-preview-sub" style="font-size: 11.5px; color: var(--ff-text-2, #64748b); display: flex; gap: 6px; align-items: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 1px;">
                        <span>{{ strtoupper($ext) }}</span>
                        <span>•</span>
                        <span>{{ BytetoSize($file->size) }}</span>
                        <span class="ff-hide-mobile" style="color: #10b981; font-weight: 600;">• 🔒 AES-256</span>
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                <a href="{{ $downloadUrl }}" class="ff-btn" style="padding: 6px 12px; font-size: 12.5px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;" download title="Download Decrypted File">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <span class="ff-preview-download-text">Download</span>
                </a>
                <button type="button" onclick="closeUniversalPreviewModal()" class="ff-hint" style="padding: 4px; background: none; border: none; color: var(--ff-muted, #64748b); cursor: pointer; display: flex; align-items: center; justify-content: center;" title="Close preview">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
        </div>

        {{-- Modal Body Viewport --}}
        <div class="ff-preview-body" style="flex: 1; overflow-y: auto; padding: 20px; background: var(--ff-bg, #f8fafc); display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 350px;">

            @if ($category === 'image')
                {{-- Image Lightbox --}}
                <div style="max-width: 100%; max-height: 70vh; display: flex; align-items: center; justify-content: center;">
                    <img src="{{ $previewUrl }}" alt="{{ $file->name }}" style="max-width: 100%; max-height: 68vh; object-fit: contain; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
                </div>

            @elseif ($category === 'video')
                {{-- Video Player --}}
                <div style="width: 100%; max-width: 800px;">
                    <video controls autoplay style="width: 100%; max-height: 65vh; border-radius: 10px; background: #000; outline: none;" controlsList="nodownload">
                        <source src="{{ $previewUrl }}" type="{{ $file->type ?? 'video/mp4' }}">
                        Your browser does not support HTML5 video preview.
                    </video>
                </div>

            @elseif ($category === 'audio')
                {{-- Audio Player --}}
                <div style="width: 100%; max-width: 500px; padding: 30px; text-align: center; background: var(--ff-surface, #27272a); border-radius: 12px;">
                    <div style="font-size: 48px; margin-bottom: 16px;">🎵</div>
                    <h4 style="font-weight: 700; margin-bottom: 16px; color: var(--ff-text, #fff);">{{ $file->name }}</h4>
                    <audio controls style="width: 100%; outline: none;" autoplay controlsList="nodownload">
                        <source src="{{ $previewUrl }}" type="{{ $file->type ?? 'audio/mpeg' }}">
                        Your browser does not support HTML5 audio playback.
                    </audio>
                </div>

            @elseif ($category === 'pdf')
                {{-- PDF Sandbox Viewport --}}
                <div style="width: 100%; height: 70vh;">
                    <iframe src="{{ $previewUrl }}#toolbar=1" style="width: 100%; height: 100%; border: none; border-radius: 10px; background: #525659;" sandbox="allow-scripts allow-same-origin allow-forms"></iframe>
                </div>

            @elseif ($category === 'spreadsheet')
                {{-- CSV / Spreadsheet Interactive Data Grid --}}
                <div style="width: 100%; height: 68vh; display: flex; flex-direction: column;" id="csvContainer">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; gap: 10px;">
                        <input type="text" id="csvSearch" placeholder="Search data in table..." class="ff-input" style="max-width: 300px; font-size: 12px;" onkeyup="filterCsvTable()">
                        <span id="csvRowCount" style="font-size: 12px; color: var(--ff-muted, #a1a1aa);">Loading rows...</span>
                    </div>
                    <div style="flex: 1; overflow: auto; border: 1px solid var(--ff-border, #27272a); border-radius: 8px; background: var(--ff-surface, #18181b);">
                        <table id="csvTable" style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                            <thead id="csvThead" style="position: sticky; top: 0; background: #27272a; color: #fff; z-index: 2;">
                                <tr><th style="padding: 10px 14px;">Loading columns...</th></tr>
                            </thead>
                            <tbody id="csvTbody">
                                <tr><td style="padding: 20px; text-align: center; color: var(--ff-muted, #a1a1aa);">Fetching and parsing decrypted CSV data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <script>
                    (function() {
                        fetch("{{ route('panel.previewCsv', encrypt($file->id)) }}")
                            .then(r => r.json())
                            .then(data => {
                                if (data.ok && data.headers) {
                                    let theadHtml = '<tr>';
                                    data.headers.forEach(h => {
                                        theadHtml += `<th style="padding: 10px 14px; border-bottom: 1px solid #3f3f46; border-right: 1px solid #27272a; white-space: nowrap;">${h || ''}</th>`;
                                    });
                                    theadHtml += '</tr>';
                                    document.getElementById('csvThead').innerHTML = theadHtml;

                                    let tbodyHtml = '';
                                    data.rows.forEach((r, idx) => {
                                        tbodyHtml += `<tr style="border-bottom: 1px solid #27272a;">`;
                                        r.forEach(cell => {
                                            tbodyHtml += `<td style="padding: 8px 14px; border-right: 1px solid #27272a; white-space: nowrap; color: #e4e4e7;">${cell || ''}</td>`;
                                        });
                                        tbodyHtml += `</tr>`;
                                    });
                                    document.getElementById('csvTbody').innerHTML = tbodyHtml;
                                    document.getElementById('csvRowCount').textContent = `Showing ${data.rows.length} rows (sampled)`;
                                } else {
                                    document.getElementById('csvTbody').innerHTML = `<tr><td style="padding:20px; color:#ef4444; text-align:center;">Could not parse spreadsheet data.</td></tr>`;
                                }
                            })
                            .catch(err => {
                                document.getElementById('csvTbody').innerHTML = `<tr><td style="padding:20px; color:#ef4444; text-align:center;">Error loading CSV: ${err}</td></tr>`;
                            });
                    })();

                    function filterCsvTable() {
                        let query = document.getElementById('csvSearch').value.toLowerCase();
                        let rows = document.querySelectorAll('#csvTbody tr');
                        rows.forEach(tr => {
                            let text = tr.innerText.toLowerCase();
                            tr.style.display = text.includes(query) ? '' : 'none';
                        });
                    }
                </script>

            @elseif ($category === 'code')
                {{-- Code & Text Viewer --}}
                <div style="width: 100%; height: 70vh; display: flex; flex-direction: column;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; background: rgba(99,102,241,0.15); color: #6366f1; padding: 3px 8px; border-radius: 4px;">
                            {{ strtoupper($ext) }} SOURCE
                        </span>
                        <button type="button" class="ff-btn" onclick="copyCodePreview()" id="copyCodeBtn" style="padding: 4px 10px; font-size: 12px;">
                            📋 Copy Text
                        </button>
                    </div>
                    <pre id="codePreviewContent" style="flex: 1; margin: 0; padding: 16px; background: #121214; border: 1px solid var(--ff-border, #27272a); border-radius: 10px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 13px; line-height: 1.6; color: #e4e4e7; overflow: auto; white-space: pre-wrap; word-break: break-word;">{{ $codeContent }}</pre>
                </div>

                <script>
                    function copyCodePreview() {
                        let el = document.getElementById('codePreviewContent');
                        if (!el) return;
                        let code = el.innerText || el.textContent;
                        if (window.ff && typeof window.ff.copy === 'function') {
                            window.ff.copy(code, 'Code copied to clipboard!');
                        } else if (window.copyToClipboard) {
                            window.copyToClipboard(code, 'Code copied to clipboard!');
                        }
                        let btn = document.getElementById('copyCodeBtn');
                        if (btn) {
                            btn.textContent = '✅ Copied!';
                            setTimeout(() => { btn.textContent = '📋 Copy Text'; }, 2000);
                        }
                    }
                </script>

            @else
                {{-- Fallback Card --}}
                <div style="text-align: center; padding: 40px; color: var(--ff-muted, #a1a1aa);">
                    <div style="font-size: 48px; margin-bottom: 14px;">📁</div>
                    <h3 style="color: var(--ff-text, #fff); font-weight: 700; margin-bottom: 8px;">No Direct In-Browser Preview Available</h3>
                    <p style="font-size: 13px; max-width: 400px; margin: 0 auto 20px auto;">
                        This file format ({{ $ext ? '.' . $ext : 'binary' }}) cannot be safely previewed in the browser. You can download the decrypted file directly.
                    </p>
                    <a href="{{ $downloadUrl }}" class="ff-btn is-primary" style="background: #6366f1; border-color: #6366f1; text-decoration: none;" download>
                        Download {{ $file->name }}
                    </a>
                </div>
            @endif

        </div>

    </div>
</div>
