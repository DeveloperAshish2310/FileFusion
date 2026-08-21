@extends('layout.backend')
@push('title', 'Hidden Files')

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

    <div class="ff-banner-vault">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.7 18.7 0 0 1 5.06-5.94" />
            <line x1="1" y1="1" x2="23" y2="23" />
        </svg>
        Vault unlocked — showing hidden files only
    </div>

    <div class="ff-row-between" style="align-items:flex-start; margin-bottom:22px;">
        <div>
            <h1 class="ff-h1">Hidden Files</h1>
            <p class="ff-sub" style="margin-bottom:0;">Files you have marked as private</p>
        </div>

        <div class="ff-row" style="gap:10px;">
            <span class="ff-badge-type" id="sessionTimerBadge" title="Vault session remaining">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
                <span id="sessionTimer">{{ ($remainingTime ?? 1800) === 0 ? 'Ask Always' : sprintf('%02d:%02d', floor(($remainingTime ?? 1800) / 60), ($remainingTime ?? 1800) % 60) }}</span>
            </span>
            <form action="{{ route('panel.logoutHiddenFiles') }}" method="POST">
                @csrf
                <button type="submit" class="ff-btn ff-btn-danger ff-btn-sm">Lock Vault</button>
            </form>
        </div>
    </div>

    <div class="ff-toolbar">
        <label class="ff-input-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="search" id="hiddenFileSearch" placeholder="Search hidden files..."
                value="{{ request('q') }}" autocomplete="off">
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
        <div class="ff-chip-row-single" id="hiddenTypeFilters" style="margin-bottom:0; flex:1; min-width:0;">
            @foreach ($typeFilters as $value => $label)
                <button type="button" class="ff-chip {{ $activeType === $value ? 'is-active' : '' }}"
                    data-type="{{ $value }}">{{ $label }}</button>
            @endforeach
        </div>

        <div style="display:inline-flex; align-items:center; gap:6px; background:var(--ff-surface); border:1px solid var(--ff-border); padding:4px 10px; border-radius:10px; flex-shrink:0;">
            <span style="font-size:12px; color:var(--ff-muted); font-weight:600;">Show:</span>
            <select id="hiddenFilePerPageSelect" class="ff-select" style="border:none; background:transparent; padding:2px 4px; font-size:12px; font-weight:600; cursor:pointer; color:var(--ff-text); outline:none;" title="Items visible per page">
                <option value="12" {{ $currentPerPage == 12 ? 'selected' : '' }}>12 / page</option>
                <option value="24" {{ $currentPerPage == 24 ? 'selected' : '' }}>24 / page</option>
                <option value="48" {{ $currentPerPage == 48 ? 'selected' : '' }}>48 / page</option>
                <option value="96" {{ $currentPerPage == 96 ? 'selected' : '' }}>96 / page</option>
            </select>
        </div>
    </div>

    <div id="bulkActionBar" class="ff-bulkbar" hidden>
        <label class="ff-bulkbar-label">
            <input type="checkbox" id="selectAllCheckbox" class="ff-checkbox">
            Select all — <span id="selectedCount">0</span> selected
        </label>
        <div class="ff-row ff-bulkbar-actions" style="gap:8px;">
            <button type="button" id="bulkUnhideBtn" class="ff-btn ff-btn-sm">Unhide Selected</button>
            <button type="button" id="bulkDeleteBtn" class="ff-btn ff-btn-danger ff-btn-sm">Delete Selected</button>
        </div>
    </div>

    <div id="loadlist">
        @include('panel.ajax.hidden_files_load')
    </div>

    <div id="sharemodal" hidden></div>
    <div id="deletemodal" hidden></div>
@endsection

