<div data-ff-view-target="links" data-ff-view="grid">

    {{-- ================================ Grid view ================================ --}}
    <div class="ff-view-grid ff-grid-cards">
        @forelse ($links as $link)
            @php
                $lid = encrypt($link->id);
                $domain = parse_url($link->url, PHP_URL_HOST) ?: $link->url;
                $thumbSrc = null;
                if ($link->thumbnail) {
                    $thumbSrc = \Illuminate\Support\Str::startsWith($link->thumbnail, ['http://', 'https://', '//', 'data:'])
                        ? $link->thumbnail
                        : asset($link->thumbnail);
                }
            @endphp

            <div class="ff-tile is-flush link-card">
                <div class="ff-thumb">
                    <span class="ff-tile-select" style="top:10px; left:10px;">
                        <input type="checkbox" class="ff-checkbox link-checkbox" data-id="{{ $lid }}">
                    </span>

                    <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer"
                        style="position:absolute; inset:0; display:block;">
                        @if ($thumbSrc)
                            <img src="{{ $thumbSrc }}" alt="{{ $link->title }}" loading="lazy">
                        @endif
                    </a>

                    @if (!$thumbSrc)
                        <span class="ff-thumb-label">thumbnail</span>
                    @endif

                    <div class="ff-quick-row">
                        <button type="button" class="ff-quick-btn js-copy-url-btn" data-url="{{ $link->url }}" title="Copy Link URL">
                            <i data-lucide="copy" class="w-[15px] h-[15px]"></i>
                        </button>
                        <button type="button" class="ff-quick-btn open-link-share-modal" data-link-id="{{ $lid }}" data-title="{{ $link->title ?? 'this link' }}" title="Share Link">
                            <i data-lucide="share-2" class="w-[15px] h-[15px]"></i>
                        </button>
                        <a href="{{ route('panel.editlink', $lid) }}" class="ff-quick-btn" title="Edit">
                            <i data-lucide="pencil" class="w-[15px] h-[15px]"></i>
                        </a>
                        <button type="button" class="ff-quick-btn toggle-star {{ $link->is_starred ? 'is-starred' : '' }}"
                            data-link-id="{{ $lid }}" title="{{ $link->is_starred ? 'Unstar' : 'Star' }}">
                            <svg width="15" height="15" viewBox="0 0 24 24"
                                fill="{{ $link->is_starred ? 'currentColor' : 'none' }}" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                        </button>
                        <a href="{{ route('panel.deletelink', $lid) }}" class="ff-quick-btn is-danger ff-link-delete-btn"
                            data-url="{{ route('panel.deletelink', $lid) }}" data-title="{{ $link->title ?? 'this link' }}" title="Delete">
                            <i data-lucide="trash-2" class="w-[15px] h-[15px]"></i>
                        </a>
                    </div>
                </div>

                <div class="ff-tile-body">
                    <div class="ff-row-top">
                        <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="ff-tile-name ff-grow"
                            style="text-decoration:none;" title="{{ $link->title }}">{{ $link->title }}</a>
                        @include('panel.ajax.partials.link_menu', ['link' => $link, 'lid' => $lid])
                    </div>

                    <div class="ff-truncate" style="font-size:12.5px; color:var(--ff-accent); margin-top:6px;">
                        {{ $domain }}
                    </div>

                    @if ($link->description)
                        <div class="ff-clamp-2" style="font-size:12.5px; color:var(--ff-text-soft); margin-top:6px;">
                            {{ $link->description }}
                        </div>
                    @endif

                    <div class="ff-row-between" style="gap:10px; margin-top:12px;">
                        <span class="ff-row" style="gap:6px; flex-wrap:wrap;">
                            @if ($link->category)
                                <span class="ff-badge-type">{{ $link->category->title }}</span>
                            @endif
                            @if ($link->is_new)
                                <span class="ff-badge-new">New</span>
                            @endif
                        </span>
                        <span class="ff-hint" style="flex-shrink:0;">{{ $link->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="ff-empty" style="grid-column:1/-1;">
                <span class="ff-empty-icon" style="width:64px; height:64px; border-radius:16px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                    </svg>
                </span>
                <div class="ff-section-title" style="font-size:17px;">No links added yet</div>
                <p class="ff-empty-text" style="max-width:380px; margin:0;">
                    Save bookmarks, web links and useful resources for quick access.
                </p>
                <a href="{{ route('panel.addlinkview') }}" class="ff-btn ff-btn-primary" style="margin-top:6px;">Add
                    First Link</a>
            </div>
        @endforelse
    </div>

    {{-- ================================ List view ================================ --}}
    @if ($links->count())
        <div class="ff-view-list ff-list">
            <div class="ff-list-head ff-hide-mobile">
                <span class="ff-list-col" style="width:20px;"></span>
                <span class="ff-list-col ff-grow">Title</span>
                <span class="ff-list-col" style="width:140px;">Category</span>
                <span class="ff-list-col" style="width:130px;">Added</span>
                <span class="ff-list-col" style="width:120px;"></span>
            </div>

            @foreach ($links as $link)
                @php
                    $lid = encrypt($link->id);
                    $domain = parse_url($link->url, PHP_URL_HOST) ?: $link->url;
                    $thumbSrc = null;
                    if ($link->thumbnail) {
                        $thumbSrc = \Illuminate\Support\Str::startsWith($link->thumbnail, ['http://', 'https://', '//', 'data:'])
                            ? $link->thumbnail
                            : asset($link->thumbnail);
                    }
                @endphp

                <div class="ff-list-row link-card">
                    <input type="checkbox" class="ff-checkbox link-checkbox" data-id="{{ $lid }}">

                    <div class="ff-list-main">
                        <span class="ff-thumb-sm">
                            @if ($thumbSrc)
                                <img src="{{ $thumbSrc }}" alt="{{ $link->title }}" loading="lazy">
                            @else
                                <span class="ff-thumb-sm-label">img</span>
                            @endif
                        </span>
                        <div class="ff-min0">
                            <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="ff-list-title"
                                style="display:block; text-decoration:none;">{{ $link->title }}</a>
                            <div class="ff-list-subtitle" style="color:var(--ff-accent);">{{ $domain }}</div>
                            <div class="ff-list-subtitle ff-show-mobile">
                                {{ $link->category->title ?? 'Uncategorised' }} ·
                                {{ $link->created_at->format('M d, Y') }}
                            </div>
                        </div>
                    </div>

                    <span class="ff-list-cell ff-hide-mobile" style="width:140px;">
                        @if ($link->category)
                            <span class="ff-badge-type">{{ $link->category->title }}</span>
                        @else
                            <span class="ff-muted">—</span>
                        @endif
                    </span>
                    <span class="ff-list-cell ff-hide-mobile"
                        style="width:130px;">{{ $link->created_at->format('M d, Y') }}</span>

                    <span class="ff-row ff-hide-mobile" style="width:140px; gap:4px; justify-content:flex-end;">
                        <button type="button" class="ff-menu-btn js-copy-url-btn" data-url="{{ $link->url }}" title="Copy Link URL">
                            <i data-lucide="copy" class="w-[14px] h-[14px]"></i>
                        </button>
                        <button type="button" class="ff-menu-btn open-link-share-modal" data-link-id="{{ $lid }}" data-title="{{ $link->title ?? 'this link' }}" title="Share Link">
                            <i data-lucide="share-2" class="w-[14px] h-[14px]"></i>
                        </button>
                        <button type="button" class="ff-menu-btn toggle-star {{ $link->is_starred ? 'is-starred' : '' }}"
                            data-link-id="{{ $lid }}" title="{{ $link->is_starred ? 'Unstar' : 'Star' }}">
                            <svg width="15" height="15" viewBox="0 0 24 24"
                                fill="{{ $link->is_starred ? 'currentColor' : 'none' }}" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                        </button>
                        @include('panel.ajax.partials.link_menu', ['link' => $link, 'lid' => $lid])
                    </span>

                    <span class="ff-show-mobile">
                        @include('panel.ajax.partials.link_menu', ['link' => $link, 'lid' => $lid])
                    </span>
                </div>
            @endforeach
        </div>
    @endif

    @if ($links->hasPages())
        <div class="ff-pagination">
            {{ $links->links('pagination::tailwind') }}
        </div>
    @endif
</div>
