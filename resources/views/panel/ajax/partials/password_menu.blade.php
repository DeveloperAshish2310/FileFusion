<div class="ff-dropdown-wrap" data-ff-menu>
    <button type="button" class="ff-menu-btn" data-ff-menu-trigger aria-label="Credential actions">
        <i data-lucide="more-vertical" class="w-4 h-4"></i>
    </button>
    <div class="ff-dropdown" data-ff-menu-panel hidden>
        <button type="button" class="ff-dropdown-item ff-pw-open" data-reveal-token="{{ $pw->reveal_token ?? '' }}">
            <i data-lucide="eye" class="w-[15px] h-[15px]"></i> View
        </button>
        <button type="button" class="ff-dropdown-item ff-jit-copy-btn" data-reveal-token="{{ $pw->reveal_token ?? '' }}">
            <i data-lucide="copy" class="w-[15px] h-[15px]"></i> Copy password
        </button>
        <a href="{{ route('panel.editpassword', encrypt($pw->id)) }}" class="ff-dropdown-item">
            <i data-lucide="pencil" class="w-[15px] h-[15px]"></i> Edit
        </a>
        <button type="button" class="ff-dropdown-item open-password-share-modal" data-pw-id="{{ encrypt($pw->id) }}" data-title="{{ $pw->title }}">
            <i data-lucide="share-2" class="w-[15px] h-[15px]"></i> Share secret
        </button>
        @if ($pw->url)
            <button type="button" class="ff-dropdown-item js-copy-url-btn" data-url="{{ $pw->url }}">
                <i data-lucide="copy" class="w-[15px] h-[15px]"></i> Copy site link
            </button>
            <a href="{{ $pw->url }}" target="_blank" rel="noopener noreferrer" class="ff-dropdown-item">
                <i data-lucide="external-link" class="w-[15px] h-[15px]"></i> Open site
            </a>
        @endif
        <button type="button" class="ff-dropdown-item ff-pw-hide-btn" data-token="{{ $pw->reveal_token ?? '' }}" data-hidden="{{ $pw->is_hidden ? '1' : '0' }}">
            <i data-lucide="{{ $pw->is_hidden ? 'eye' : 'eye-off' }}" class="w-[15px] h-[15px]"></i>
            {{ $pw->is_hidden ? 'Unhide' : 'Hide' }}
        </button>

        <div class="ff-dropdown-divider"></div>

        <button type="button" class="ff-dropdown-item is-danger ff-pw-delete-btn" data-token="{{ $pw->reveal_token ?? '' }}" data-title="{{ $pw->title }}">
            <i data-lucide="trash-2" class="w-[15px] h-[15px]"></i> Delete
        </button>
    </div>
</div>
