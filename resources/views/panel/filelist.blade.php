@extends('layout.backend')
@push('title', 'Files')

@section('content')
    @php
        $activeType = request('type', '');
        $currentPerPage = \App\Helpers\SettingHelper::getItemsPerPage(24);
        $typeFilters = [
            '' => 'All',
            'document' => 'Documents',
            'image' => 'Images',
            'video' => 'Videos',
            'audio' => 'Audio',
            'others' => 'Other',
        ];
    @endphp

    <div class="ff-row-between" style="align-items:flex-start; margin-bottom:22px;">
        <div>
            <h1 class="ff-h1">Files</h1>
            <p class="ff-sub" style="margin-bottom:0;">Manage and organize your files</p>
        </div>
        <a href="{{ route('panel.uploadfile') }}" class="ff-btn ff-btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="17 8 12 3 7 8" />
                <line x1="12" y1="3" x2="12" y2="15" />
            </svg>
            Upload File
        </a>
    </div>

    <div class="ff-toolbar">
        <label class="ff-input-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="search" 
                id="fileSearch" 
                name="files_search_query"
                placeholder="Search files..." 
                value="{{ request('q') }}"
                autocomplete="new-password"
                autocorrect="off"
                autocapitalize="off"
                spellcheck="false"
                data-lpignore="true"
                data-form-type="other"
                data-dashlane-ignore="true"
                readonly
                onfocus="this.removeAttribute('readonly');">
        </label>

        <div class="ff-viewtoggle" id="fileViewToggle">
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
        <div class="ff-chip-row-single" id="fileTypeFilters" style="margin-bottom:0; flex:1; min-width:0;">
            @foreach ($typeFilters as $value => $label)
                <button type="button" class="ff-chip {{ $activeType === $value ? 'is-active' : '' }}"
                    data-type="{{ $value }}">{{ $label }}</button>
            @endforeach
        </div>

        <div style="display:inline-flex; align-items:center; gap:6px; background:var(--ff-surface); border:1px solid var(--ff-border); padding:4px 10px; border-radius:10px; flex-shrink:0;">
            <span style="font-size:12px; color:var(--ff-muted); font-weight:600;">Show:</span>
            <select id="filePerPageSelect" class="ff-select" style="border:none; background:transparent; padding:2px 4px; font-size:12px; font-weight:600; cursor:pointer; color:var(--ff-text); outline:none;" title="Items visible per page">
                <option value="12" {{ $currentPerPage == 12 ? 'selected' : '' }}>12 / page</option>
                <option value="24" {{ $currentPerPage == 24 ? 'selected' : '' }}>24 / page</option>
                <option value="48" {{ $currentPerPage == 48 ? 'selected' : '' }}>48 / page</option>
                <option value="96" {{ $currentPerPage == 96 ? 'selected' : '' }}>96 / page</option>
            </select>
        </div>
    </div>

    {{-- ---------------------------- Bulk action bar ---------------------------- --}}
    <div id="bulkActionBar" class="ff-bulkbar" hidden>
        <label class="ff-bulkbar-label">
            <input type="checkbox" id="selectAllCheckbox" class="ff-checkbox">
            Select all — <span id="selectedCount">0</span> selected
        </label>
        <div class="ff-row ff-bulkbar-actions" style="gap:8px;">
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

    <div id="loadlist">
        @include('panel.ajax.file_load')
    </div>

    <div id="sharemodal" hidden></div>
    <div id="deletemodal" hidden></div>
@endsection

