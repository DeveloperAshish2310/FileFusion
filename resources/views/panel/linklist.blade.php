@extends('layout.backend')
@push('title', 'Links')

@section('content')
    @php
        $activeCategory = request('category', '');
        $starredActive = request('starred') == '1';
        $currentPerPage = \App\Helpers\SettingHelper::getItemsPerPage(12);
    @endphp

    <div class="ff-vault-header-wrap">
        <div>
            <h1 class="ff-h1">Links</h1>
            <p class="ff-sub" style="margin-bottom:0;">Your complete collection of saved links</p>
        </div>
        <div class="ff-vault-header-actions">
            @include('panel.includes.mode_switcher', ['module' => 'links', 'currentMode' => 'normal'])
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
            <a href="{{ route('panel.addlinkview') }}" class="ff-btn ff-btn-primary" style="display:inline-flex; align-items:center; gap:6px;">
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
            <input type="search" 
                id="search-input" 
                name="links_search_filter_query" 
                placeholder="Search by title or URL… (Ctrl+K)"
                value="{{ request('search') }}" 
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
        <div class="ff-row ff-bulkbar-actions" style="gap:8px;">
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

    @include('panel.includes.links-import-export-modals')
    {{-- ==================== LINK SHARE MODAL ==================== --}}
    <div id="shareLinkModal" class="ff-modal-overlay" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.65); align-items:center; justify-content:center; padding:16px;">
        <div class="ff-modal-card" style="background:var(--ff-card, #ffffff) !important; color:var(--ff-text, #0f172a) !important; border:1px solid var(--ff-border, #e2e8f0); border-radius:16px; width:100%; max-width:500px; max-height:90vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.3);">
            <div style="padding:18px 24px; border-bottom:1px solid var(--ff-border, #e2e8f0); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="ff-section-icon" style="width:34px; height:34px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; background:var(--ff-icon-bg, color-mix(in srgb, var(--ff-accent) 15%, transparent)); color:var(--ff-accent);">
                        <i data-lucide="share-2" style="width:18px; height:18px;"></i>
                    </span>
                    <div>
                        <div style="font-weight:700; font-size:16px; color:var(--ff-text, #0f172a);">Share Bookmark Link</div>
                        <div id="shareLinkModalSubtitle" style="font-size:12px; color:var(--ff-text-2, var(--ff-muted, #64748b));">Create public or protected link</div>
                    </div>
                </div>
                <button type="button" class="ff-menu-btn closeShareLinkModalBtn" style="border:none; background:transparent; cursor:pointer; color:var(--ff-muted, #64748b);">
                    <i data-lucide="x" style="width:18px; height:18px;"></i>
                </button>
            </div>

            <div style="padding:20px 24px; overflow-y:auto; display:flex; flex-direction:column; gap:14px;">
                <input type="hidden" id="shareLinkIdInput">

                <div class="ff-field">
                    <label class="ff-label" style="font-size:12px; margin-bottom:4px;">Share Mode</label>
                    <select id="shareLinkTypeSelect" class="ff-select" style="width:100%;">
                        <option value="public_link">🌐 Public Link (Anyone with URL)</option>
                        <option value="anonymous_qr">👤 Anonymous Transfer (Hide owner)</option>
                    </select>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="ff-field">
                        <label class="ff-label" style="font-size:12px; margin-bottom:4px;">Expiration Window</label>
                        <select id="shareLinkExpirySelect" class="ff-select" style="width:100%;">
                            <option value="">Never (Permanent)</option>
                            <option value="60">1 Hour</option>
                            <option value="1440" selected>24 Hours</option>
                            <option value="10080">7 Days</option>
                            <option value="43200">30 Days</option>
                        </select>
                    </div>

                    <div class="ff-field">
                        <label class="ff-label" style="font-size:12px; margin-bottom:4px;">Click Limit</label>
                        <input type="number" id="shareLinkMaxClicksInput" class="ff-input" placeholder="Unlimited" min="1" style="width:100%;">
                    </div>
                </div>

                <div class="ff-field">
                    <label class="ff-label" style="font-size:12px; margin-bottom:4px;">Optional Passcode PIN</label>
                    <input type="password" id="shareLinkPasscodeInput" class="ff-input" placeholder="Leave empty for open access" autocomplete="new-password" data-lpignore="true" style="width:100%;">
                </div>

                <div id="shareLinkResultBox" style="display:none; padding:14px; background:rgba(99, 102, 241, 0.08); border:1px solid rgba(99, 102, 241, 0.3); border-radius:10px;">
                    <div style="font-size:12px; font-weight:700; color:var(--ff-accent); margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                        <i data-lucide="check-circle-2" style="width:14px; height:14px;"></i> Share Link Ready!
                    </div>
                    <div style="display:flex; gap:8px;">
                        <input type="text" id="shareLinkResultUrl" class="ff-input" readonly style="font-size:12px; flex:1; font-family:monospace; background:rgba(0,0,0,0.2);">
                        <button type="button" class="ff-btn ff-btn-primary" id="copyShareLinkResultBtn" style="font-size:12px; padding:6px 12px;">Copy</button>
                    </div>
                </div>
            </div>

            <div style="padding:14px 24px; border-top:1px solid var(--ff-border, #e2e8f0); background:var(--ff-surface-subtle, rgba(0,0,0,0.02)); display:flex; align-items:center; justify-content:flex-end; gap:10px;">
                <button type="button" class="ff-btn closeShareLinkModalBtn">Close</button>
                <button type="button" id="executeCreateShareLinkBtn" class="ff-btn ff-btn-primary">Generate Share Link</button>
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

        $(document).on('click', '.js-copy-url-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var btn = $(this);
            var url = btn.attr('data-url') || btn.data('url');
            if (url) {
                if (window.ff && typeof window.ff.copy === 'function') {
                    window.ff.copy(url, 'Link copied to clipboard! 📋');
                } else if (window.copyToClipboard) {
                    window.copyToClipboard(url, 'Link copied to clipboard! 📋');
                }
                var $icon = btn.find('i, svg').first();
                $icon.attr('data-lucide', 'check');
                if (window.lucide) {
                    window.lucide.createIcons({ root: btn[0] });
                } else if (window.ff && window.ff.icons) {
                    window.ff.icons();
                }
                setTimeout(function() {
                    $icon.attr('data-lucide', 'copy');
                    if (window.lucide) {
                        window.lucide.createIcons({ root: btn[0] });
                    } else if (window.ff && window.ff.icons) {
                        window.ff.icons();
                    }
                }, 1800);
            }
        });

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
        // LINK SHARE MODAL HANDLERS
        // =========================================================================
        $(document).on('click', '.open-link-share-modal', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const linkId = $(this).data('link-id');
            const title = $(this).data('title') || 'Bookmark';

            $('#shareLinkIdInput').val(linkId);
            $('#shareLinkModalSubtitle').text('Configure access policies for "' + title + '"');
            $('#shareLinkPasscodeInput').val('');
            $('#shareLinkMaxClicksInput').val('');
            $('#shareLinkResultBox').hide();
            $('#executeCreateShareLinkBtn').prop('disabled', false).text('Generate Share Link');

            $('#shareLinkModal').css('display', 'flex');
            if (window.lucide) window.lucide.createIcons();
        });

        $(document).on('click', '.closeShareLinkModalBtn', function() {
            $('#shareLinkModal').css('display', 'none');
        });

        $(document).on('click', '#executeCreateShareLinkBtn', function() {
            const linkId = $('#shareLinkIdInput').val();
            const shareType = $('#shareLinkTypeSelect').val();
            const expiresInMins = $('#shareLinkExpirySelect').val();
            const maxClicks = $('#shareLinkMaxClicksInput').val();
            const passcode = $('#shareLinkPasscodeInput').val();
            const btn = $(this);

            btn.prop('disabled', true).text('Generating...');

            $.ajax({
                url: "{{ route('panel.share.link') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    link_id: linkId,
                    share_type: shareType,
                    expires_in_minutes: expiresInMins || null,
                    max_clicks: maxClicks || null,
                    password: passcode || null,
                    is_anonymous: shareType === 'anonymous_qr' ? 1 : 0
                },
                success: function(res) {
                    btn.prop('disabled', false).text('Generate Share Link');
                    if (res.ok && res.share) {
                        $('#shareLinkResultUrl').val(res.share.public_url);
                        $('#shareLinkResultBox').slideDown();
                        window.ff.toast('Bookmark share link generated!', 'success');
                        if (window.lucide) window.lucide.createIcons();
                    } else {
                        window.ff.toast(res.message || 'Failed to generate share link.', 'error');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Generate Share Link');
                    window.ff.toast(xhr.responseJSON?.message || 'Error generating link share.', 'error');
                }
            });
        });

        $(document).on('click', '#copyShareLinkResultBtn', function() {
            const url = $('#shareLinkResultUrl').val();
            if (window.ff && typeof window.ff.copy === 'function') {
                window.ff.copy(url, 'Share link copied to clipboard!');
            } else if (window.copyToClipboard) {
                window.copyToClipboard(url, 'Share link copied to clipboard!');
            }
        });

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, function(c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }
    </script>
@endsection
