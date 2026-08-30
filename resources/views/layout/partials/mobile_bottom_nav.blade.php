{{-- =========================================================================
     NATIVE MOBILE BOTTOM NAVIGATION BAR
     Ergonomic 5-tab floating bottom navigation with active pill indicators
     ========================================================================= --}}
<nav class="ff-bottom-nav" id="ffMobileBottomNav" aria-label="Mobile Navigation">
    <a href="{{ route('panel.dashboard') }}" 
       class="ff-bottom-nav-item {{ request()->routeIs('panel.dashboard') ? 'is-active' : '' }}" 
       data-tab="dashboard"
       title="Dashboard">
        <i data-lucide="layout-grid"></i>
        <span>Home</span>
    </a>

    <a href="{{ route('panel.filelist') }}" 
       class="ff-bottom-nav-item {{ (request()->routeIs('panel.filelist*') || request()->routeIs('panel.uploadfile*') || request()->routeIs('panel.newfile*')) ? 'is-active' : '' }}" 
       data-tab="files"
       title="Files">
        <i data-lucide="folder"></i>
        <span>Files</span>
    </a>

    <a href="{{ route('panel.linklist') }}" 
       class="ff-bottom-nav-item {{ (request()->routeIs('panel.linklist*') || request()->routeIs('panel.addlinkview*')) ? 'is-active' : '' }}" 
       data-tab="links"
       title="Bookmarks">
        <i data-lucide="bookmark"></i>
        <span>Links</span>
    </a>

    <a href="{{ route('panel.passwords') }}" 
       class="ff-bottom-nav-item {{ (request()->routeIs('panel.passwords*') || request()->routeIs('panel.addpasswordview*')) ? 'is-active' : '' }}" 
       data-tab="passwords"
       title="Passwords">
        <i data-lucide="key-round"></i>
        <span>Vault</span>
    </a>

    <a href="{{ route('panel.hiddenFiles') }}" 
       class="ff-bottom-nav-item {{ request()->routeIs('panel.hidden*') ? 'is-active' : '' }}" 
       data-tab="private_vault"
       title="Private Encrypted Vault">
        <i data-lucide="shield-check"></i>
        <span>Private</span>
    </a>
</nav>

{{-- =========================================================================
     NATIVE MOBILE BOTTOM SHEET DRAWER CONTAINER
     Slide-up drawer for 3-dot kebab menus and mobile quick actions
     ========================================================================= --}}
<div id="ffGlobalBottomSheetBackdrop" class="ff-bottom-sheet-backdrop" onclick="window.ff && window.ff.closeBottomSheet && window.ff.closeBottomSheet(event)">
    <div class="ff-bottom-sheet" id="ffGlobalBottomSheet" onclick="event.stopPropagation()">
        <div class="ff-bottom-sheet-handle"></div>
        <div class="ff-bottom-sheet-head">
            <div style="min-width:0; flex:1;">
                <div class="ff-bottom-sheet-title" id="ffBottomSheetTitle">Actions</div>
                <div class="ff-bottom-sheet-sub" id="ffBottomSheetSubtitle">Select an action</div>
            </div>
            <button type="button" class="ff-menu-btn" onclick="window.ff.closeBottomSheet()" style="background:transparent; border:none; color:var(--ff-muted); cursor:pointer; padding:4px;">
                <i data-lucide="x" style="width:20px; height:20px;"></i>
            </button>
        </div>
        <div class="ff-bottom-sheet-actions" id="ffBottomSheetActions">
            {{-- Dynamically injected actions --}}
        </div>
    </div>
</div>
