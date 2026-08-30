@extends('layout.backend')
@push('title', 'Categories')

@section('content')
    @php
        $activeType = $type ?? 'both';
        $currentPerPage = \App\Helpers\SettingHelper::getItemsPerPage(12);
        $typeChips = ['both' => 'All', 'links' => 'Links', 'files' => 'Files', 'tasks' => 'Tasks', 'hidden' => '🔒 Hidden'];
    @endphp

    <h1 class="ff-h1">Categories</h1>
    <p class="ff-sub" style="margin-bottom:22px;">Collections that organize your links, files, and tasks</p>

    <div class="ff-toolbar" style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
        <label class="ff-input-icon" style="flex:1; min-width:220px; position:relative;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="search" id="categorySearch" placeholder="Search categories..." autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" style="padding-right:28px;">
            <button type="button" id="clearCatSearchBtn" style="display:none; position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--ff-muted); cursor:pointer; font-size:16px; padding:2px 6px;" title="Clear search">&times;</button>
        </label>

        <div style="display:flex; align-items:center; gap:8px;">
            <div style="display:inline-flex; align-items:center; gap:6px; background:var(--ff-surface, #ffffff); border:1px solid var(--ff-border, #cbd5e1); padding:2px 8px; border-radius:8px;">
                <span style="font-size:11.5px; color:var(--ff-text-muted, #64748b); font-weight:600;">Show:</span>
                <select id="catPerPageSelect" class="ff-select" style="border:none; background:transparent; padding:4px 4px; font-size:12px; font-weight:600; cursor:pointer; color:var(--ff-text-main, #0f172a); outline:none;" title="Items visible per page">
                    <option value="12" {{ $currentPerPage == 12 ? 'selected' : '' }}>12 / page</option>
                    <option value="24" {{ $currentPerPage == 24 ? 'selected' : '' }}>24 / page</option>
                    <option value="48" {{ $currentPerPage == 48 ? 'selected' : '' }}>48 / page</option>
                    <option value="96" {{ $currentPerPage == 96 ? 'selected' : '' }}>96 / page</option>
                </select>
            </div>

            <a href="{{ route('panel.categories.create') }}" class="ff-btn ff-btn-primary">+ Add Category</a>

            <div class="ff-viewtoggle" id="categoryViewToggle">
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
    </div>

    <div class="ff-chip-row-single" style="margin-bottom:16px;">
        @foreach ($typeChips as $value => $label)
            <a href="{{ route('panel.categories.index', ['type' => $value]) }}"
                class="ff-chip {{ $activeType === $value ? 'is-active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    @if ($categories->count() === 0)
        <div class="ff-list">
            <div class="ff-empty">
                <span class="ff-empty-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.6 12.6L12.4 20.8a2 2 0 0 1-2.8 0l-8-8a2 2 0 0 1 0-2.8L9.8 1.8a2 2 0 0 1 2.8 0l8 8a2 2 0 0 1 0 2.8z" />
                        <line x1="7" y1="7" x2="7.01" y2="7" />
                    </svg>
                </span>
                <div class="ff-section-title" style="font-size:17px;">No categories yet</div>
                <p class="ff-empty-text" style="margin:0;">Get started by creating your first collection.</p>
                <a href="{{ route('panel.categories.create') }}" class="ff-btn ff-btn-primary" style="margin-top:6px;">Add
                    Category</a>
            </div>
        </div>
    @else
        <div data-ff-view-target="categories" data-ff-view="grid">

            {{-- ============================== Grid view ============================== --}}
            <div class="ff-view-grid ff-grid-cards" id="categoryGrid">
                @foreach ($categories as $category)
                    @php
                        $tags = is_array($category->categories) ? $category->categories : [];
                        $thumbSrc = null;
                        if ($category->thumbnail) {
                            $thumbSrc = \Illuminate\Support\Str::startsWith($category->thumbnail, ['http://', 'https://', '//', 'data:'])
                                ? $category->thumbnail
                                : asset($category->thumbnail);
                        }
                    @endphp

                    <div class="ff-tile is-flush ff-cat-item" data-title="{{ Str::lower($category->title) }}" data-tags="{{ Str::lower(implode(' ', $tags)) }}">
                        <div class="ff-thumb">
                            <a href="{{ route('panel.categories.show', $category) }}" style="display:block; width:100%; height:100%; text-decoration:none;">
                                @if ($thumbSrc)
                                    <img src="{{ $thumbSrc }}" alt="{{ $category->title }}" loading="lazy">
                                @else
                                    <span class="ff-thumb-label">thumbnail</span>
                                @endif
                            </a>
                            <div class="ff-quick-row">
                                <button type="button" class="ff-quick-btn open-category-share-modal"
                                    data-cat-id="{{ encrypt($category->id) }}" data-name="{{ $category->title }}"
                                    title="Share Category Bundle">
                                    <i data-lucide="share-2" class="w-[15px] h-[15px]"></i>
                                </button>
                                <a href="{{ route('panel.categories.show', $category) }}" class="ff-quick-btn"
                                    title="View Category">
                                    <i data-lucide="eye" class="w-[15px] h-[15px]"></i>
                                </a>
                                <a href="{{ route('panel.categories.edit', $category) }}" class="ff-quick-btn"
                                    title="Edit">
                                    <i data-lucide="pencil" class="w-[15px] h-[15px]"></i>
                                </a>
                                <button type="button" class="ff-quick-btn is-danger"
                                    onclick="document.getElementById('cat-delete-{{ $category->id }}').submit()"
                                    title="Delete">
                                    <i data-lucide="trash-2" class="w-[15px] h-[15px]"></i>
                                </button>
                            </div>
                        </div>

                        <div class="ff-tile-body">
                            <div class="ff-row" style="gap:8px;">
                                <a href="{{ route('panel.categories.show', $category) }}" class="ff-tile-name ff-grow" style="text-decoration:none; color:var(--ff-text);" title="{{ $category->title }}">{{ $category->title }}</a>
                                @if ($category->is_new)
                                    <span class="ff-badge-new">New</span>
                                @endif
                                @include('panel.ajax.partials.category_menu', ['category' => $category])
                            </div>

                            <div class="ff-clamp-2" style="font-size:12.5px; color:var(--ff-text-soft); margin-top:6px;">
                                {{ $category->description ?: 'No description.' }}
                            </div>

                            <div class="ff-row" style="gap:8px; margin-top:12px; flex-wrap:wrap;">
                                <span class="ff-badge-type">{{ ucfirst($category->type) }}</span>
                                @if ($category->is_hidden)
                                    <span class="ff-badge-hidden">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.7 18.7 0 0 1 5.06-5.94" />
                                            <line x1="1" y1="1" x2="23" y2="23" />
                                        </svg>
                                        Hidden
                                    </span>
                                @endif
                                @if (count($tags) > 0)
                                    <div style="display:flex; gap:4px; flex-wrap:wrap; margin-left:auto;">
                                        @foreach (array_slice($tags, 0, 2) as $t)
                                            <span style="font-size:10.5px; padding:1px 6px; border-radius:10px; background:rgba(99,102,241,0.1); color:#6366f1; font-weight:600;">#{{ $t }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ============================== List view ============================== --}}
            <div class="ff-view-list ff-list" id="categoryList">
                <div class="ff-list-head ff-hide-mobile">
                    <span class="ff-list-col ff-grow">Name</span>
                    <span class="ff-list-col" style="width:110px;">Type</span>
                    <span class="ff-list-col" style="width:150px;">Tags</span>
                    <span class="ff-list-col" style="width:120px;">Created</span>
                    <span class="ff-list-col" style="width:40px;"></span>
                </div>

                @foreach ($categories as $category)
                    @php
                        $tags = is_array($category->categories) ? $category->categories : [];
                        $thumbSrc = null;
                        if ($category->thumbnail) {
                            $thumbSrc = \Illuminate\Support\Str::startsWith($category->thumbnail, ['http://', 'https://', '//', 'data:'])
                                ? $category->thumbnail
                                : asset($category->thumbnail);
                        }
                    @endphp

                    <div class="ff-list-row ff-cat-item" data-title="{{ Str::lower($category->title) }}" data-tags="{{ Str::lower(implode(' ', $tags)) }}">
                        <div class="ff-list-main">
                            <a href="{{ route('panel.categories.show', $category) }}" class="ff-thumb-sm" style="display:inline-flex; text-decoration:none;">
                                @if ($thumbSrc)
                                    <img src="{{ $thumbSrc }}" alt="{{ $category->title }}" loading="lazy">
                                @else
                                    <span class="ff-thumb-sm-label">img</span>
                                @endif
                            </a>
                            <div class="ff-min0">
                                <div class="ff-row" style="gap:8px;">
                                    <a href="{{ route('panel.categories.show', $category) }}" class="ff-list-title" style="text-decoration:none; color:var(--ff-text);">{{ $category->title }}</a>
                                    @if ($category->is_new)
                                        <span class="ff-badge-new">New</span>
                                    @endif
                                    @if ($category->is_hidden)
                                        <span class="ff-badge-hidden">Hidden</span>
                                    @endif
                                </div>
                                <div class="ff-list-subtitle">{{ Str::limit($category->description, 70) }}</div>
                            </div>
                        </div>

                        <span class="ff-list-cell ff-hide-mobile" style="width:110px;">
                            <span class="ff-badge-type">{{ ucfirst($category->type) }}</span>
                        </span>
                        <span class="ff-list-cell ff-hide-mobile ff-truncate" style="width:150px;">
                            @if (count($tags) > 0)
                                @foreach (array_slice($tags, 0, 3) as $t)
                                    <span style="font-size:11px; padding:1px 5px; border-radius:8px; background:rgba(99,102,241,0.1); color:#6366f1; margin-right:3px;">#{{ $t }}</span>
                                @endforeach
                            @else
                                <span style="color:var(--ff-muted);">—</span>
                            @endif
                        </span>
                        <span class="ff-list-cell ff-hide-mobile" style="width:120px;">
                            {{ $category->created_at->format('M j, Y') }}
                        </span>

                        <div class="ff-row" style="gap:4px; align-items:center;">
                            <button type="button" class="ff-menu-btn open-category-share-modal" data-cat-id="{{ encrypt($category->id) }}" data-name="{{ $category->title }}" title="Share Category Bundle">
                                <i data-lucide="share-2" class="w-[15px] h-[15px]"></i>
                            </button>
                            @include('panel.ajax.partials.category_menu', ['category' => $category])
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="ff-pagination">
                {{ $categories->appends(['type' => $activeType])->links('pagination::tailwind') }}
            </div>
        </div>

        {{-- ==================== CATEGORY BUNDLE SHARE MODAL ==================== --}}
        <div id="shareCategoryModal" class="ff-modal-overlay" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.7); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:16px;">
            <div class="ff-modal-card" style="background:var(--ff-card, #ffffff) !important; color:var(--ff-text, #0f172a) !important; border:1px solid var(--ff-border, #e2e8f0); border-radius:16px; width:100%; max-width:500px; max-height:90vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.3);">
                <div style="padding:18px 24px; border-bottom:1px solid var(--ff-border, #e2e8f0); display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span class="ff-section-icon" style="width:34px; height:34px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; background:var(--ff-icon-bg, color-mix(in srgb, var(--ff-accent) 15%, transparent)); color:var(--ff-accent);">
                            <i data-lucide="folder-archive" style="width:18px; height:18px;"></i>
                        </span>
                        <div>
                            <div style="font-weight:700; font-size:16px; color:var(--ff-text, #0f172a);">Share Category Bundle</div>
                            <div id="shareCategoryModalSubtitle" style="font-size:12px; color:var(--ff-text-2, var(--ff-muted, #64748b));">Curated public bundle of assets</div>
                        </div>
                    </div>
                    <button type="button" class="ff-menu-btn closeShareCategoryModalBtn" style="border:none; background:transparent; cursor:pointer; color:var(--ff-muted, #64748b);">
                        <i data-lucide="x" style="width:18px; height:18px;"></i>
                    </button>
                </div>

                <div style="padding:20px 24px; overflow-y:auto; display:flex; flex-direction:column; gap:14px;">
                    <input type="hidden" id="shareCategoryIdInput">

                    <div class="ff-field">
                        <label class="ff-label" style="font-size:12px; margin-bottom:4px;">Include Assets in Bundle:</label>
                        <div style="display:flex; gap:14px; margin-top:4px;">
                            <label style="font-size:13px; display:flex; align-items:center; gap:6px; cursor:pointer;">
                                <input type="checkbox" id="catShareFiles" checked> Files
                            </label>
                            <label style="font-size:13px; display:flex; align-items:center; gap:6px; cursor:pointer;">
                                <input type="checkbox" id="catShareLinks" checked> Bookmarks
                            </label>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="ff-field">
                            <label class="ff-label" style="font-size:12px; margin-bottom:4px;">Expiration Window</label>
                            <select id="shareCategoryExpirySelect" class="ff-select" style="width:100%;">
                                <option value="">Never (Permanent)</option>
                                <option value="1440" selected>24 Hours</option>
                                <option value="10080">7 Days</option>
                                <option value="43200">30 Days</option>
                            </select>
                        </div>

                        <div class="ff-field">
                            <label class="ff-label" style="font-size:12px; margin-bottom:4px;">PIN Passcode (Optional)</label>
                            <input type="password" id="shareCategoryPasscodeInput" class="ff-input" placeholder="Leave blank for open" style="width:100%;">
                        </div>
                    </div>

                    <div id="shareCategoryResultBox" style="display:none; padding:14px; background:rgba(99, 102, 241, 0.08); border:1px solid rgba(99, 102, 241, 0.3); border-radius:10px;">
                        <div style="font-size:12px; font-weight:700; color:var(--ff-accent); margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                            <i data-lucide="check-circle-2" style="width:14px; height:14px;"></i> Category Bundle Portal Created!
                        </div>
                        <div style="display:flex; gap:8px;">
                            <input type="text" id="shareCategoryResultUrl" class="ff-input" readonly style="font-size:12px; flex:1; font-family:monospace; background:rgba(0,0,0,0.2);">
                            <button type="button" class="ff-btn ff-btn-primary" id="copyShareCategoryResultBtn" style="font-size:12px; padding:6px 12px;">Copy</button>
                        </div>
                    </div>
                </div>

                <div style="padding:14px 24px; border-top:1px solid var(--ff-border, #e2e8f0); background:var(--ff-surface-subtle, rgba(0,0,0,0.02)); display:flex; align-items:center; justify-content:flex-end; gap:10px;">
                    <button type="button" class="ff-btn closeShareCategoryModalBtn">Close</button>
                    <button type="button" id="executeCreateShareCategoryBtn" class="ff-btn ff-btn-primary">Generate Bundle Portal</button>
                </div>
            </div>
        </div>

        {{-- Hidden forms driving the destructive / state-changing menu items --}}
        @foreach ($categories as $category)
            <form id="cat-toggle-{{ $category->id }}" action="{{ route('panel.categories.toggle', $category) }}"
                method="POST" hidden>@csrf @method('PATCH')</form>
            <form id="cat-delete-{{ $category->id }}" action="{{ route('panel.categories.destroy', $category) }}"
                method="POST" hidden>@csrf @method('DELETE')</form>
        @endforeach
    @endif
