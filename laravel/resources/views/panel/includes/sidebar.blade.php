@php
    $appName = config('app.name', 'File Fusion');
    $brandMark = collect(preg_split('/[\s_-]+/', $appName))
        ->filter()
        ->take(2)
        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
        ->implode('');

    $isTrash = fn($type) => request()->routeIs('panel.trashview') && request()->route('type') === $type;
@endphp

<aside class="ff-sidebar" id="ffSidebar">

    <div class="ff-sidebar-head">
        <a href="{{ route('panel.dashboard') }}" class="ff-brand" title="{{ $appName }}">
            <span class="ff-brand-mark">{{ $brandMark ?: 'FF' }}</span>
            <span class="ff-brand-name">{{ $appName }}</span>
        </a>
        <button type="button" class="ff-sidebar-close" data-ff-nav-close aria-label="Close navigation">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>
    </div>

    <nav class="ff-nav-scroll">

        {{-- ------------------------------ Menu ------------------------------ --}}
        <div class="ff-nav-group">Menu</div>

        <a href="{{ route('panel.dashboard') }}" title="Dashboard"
            class="ff-nav-item {{ request()->routeIs('panel.dashboard') ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 11l9-8 9 8" />
                <path d="M5 10v10h14V10" />
            </svg>
            <span class="ff-nav-text">Dashboard</span>
        </a>

        <a href="{{ route('panel.categories.index') }}" title="List Categories"
            class="ff-nav-item {{ request()->routeIs('panel.categories.index') || request()->routeIs('panel.categories.edit') ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.6 12.6L12.4 20.8a2 2 0 0 1-2.8 0l-8-8a2 2 0 0 1 0-2.8L9.8 1.8a2 2 0 0 1 2.8 0l8 8a2 2 0 0 1 0 2.8z" />
                <circle cx="7" cy="7" r="1.2" fill="currentColor" stroke="none" />
            </svg>
            <span class="ff-nav-text">List Categories</span>
        </a>

        <a href="{{ route('panel.categories.create') }}" title="Add Categories"
            class="ff-nav-item {{ request()->routeIs('panel.categories.create') ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="9" y1="6" x2="20" y2="6" />
                <line x1="9" y1="12" x2="20" y2="12" />
                <line x1="9" y1="18" x2="15" y2="18" />
                <line x1="4" y1="6" x2="4" y2="6" />
                <line x1="4" y1="12" x2="4" y2="12" />
                <circle cx="4" cy="18" r="2" />
            </svg>
            <span class="ff-nav-text">Add Categories</span>
        </a>

        {{-- ------------------------------ Files ------------------------------ --}}
        <div class="ff-nav-group">Files</div>

        <a href="{{ route('panel.filelist') }}" title="Files List"
            class="ff-nav-item {{ request()->routeIs('panel.filelist') ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="8" y1="6" x2="21" y2="6" />
                <line x1="8" y1="12" x2="21" y2="12" />
                <line x1="8" y1="18" x2="21" y2="18" />
                <line x1="3" y1="6" x2="3.01" y2="6" />
                <line x1="3" y1="12" x2="3.01" y2="12" />
                <line x1="3" y1="18" x2="3.01" y2="18" />
            </svg>
            <span class="ff-nav-text">List</span>
        </a>

        <a href="{{ route('panel.sharedWithMe') }}" title="Shared with Me"
            class="ff-nav-item {{ request()->routeIs('panel.sharedWithMe') ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
            <span class="ff-nav-text">Shared with Me</span>
        </a>

        <a href="{{ route('panel.uploadfile') }}" title="Upload File"
            class="ff-nav-item {{ request()->routeIs('panel.uploadfile') ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="17 8 12 3 7 8" />
                <line x1="12" y1="3" x2="12" y2="15" />
            </svg>
            <span class="ff-nav-text">Upload File</span>
        </a>

        <a href="{{ route('panel.newfile') }}" title="New File"
            class="ff-nav-item {{ request()->routeIs('panel.newfile') || request()->routeIs('panel.editFile') ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline points="14 2 14 8 20 8" />
                <line x1="12" y1="12" x2="12" y2="18" />
                <line x1="9" y1="15" x2="15" y2="15" />
            </svg>
            <span class="ff-nav-text">New File</span>
        </a>

        <a href="{{ route('panel.trashview', 'files') }}" title="Files Trash"
            class="ff-nav-item {{ $isTrash('files') ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6" />
                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                <path d="M10 11v6" />
                <path d="M14 11v6" />
            </svg>
            <span class="ff-nav-text">Trash Can</span>
        </a>

        <a href="{{ route('panel.hiddenFiles') }}" title="Hidden Files"
            class="ff-nav-item {{ request()->routeIs('panel.hiddenFiles*') ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.7 18.7 0 0 1 5.06-5.94" />
                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                <line x1="1" y1="1" x2="23" y2="23" />
            </svg>
            <span class="ff-nav-text">Hidden Files</span>
        </a>

        {{-- ------------------------------ Links ------------------------------ --}}
        <div class="ff-nav-group">Links</div>

        <a href="{{ route('panel.linklist') }}" title="Links List"
            class="ff-nav-item {{ request()->routeIs('panel.linklist') ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
            </svg>
            <span class="ff-nav-text">List</span>
        </a>

        <a href="{{ route('panel.addlinkview') }}" title="Add Link"
            class="ff-nav-item {{ request()->routeIs('panel.addlinkview') || request()->routeIs('panel.editlink') ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 17H7A5 5 0 0 1 7 7h2" />
                <path d="M15 7h2a5 5 0 1 1 0 10h-2" />
                <line x1="12" y1="7" x2="12" y2="7.01" />
                <line x1="9" y1="12" x2="15" y2="12" />
            </svg>
            <span class="ff-nav-text">Add Link</span>
        </a>

        <a href="{{ route('panel.trashview', 'links') }}" title="Links Trash"
            class="ff-nav-item {{ $isTrash('links') ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6" />
                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
            </svg>
            <span class="ff-nav-text">Trash Can</span>
        </a>

        <a href="{{ route('panel.hiddenLinks') }}" title="Hidden Links"
            class="ff-nav-item {{ request()->routeIs('panel.hiddenLinks*') ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.7 18.7 0 0 1 5.06-5.94" />
                <line x1="1" y1="1" x2="23" y2="23" />
            </svg>
            <span class="ff-nav-text">Hidden Links</span>
        </a>

        {{-- ---------------------------- Passwords ---------------------------- --}}
        <div class="ff-nav-group">Passwords</div>

        <a href="{{ route('panel.passwords') }}" title="Passwords List"
            class="ff-nav-item {{ request()->routeIs('panel.passwords') ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="10" rx="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
            </svg>
            <span class="ff-nav-text">List</span>
        </a>

        <a href="{{ route('panel.addpasswordview') }}" title="Add Password"
            class="ff-nav-item {{ request()->routeIs('panel.addpasswordview') || request()->routeIs('panel.editpassword') ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="10" rx="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                <line x1="12" y1="15" x2="12" y2="17" />
            </svg>
            <span class="ff-nav-text">Add Password</span>
        </a>

        <a href="{{ route('panel.trashview', 'passwords') }}" title="Passwords Trash"
            class="ff-nav-item {{ $isTrash('passwords') ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6" />
                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
            </svg>
            <span class="ff-nav-text">Trash Can</span>
        </a>

        <a href="{{ route('panel.hiddenPasswords') }}" title="Hidden Passwords"
            class="ff-nav-item {{ request()->routeIs('panel.hiddenPasswords*') ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.7 18.7 0 0 1 5.06-5.94" />
                <line x1="1" y1="1" x2="23" y2="23" />
            </svg>
            <span class="ff-nav-text">Hidden Passwords</span>
        </a>

        {{-- ---------------------------- Utilities ---------------------------- --}}
        <div class="ff-nav-group">Workspace</div>

        <a href="{{ route('panel.globalSearch') }}" title="Global Search"
            class="ff-nav-item {{ request()->routeIs('panel.globalSearch') ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <span class="ff-nav-text">Global Search</span>
        </a>

        @if (Auth::user() && Auth::user()->isSuperAdmin())
            {{-- ---------------------------- Super Admin ---------------------------- --}}
            <div class="ff-nav-group">Super Admin Console</div>

            <a href="{{ route('panel.admin.dashboard') }}" title="Admin Overview"
                class="ff-nav-item {{ request()->routeIs('panel.admin.dashboard') ? 'is-active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1" />
                    <rect x="14" y="3" width="7" height="7" rx="1" />
                    <rect x="14" y="14" width="7" height="7" rx="1" />
                    <rect x="3" y="14" width="7" height="7" rx="1" />
                </svg>
                <span class="ff-nav-text">Admin Overview</span>
            </a>

            <a href="{{ route('panel.admin.users') }}" title="User Manager"
                class="ff-nav-item {{ request()->routeIs('panel.admin.users*') ? 'is-active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                <span class="ff-nav-text">User Manager</span>
            </a>

            <a href="{{ route('panel.admin.landingPage') }}" title="Landing Page CMS"
                class="ff-nav-item {{ request()->routeIs('panel.admin.landingPage*') ? 'is-active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 20h9"/>
                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                </svg>
                <span class="ff-nav-text">Landing Page CMS</span>
            </a>

            <a href="{{ route('panel.admin.files') }}" title="Global Files Vault"
                class="ff-nav-item {{ request()->routeIs('panel.admin.files*') ? 'is-active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                </svg>
                <span class="ff-nav-text">Global Files Vault</span>
            </a>

            <a href="{{ route('panel.admin.links') }}" title="Global Links Audit"
                class="ff-nav-item {{ request()->routeIs('panel.admin.links*') ? 'is-active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                </svg>
                <span class="ff-nav-text">Global Links Audit</span>
            </a>

            <a href="{{ route('panel.admin.storage') }}" title="Storage Analytics"
                class="ff-nav-item {{ request()->routeIs('panel.admin.storage*') ? 'is-active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"/>
                    <line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6" y1="20" x2="6" y2="14"/>
                </svg>
                <span class="ff-nav-text">Storage Analytics</span>
            </a>

            <a href="{{ route('panel.admin.settings') }}" title="System Settings"
                class="ff-nav-item {{ request()->routeIs('panel.admin.settings*') ? 'is-active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                <span class="ff-nav-text">System Settings</span>
            </a>

            <a href="{{ route('panel.cleanStoragePage') }}" title="Storage Cleaner"
                class="ff-nav-item {{ request()->routeIs('panel.cleanStoragePage') ? 'is-active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h18" />
                    <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                    <path d="M5 6l1 14a2 2 0 0 1 2 2h8a2 2 0 0 1 2-2l1-14" />
                    <path d="M12 11v6" />
                </svg>
                <span class="ff-nav-text">Storage Cleaner</span>
            </a>

            <a href="{{ route('panel.admin.backups') }}" title="System Backups"
                class="ff-nav-item {{ request()->routeIs('panel.admin.backups*') ? 'is-active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                <span class="ff-nav-text">System Backups</span>
            </a>

            <a href="{{ route('panel.admin.emailSettings') }}" title="Email System & CMS"
                class="ff-nav-item {{ request()->routeIs('panel.admin.emailSettings*') ? 'is-active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                <span class="ff-nav-text">Email System &amp; CMS</span>
            </a>

            <a href="{{ route('panel.admin.activityLogs') }}" title="Security & Audit Trail"
                class="ff-nav-item {{ request()->routeIs('panel.admin.activityLogs*') ? 'is-active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <polyline points="9 12 11 14 15 10"/>
                </svg>
                <span class="ff-nav-text">Security &amp; Audit Trail</span>
            </a>
        @endif

    </nav>

    <div class="ff-nav-foot">
        <button type="button" class="ff-nav-item" data-ff-pwa-install onclick="window.ff.installPwa()" style="display: none; width: 100%; border: none; background: transparent; cursor: pointer; text-align: left;" title="Install App">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            <span class="ff-nav-text">Install App</span>
        </button>

        <a href="{{ route('panel.settings') }}" title="Settings"
            class="ff-nav-item {{ request()->routeIs('panel.settings') ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3" />
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
            </svg>
            <span class="ff-nav-text">Settings</span>
        </a>

        <a href="{{ route('logout') }}" class="ff-nav-item is-logout" title="Logout">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                <polyline points="16 17 21 12 16 7" />
                <line x1="21" y1="12" x2="9" y2="12" />
            </svg>
            <span class="ff-nav-text">Logout</span>
        </a>
    </div>
</aside>
