@extends('layout.backend')
@push('title', 'Search')

@section('content')
    @php $activeTab = request('tab', 'all'); @endphp

    <div class="ff-row-between" style="align-items:flex-start; margin-bottom:22px;">
        <div>
            <h1 class="ff-h1">Global Search</h1>
            <p class="ff-sub" style="margin-bottom:0;">Search across all your files and links</p>
        </div>
        <a href="{{ route('panel.dashboard') }}" class="ff-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5M12 19l-7-7 7-7" />
            </svg>
            Back
        </a>
    </div>

    <div class="ff-form-card" style="margin-bottom:20px;">
        <div class="ff-row" style="gap:12px; flex-wrap:wrap;">
            <label class="ff-input-icon" style="padding:13px 16px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="search" id="global-search-input" placeholder="Search for files, links, or content…"
                    value="{{ request('q') }}" autofocus autocomplete="off" style="font-size:15px;">
            </label>
            <button type="button" id="search-btn" class="ff-btn ff-btn-primary">Search</button>
        </div>

        <div class="ff-row" style="gap:18px; flex-wrap:wrap;">
            <label class="ff-checkbox-label">
                <input type="checkbox" id="include-trashed" class="ff-checkbox"
                    {{ request('include_trashed') ? 'checked' : '' }}>
                Include trashed items
            </label>
            <label class="ff-checkbox-label">
                <input type="checkbox" id="include-hidden" class="ff-checkbox"
                    {{ request('include_hidden') ? 'checked' : '' }}>
                Include hidden items
            </label>
        </div>
    </div>

    @if (request('q'))
        <div class="ff-alert">
            <i data-lucide="info" class="w-4 h-4"></i>
            <span>
                Found <strong>{{ $totalResults }}</strong> {{ Str::plural('result', $totalResults) }} for
                “<strong>{{ request('q') }}</strong>” — {{ $filesCount }} {{ Str::plural('file', $filesCount) }},
                {{ $linksCount }} {{ Str::plural('link', $linksCount) }},
                {{ $passwordsCount ?? 0 }} {{ Str::plural('password', $passwordsCount ?? 0) }}
            </span>
        </div>

        <div class="ff-chip-row">
            <button type="button" class="ff-chip tab-button {{ $activeTab === 'all' ? 'is-active' : '' }}"
                data-tab="all">All ({{ $totalResults }})</button>
            <button type="button" class="ff-chip tab-button {{ $activeTab === 'files' ? 'is-active' : '' }}"
                data-tab="files">Files ({{ $filesCount }})</button>
            <button type="button" class="ff-chip tab-button {{ $activeTab === 'links' ? 'is-active' : '' }}"
                data-tab="links">Links ({{ $linksCount }})</button>
            <button type="button" class="ff-chip tab-button {{ $activeTab === 'passwords' ? 'is-active' : '' }}"
                data-tab="passwords">Passwords ({{ $passwordsCount ?? 0 }})</button>
        </div>

        @if ($totalResults > 0)
            {{-- ------------------------------- Files ------------------------------- --}}
            @if (($activeTab === 'all' || $activeTab === 'files') && $filesCount > 0)
                <div style="margin-bottom:26px;">
                    <div class="ff-section-head" style="margin-bottom:12px;">
                        <span class="ff-section-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                            </svg>
                        </span>
                        <div class="ff-section-title">Files</div>
                    </div>

                    <div class="ff-list">
                        @foreach ($files as $file)
                            @php $iconData = getFileIcon($file->extension); @endphp
                            <div class="ff-list-row">
                                <div class="ff-list-main">
                                    <span class="ff-tile-icon ff-tile-icon-sm">
                                        <i data-lucide="{{ $iconData['icon'] }}" class="w-[17px] h-[17px]"></i>
                                    </span>
                                    <div class="ff-min0">
                                        <div class="ff-row" style="gap:8px;">
                                            <span class="ff-list-title">{{ $file->name }}</span>
                                            @if ($file->is_trashed)
                                                <span class="ff-badge-hidden">Trashed</span>
                                            @endif
                                            @if ($file->is_hidden)
                                                <span class="ff-badge-hidden">Hidden</span>
                                            @endif
                                        </div>
                                        <div class="ff-list-subtitle">
                                            {{ BytetoSize($file->size) }} · {{ $file->created_at->format('M d, Y') }}
                                            @if ($file->extension)
                                                · {{ strtoupper($file->extension) }}
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if (!$file->is_trashed)
                                    <a href="{{ route('panel.downloadFile', ['fileid' => encrypt($file->id)]) }}"
                                        class="ff-menu-btn" title="Download">
                                        <i data-lucide="download" class="w-4 h-4"></i>
                                    </a>
                                @else
                                    <a href="{{ route('panel.restoreFile', encrypt($file->id)) }}" class="ff-btn ff-btn-sm"
                                        title="Restore">Restore</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ------------------------------- Links ------------------------------- --}}
            @if (($activeTab === 'all' || $activeTab === 'links') && $linksCount > 0)
                <div style="margin-bottom:26px;">
                    <div class="ff-section-head" style="margin-bottom:12px;">
                        <span class="ff-section-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                            </svg>
                        </span>
                        <div class="ff-section-title">Links</div>
                    </div>

                    <div class="ff-list">
                        @foreach ($links as $link)
                            @php
                                $thumbSrc = null;
                                if ($link->thumbnail) {
                                    $thumbSrc = \Illuminate\Support\Str::startsWith($link->thumbnail, ['http://', 'https://', '//', 'data:'])
                                        ? $link->thumbnail
                                        : asset($link->thumbnail);
                                }
                            @endphp
                            <div class="ff-list-row">
                                <div class="ff-list-main">
                                    <span class="ff-thumb-sm">
                                        @if ($thumbSrc)
                                            <img src="{{ $thumbSrc }}" alt="{{ $link->title }}" loading="lazy">
                                        @else
                                            <span class="ff-thumb-sm-label">img</span>
                                        @endif
                                    </span>
                                    <div class="ff-min0">
                                        <div class="ff-row" style="gap:8px;">
                                            <span class="ff-list-title">{{ $link->title }}</span>
                                            @if ($link->deleted_at)
                                                <span class="ff-badge-hidden">Trashed</span>
                                            @endif
                                            @if ($link->is_hidden)
                                                <span class="ff-badge-hidden">Hidden</span>
                                            @endif
                                        </div>
                                        <div class="ff-list-subtitle" style="color:var(--ff-accent);">
                                            {{ parse_url($link->url, PHP_URL_HOST) }}
                                        </div>
                                        @if ($link->description)
                                            <div class="ff-list-subtitle">{{ Str::limit($link->description, 90) }}</div>
                                        @endif
                                    </div>
                                </div>

                                @if (!$link->deleted_at)
                                    <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer"
                                        class="ff-menu-btn" title="Visit">
                                        <i data-lucide="external-link" class="w-4 h-4"></i>
                                    </a>
                                @else
                                    <button type="button" class="ff-btn ff-btn-sm"
                                        onclick="restoreLink('{{ encrypt($link->id) }}')">Restore</button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ----------------------------- Passwords ----------------------------- --}}
            @if (($activeTab === 'all' || $activeTab === 'passwords') && isset($passwords) && $passwordsCount > 0)
                <div>
                    <div class="ff-section-head" style="margin-bottom:12px;">
                        <span class="ff-section-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="10" rx="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                        </span>
                        <div class="ff-section-title">Passwords</div>
                    </div>

                    <div class="ff-list">
                        @foreach ($passwords as $pw)
                            <div class="ff-list-row">
                                <div class="ff-list-main">
                                    <span class="ff-tile-icon ff-tile-icon-sm">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="10" rx="2" />
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                        </svg>
                                    </span>
                                    <div class="ff-min0">
                                        <div class="ff-row" style="gap:8px;">
                                            <span class="ff-list-title">{{ $pw->title }}</span>
                                            @if ($pw->deleted_at)
                                                <span class="ff-badge-hidden">Trashed</span>
                                            @endif
                                            @if ($pw->is_hidden)
                                                <span class="ff-badge-hidden">Hidden</span>
                                            @endif
                                        </div>
                                        <div class="ff-list-subtitle">
                                            {{ $pw->username ?: 'No username' }}
                                            @if ($pw->url)
                                                · {{ parse_url($pw->url, PHP_URL_HOST) ?: $pw->url }}
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if (!$pw->deleted_at)
                                    <a href="{{ route('panel.passwords') }}?q={{ urlencode($pw->title) }}"
                                        class="ff-btn ff-btn-sm">View in Vault</a>
                                @else
                                    <a href="{{ route('panel.trashview', 'passwords') }}" class="ff-btn ff-btn-sm"
                                        title="View in Trash">View in Trash</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            <div class="ff-list">
                <div class="ff-empty">
                    <span class="ff-empty-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="7" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                    </span>
                    <div class="ff-section-title" style="font-size:17px;">No results found</div>
                    <p class="ff-empty-text" style="margin:0;">Try adjusting your search terms or filters.</p>
                </div>
            </div>
        @endif
    @else
        <div class="ff-list">
            <div class="ff-empty">
                <span class="ff-empty-icon" style="width:64px; height:64px; border-radius:16px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                </span>
                <div class="ff-section-title" style="font-size:17px;">Start searching</div>
                <p class="ff-empty-text" style="max-width:420px; margin:0;">
                    Enter a search term to find files and links across your entire workspace — by name, content or
                    description.
                </p>
            </div>
        </div>
    @endif