@endsection

@section('push-script')
    <script>
        (function() {
            var grid = document.getElementById('categoryGrid');
            var list = document.getElementById('categoryList');
            var target = document.querySelector('[data-ff-view-target="categories"]');
            if (!target) return;

            function applyView(mode) {
                target.setAttribute('data-ff-view', mode);
                localStorage.setItem('ff-category-view', mode);
                document.querySelectorAll('#categoryViewToggle .ff-viewtoggle-btn').forEach(function(btn) {
                    btn.classList.toggle('is-active', btn.dataset.view === mode);
                });
            }
            document.getElementById('categoryViewToggle').addEventListener('click', function(e) {
                var btn = e.target.closest('.ff-viewtoggle-btn');
                if (btn) applyView(btn.dataset.view);
            });
            applyView(localStorage.getItem('ff-category-view') || 'grid');

            var search = document.getElementById('categorySearch');
            var clearBtn = document.getElementById('clearCatSearchBtn');

            function filterCategories(term) {
                term = (term || '').trim().toLowerCase();
                if (clearBtn) clearBtn.style.display = term ? 'block' : 'none';
                document.querySelectorAll('.ff-cat-item').forEach(function(item) {
                    var title = (item.dataset.title || '').toLowerCase();
                    var tags = (item.dataset.tags || '').toLowerCase();
                    item.style.display = !term || title.indexOf(term) !== -1 || tags.indexOf(term) !== -1 ? '' : 'none';
                });
            }

            if (search) {
                search.addEventListener('input', function() {
                    filterCategories(search.value);
                });
            }

            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    search.value = '';
                    filterCategories('');
                    search.focus();
                });
            }

            // Category Delete Confirmation Modal
            $(document).on('click', '.ff-cat-delete-btn', async function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const name = $(this).data('name') || 'this category';
                const confirmed = await window.ff.confirm({
                    title: '🗑️ Delete Category',
                    message: `Permanently delete category "${name}"? This action cannot be undone.`,
                    confirmText: 'Delete Category',
                    isDanger: true
                });
                if (confirmed) {
                    const form = document.getElementById('cat-delete-' + id);
                    if (form) form.submit();
                }
            });

            // =========================================================================
            // CATEGORY SHARE MODAL HANDLERS
            // =========================================================================
            $(document).on('click', '.open-category-share-modal', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const catId = $(this).data('cat-id');
                const name = $(this).data('name') || 'Category';

                $('#shareCategoryIdInput').val(catId);
                $('#shareCategoryModalSubtitle').text('Generate public bundle for "' + name + '"');
                $('#shareCategoryPasscodeInput').val('');
                $('#shareCategoryResultBox').hide();
                $('#executeCreateShareCategoryBtn').prop('disabled', false).text('Generate Bundle Portal');

                $('#shareCategoryModal').css('display', 'flex');
                if (window.lucide) window.lucide.createIcons();
            });

            $(document).on('click', '.closeShareCategoryModalBtn', function() {
                $('#shareCategoryModal').css('display', 'none');
            });

            $(document).on('click', '#executeCreateShareCategoryBtn', function() {
                const catId = $('#shareCategoryIdInput').val();
                const expiresInMins = $('#shareCategoryExpirySelect').val();
                const passcode = $('#shareCategoryPasscodeInput').val();
                const incFiles = $('#catShareFiles').is(':checked') ? 1 : 0;
                const incLinks = $('#catShareLinks').is(':checked') ? 1 : 0;
                const btn = $(this);

                btn.prop('disabled', true).text('Generating...');

                $.ajax({
                    url: "{{ route('panel.share.category') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        category_id: catId,
                        expires_in_minutes: expiresInMins || null,
                        include_files: incFiles,
                        include_links: incLinks,
                        password: passcode || null
                    },
                    success: function(res) {
                        btn.prop('disabled', false).text('Generate Bundle Portal');
                        if (res.ok && res.share) {
                            $('#shareCategoryResultUrl').val(res.share.public_url);
                            $('#shareCategoryResultBox').slideDown();
                            window.ff.toast('Category bundle share link generated!', 'success');
                            if (window.lucide) window.lucide.createIcons();
                        } else {
                            window.ff.toast(res.message || 'Failed to generate category share.', 'error');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).text('Generate Bundle Portal');
                        window.ff.toast(xhr.responseJSON?.message || 'Error generating category bundle.', 'error');
                    }
                });
            });

            $(document).on('click', '#copyShareCategoryResultBtn', function() {
                const url = $('#shareCategoryResultUrl').val();
                if (window.ff && typeof window.ff.copy === 'function') {
                    window.ff.copy(url, 'Category bundle URL copied!');
                } else if (window.copyToClipboard) {
                    window.copyToClipboard(url, 'Category bundle URL copied!');
                }
            });

            $(document).on('change', '#catPerPageSelect', function() {
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

            window.ff.icons();
        })();
    </script>
@endsection