@section('push-script')
    <script>
        function updateBulkBar() {
            var total = $('.hidden-file-checkbox').length;
            var checked = $('.hidden-file-checkbox:checked').length;
            document.getElementById('bulkActionBar').hidden = total === 0;
            $('#selectedCount').text(checked);
            $('#selectAllCheckbox').prop('checked', checked > 0 && checked === total);
        }

        function loadFilteredFiles(pageUrl) {
            var q = $('#hiddenFileSearch').val();
            var type = $('#hiddenTypeFilters .ff-chip.is-active').data('type') || '';

            $.ajax({
                type: 'GET',
                url: pageUrl || "{{ route('panel.hiddenFiles') }}",
                data: {
                    q: q,
                    type: type
                },
                success: function(response) {
                    $('#loadlist').html(response);
                    updateBulkBar();
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
            $('.hidden-file-checkbox:checked').each(function() {
                selectedIds.push($(this).data('id'));
            });

            if (selectedIds.length === 0) {
                window.ff.toast('Please select at least one hidden file.', 'info', 2500);
                return;
            }

            const title = action === 'delete' ? '🗑️ Move to Trash' : '👁️ Restore Files';
            const confirmMsg = action === 'delete' ?
                `Move ${selectedIds.length} selected hidden file(s) to the trash?` :
                `Unhide ${selectedIds.length} selected file(s) and move back to your regular list?`;

            const confirmed = await window.ff.confirm({
                title: title,
                message: confirmMsg,
                confirmText: action === 'delete' ? 'Move to Trash' : 'Unhide Files',
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
                        window.ff.toast(action === 'delete' ? 'Files moved to trash.' : 'Files restored to regular list.', 'success', 2500);
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

        function applyFileView(mode) {
            localStorage.setItem('ff-file-view', mode);
            document.querySelectorAll('[data-ff-view-target="files"]').forEach(function(el) {
                el.setAttribute('data-ff-view', mode);
            });
            document.querySelectorAll('#fileViewToggle .ff-viewtoggle-btn').forEach(function(btn) {
                btn.classList.toggle('is-active', btn.dataset.view === mode);
            });
        }

        const fileViewToggle = document.getElementById('fileViewToggle');
        if (fileViewToggle) {
            fileViewToggle.addEventListener('click', function(e) {
                var btn = e.target.closest('.ff-viewtoggle-btn');
                if (btn) applyFileView(btn.dataset.view);
            });
        }

        $(document).ready(function() {
            applyFileView(localStorage.getItem('ff-file-view') || 'grid');
            updateBulkBar();
            window.ff.icons();
        });

        $(document).on('change', '.hidden-file-checkbox', updateBulkBar);

        $(document).on('change', '#selectAllCheckbox', function() {
            $('.hidden-file-checkbox').prop('checked', $(this).is(':checked'));
            updateBulkBar();
        });

        $(document).on('click', '#bulkUnhideBtn', function(e) {
            e.preventDefault();
            executeBulkAction('unhide');
        });

        $(document).on('click', '#bulkDeleteBtn', function(e) {
            e.preventDefault();
            executeBulkAction('delete');
        });

        async function toggleHideFile(fileId) {
            var confirmed = await window.ff.confirm({
                title: '👁️ Unhide File',
                message: 'Restore this file and move it back to your regular files list?',
                confirmText: 'Unhide File',
                isDanger: false
            });

            if (!confirmed) return;

            $.ajax({
                type: 'POST',
                url: "{{ route('panel.toggleHide') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    fileId: fileId
                },
                success: function(response) {
                    if (response.ok === 1) {
                        window.ff.toast('File restored to regular list.', 'success', 2000);
                        loadFilteredFiles();
                    } else {
                        window.ff.toast(response.info || 'Failed to update file visibility.', 'error', 4000);
                    }
                },
                error: function() {
                    window.ff.toast('Error updating file visibility. Please try again.', 'error', 4000);
                }
            });
        }

        $(document).on('click', '.toggle-hide-btn', function(e) {
            e.preventDefault();
            var fileId = $(this).data('file');
            if (fileId) {
                toggleHideFile(fileId);
            }
        });

        $('#hiddenFileSearch').on('keyup', function() {
            loadFilteredFiles();
        });

        $('#hiddenTypeFilters').on('click', '.ff-chip', function() {
            $('#hiddenTypeFilters .ff-chip').removeClass('is-active');
            $(this).addClass('is-active');
            loadFilteredFiles();
        });

        $(document).on('click', '#loadlist .ff-pagination a', function(e) {
            e.preventDefault();
            loadFilteredFiles($(this).attr('href'));
        });

        function loadFilteredFiles(url) {
            const endpoint = url || '{{ route('panel.hiddenFiles') }}';
            const q = $('#hiddenFileSearch').val();
            const type = $('#hiddenTypeFilters .ff-chip.is-active').data('type') || '';

            $('#loadlist').css('opacity', 0.5);

            $.ajax({
                url: endpoint,
                type: 'GET',
                data: {
                    q: q,
                    type: type
                },
                success: function(response) {
                    $('#loadlist').html(response).css('opacity', 1);
                    applyFileView(localStorage.getItem('ff-file-view') || 'grid');
                    updateBulkBar();
                    window.ff.icons();
                },
                error: function() {
                    $('#loadlist').css('opacity', 1);
                }
            });
        }

        // ------------------------------------------------- vault session timer
        (function() {
            var initialRemaining = parseInt({{ $remainingTime ?? 1800 }});
            var isImmediate = initialRemaining === 0;
            var remainingSeconds = initialRemaining;
            var timerElement = document.getElementById('sessionTimer') || document.getElementById('timer-display');
            var timerBadge = document.getElementById('sessionTimerBadge') || document.getElementById('session-timer');

            if (isImmediate) {
                if (timerElement) timerElement.textContent = 'Ask Always';
                if (timerBadge) {
                    timerBadge.style.background = 'rgba(239, 68, 68, 0.15)';
                    timerBadge.style.color = '#ef4444';
                    timerBadge.style.borderColor = 'rgba(239, 68, 68, 0.4)';
                    timerBadge.setAttribute('title', 'Vault configured to Ask Always on every visit');
                }
                return;
            }

            function paintBadge() {
                if (!timerBadge) return;
                if (remainingSeconds <= 60) {
                    timerBadge.style.background = 'rgba(239, 68, 68, 0.2)';
                    timerBadge.style.color = '#ef4444';
                    timerBadge.style.borderColor = '#ef4444';
                } else if (remainingSeconds <= 300) {
                    timerBadge.style.background = 'rgba(245, 158, 11, 0.15)';
                    timerBadge.style.color = '#f59e0b';
                    timerBadge.style.borderColor = 'rgba(245, 158, 11, 0.4)';
                } else {
                    timerBadge.style.background = '';
                    timerBadge.style.color = '';
                    timerBadge.style.borderColor = '';
                }
            }

            function formatTime(totalSecs) {
                var hours = Math.floor(totalSecs / 3600);
                var minutes = Math.floor((totalSecs % 3600) / 60);
                var seconds = totalSecs % 60;
                if (hours > 0) {
                    return String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
                }
                return String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            }

            function updateTimer() {
                if (remainingSeconds <= 0) {
                    clearInterval(timerInterval);
                    if (window.ff && window.ff.toast) {
                        window.ff.toast('Vault session expired. Locking vault…', 'error', 3000);
                    }
                    setTimeout(function() {
                        window.location.href = '{{ route('panel.hiddenFilesLogin') }}';
                    }, 600);
                    return;
                }

                if (timerElement) {
                    timerElement.textContent = formatTime(remainingSeconds);
                }
                paintBadge();
                remainingSeconds--;
            }

            var timerInterval = setInterval(updateTimer, 1000);
            updateTimer();

            // Click on timer badge to manually extend vault session
            if (timerBadge) {
                timerBadge.style.cursor = 'pointer';
                timerBadge.setAttribute('title', 'Click to extend vault session');
                timerBadge.addEventListener('click', function() {
                    fetch('{{ route('panel.extendHiddenFilesSession') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.ok === 1 || data.code === 200 || data.success) {
                            remainingSeconds = data.remaining_time || 1800;
                            updateTimer();
                            if (window.ff && window.ff.toast) {
                                window.ff.toast('Vault session extended!', 'success', 2000);
                            }
                        }
                    })
                    .catch(err => console.error('Extension error:', err));
                });
            }

            $(document).on('change', '#hiddenFilePerPageSelect', function() {
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
        })();
    </script>
@endsection
