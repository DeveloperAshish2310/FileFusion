<div class="ff-dropdown-wrap" data-ff-menu>
    <button type="button" class="ff-menu-btn" data-ff-menu-trigger aria-label="Category actions">
        <i data-lucide="more-vertical" class="w-4 h-4"></i>
    </button>
    <div class="ff-dropdown" data-ff-menu-panel hidden>
        <a href="{{ route('panel.categories.show', $category) }}" class="ff-dropdown-item">
            <i data-lucide="eye" class="w-[15px] h-[15px]"></i> View
        </a>
        <a href="{{ route('panel.categories.edit', $category) }}" class="ff-dropdown-item">
            <i data-lucide="pencil" class="w-[15px] h-[15px]"></i> Edit
        </a>
        <button type="button" class="ff-dropdown-item open-category-share-modal" data-cat-id="{{ encrypt($category->id) }}" data-name="{{ $category->title }}">
            <i data-lucide="share-2" class="w-[15px] h-[15px]"></i> Share bundle
        </button>
        <button type="button" class="ff-dropdown-item"
            onclick="document.getElementById('cat-toggle-{{ $category->id }}').submit()">
            <i data-lucide="{{ $category->is_hidden ? 'eye' : 'eye-off' }}" class="w-[15px] h-[15px]"></i>
            {{ $category->is_hidden ? 'Unhide' : 'Hide' }}
        </button>

        <div class="ff-dropdown-divider"></div>

        <button type="button" class="ff-dropdown-item is-danger ff-cat-delete-btn" data-id="{{ $category->id }}" data-name="{{ $category->name }}">
            <i data-lucide="trash-2" class="w-[15px] h-[15px]"></i> Delete
        </button>
    </div>
</div>
