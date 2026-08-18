@extends('layout.backend')
@push('title', 'Categories')

@section('content')
    @php
        $activeType = $type ?? 'both';
        $currentPerPage = \App\Helpers\SettingHelper::getItemsPerPage(12);
        $typeChips = ['both' => 'All', 'links' => 'Links', 'files' => 'Files'];
    @endphp

    <h1 class="ff-h1">Categories</h1>
    <p class="ff-sub" style="margin-bottom:22px;">Collections that organize your links and files</p>

    <div class="ff-toolbar" style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
        <label class="ff-input-icon" style="flex:1; min-width:220px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="search" id="categorySearch" placeholder="Search categories..." autocomplete="off">
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

                    <div class="ff-tile is-flush ff-cat-item" data-title="{{ Str::lower($category->title) }}">
                        <div class="ff-thumb">
                            @if ($thumbSrc)
                                <img src="{{ $thumbSrc }}" alt="{{ $category->title }}" loading="lazy">
                            @else
                                <span class="ff-thumb-label">thumbnail</span>
                            @endif
                            <div class="ff-quick-row">
                                <a href="{{ route('panel.categories.show', $category) }}" class="ff-quick-btn"
                                    title="View">
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
                                <div class="ff-tile-name ff-grow" title="{{ $category->title }}">{{ $category->title }}</div>
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
                                <span class="ff-hint" style="margin-left:auto;">
                                    {{ count($tags) }} {{ Str::plural('tag', count($tags)) }}
                                </span>
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

                    <div class="ff-list-row ff-cat-item" data-title="{{ Str::lower($category->title) }}">
                        <div class="ff-list-main">
                            <span class="ff-thumb-sm">
                                @if ($thumbSrc)
                                    <img src="{{ $thumbSrc }}" alt="{{ $category->title }}" loading="lazy">
                                @else
                                    <span class="ff-thumb-sm-label">img</span>
                                @endif
                            </span>
                            <div class="ff-min0">
                                <div class="ff-row" style="gap:8px;">
                                    <span class="ff-list-title">{{ $category->title }}</span>
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
                            {{ count($tags) ? implode(', ', array_slice($tags, 0, 3)) : '—' }}
                        </span>
                        <span class="ff-list-cell ff-hide-mobile" style="width:120px;">
                            {{ $category->created_at->format('M j, Y') }}
                        </span>

                        @include('panel.ajax.partials.category_menu', ['category' => $category])
                    </div>
                @endforeach
            </div>

            <div class="ff-pagination">
                {{ $categories->appends(['type' => $activeType])->links('pagination::tailwind') }}
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
            function applyView(mode) {
                localStorage.setItem('ff-category-view', mode);
                document.querySelectorAll('[data-ff-view-target="categories"]').forEach(function(el) {
                    el.setAttribute('data-ff-view', mode);
                });
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
            search.addEventListener('input', function() {
                var term = search.value.trim().toLowerCase();
                document.querySelectorAll('.ff-cat-item').forEach(function(item) {
                    item.style.display = !term || item.dataset.title.indexOf(term) !== -1 ? '' : 'none';
                });
            });

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
