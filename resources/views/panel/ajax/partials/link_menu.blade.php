<div class="ff-dropdown-wrap" data-ff-menu>
    <button type="button" class="ff-menu-btn" data-ff-menu-trigger aria-label="Link actions">
        <i data-lucide="more-vertical" class="w-4 h-4"></i>
    </button>
    <div class="ff-dropdown" data-ff-menu-panel hidden>
        <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="ff-dropdown-item">
            <i data-lucide="external-link" class="w-[15px] h-[15px]"></i> Open link
        </a>
        <a href="{{ route('panel.editlink', $lid) }}" class="ff-dropdown-item">
            <i data-lucide="pencil" class="w-[15px] h-[15px]"></i> Edit
        </a>
        <button type="button" class="ff-dropdown-item open-link-share-modal" data-link-id="{{ $lid }}" data-title="{{ $link->title }}">
            <i data-lucide="share-2" class="w-[15px] h-[15px]"></i> Share link
        </button>
        <button type="button" class="ff-dropdown-item toggle-star" data-link-id="{{ $lid }}">
            <i data-lucide="star" class="w-[15px] h-[15px]"></i>
            {{ $link->is_starred ? 'Remove star' : 'Add star' }}
        </button>
        <button type="button" class="ff-dropdown-item toggle-hide" data-link-id="{{ $lid }}">
            <i data-lucide="eye-off" class="w-[15px] h-[15px]"></i> Hide
        </button>
        <button type="button" class="ff-dropdown-item recapture-screenshot-btn" data-link-id="{{ $lid }}">
            <i data-lucide="camera" class="w-[15px] h-[15px]"></i> Recapture screenshot
        </button>

        <div class="ff-dropdown-divider"></div>

        <a href="{{ route('panel.deletelink', $lid) }}" class="ff-dropdown-item is-danger ff-link-delete-btn"
            data-url="{{ route('panel.deletelink', $lid) }}" data-title="{{ $link->title ?? 'this link' }}">
            <i data-lucide="trash-2" class="w-[15px] h-[15px]"></i> Delete
        </a>
    </div>
</div>