@section('push-script')
    <script>
        (function() {
            // ---------------------------------------------------------- view mode
            var stored = localStorage.getItem('ff-file-view') || 'grid';

            function applyView(mode) {
                localStorage.setItem('ff-file-view', mode);
                document.querySelectorAll('[data-ff-view-target="files"]').forEach(function(el) {
                    el.setAttribute('data-ff-view', mode);
                });
                document.querySelectorAll('#fileViewToggle .ff-viewtoggle-btn').forEach(function(btn) {
                    btn.classList.toggle('is-active', btn.dataset.view === mode);
                });
            }
            window.ffApplyFileView = function() {
                applyView(localStorage.getItem('ff-file-view') || 'grid');
            };
            document.getElementById('fileViewToggle').addEventListener('click', function(e) {
                var btn = e.target.closest('.ff-viewtoggle-btn');
                if (btn) applyView(btn.dataset.view);
            });
            applyView(stored);
        })();

        function updateBulkBar() {
            var total = $('.file-checkbox').length;
            var checked = $('.file-checkbox:checked').length;
            document.getElementById('bulkActionBar').hidden = total === 0;
            $('#selectedCount').text(checked);
            $('#selectAllCheckbox').prop('checked', checked > 0 && checked === total);
        }

        function loadFilteredFiles(pageUrl) {
            var q = $('#fileSearch').val();
            var type = $('#fileTypeFilters .ff-chip.is-active').data('type') || '';

            $.ajax({
                type: 'GET',
                url: pageUrl || "{{ route('panel.filelist') }}",
                data: {
                    q: q,
                    type: type
                },
                success: function(response) {
                    $('#loadlist').html(response);
                    updateBulkBar();
                    window.ffApplyFileView();
                    window.ff.icons();

                    var newUrl = new URL(window.location.href);
                    newUrl.searchParams.set('q', q || '');
                    newUrl.searchParams.set('type', type || '');
                    window.history.replaceState({}, '', newUrl);
                }
            });
        }

        async function executeBulkAction(action) {
            var selectedIds = [];
            $('.file-checkbox:checked').each(function() {
                selectedIds.push($(this).data('id'));
            });

            if (selectedIds.length === 0) {
                window.ff.toast('Please select at least one file.', 'info', 2500);
                return;
            }

            const title = action === 'delete' ? '🗑️ Move to Trash' : '👁️ Hide Files';
            const confirmMsg = action === 'delete' ?
                `Move ${selectedIds.length} selected file(s) to the trash?` :
                `Hide ${selectedIds.length} selected file(s)? They will move to your private vault.`;

            const confirmed = await window.ff.confirm({
                title: title,
                message: confirmMsg,
                confirmText: action === 'delete' ? 'Move to Trash' : 'Hide Files',
                isDanger: action === 'delete'
            });

            if (!confirmed) return;

            $.ajax({
                type: 'POST',
                url: "{{ route('panel.bulkActionFiles') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    action: action,
                    ids: selectedIds
                },
                success: function(res) {
                    if (res.ok === 1) {
                        window.ff.toast(action === 'delete' ? 'Files moved to trash.' : 'Files hidden.', 'success', 2500);
                        loadFilteredFiles();
                    } else {
                        window.ff.toast(res.info || 'Action could not be completed.', 'error', 4000);
                    }
                },
                error: function() {
                    window.ff.toast('An error occurred. Please try again.', 'error', 4000);
                }
            });
        }

        $(document).ready(function() {
            updateBulkBar();
        });

        $(document).on('change', '.file-checkbox', updateBulkBar);

        $(document).on('change', '#selectAllCheckbox', function() {
            $('.file-checkbox').prop('checked', $(this).is(':checked'));
            updateBulkBar();
        });

        $(document).on('click', '#bulkHideBtn', function(e) {
            e.preventDefault();
            executeBulkAction('hide');
        });

        $(document).on('click', '#bulkDeleteBtn', function(e) {
            e.preventDefault();
            executeBulkAction('delete');
        });

        $('#fileSearch').on('keyup', function() {
            loadFilteredFiles();
        });

        $('#fileTypeFilters').on('click', '.ff-chip', function() {
            $('#fileTypeFilters .ff-chip').removeClass('is-active');
            $(this).addClass('is-active');
            loadFilteredFiles();
        });

        $(document).on('click', '#loadlist .ff-pagination a', function(e) {
            e.preventDefault();
            loadFilteredFiles($(this).attr('href'));
        });

        // Quick In-Browser Preview Modal Handler
        $(document).on('click', '.previewbtn', function(e) {
            e.preventDefault();
            var fileId = $(this).data('file');
            $.ajax({
                type: 'GET',
                url: "{{ route('panel.previewmodal') }}",
                data: { id: fileId },
                success: function(html) {
                    $('#previewModalMount').html(html);
                },
                error: function(err) {
                    alert('Error loading preview: ' + (err.responseJSON ? err.responseJSON.info : 'Unauthorized or file missing'));
                }
            });
        });

        $(document).on('change', '#filePerPageSelect', function() {
            const perPage = $(this).val();
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

        window.closeUniversalPreviewModal = function() {
            $('#previewModalMount').html('');
        };
    </script>
    <div id="previewModalMount"></div>
@endsection
