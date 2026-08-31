@php
    $module = $module ?? 'files';
    $currentMode = $currentMode ?? 'normal'; // 'normal' or 'hidden'
    
    $normalRoute = match($module) {
        'links' => route('panel.linklist'),
        'passwords' => route('panel.passwords'),
        'categories' => route('panel.categories.index'),
        default => route('panel.filelist'),
    };

    $hiddenRoute = match($module) {
        'links' => route('panel.hiddenLinks'),
        'passwords' => route('panel.hiddenPasswords'),
        'categories' => route('panel.categories.index', ['type' => 'hidden']),
        default => route('panel.hiddenFiles'),
    };

    $normalLabel = match($module) {
        'links' => 'Visible Links',
        'passwords' => 'Visible Passwords',
        'categories' => 'Visible',
        default => 'Visible Files',
    };

    $hiddenLabel = match($module) {
        'links' => 'Hidden Vault',
        'passwords' => 'Hidden Vault',
        'categories' => 'Hidden Vault',
        default => 'Hidden Vault',
    };
@endphp

<div class="ff-mode-switcher" role="tablist" aria-label="Visibility Mode Switcher">
    <a href="{{ $normalRoute }}" 
       class="ff-mode-btn {{ $currentMode === 'normal' ? 'is-active' : '' }}" 
       title="Switch to visible collection">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
        </svg>
        <span>{{ $normalLabel }}</span>
    </a>

    <a href="{{ $hiddenRoute }}" 
       class="ff-mode-btn {{ $currentMode === 'hidden' ? 'is-active is-vault' : '' }}" 
       title="Switch to private hidden vault">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
        <span>{{ $hiddenLabel }}</span>
    </a>
</div>
