@extends('layout.backend')
@push('title', 'Storage Cleaner')
@section('page-width', 'ff-page-narrow')

@section('content')
    <div class="ff-row-between" style="align-items:flex-start; margin-bottom:22px;">
        <div>
            <h1 class="ff-h1">Storage Cleaner</h1>
            <p class="ff-sub" style="margin-bottom:0;">Clean up unused thumbnail files and free up space</p>
        </div>
        <a href="{{ route('panel.dashboard') }}" class="ff-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5M12 19l-7-7 7-7" />
            </svg>
            Back
        </a>
    </div>

    <div class="ff-alert">
        <i data-lucide="info" class="w-4 h-4"></i>
        <span>
            This tool scans your Browsershot directory and removes thumbnail screenshots that are no longer attached to
            any link. Your active links are untouched.
        </span>
    </div>

    <div class="ff-form-card">

        {{-- ------------------------------ Idle state ------------------------------ --}}
        <div id="before-cleanup">
            <div class="ff-empty">
                <span class="ff-empty-icon" style="width:64px; height:64px; border-radius:16px; color:var(--ff-accent);">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18" />
                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                        <path d="M5 6l1 14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-14" />
                        <path d="M12 11v6" />
                    </svg>
                </span>
                <div class="ff-section-title" style="font-size:17px;">Ready to clean storage</div>
                <p class="ff-empty-text" style="max-width:420px; margin:0 0 16px 0;">
                    Scan for unused thumbnail files, clear application cache, and selectively purge trash storage.
                </p>

                <!-- Selective Trash & Storage Purge Policy Controls -->
                <div style="margin: 16px 0; padding: 20px; background: var(--ff-bg2); border: 1px solid var(--ff-border); border-radius: 14px; text-align: left; max-width: 580px; width: 100%;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; border-bottom: 1px solid var(--ff-border); padding-bottom: 8px;">
                        <div style="font-weight: 800; font-size: 14.5px; color: var(--ff-text);">
                            Live Pre-Scan & Target Selection
                        </div>
                        <button type="button" id="btn-refresh-scan" class="ff-btn" style="font-size: 11.5px; padding: 4px 10px; height: auto; border-radius: 6px;">
                            Refresh Live Scan
                        </button>
                    </div>

                    <p style="font-size: 12px; color: var(--ff-muted); margin: 0 0 14px 0; line-height: 1.5;">
                        Review exact scanned values in each section below. Check the specific items you wish to purge:
                    </p>

                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; padding: 8px; border-radius: 8px; background: var(--ff-surface);">
                            <input type="checkbox" id="chk_target_screenshots" value="screenshots" checked style="width: 18px; height: 18px; margin-top: 2px; accent-color: var(--ff-accent);">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 13.5px; font-weight: 700; color: var(--ff-text);">Orphan Screenshots</span>
                                    <span id="badge_screenshots" style="font-size: 12px; font-weight: 800; color: var(--ff-text); background: var(--ff-bg2); padding: 2px 8px; border-radius: 12px; border: 1px solid var(--ff-border);">
                                        {{ $scannedStats['screenshots']['count'] ?? 0 }} files ({{ $scannedStats['screenshots']['formatted'] ?? '0 B' }})
                                    </span>
                                </div>
                                <div style="font-size: 11.5px; color: var(--ff-muted); margin-top: 2px;">Unused thumbnail files in Browsershot directory not linked to active bookmarks.</div>
                            </div>
                        </label>

                        <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; padding: 8px; border-radius: 8px; background: var(--ff-surface);">
                            <input type="checkbox" id="chk_trash_full" value="storage_full" style="width: 18px; height: 18px; margin-top: 2px; accent-color: var(--ff-accent);">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 13.5px; font-weight: 700; color: var(--ff-text);">Trash: Full Storage Accounts (≥90% Quota)</span>
                                    <span id="badge_trashed_full" style="font-size: 12px; font-weight: 800; color: var(--ff-text); background: var(--ff-bg2); padding: 2px 8px; border-radius: 12px; border: 1px solid var(--ff-border);">
                                        {{ $scannedStats['trashed_full']['count'] ?? 0 }} files ({{ $scannedStats['trashed_full']['formatted'] ?? '0 B' }})
                                    </span>
                                </div>
                                <div style="font-size: 11.5px; color: var(--ff-muted); margin-top: 2px;">Purge soft-deleted trash for accounts at or near capacity limit.</div>
                            </div>
                        </label>

                        <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; padding: 8px; border-radius: 8px; background: var(--ff-surface);">
                            <input type="checkbox" id="chk_trash_non_admin" value="non_admin" style="width: 18px; height: 18px; margin-top: 2px; accent-color: var(--ff-accent);">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 13.5px; font-weight: 700; color: var(--ff-text);">Trash: Standard Non-Admin Accounts</span>
                                    <span id="badge_trashed_non_admin" style="font-size: 12px; font-weight: 800; color: var(--ff-text); background: var(--ff-bg2); padding: 2px 8px; border-radius: 12px; border: 1px solid var(--ff-border);">
                                        {{ $scannedStats['trashed_non_admin']['count'] ?? 0 }} files ({{ $scannedStats['trashed_non_admin']['formatted'] ?? '0 B' }})
                                    </span>
                                </div>
                                <div style="font-size: 11.5px; color: var(--ff-muted); margin-top: 2px;">Purge soft-deleted trash for regular user accounts.</div>
                            </div>
                        </label>

                        <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; padding: 8px; border-radius: 8px; background: var(--ff-surface);">
                            <input type="checkbox" id="chk_trash_all" value="all_manual" style="width: 18px; height: 18px; margin-top: 2px; accent-color: var(--ff-accent);">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 13.5px; font-weight: 700; color: var(--ff-text);">Trash: All Soft-Deleted Records</span>
                                    <span id="badge_trashed_total" style="font-size: 12px; font-weight: 800; color: var(--ff-text); background: var(--ff-bg2); padding: 2px 8px; border-radius: 12px; border: 1px solid var(--ff-border);">
                                        {{ $scannedStats['trashed_total']['count'] ?? 0 }} files ({{ $scannedStats['trashed_total']['formatted'] ?? '0 B' }})
                                    </span>
                                </div>
                                <div style="font-size: 11.5px; color: var(--ff-muted); margin-top: 2px;">Manual authorization to permanently delete all soft-deleted trash.</div>
                            </div>
                        </label>

                        <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; padding: 8px; border-radius: 8px; background: var(--ff-surface);">
                            <input type="checkbox" id="chk_target_shares" value="shares" checked style="width: 18px; height: 18px; margin-top: 2px; accent-color: var(--ff-accent);">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 13.5px; font-weight: 700; color: var(--ff-text);">Expired Share Tokens</span>
                                    <span id="badge_expired_shares" style="font-size: 12px; font-weight: 800; color: var(--ff-text); background: var(--ff-bg2); padding: 2px 8px; border-radius: 12px; border: 1px solid var(--ff-border);">
                                        {{ $scannedStats['expired_shares']['count'] ?? 0 }} expired tokens
                                    </span>
                                </div>
                                <div style="font-size: 11.5px; color: var(--ff-muted); margin-top: 2px;">Remove expired private and public share link tokens.</div>
                            </div>
                        </label>

                        <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; padding: 8px; border-radius: 8px; background: var(--ff-surface);">
                            <input type="checkbox" id="chk_target_cache" value="cache" checked style="width: 18px; height: 18px; margin-top: 2px; accent-color: var(--ff-accent);">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 13.5px; font-weight: 700; color: var(--ff-text);">Application & View Cache</span>
                                    <span style="font-size: 12px; font-weight: 800; color: var(--ff-text); background: var(--ff-bg2); padding: 2px 8px; border-radius: 12px; border: 1px solid var(--ff-border);">
                                        Blade & System Cache
                                    </span>
                                </div>
                                <div style="font-size: 11.5px; color: var(--ff-muted); margin-top: 2px;">Reset compiled view templates and framework application cache.</div>
                            </div>
                        </label>
                    </div>
                </div>

                <button type="button" id="start-cleanup-btn" class="ff-btn ff-btn-primary" style="margin-top:6px; padding: 12px 28px; font-size: 14px; font-weight: 800;">
                    Delete Selected Items
                </button>
            </div>
        </div>




        {{-- ----------------------------- Working state ----------------------------- --}}
        <div id="during-cleanup" hidden>
            <div class="ff-empty">
                <span class="ff-empty-icon" style="width:64px; height:64px; border-radius:16px; color:var(--ff-accent);">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="ff-spin">
                        <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                    </svg>
                </span>
                <div class="ff-section-title" style="font-size:17px;">Cleaning storage…</div>
                <p class="ff-empty-text" style="margin:0;">Scanning and removing unused files.</p>
            </div>
        </div>

        {{-- ------------------------------ Done state ------------------------------ --}}
        <div id="after-cleanup" hidden>
            <div class="ff-alert is-success">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                <span>Storage cleaned. Unused files have been removed.</span>
            </div>

            <div class="ff-split-even" style="margin-bottom:18px;">
                <div class="ff-card">
                    <div class="ff-row-between" style="flex-wrap:nowrap;">
                        <div>
                            <div class="ff-hint" style="font-weight:600;">Files Deleted</div>
                            <div id="deleted-count"
                                style="font-family:var(--ff-font-display); font-size:30px; font-weight:700; color:var(--ff-text); margin-top:4px;">
                                0</div>
                            <div class="ff-hint">Unused thumbnails</div>
                        </div>
                        <span class="ff-tile-icon">
                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                        </span>
                    </div>
                </div>

                <div class="ff-card">
                    <div class="ff-row-between" style="flex-wrap:nowrap;">
                        <div>
                            <div class="ff-hint" style="font-weight:600;">Space Freed</div>
                            <div id="freed-space"
                                style="font-family:var(--ff-font-display); font-size:30px; font-weight:700; color:var(--ff-text); margin-top:4px;">
                                0 B</div>
                            <div class="ff-hint">Storage reclaimed</div>
                        </div>
                        <span class="ff-tile-icon">
                            <i data-lucide="hard-drive" class="w-5 h-5"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div class="ff-list">
                <div class="ff-list-row">
                    <span class="ff-grow ff-soft" style="font-size:13.5px;">Files scanned</span>
                    <span class="ff-list-title" id="files-scanned">—</span>
                </div>
                <div class="ff-list-row">
                    <span class="ff-grow ff-soft" style="font-size:13.5px;">Files removed</span>
                    <span class="ff-list-title" id="files-removed">—</span>
                </div>
                <div class="ff-list-row">
                    <span class="ff-grow ff-soft" style="font-size:13.5px;">Space recovered</span>
                    <span class="ff-list-title" id="space-recovered">—</span>
                </div>
            </div>

            <div id="detailed-summary-list" style="margin-top: 14px;"></div>


            <div class="ff-form-actions" style="margin-top:18px;">
                <a href="{{ route('panel.dashboard') }}" class="ff-btn">Back to Dashboard</a>
                <button type="button" id="cleanup-again-btn" class="ff-btn ff-btn-primary">Clean Again</button>
            </div>
        </div>

        {{-- ------------------------------ Error state ------------------------------ --}}
        <div id="error-cleanup" hidden>
            <div class="ff-alert is-error">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                <span id="error-message">An error occurred during cleanup.</span>
            </div>
            <button type="button" id="retry-cleanup-btn" class="ff-btn ff-btn-primary ff-btn-block">Try Again</button>
        </div>
    </div>

    <div class="ff-form-card" style="margin-top:20px;">
        <div class="ff-section-head">
            <span class="ff-section-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z" />
                </svg>
            </span>
            <div>
                <div class="ff-section-title">Good to know</div>
                <div class="ff-section-sub">How the cleaner behaves</div>
            </div>
        </div>

        <div class="ff-stack-sm">
            <div class="ff-toggle-row">
                <div>
                    <div class="ff-toggle-label">Safe by design</div>
                    <div class="ff-toggle-sub">Only thumbnails with no matching link are removed.</div>
                </div>
            </div>
            <div class="ff-toggle-row">
                <div>
                    <div class="ff-toggle-label">Run it any time</div>
                    <div class="ff-toggle-sub">Thumbnails regenerate automatically for links that need them.</div>
                </div>
            </div>
            <div class="ff-toggle-row">
                <div>
                    <div class="ff-toggle-label">Space is freed immediately</div>
                    <div class="ff-toggle-sub">Recovered space is available for new uploads straight away.</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('push-script')
    <script>
        function refreshLiveScan() {
            const btnRefresh = $('#btn-refresh-scan');
            btnRefresh.text('⏳ Scanning...');
            
            $.ajax({
                url: '{{ route('panel.scanStorage') }}',
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(res) {
                    btnRefresh.text('🔄 Refresh Live Scan');
                    if (res.ok && res.stats) {
                        const s = res.stats;
                        $('#badge_screenshots').text(`${s.screenshots.count} files (${s.screenshots.formatted})`);
                        $('#badge_trashed_full').text(`${s.trashed_full.count} files (${s.trashed_full.formatted})`);
                        $('#badge_trashed_non_admin').text(`${s.trashed_non_admin.count} files (${s.trashed_non_admin.formatted})`);
                        $('#badge_trashed_total').text(`${s.trashed_total.count} files (${s.trashed_total.formatted})`);
                        $('#badge_expired_shares').text(`${s.expired_shares.count} expired tokens`);
                    }
                },
                error: function() {
                    btnRefresh.text('🔄 Refresh Live Scan');
                }
            });
        }

        function performCleanup() {
            // Collect checked target categories
            const selectedTargets = [];
            if ($('#chk_target_screenshots').is(':checked')) selectedTargets.push('screenshots');
            if ($('#chk_target_shares').is(':checked')) selectedTargets.push('shares');
            if ($('#chk_target_cache').is(':checked')) selectedTargets.push('cache');

            const isFull = $('#chk_trash_full').is(':checked');
            const isNonAdmin = $('#chk_trash_non_admin').is(':checked');
            const isAll = $('#chk_trash_all').is(':checked');

            let trashFilter = '';
            if (isAll) {
                trashFilter = 'all_manual';
                selectedTargets.push('trashed');
            } else if (isFull && isNonAdmin) {
                trashFilter = 'both';
                selectedTargets.push('trashed');
            } else if (isFull) {
                trashFilter = 'storage_full';
                selectedTargets.push('trashed');
            } else if (isNonAdmin) {
                trashFilter = 'non_admin';
                selectedTargets.push('trashed');
            }

            if (selectedTargets.length === 0) {
                alert('Please check at least one section checkbox to delete.');
                return;
            }

            document.getElementById('before-cleanup').hidden = true;
            document.getElementById('after-cleanup').hidden = true;
            document.getElementById('error-cleanup').hidden = true;
            document.getElementById('during-cleanup').hidden = false;

            const targetParam = selectedTargets.join(',');

            $.ajax({
                url: `{{ route('panel.cleanStorage') }}?target=${targetParam}&trash_filter=${trashFilter}`,
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    document.getElementById('during-cleanup').hidden = true;

                    if (response.status === 'success') {
                        document.getElementById('after-cleanup').hidden = false;
                        $('#deleted-count').text(response.deleted_files_count);
                        $('#freed-space').text(response.freed_space_human_readable);
                        $('#files-scanned').text(response.deleted_files_count);
                        $('#files-removed').text(response.deleted_files_count);
                        $('#space-recovered').text(response.freed_space_human_readable);

                        if (response.summary && response.summary.length > 0) {
                            let summaryHtml = '<div style="font-weight: 700; font-size: 13px; margin-bottom: 8px; color: var(--ff-text);">Cleaned Item Breakdown:</div><div style="display: flex; flex-direction: column; gap: 6px;">';
                            response.summary.forEach(function(item) {
                                summaryHtml += `<div style="background: var(--ff-bg2); padding: 8px 12px; border-radius: 6px; font-size: 12.5px; font-weight: 600; color: var(--ff-text); border-left: 3px solid #6366f1;">${item}</div>`;
                            });
                            summaryHtml += '</div>';
                            $('#detailed-summary-list').html(summaryHtml);
                        }

                        // Refresh live scan values
                        refreshLiveScan();
                        window.ff.icons();
                    } else {
                        document.getElementById('error-cleanup').hidden = false;
                        $('#error-message').text(response.message || 'An error occurred during cleanup.');
                    }
                },
                error: function() {
                    document.getElementById('during-cleanup').hidden = true;
                    document.getElementById('error-cleanup').hidden = false;
                    $('#error-message').text('Failed to reach the server. Please try again.');
                }
            });
        }

        $(document).ready(function() {
            $('#start-cleanup-btn, #cleanup-again-btn, #retry-cleanup-btn').on('click', performCleanup);
            $('#btn-refresh-scan').on('click', refreshLiveScan);
            window.ff.icons();
        });
    </script>
@endsection