@endsection

@section('push-script')
    <script>
        function performSearch(tabOverride) {
            var query = $('#global-search-input').val().trim();
            if (query.length === 0) return;

            var url = new URL(window.location.href);
            url.searchParams.set('q', query);
            url.searchParams.set('tab', tabOverride || $('.tab-button.is-active').data('tab') || 'all');

            if ($('#include-trashed').is(':checked')) {
                url.searchParams.set('include_trashed', '1');
            } else {
                url.searchParams.delete('include_trashed');
            }

            if ($('#include-hidden').is(':checked')) {
                url.searchParams.set('include_hidden', '1');
            } else {
                url.searchParams.delete('include_hidden');
            }

            window.location.href = url.toString();
        }

        $('#search-btn').on('click', function() {
            performSearch();
        });

        $('#global-search-input').on('keypress', function(e) {
            if (e.which === 13) performSearch();
        });

        $('.tab-button').on('click', function() {
            performSearch($(this).data('tab'));
        });

        $('#include-trashed, #include-hidden').on('change', function() {
            if ($('#global-search-input').val().trim().length > 0) performSearch();
        });

        async function restoreLink(linkId) {
            const confirmed = await window.ff.confirm({
                title: '♻️ Restore Link',
                message: 'Restore this link from the trash?',
                confirmText: 'Restore',
                isDanger: false
            });
            if (!confirmed) return;

            $.ajax({
                url: '{{ route('panel.restoreLink', ':id') }}'.replace(':id', linkId),
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.ok) {
                        window.ff.toast('Link restored successfully.', 'success', 2000);
                        setTimeout(() => location.reload(), 700);
                    } else {
                        window.ff.toast('Failed to restore link.', 'error', 4000);
                    }
                },
                error: function() {
                    window.ff.toast('An error occurred. Please try again.', 'error', 4000);
                }
            });
        }

        $(document).ready(function() {
            $('#global-search-input').focus();
            window.ff.icons();
        });

        $(document).on('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                $('#global-search-input').focus().select();
            }
        });
    </script>
@endsection
