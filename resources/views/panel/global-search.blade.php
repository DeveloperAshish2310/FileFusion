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
            <label class="ff-checkbox-label" style="display:inline-flex; align-items:center; gap:6px;">
                <input type="checkbox" id="include-hidden" class="ff-checkbox"
                    {{ (request('include_hidden') && ($isVaultUnlocked ?? false)) ? 'checked' : '' }}>
                <span>Include hidden items</span>
                <span id="vault-lock-indicator" style="font-size:12px; color:{{ ($isVaultUnlocked ?? false) ? '#10b981' : 'var(--ff-muted)' }};" title="{{ ($isVaultUnlocked ?? false) ? 'Vault Unlocked' : 'Requires Vault Passcode' }}">
                    {{ ($isVaultUnlocked ?? false) ? '🔓' : '🔒' }}
                </span>
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

    {{-- ==================== VAULT AUTHENTICATION MODAL FOR SEARCH ==================== --}}
    <div id="searchVaultAuthModal" class="ff-modal-overlay" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.65); align-items:center; justify-content:center; padding:16px;">
        <div class="ff-modal-card" style="background:var(--ff-card, #ffffff) !important; color:var(--ff-text, #0f172a) !important; border:1px solid var(--ff-border, #e2e8f0); border-radius:14px; width:100%; max-width:440px; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.3);">
            <div style="padding:18px 20px; border-bottom:1px solid var(--ff-border, #e2e8f0); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="ff-section-icon" style="width:34px; height:34px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; background:var(--ff-icon-bg, color-mix(in srgb, var(--ff-accent) 15%, transparent)); color:var(--ff-accent);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </span>
                    <div>
                        <div style="font-weight:700; font-size:15.5px; color:var(--ff-text, #0f172a);">Unlock Hidden Search</div>
                        <div style="font-size:12px; color:var(--ff-text-2, var(--ff-muted, #64748b));">Enter passcode to search hidden items</div>
                    </div>
                </div>
                <button type="button" class="ff-menu-btn" id="closeSearchVaultAuthModalBtn" style="border:none; background:transparent; cursor:pointer; color:var(--ff-muted, #64748b);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <form id="searchVaultAuthForm" style="padding:20px;">
                @csrf
                <div id="searchVaultAuthAlert" style="display:none; padding:10px 14px; border-radius:8px; font-size:12.5px; margin-bottom:14px; background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.2);"></div>

                <div class="ff-field" style="margin-bottom:18px;">
                    <label class="ff-label" style="font-size:12.5px; font-weight:600; margin-bottom:6px; display:block;">Vault Passcode or Account Password</label>
                    <input type="password" id="searchVaultPasscodeInput" class="ff-input" placeholder="Enter PIN or Password..." required autofocus style="width:100%; height:40px; font-size:14px;">
                    <p class="ff-hint" style="font-size:11.5px; margin-top:6px; color:var(--ff-text-2, var(--ff-muted));">
                        Verification is valid for this session.
                    </p>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="ff-btn" id="cancelSearchVaultAuthBtn">Cancel</button>
                    <button type="submit" id="submitSearchVaultAuthBtn" class="ff-btn ff-btn-primary" style="display:inline-flex; align-items:center; gap:6px;">
                        <span>🔓</span> Verify &amp; Unlock
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('push-script')
    <script>
        let isVaultUnlocked = {{ ($isVaultUnlocked ?? false) ? 'true' : 'false' }};

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

            if ($('#include-hidden').is(':checked') && isVaultUnlocked) {
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

        $('#include-trashed').on('change', function() {
            if ($('#global-search-input').val().trim().length > 0) performSearch();
        });

        // Intercept include-hidden click if vault is not unlocked
        $('#include-hidden').on('click', function(e) {
            if (!isVaultUnlocked) {
                e.preventDefault();
                openVaultAuthModal();
            } else {
                if ($('#global-search-input').val().trim().length > 0) {
                    performSearch();
                }
            }
        });

        function openVaultAuthModal() {
            $('#searchVaultAuthAlert').hide().text('');
            $('#searchVaultPasscodeInput').val('');
            $('#searchVaultAuthModal').css('display', 'flex');
            setTimeout(() => $('#searchVaultPasscodeInput').focus(), 100);
        }

        function closeVaultAuthModal() {
            $('#searchVaultAuthModal').hide();
        }

        $('#closeSearchVaultAuthModalBtn, #cancelSearchVaultAuthBtn').on('click', function() {
            closeVaultAuthModal();
        });

        $('#searchVaultAuthModal').on('click', function(e) {
            if (e.target === this) closeVaultAuthModal();
        });

        $('#searchVaultAuthForm').on('submit', function(e) {
            e.preventDefault();
            const passcode = $('#searchVaultPasscodeInput').val().trim();
            if (!passcode) return;

            const $btn = $('#submitSearchVaultAuthBtn');
            $btn.prop('disabled', true).text('Verifying...');
            $('#searchVaultAuthAlert').hide();

            $.ajax({
                url: '{{ route('panel.search.verifyHiddenAuth') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    passcode: passcode
                },
                success: function(res) {
                    $btn.prop('disabled', false).html('<span>🔓</span> Verify &amp; Unlock');
                    if (res.ok) {
                        isVaultUnlocked = true;
                        $('#include-hidden').prop('checked', true);
                        $('#vault-lock-indicator').text('🔓').css('color', '#10b981').attr('title', 'Vault Unlocked');
                        closeVaultAuthModal();
                        window.ff.toast('Vault unlocked. Searching hidden items...', 'success', 2000);
                        performSearch();
                    } else {
                        $('#searchVaultAuthAlert').text(res.info || 'Incorrect passcode.').show();
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html('<span>🔓</span> Verify &amp; Unlock');
                    const err = (xhr.responseJSON && xhr.responseJSON.info) ? xhr.responseJSON.info : 'Invalid passcode or server error.';
                    $('#searchVaultAuthAlert').text(err).show();
                }
            });
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
