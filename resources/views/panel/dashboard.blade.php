@extends('layout.backend')
@push('title', 'Dashboard')

@section('content')
    @php
        $barState = $storagePercentage > 90 ? 'is-danger' : ($storagePercentage > 75 ? 'is-warn' : '');
    @endphp

    <div class="ff-row-between" style="align-items:flex-start; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
        <div>
            <h1 class="ff-h1" style="margin-bottom:4px;">My Dashboard</h1>
            <p class="ff-sub" style="margin-bottom:0;">Welcome back, <strong style="color:var(--ff-text);">{{ $user->name ?? 'User' }}</strong>! Here is an overview of your files, bookmarks, and credentials.</p>
        </div>
        <div class="ff-row" style="gap:10px;">
            <a href="{{ route('panel.uploadfile') }}" class="ff-btn ff-btn-primary" style="display:inline-flex; align-items:center; gap:6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="17 8 12 3 7 8" />
                    <line x1="12" y1="3" x2="12" y2="15" />
                </svg>
                Upload File
            </a>
            <a href="{{ route('panel.addlinkview') }}" class="ff-btn" style="display:inline-flex; align-items:center; gap:6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                </svg>
                Add Link
            </a>
        </div>
    </div>

    {{-- ============================== 1. Top KPI Summary Strip ============================== --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:26px;">
        {{-- Total Files Card --}}
        <a href="{{ route('panel.filelist') }}" class="ff-card" style="padding:18px; text-decoration:none; display:flex; align-items:center; gap:16px; transition:transform 0.2s, box-shadow 0.2s;">
            <span class="ff-tile-icon" style="background:rgba(59,130,246,0.12); color:#3b82f6; width:46px; height:46px; border-radius:12px; flex-shrink:0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                </svg>
            </span>
            <div style="flex:1; min-width:0;">
                <div style="font-size:12px; font-weight:600; color:var(--ff-muted); text-transform:uppercase; letter-spacing:0.5px;">Files</div>
                <div style="font-size:22px; font-weight:700; color:var(--ff-text); margin-top:2px;">{{ $totalFiles }}</div>
                <div style="font-size:12px; color:var(--ff-text-soft); margin-top:2px;">{{ $storageUsed }} in use</div>
            </div>
        </a>

        {{-- Total Bookmarks Card --}}
        <a href="{{ route('panel.linklist') }}" class="ff-card" style="padding:18px; text-decoration:none; display:flex; align-items:center; gap:16px; transition:transform 0.2s, box-shadow 0.2s;">
            <span class="ff-tile-icon" style="background:rgba(16,185,129,0.12); color:#10b981; width:46px; height:46px; border-radius:12px; flex-shrink:0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                </svg>
            </span>
            <div style="flex:1; min-width:0;">
                <div style="font-size:12px; font-weight:600; color:var(--ff-muted); text-transform:uppercase; letter-spacing:0.5px;">Bookmarks</div>
                <div style="font-size:22px; font-weight:700; color:var(--ff-text); margin-top:2px;">{{ $totalLinks }}</div>
                <div style="font-size:12px; color:var(--ff-text-soft); margin-top:2px;">{{ $totalStarredLinks }} starred</div>
            </div>
        </a>

        {{-- Total Passwords Card --}}
        <a href="{{ route('panel.passwords') }}" class="ff-card" style="padding:18px; text-decoration:none; display:flex; align-items:center; gap:16px; transition:transform 0.2s, box-shadow 0.2s;">
            <span class="ff-tile-icon" style="background:rgba(245,158,11,0.12); color:#f59e0b; width:46px; height:46px; border-radius:12px; flex-shrink:0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="10" rx="2" />
                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                </svg>
            </span>
            <div style="flex:1; min-width:0;">
                <div style="font-size:12px; font-weight:600; color:var(--ff-muted); text-transform:uppercase; letter-spacing:0.5px;">Credentials</div>
                <div style="font-size:22px; font-weight:700; color:var(--ff-text); margin-top:2px;">{{ $totalPasswords }}</div>
                <div style="font-size:12px; color:var(--ff-text-soft); margin-top:2px;">Zero-knowledge AES</div>
            </div>
        </a>

        {{-- Security Status Card --}}
        <a href="{{ route('panel.settings') }}#two-factor-section" class="ff-card" style="padding:18px; text-decoration:none; display:flex; align-items:center; gap:16px; transition:transform 0.2s, box-shadow 0.2s;">
            <span class="ff-tile-icon" style="background:rgba(139,92,246,0.12); color:#8b5cf6; width:46px; height:46px; border-radius:12px; flex-shrink:0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </span>
            <div style="flex:1; min-width:0;">
                <div style="font-size:12px; font-weight:600; color:var(--ff-muted); text-transform:uppercase; letter-spacing:0.5px;">Security Status</div>
                <div style="font-size:15px; font-weight:700; color:{{ $twoFactorActive ? '#10b981' : '#f59e0b' }}; margin-top:4px; display:flex; align-items:center; gap:6px;">
                    <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:{{ $twoFactorActive ? '#10b981' : '#f59e0b' }};"></span>
                    {{ $twoFactorActive ? '2FA Active' : '2FA Recommended' }}
                </div>
                <div style="font-size:12px; color:var(--ff-text-soft); margin-top:2px;">
                    {{ $vaultPassSet ? 'Vault Passcode Set' : 'Vault Ready' }}
                </div>
            </div>
        </a>
    </div>

    {{-- ============================== Main Layout Split ============================== --}}
    <div class="ff-split-dashboard">

        {{-- ============================== Left Column ============================== --}}
        <div class="ff-stack" style="gap:20px;">

            {{-- ---------------------------- Pinned Tasks & Action Items ---------------------------- --}}
            @if (isset($pinnedTasks) && $pinnedTasks->count() > 0)
                <div class="ff-card" id="dashboard-pinned-tasks-card" style="border-left:4px solid #a855f7;">
                    <div class="ff-card-head">
                        <div class="ff-row" style="gap:8px;">
                            <span class="ff-tile-icon" style="background:rgba(168,85,247,0.12); color:#a855f7; width:28px; height:28px; border-radius:7px;">
                                <i data-lucide="pin" style="width:14px; height:14px;"></i>
                            </span>
                            <h3 class="ff-card-title">Pinned Action Items</h3>
                        </div>
                        <a href="{{ route('panel.todos.index', ['filter' => 'dashboard']) }}" class="ff-viewall" style="color:#a855f7;">
                            Open Todos
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6" />
                            </svg>
                        </a>
                    </div>

                    <div id="dashboard-pinned-tasks-container" style="display:flex; flex-direction:column; gap:6px;">
                        @foreach ($pinnedTasks as $t)
                            <div class="ff-row-between dashboard-task-item" style="padding:8px 10px; background:var(--ff-bg-2, #f8fafc); border-radius:10px; gap:10px; transition:all 0.25s ease;" id="dash-task-{{ $t->id }}">
                                <div class="ff-row" style="gap:10px; flex:1; min-width:0;">
                                    <input type="checkbox" onchange="quickToggleDashTask('{{ $t->id }}')" style="accent-color:#10b981; width:17px; height:17px; cursor:pointer; flex-shrink:0;">
                                    <a href="{{ route('panel.todos.index', ['task' => $t->id]) }}" class="ff-truncate dash-task-title" style="text-decoration:none; color:var(--ff-text); font-size:13.5px; font-weight:500;">
                                        {{ $t->title }}
                                    </a>
                                </div>
                                <div class="ff-row" style="gap:6px; flex-shrink:0; flex-wrap:wrap;">
                                    @if ($t->collection)
                                        <span class="chip" style="background:rgba(99,102,241,0.1); color:{{ $t->collection->color }}; font-size:10.5px; padding:2px 6px;">
                                            {{ $t->collection->name }}
                                        </span>
                                    @endif

                                    @if ($t->progress['total'] > 0)
                                        <span class="chip chip-substep" style="font-size:10.5px; padding:2px 6px; background:rgba(16,185,129,0.12); color:#10b981; display:inline-flex; align-items:center; gap:3px;">
                                            <i data-lucide="check-circle-2" style="width:10px; height:10px;"></i>
                                            {{ $t->progress['completed'] }}/{{ $t->progress['total'] }} steps
                                        </span>
                                    @endif

                                    @if ($t->due_date)
                                        <span class="chip {{ $t->is_overdue ? 'chip-overdue' : 'chip-due' }}" style="font-size:10.5px; padding:2px 6px;">
                                            {{ $t->due_badge }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ---------------------------- Recent Files ---------------------------- --}}
            <div class="ff-card">
                <div class="ff-card-head">
                    <div class="ff-row" style="gap:8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--ff-accent);">
                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                        </svg>
                        <h3 class="ff-card-title">Recent Files</h3>
                    </div>
                    <a href="{{ route('panel.filelist') }}" class="ff-viewall">
                        View All Files
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6" />
                        </svg>
                    </a>
                </div>

                @forelse ($recentFiles as $file)
                    @php 
                        $eid = encrypt($file->id);
                        $icon = getFileIcon($file->extension); 
                    @endphp
                    <div class="ff-row-between" style="padding:10px 8px; border-radius:10px; transition:background 0.15s; margin-bottom:2px;">
                        <a href="{{ route('panel.downloadFile', $eid) }}" class="ff-row ff-grow" style="text-decoration:none; min-width:0; gap:10px;">
                            <span class="ff-tile-icon ff-tile-icon-sm">
                                <i data-lucide="{{ $icon['icon'] }}" class="w-[17px] h-[17px]"></i>
                            </span>
                            <span class="ff-min0">
                                <span class="ff-list-title" style="display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $file->name }}</span>
                                <span class="ff-list-subtitle" style="display:block;">
                                    {{ BytetoSize($file->size) }} · {{ timeAgo($file->updated_at) }}
                                </span>
                            </span>
                        </a>
                        <div class="ff-row" style="gap:6px; flex-shrink:0;">
                            <a href="{{ route('panel.downloadFile', $eid) }}" class="ff-quick-btn ff-download-link" download="{{ $file->name }}" data-filename="{{ $file->name }}" title="Download">
                                <i data-lucide="download" class="w-[14px] h-[14px]"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="ff-empty ff-empty-sm">
                        <span class="ff-empty-icon">
                            <i data-lucide="file-plus" class="w-5 h-5"></i>
                        </span>
                        <span class="ff-empty-text">No files uploaded yet.</span>
                        <a href="{{ route('panel.uploadfile') }}" class="ff-btn ff-btn-sm ff-btn-primary" style="margin-top:6px;">Upload First File</a>
                    </div>
                @endforelse
            </div>

            {{-- ---------------------------- Recent Passwords / Credentials ---------------------------- --}}
            <div class="ff-card">
                <div class="ff-card-head">
                    <div class="ff-row" style="gap:8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#f59e0b;">
                            <rect x="3" y="11" width="18" height="10" rx="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        <h3 class="ff-card-title">Saved Credentials</h3>
                    </div>
                    <a href="{{ route('panel.passwords') }}" class="ff-viewall">
                        View All Passwords
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6" />
                        </svg>
                    </a>
                </div>

                @forelse ($latestPasswords as $pw)
                    @php $domain = parse_url($pw->url, PHP_URL_HOST) ?: ($pw->url ?: 'Account Login'); @endphp
                    <div class="ff-row-between" style="padding:10px 8px; border-radius:10px; transition:background 0.15s; margin-bottom:2px;">
                        <div class="ff-row ff-grow" style="gap:10px; min-width:0;">
                            <span class="ff-tile-icon ff-tile-icon-sm" style="background:rgba(245,158,11,0.12); color:#f59e0b;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="10" rx="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                            </span>
                            <div class="ff-min0">
                                <span class="ff-list-title" style="display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $pw->title }}</span>
                                <span class="ff-list-subtitle" style="display:block;">
                                    @if ($pw->username)
                                        {{ $pw->username }} · 
                                    @endif
                                    {{ $domain }}
                                </span>
                            </div>
                        </div>

                        <div class="ff-row" style="gap:6px; flex-shrink:0;">
                            @if ($pw->username)
                                <button type="button" class="ff-quick-btn copy-username-btn" data-username="{{ $pw->username }}" title="Copy Username">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                    </svg>
                                </button>
                            @endif
                            <a href="{{ route('panel.passwords') }}" class="ff-quick-btn" title="View in Passwords">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="ff-empty ff-empty-sm">
                        <span class="ff-empty-icon">
                            <i data-lucide="key" class="w-5 h-5"></i>
                        </span>
                        <span class="ff-empty-text">No passwords saved yet.</span>
                        <a href="{{ route('panel.addpasswordview') }}" class="ff-btn ff-btn-sm" style="margin-top:6px;">Add First Password</a>
                    </div>
                @endforelse
            </div>

            {{-- ---------------------------- Latest Bookmarks / Links ---------------------------- --}}
            <div class="ff-card">
                <div class="ff-card-head">
                    <div class="ff-row" style="gap:8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#10b981;">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                        </svg>
                        <h3 class="ff-card-title">Latest Bookmarks</h3>
                    </div>
                    <a href="{{ route('panel.linklist') }}" class="ff-viewall">
                        View All Links
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6" />
                        </svg>
                    </a>
                </div>

                @if ($latestLinks->isEmpty())
                    <div class="ff-empty ff-empty-sm">
                        <span class="ff-empty-icon">
                            <i data-lucide="link" class="w-5 h-5"></i>
                        </span>
                        <span class="ff-empty-text">No bookmarks saved yet.</span>
                        <a href="{{ route('panel.addlinkview') }}" class="ff-btn ff-btn-sm" style="margin-top:6px;">Add First Link</a>
                    </div>
                @else
                    <div class="ff-grid-links" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:12px;">
                        @foreach ($latestLinks as $link)
                            @php $domain = parse_url($link->url, PHP_URL_HOST) ?: ($link->url ?: 'Link'); @endphp
                            <div class="ff-dashboard-link-card"
                                style="border:1px solid var(--ff-border); border-radius:12px; padding:14px; display:flex; flex-direction:column; gap:8px; background:var(--ff-bg-2); transition:border-color 0.2s, transform 0.15s;">
                                <div class="ff-row-between">
                                    <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" style="display:flex; align-items:center; gap:8px; text-decoration:none; min-width:0; flex:1;">
                                        <span class="ff-quick-action-icon" style="width:30px; height:30px; border-radius:8px; flex-shrink:0;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10" />
                                                <line x1="2" y1="12" x2="22" y2="12" />
                                                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                                            </svg>
                                        </span>
                                        <span style="font-size:11.5px; color:var(--ff-accent); font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $domain }}</span>
                                    </a>

                                    <button type="button" class="ff-quick-btn copy-link-btn" data-url="{{ $link->url }}" title="Copy Link" style="width:28px; height:28px; border-radius:6px; flex-shrink:0; padding:0; display:inline-flex; align-items:center; justify-content:center;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                        </svg>
                                    </button>
                                </div>
                                <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" style="min-height:36px; text-decoration:none;">
                                    <span class="ff-clamp-2"
                                        style="display:-webkit-box; font-size:13.5px; font-weight:600; color:var(--ff-text); line-height:1.35;">{{ $link->title }}</span>
                                </a>
                                <div class="ff-row-between ff-mt-auto" style="font-size:11.5px; color:var(--ff-muted);">
                                    <span>{{ $link->created_at->format('M d, Y') }}</span>
                                    <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" style="color:var(--ff-accent); text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:3px;">
                                        Open <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ---------------------------- Shared Files --------------------------- --}}
            <div class="ff-card">
                <div class="ff-card-head">
                    <div class="ff-row" style="gap:8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#6366f1;">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <h3 class="ff-card-title">Shared Files</h3>
                    </div>
                    <a href="{{ route('panel.sharedWithMe') }}" class="ff-viewall">
                        Shared Center
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6" />
                        </svg>
                    </a>
                </div>

                @forelse ($sharedFiles as $file)
                    @php
                        $eid = encrypt($file->id);
                        $icon = getFileIcon($file->extension);
                    @endphp
                    <div class="ff-row-between" style="padding:10px 8px; border-radius:10px; transition:background 0.15s; margin-bottom:2px;">
                        <a href="{{ route('panel.downloadFile', $eid) }}" class="ff-row ff-grow" style="text-decoration:none; min-width:0; gap:10px;">
                            <span class="ff-tile-icon ff-tile-icon-sm">
                                <i data-lucide="{{ $icon['icon'] ?? 'file' }}" class="w-[17px] h-[17px]"></i>
                            </span>
                            <span class="ff-min0">
                                <span class="ff-list-title" style="display:flex; align-items:center; gap:6px;">
                                    <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $file->name }}</span>
                                    @if (!empty($file->is_anonymous))
                                        <span class="ff-badge" style="background:rgba(168,85,247,0.15); color:#c084fc; border:1px solid rgba(168,85,247,0.3); padding:1px 6px; font-size:10px; font-weight:700; border-radius:4px;">
                                            Anonymous
                                        </span>
                                    @elseif ($file->share_type === 'private_user')
                                        <span class="ff-badge" style="background:rgba(59,130,246,0.15); color:#3b82f6; border:1px solid rgba(59,130,246,0.3); padding:1px 6px; font-size:10px; font-weight:700; border-radius:4px;">
                                            Private
                                        </span>
                                    @else
                                        <span class="ff-badge" style="background:rgba(16,185,129,0.15); color:#10b981; border:1px solid rgba(16,185,129,0.3); padding:1px 6px; font-size:10px; font-weight:700; border-radius:4px;">
                                            Public Link
                                        </span>
                                    @endif
                                    @if (!empty($file->is_locked))
                                        <span class="ff-badge" style="background:rgba(239,68,68,0.15); color:#ef4444; border:1px solid rgba(239,68,68,0.3); padding:1px 6px; font-size:10px; font-weight:700; border-radius:4px;">
                                            Locked
                                        </span>
                                    @endif
                                </span>
                                <span class="ff-list-subtitle" style="display:block;">
                                    @if (($file->shared_by_name ?? '') === 'Me')
                                        Shared by You ({{ $file->recipient_label ?? 'Public' }})
                                    @else
                                        Shared by {{ $file->shared_by_name ?? 'User' }}
                                    @endif
                                    · {{ BytetoSize($file->size) }}
                                    @if (!empty($file->max_downloads))
                                        · {{ $file->download_count }}/{{ $file->max_downloads }} DLs
                                    @endif
                                </span>
                            </span>
                        </a>
                        <div class="ff-row" style="gap:6px; flex-shrink:0;">
                            @if (!empty($file->share_token) && ($file->share_type ?? '') !== 'private_user')
                                <button type="button" class="ff-quick-btn" onclick="window.ff.copy('{{ appShareUrl('/s/' . $file->share_token) }}', 'Share link copied!')" title="Copy Share Link">
                                    <i data-lucide="copy" class="w-[14px] h-[14px]"></i>
                                </button>
                            @endif
                            <a href="{{ route('panel.downloadFile', $eid) }}" class="ff-quick-btn" title="Download">
                                <i data-lucide="download" class="w-[14px] h-[14px]"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="ff-empty ff-empty-sm">
                        <span class="ff-empty-icon">
                            <i data-lucide="share-2" class="w-5 h-5"></i>
                        </span>
                        <span class="ff-empty-text">No active shared files.</span>
                        <a href="{{ route('panel.sharedWithMe') }}" class="ff-btn ff-btn-sm" style="margin-top:6px;">Open Shared Center</a>
                    </div>
                @endforelse
            </div>

        </div>

        {{-- ============================== Right Column (Sidebar) ============================== --}}
        <div class="ff-stack" style="gap:20px;">

            {{-- ---------------------------- Encrypted Vault Card ---------------------------- --}}
            <div class="ff-card">
                <div class="ff-row-between" style="margin-bottom:14px;">
                    <span style="display:inline-flex; align-items:center; gap:7px; font-size:13px; font-weight:700; color:var(--ff-text); text-transform:uppercase; letter-spacing:0.5px;">
                        <span class="ff-tile-icon ff-tile-icon-sm" style="background:rgba(99,102,241,0.12); color:#6366f1; width:28px; height:28px; border-radius:7px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        Private Vault
                    </span>
                    @if ($isVaultUnlocked)
                        <span style="font-size:11px; background:rgba(16,185,129,0.12); color:#10b981; border:1px solid rgba(16,185,129,0.25); padding:3px 9px; border-radius:12px; font-weight:700; display:inline-flex; align-items:center; gap:5px;">
                            <span style="width:6px; height:6px; border-radius:50%; background:#10b981;"></span> Unlocked
                        </span>
                    @else
                        <span style="font-size:11px; background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.25); padding:3px 9px; border-radius:12px; font-weight:700; display:inline-flex; align-items:center; gap:5px;">
                            <span style="width:6px; height:6px; border-radius:50%; background:#ef4444;"></span> Locked
                        </span>
                    @endif
                </div>

                @if ($isVaultUnlocked)
                    {{-- Authenticated View: Show live hidden counts with clean theme styling --}}
                    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:8px; margin-bottom:14px; background:var(--ff-bg-2); padding:12px 8px; border-radius:10px; border:1px solid var(--ff-border); text-align:center;">
                        <a href="{{ route('panel.hiddenFiles') }}" style="text-decoration:none;">
                            <div style="font-size:20px; font-weight:700; color:var(--ff-text);">{{ $hiddenFilesCount }}</div>
                            <div style="font-size:11px; color:var(--ff-muted); font-weight:600; margin-top:2px;">Files</div>
                        </a>
                        <a href="{{ route('panel.hiddenLinks') }}" style="text-decoration:none; border-left:1px solid var(--ff-border); border-right:1px solid var(--ff-border);">
                            <div style="font-size:20px; font-weight:700; color:#10b981;">{{ $hiddenLinksCount }}</div>
                            <div style="font-size:11px; color:var(--ff-muted); font-weight:600; margin-top:2px;">Links</div>
                        </a>
                        <a href="{{ route('panel.hiddenPasswords') }}" style="text-decoration:none;">
                            <div style="font-size:20px; font-weight:700; color:#f59e0b;">{{ $hiddenPasswordsCount }}</div>
                            <div style="font-size:11px; color:var(--ff-muted); font-weight:600; margin-top:2px;">Keys</div>
                        </a>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px; margin-bottom:10px;">
                        <a href="{{ route('panel.hiddenFiles') }}" class="ff-btn ff-btn-sm" style="font-size:11.5px; justify-content:center; padding:6px 4px;">
                            Files
                        </a>
                        <a href="{{ route('panel.hiddenLinks') }}" class="ff-btn ff-btn-sm" style="font-size:11.5px; justify-content:center; padding:6px 4px;">
                            Links
                        </a>
                        <a href="{{ route('panel.hiddenPasswords') }}" class="ff-btn ff-btn-sm" style="font-size:11.5px; justify-content:center; padding:6px 4px;">
                            Passwords
                        </a>
                    </div>

                    <form action="{{ route('panel.logoutHiddenFiles') }}" method="POST">
                        @csrf
                        <button type="submit" class="ff-btn ff-btn-danger ff-btn-sm" style="width:100%; justify-content:center; font-size:11.5px; padding:7px 10px;">
                            🔒 Lock Vault Now
                        </button>
                    </form>
                @else
                    {{-- Unauthenticated Locked View --}}
                    <p style="font-size:12.5px; color:var(--ff-text-soft); line-height:1.45; margin-bottom:14px;">
                        Confidential files, links, and credentials are encrypted. Enter your vault passcode to reveal counts and access records.
                    </p>
                    <a href="{{ route('panel.hiddenFilesLogin') }}" class="ff-btn ff-btn-primary ff-btn-sm" style="width:100%; justify-content:center; font-size:12px; padding:8px 12px; gap:6px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        Unlock Vault
                    </a>
                @endif
            </div>

            {{-- ---------------------------- Quick Actions ---------------------------- --}}
            <div class="ff-card ff-stack-xs">
                <h3 class="ff-card-title" style="margin-bottom:6px;">Quick Actions</h3>

                <a href="{{ route('panel.uploadfile') }}" class="ff-quick-action">
                    <span class="ff-quick-action-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                    </span>
                    Upload New File
                </a>

                <a href="{{ route('panel.addlinkview') }}" class="ff-quick-action">
                    <span class="ff-quick-action-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                        </svg>
                    </span>
                    Add Bookmark
                </a>

                <a href="{{ route('panel.addpasswordview') }}" class="ff-quick-action">
                    <span class="ff-quick-action-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="10" rx="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </span>
                    Add Password
                </a>

                <a href="{{ route('panel.categories.index') }}" class="ff-quick-action">
                    <span class="ff-quick-action-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                    </span>
                    Manage Categories
                </a>
            </div>

            {{-- ---------------------------- Storage Breakdown ---------------------------- --}}
            <div class="ff-card">
                <div class="ff-card-head" style="margin-bottom:12px;">
                    <h3 class="ff-card-title">Storage Breakdown</h3>
                    <span style="font-size:12px; font-weight:700; color:var(--ff-accent);">{{ $storageUsed }}</span>
                </div>

                {{-- Visual Multi-Color Segmented Bar --}}
                <div style="height:10px; width:100%; border-radius:10px; overflow:hidden; background:var(--ff-border); display:flex; margin-bottom:14px;">
                    @foreach ($storageCategories as $cat)
                        @if ($cat['percent'] > 0)
                            <div style="height:100%; width:{{ $cat['percent'] }}%; background:{{ $cat['color'] }};" title="{{ $cat['label'] }}: {{ $cat['formatted'] }} ({{ $cat['percent'] }}%)"></div>
                        @endif
                    @endforeach
                </div>

                {{-- Category Legend List --}}
                <div style="display:flex; flex-direction:column; gap:8px;">
                    @foreach ($storageCategories as $cat)
                        <div class="ff-row-between" style="font-size:12px;">
                            <span class="ff-row" style="gap:8px;">
                                <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:{{ $cat['color'] }}; flex-shrink:0;"></span>
                                <span style="color:var(--ff-text);">{{ $cat['label'] }}</span>
                            </span>
                            <span style="font-weight:600; color:var(--ff-muted);">{{ $cat['formatted'] }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- Storage Quota Bar --}}
                <div style="margin-top:16px; padding-top:14px; border-top:1px solid var(--ff-border);">
                    <div class="ff-row-between" style="font-size:12px; margin-bottom:6px;">
                        <span style="color:var(--ff-muted);">Total Quota Used</span>
                        <span style="font-weight:700; color:var(--ff-text);">{{ min($storagePercentage, 100) }}%</span>
                    </div>
                    <div class="ff-progress" style="margin-bottom:6px;">
                        <div class="ff-progress-bar {{ $barState }}" style="width: {{ min($storagePercentage, 100) }}%"></div>
                    </div>
                    <div class="ff-meter-legend">
                        <span>{{ $storageUsed }} used</span>
                        <span>{{ $storageQuota }} limit</span>
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection

@section('push-script')
    <script>
        // Universal Copy Handlers for Dashboard
        $(document).on('click', '.copy-username-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const username = $(this).data('username') || $(this).attr('data-username');
            if (!username) return;

            if (window.ff && typeof window.ff.copy === 'function') {
                window.ff.copy(username, 'Username copied to clipboard!');
            } else if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(username).then(() => {
                    if (window.ff?.toast) window.ff.toast('Username copied to clipboard!', 'success', 2000);
                }).catch(() => {
                    if (window.ff?.toast) window.ff.toast('Failed to copy username', 'error');
                });
            }
        });

        $(document).on('click', '.copy-link-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const url = $(this).data('url') || $(this).attr('data-url');
            if (!url) return;

            if (window.ff && typeof window.ff.copy === 'function') {
                window.ff.copy(url, 'Link copied to clipboard!');
            } else if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(() => {
                    if (window.ff?.toast) window.ff.toast('Link copied to clipboard!', 'success', 2000);
                }).catch(() => {
                    if (window.ff?.toast) window.ff.toast('Failed to copy link', 'error');
                });
            }
        });

        async function quickToggleDashTask(taskId) {
            const row = document.getElementById(`dash-task-${taskId}`);
            if (row) {
                row.style.opacity = '0.5';
                const titleEl = row.querySelector('.dash-task-title');
                if (titleEl) titleEl.style.textDecoration = 'line-through';
            }

            try {
                const res = await fetch(`/panel/todos/tasks/${taskId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.ok) {
                    if (row) {
                        row.style.transition = 'all 0.3s ease';
                        row.style.transform = 'translateX(20px)';
                        row.style.opacity = '0';
                        row.style.maxHeight = '0';
                        row.style.padding = '0';
                        row.style.margin = '0';
                        setTimeout(() => {
                            row.remove();
                            const container = document.getElementById('dashboard-pinned-tasks-container');
                            if (container && container.querySelectorAll('.dashboard-task-item').length === 0) {
                                const card = document.getElementById('dashboard-pinned-tasks-card');
                                if (card) {
                                    card.style.transition = 'all 0.3s ease';
                                    card.style.opacity = '0';
                                    setTimeout(() => card.remove(), 300);
                                }
                            }
                        }, 300);
                    }
                    if (window.ff?.toast) window.ff.toast('Task completed!', 'success', 2000);
                }
            } catch (e) {
                console.error(e);
                if (row) {
                    row.style.opacity = '1';
                    const titleEl = row.querySelector('.dash-task-title');
                    if (titleEl) titleEl.style.textDecoration = 'none';
                }
            }
        }

        window.ff.icons();
    </script>
@endsection
