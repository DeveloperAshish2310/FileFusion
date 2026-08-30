@extends('layout.backend')
@push('title', 'Universal Shared Center')

@section('content')
    <style>
        .shared-row-actions {
            width: 180px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        .shared-title-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            min-width: 0;
        }
        .shared-item-title {
            font-weight: 600;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
            max-width: 100%;
        }
        @media (max-width: 640px) {
            .shared-row-actions {
                width: auto !important;
                gap: 6px !important;
            }
            .shared-row-actions .btn-label {
                display: none !important;
            }
            .shared-row-actions .ff-btn {
                padding: 6px 8px !important;
                min-width: 32px !important;
                justify-content: center !important;
            }
            .shared-title-wrap {
                gap: 4px 6px !important;
            }
            .shared-item-title {
                font-size: 13.5px !important;
                max-width: calc(100vw - 190px) !important;
            }
        }
    </style>

    <div class="ff-row-between" style="align-items:flex-start; margin-bottom:22px;">
        <div>
            <h1 class="ff-h1">Universal Shared Center</h1>
            <p class="ff-sub" style="margin-bottom:0;">Manage, monitor, adjust settings, and revoke access for all your shared files, bookmark links, password secrets, and category portals.</p>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="ff-toolbar" style="margin-bottom:20px;">
        <form action="{{ route('panel.sharedWithMe') }}" method="GET" class="ff-input-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="search" 
                name="q" 
                placeholder="Search shared items by name..." 
                value="{{ $searchTerm ?? '' }}" 
                autocomplete="new-password"
                autocorrect="off"
                autocapitalize="off"
                spellcheck="false"
                data-lpignore="true"
                data-form-type="other"
                data-dashlane-ignore="true"
                readonly
                onfocus="this.removeAttribute('readonly');">
        </form>
    </div>

    <!-- Navigation Tabs -->
    <div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid var(--ff-border); padding-bottom: 12px; flex-wrap: wrap;">
        <button type="button" id="tab-btn-with-me" class="ff-btn is-primary shared-tab-btn" onclick="switchSharedTab('with-me')" style="font-size: 13px; font-weight: 700; border-radius: 8px;">
            📥 Files Received ({{ count($sharesWithMe) }})
        </button>
        <button type="button" id="tab-btn-by-me" class="ff-btn shared-tab-btn" onclick="switchSharedTab('by-me')" style="font-size: 13px; font-weight: 700; border-radius: 8px; background: var(--ff-bg2); color: var(--ff-text);">
            📁 Files Shared ({{ count($sharesByMe) }})
        </button>
        <button type="button" id="tab-btn-links" class="ff-btn shared-tab-btn" onclick="switchSharedTab('links')" style="font-size: 13px; font-weight: 700; border-radius: 8px; background: var(--ff-bg2); color: var(--ff-text);">
            🔗 Links Shared ({{ count($linkSharesByMe) }})
        </button>
        <button type="button" id="tab-btn-passwords" class="ff-btn shared-tab-btn" onclick="switchSharedTab('passwords')" style="font-size: 13px; font-weight: 700; border-radius: 8px; background: var(--ff-bg2); color: var(--ff-text);">
            🔐 Secrets Shared ({{ count($passwordSharesByMe) }})
        </button>
        <button type="button" id="tab-btn-categories" class="ff-btn shared-tab-btn" onclick="switchSharedTab('categories')" style="font-size: 13px; font-weight: 700; border-radius: 8px; background: var(--ff-bg2); color: var(--ff-text);">
            🗂️ Categories Shared ({{ count($categorySharesByMe) }})
        </button>
    </div>

    <!-- 1. SHARED WITH ME (FILES) SECTION -->
    <div id="section-shared-with-me" class="shared-tab-section">
        <div class="ff-list" style="box-sizing: border-box; width: 100%;">
            <div class="ff-list-head ff-hide-mobile" style="display: flex; align-items: center; padding: 12px 16px;">
                <span class="ff-list-col ff-grow" style="flex: 1;">File Name</span>
                <span class="ff-list-col" style="width: 180px;">Shared By</span>
                <span class="ff-list-col" style="width: 120px;">Size</span>
                <span class="ff-list-col" style="width: 140px;">Shared Date</span>
                <span class="ff-list-col" style="width: 130px; text-align: right;">Action</span>
            </div>

            @forelse ($sharesWithMe as $share)
                @php
                    $file = $share->file;
                    $owner = $share->owner;
                @endphp
                @if ($file)
                    <div class="ff-list-row" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; box-sizing: border-box;">
                        <div class="ff-list-main" style="flex: 1; min-width: 0; display: flex; align-items: center; gap: 12px;">
                            <span class="ff-tile-icon ff-tile-icon-sm" style="flex-shrink: 0;">
                                <i data-lucide="file" class="w-[17px] h-[17px]"></i>
                            </span>
                            <div class="ff-min0" style="overflow: hidden; flex: 1;">
                                <span class="ff-list-title shared-item-title" style="display: block;">{{ $file->name }}</span>
                                <span class="ff-list-subtitle ff-hide-desktop" style="font-size: 11.5px; color: var(--ff-muted);">{{ BytetoSize($file->size) }} · by {{ $owner->name ?? 'User' }}</span>
                            </div>
                        </div>

                        <span class="ff-list-cell ff-hide-mobile" style="width: 180px;">
                            <span class="ff-hint" style="color: var(--ff-text); font-weight: 600;">{{ $owner->name ?? 'User' }}</span>
                        </span>

                        <span class="ff-list-cell ff-hide-mobile" style="width: 120px; color: var(--ff-muted);">
                            {{ BytetoSize($file->size) }}
                        </span>

                        <span class="ff-list-cell ff-hide-mobile" style="width: 140px; color: var(--ff-muted);">
                            {{ $share->created_at->format('M d, Y') }}
                        </span>

                        <div class="shared-row-actions" style="width: 130px;">
                            <a href="{{ route('panel.downloadFile', ['fileid' => encrypt($file->id)]) }}" class="ff-btn is-primary" style="padding: 6px 14px; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; border-radius: 8px;" title="Download File">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i> <span class="btn-label">Download</span>
                            </a>
                        </div>
                    </div>
                @endif
            @empty
                <div class="ff-empty">
                    <span class="ff-empty-icon" style="width:64px; height:64px; border-radius:16px;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </span>
                    <div class="ff-section-title" style="font-size:17px;">No shared files with you</div>
                    <p class="ff-empty-text" style="max-width:380px; margin:0;">
                        When other users share files with your email address, they will show up here for you to download.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- 2. SHARED BY ME (FILES) SECTION -->
    <div id="section-shared-by-me" class="shared-tab-section" style="display: none;">
        <div class="ff-list" style="box-sizing: border-box; width: 100%;">
            <div class="ff-list-head ff-hide-mobile" style="display: flex; align-items: center; padding: 12px 16px;">
                <span class="ff-list-col ff-grow" style="flex: 1;">File Name</span>
                <span class="ff-list-col" style="width: 140px;">Share Mode</span>
                <span class="ff-list-col" style="width: 170px;">Limits &amp; Expiry</span>
                <span class="ff-list-col" style="width: 170px;">Recipient / Link</span>
                <span class="ff-list-col" style="width: 180px; text-align: right;">Actions</span>
            </div>

            @forelse ($sharesByMe as $share)
                @php
                    $file = $share->file;
                    $recipient = $share->recipient;
                    $publicUrl = appShareUrl('/s/' . $share->share_token);
                    $isLocked = $share->hasReachedDownloadLimit() || $share->isExpired();
                @endphp
                @if ($file)
                    <div class="ff-list-row" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; box-sizing: border-box;">
                        <div class="ff-list-main" style="flex: 1; min-width: 0; display: flex; align-items: center; gap: 12px;">
                            <span class="ff-tile-icon ff-tile-icon-sm" style="flex-shrink: 0;">
                                <i data-lucide="file" class="w-[17px] h-[17px]"></i>
                            </span>
                            <div class="ff-min0" style="overflow: hidden; flex: 1;">
                                <div class="shared-title-wrap">
                                    <span class="ff-list-title shared-item-title">{{ $file->name }}</span>
                                    @if ($file->is_hidden)
                                        <span class="ff-badge-hidden" style="font-size: 10px; padding: 2px 6px;">🔒 Vault</span>
                                    @endif
                                    @if ($isLocked)
                                        <span class="ff-badge-hidden" style="font-size: 10px; padding: 2px 6px;">Expired / Locked</span>
                                    @endif
                                </div>
                                <span class="ff-list-subtitle" style="font-size: 11.5px; color: var(--ff-muted);">
                                    Created {{ $share->created_at->diffForHumans() }} · {{ BytetoSize($file->size) }}
                                </span>
                            </div>
                        </div>

                        <!-- Share Mode Badge -->
                        <span class="ff-list-cell ff-hide-mobile" style="width: 140px;">
                            @if ($share->share_type === 'public_link')
                                <span class="ff-badge-type" style="background: rgba(99,102,241,0.12); color: #6366f1;">🌐 Public Link</span>
                            @elseif ($share->share_type === 'private_user')
                                <span class="ff-badge-type" style="background: rgba(16,185,129,0.12); color: #10b981;">👤 Private</span>
                            @else
                                <span class="ff-badge-type" style="background: rgba(245,158,11,0.12); color: #f59e0b;">👁️ Anonymous</span>
                            @endif
                        </span>

                        <!-- Limits & Expiry -->
                        <span class="ff-list-cell ff-hide-mobile" style="width: 170px; font-size: 12px;">
                            <div style="color: var(--ff-text); font-weight: 500;">
                                📥 {{ $share->download_count }} / {{ $share->max_downloads ?? '∞' }} downloads
                            </div>
                            <div style="color: var(--ff-muted); font-size: 11px;">
                                ⏱️ {{ $share->expires_at ? $share->expires_at->format('M d, H:i') : 'Never expires' }}
                            </div>
                        </span>

                        <!-- Recipient / Link URL -->
                        <span class="ff-list-cell ff-hide-mobile" style="width: 170px; font-size: 12px;">
                            @if ($share->share_type === 'private_user')
                                <div style="font-weight: 600; color: var(--ff-text); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                    {{ $recipient->name ?? $share->recipient_email }}
                                </div>
                                <div style="color: var(--ff-muted); font-size: 11px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                    {{ $share->recipient_email }}
                                </div>
                            @else
                                <button type="button" class="ff-hint" onclick="copyShareUrl('{{ $publicUrl }}')" style="background: none; border: none; padding: 0; color: #6366f1; cursor: pointer; display: flex; align-items: center; gap: 4px; font-weight: 600; font-size: 12px;">
                                    <i data-lucide="copy" class="w-3.5 h-3.5"></i> Copy Link
                                </button>
                            @endif
                        </span>

                        <!-- Actions -->
                        <div class="shared-row-actions">
                            <button type="button" class="ff-btn" onclick="openEditFileShareModal({{ json_encode([
                                'id' => $share->id,
                                'file_name' => $file->name,
                                'share_type' => $share->share_type,
                                'max_downloads' => $share->max_downloads,
                                'download_count' => $share->download_count,
                                'expires_at' => $share->expires_at ? $share->expires_at->format('Y-m-d\TH:i') : '',
                                'is_password_protected' => $share->isPasswordProtected(),
                                'is_anonymous' => (bool)$share->is_anonymous,
                                'recipient_email' => $share->recipient_email,
                            ]) }})" style="padding: 6px 10px; font-size: 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;" title="Edit Share Settings">
                                <i data-lucide="sliders" class="w-3.5 h-3.5"></i> <span class="btn-label">Settings</span>
                            </button>
                            <button type="button" class="ff-btn is-danger" onclick="revokeShareAjax({{ $share->id }}, this)" style="padding: 6px 10px; font-size: 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;" title="Revoke Share Access">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> <span class="btn-label">Revoke</span>
                            </button>
                        </div>
                    </div>
                @endif
            @empty
                <div class="ff-empty">
                    <span class="ff-empty-icon" style="width:64px; height:64px; border-radius:16px;">
                        <i data-lucide="share-2" class="w-7 h-7"></i>
                    </span>
                    <div class="ff-section-title" style="font-size:17px;">No active file shares</div>
                    <p class="ff-empty-text" style="max-width:380px; margin:0;">
                        Files you share publicly or privately with others will appear here for you to manage settings, adjust download limits, or revoke access anytime.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- 3. SHARED BOOKMARK LINKS SECTION -->
    <div id="section-shared-links" class="shared-tab-section" style="display: none;">
        <div class="ff-list" style="box-sizing: border-box; width: 100%;">
            <div class="ff-list-head ff-hide-mobile" style="display: flex; align-items: center; padding: 12px 16px;">
                <span class="ff-list-col ff-grow" style="flex: 1;">Bookmark Title</span>
                <span class="ff-list-col" style="width: 140px;">Mode</span>
                <span class="ff-list-col" style="width: 170px;">Clicks &amp; Expiry</span>
                <span class="ff-list-col" style="width: 170px;">Share URL</span>
                <span class="ff-list-col" style="width: 180px; text-align: right;">Actions</span>
            </div>

            @forelse ($linkSharesByMe as $lShare)
                @php
                    $link = $lShare->link;
                    $pubUrl = appShareUrl('/s/l/' . $lShare->share_token);
                    $isLocked = $lShare->hasReachedClickLimit() || $lShare->isExpired();
                @endphp
                <div class="ff-list-row" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; box-sizing: border-box;">
                    <div class="ff-list-main" style="flex: 1; min-width: 0; display: flex; align-items: center; gap: 12px;">
                        <span class="ff-tile-icon ff-tile-icon-sm" style="flex-shrink: 0; background: rgba(99,102,241,0.15); color: #6366f1;">
                            <i data-lucide="link-2" class="w-[17px] h-[17px]"></i>
                        </span>
                        <div class="ff-min0" style="overflow: hidden; flex: 1;">
                            <div class="shared-title-wrap">
                                <span class="ff-list-title shared-item-title">
                                    {{ $link ? $link->title : 'Shared Bookmark' }}
                                </span>
                                @if ($link && $link->is_hidden)
                                    <span class="ff-badge-hidden" style="font-size: 10px; padding: 2px 6px;">🔒 Vault</span>
                                @endif
                                @if ($isLocked)
                                    <span class="ff-badge-hidden" style="font-size: 10px; padding: 2px 6px;">Expired</span>
                                @endif
                            </div>
                            <span class="ff-list-subtitle" style="font-size: 11.5px; color: var(--ff-muted);">
                                {{ $link ? Str::limit($link->url, 40) : 'Link' }} · Created {{ $lShare->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>

                    <span class="ff-list-cell ff-hide-mobile" style="width: 140px;">
                        <span class="ff-badge-type" style="background: rgba(99,102,241,0.12); color: #6366f1;">
                            {{ $lShare->is_anonymous ? '👤 Anonymous' : '🌐 Public Link' }}
                        </span>
                    </span>

                    <span class="ff-list-cell ff-hide-mobile" style="width: 170px; font-size: 12px;">
                        <div style="color: var(--ff-text); font-weight: 500;">
                            👁️ {{ $lShare->click_count }} / {{ $lShare->max_clicks ?? '∞' }} clicks
                        </div>
                        <div style="color: var(--ff-muted); font-size: 11px;">
                            ⏱️ {{ $lShare->expires_at ? $lShare->expires_at->format('M d, H:i') : 'Never' }}
                        </div>
                    </span>

                    <span class="ff-list-cell ff-hide-mobile" style="width: 170px;">
                        <button type="button" class="ff-hint" onclick="copyShareUrl('{{ $pubUrl }}')" style="background: none; border: none; padding: 0; color: #6366f1; cursor: pointer; display: flex; align-items: center; gap: 4px; font-weight: 600; font-size: 12px;">
                            <i data-lucide="copy" class="w-3.5 h-3.5"></i> Copy Link
                        </button>
                    </span>

                    <div class="shared-row-actions">
                        <button type="button" class="ff-btn" onclick="openEditLinkShareModal({{ json_encode([
                            'id' => encrypt($lShare->id),
                            'title' => $link ? $link->title : 'Bookmark',
                            'max_clicks' => $lShare->max_clicks,
                            'click_count' => $lShare->click_count,
                            'is_anonymous' => (bool)$lShare->is_anonymous,
                            'is_password_protected' => !empty($lShare->password),
                        ]) }})" style="padding: 6px 10px; font-size: 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;" title="Edit Link Settings">
                            <i data-lucide="sliders" class="w-3.5 h-3.5"></i> <span class="btn-label">Settings</span>
                        </button>
                        <button type="button" class="ff-btn is-danger" onclick="revokeUniversalShare('{{ encrypt($lShare->id) }}', this, 'link')" style="padding: 6px 10px; font-size: 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;" title="Revoke Link Access">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> <span class="btn-label">Revoke</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="ff-empty">
                    <span class="ff-empty-icon" style="width:64px; height:64px; border-radius:16px;">
                        <i data-lucide="link" class="w-7 h-7"></i>
                    </span>
                    <div class="ff-section-title" style="font-size:17px;">No active bookmark shares</div>
                    <p class="ff-empty-text" style="max-width:380px; margin:0;">
                        When you share bookmark links from your Bookmarks list, they will appear here.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- 4. SHARED PASSWORD SECRETS SECTION -->
    <div id="section-shared-passwords" class="shared-tab-section" style="display: none;">
        <div class="ff-list" style="box-sizing: border-box; width: 100%;">
            <div class="ff-list-head ff-hide-mobile" style="display: flex; align-items: center; padding: 12px 16px;">
                <span class="ff-list-col ff-grow" style="flex: 1;">Secret Title</span>
                <span class="ff-list-col" style="width: 140px;">Security Type</span>
                <span class="ff-list-col" style="width: 170px;">Status &amp; Expiry</span>
                <span class="ff-list-col" style="width: 170px;">Secret URL</span>
                <span class="ff-list-col" style="width: 180px; text-align: right;">Actions</span>
            </div>

            @forelse ($passwordSharesByMe as $pwShare)
                @php
                    $pw = $pwShare->credential;
                    $pubUrl = appShareUrl('/s/v/' . $pwShare->share_token);
                    $isBurned = empty($pwShare->encrypted_payload) || $pwShare->reveal_count > 0;
                    $isLocked = $isBurned || $pwShare->isExpired();
                @endphp
                <div class="ff-list-row" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; box-sizing: border-box;">
                    <div class="ff-list-main" style="flex: 1; min-width: 0; display: flex; align-items: center; gap: 12px;">
                        <span class="ff-tile-icon ff-tile-icon-sm" style="flex-shrink: 0; background: rgba(244,63,94,0.15); color: #f43f5e;">
                            <i data-lucide="shield-alert" class="w-[17px] h-[17px]"></i>
                        </span>
                        <div class="ff-min0" style="overflow: hidden; flex: 1;">
                            <div class="shared-title-wrap">
                                <span class="ff-list-title shared-item-title">
                                    {{ $pw ? $pw->title : 'Vault Credential' }}
                                </span>
                                @if ($pw && $pw->is_hidden)
                                    <span class="ff-badge-hidden" style="font-size: 10px; padding: 2px 6px;">🔒 Vault</span>
                                @endif
                                @if ($isBurned)
                                    <span class="ff-badge-hidden" style="font-size: 10px; padding: 2px 6px; background: rgba(244,63,94,0.2); color: #f43f5e;">🔥 Burned &amp; Purged</span>
                                @elseif ($pwShare->isExpired())
                                    <span class="ff-badge-hidden" style="font-size: 10px; padding: 2px 6px;">Expired</span>
                                @else
                                    <span class="ff-badge-type" style="font-size: 10px; padding: 2px 6px; background: rgba(16,185,129,0.15); color: #10b981;">Active</span>
                                @endif
                            </div>
                            <span class="ff-list-subtitle" style="font-size: 11.5px; color: var(--ff-muted);">
                                Zero-Knowledge Share · Created {{ $pwShare->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>

                    <span class="ff-list-cell ff-hide-mobile" style="width: 140px;">
                        <span class="ff-badge-type" style="background: rgba(244,63,94,0.12); color: #f43f5e;">
                            🔥 Single-use Burn
                        </span>
                    </span>

                    <span class="ff-list-cell ff-hide-mobile" style="width: 170px; font-size: 12px;">
                        <div style="color: var(--ff-text); font-weight: 500;">
                            {{ $isBurned ? 'Read & Destroyed' : 'Unopened' }}
                        </div>
                        <div style="color: var(--ff-muted); font-size: 11px;">
                            ⏱️ {{ $pwShare->expires_at ? $pwShare->expires_at->format('M d, H:i') : 'Never' }}
                        </div>
                    </span>

                    <span class="ff-list-cell ff-hide-mobile" style="width: 170px;">
                        @if (!$isBurned)
                            <button type="button" class="ff-hint" onclick="copyShareUrl('{{ $pubUrl }}')" style="background: none; border: none; padding: 0; color: #f43f5e; cursor: pointer; display: flex; align-items: center; gap: 4px; font-weight: 600; font-size: 12px;">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i> Copy Secret URL
                            </button>
                        @else
                            <span style="font-size: 11.5px; color: var(--ff-muted);">Purged</span>
                        @endif
                    </span>

                    <div class="shared-row-actions">
                        @if (!$isBurned)
                            <button type="button" class="ff-btn" onclick="openEditPasswordShareModal({{ json_encode([
                                'id' => encrypt($pwShare->id),
                                'title' => $pw ? $pw->title : 'Credential Secret',
                                'is_password_protected' => !empty($pwShare->password),
                            ]) }})" style="padding: 6px 10px; font-size: 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;" title="Edit Secret Settings">
                                <i data-lucide="sliders" class="w-3.5 h-3.5"></i> <span class="btn-label">Settings</span>
                            </button>
                        @endif
                        <button type="button" class="ff-btn is-danger" onclick="revokeUniversalShare('{{ encrypt($pwShare->id) }}', this, 'password')" style="padding: 6px 10px; font-size: 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;" title="Revoke Secret Share">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> <span class="btn-label">Revoke</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="ff-empty">
                    <span class="ff-empty-icon" style="width:64px; height:64px; border-radius:16px;">
                        <i data-lucide="shield-alert" class="w-7 h-7"></i>
                    </span>
                    <div class="ff-section-title" style="font-size:17px;">No active secret shares</div>
                    <p class="ff-empty-text" style="max-width:380px; margin:0;">
                        Zero-knowledge secret links generated from your password vault will appear here.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- 5. SHARED CATEGORY BUNDLES SECTION -->
    <div id="section-shared-categories" class="shared-tab-section" style="display: none;">
        <div class="ff-list" style="box-sizing: border-box; width: 100%;">
            <div class="ff-list-head ff-hide-mobile" style="display: flex; align-items: center; padding: 12px 16px;">
                <span class="ff-list-col ff-grow" style="flex: 1;">Category Bundle</span>
                <span class="ff-list-col" style="width: 140px;">Contents</span>
                <span class="ff-list-col" style="width: 170px;">Views &amp; Expiry</span>
                <span class="ff-list-col" style="width: 170px;">Portal URL</span>
                <span class="ff-list-col" style="width: 180px; text-align: right;">Actions</span>
            </div>

            @forelse ($categorySharesByMe as $cShare)
                @php
                    $cat = $cShare->category;
                    $pubUrl = appShareUrl('/s/c/' . $cShare->share_token);
                    $isLocked = $cShare->hasReachedViewLimit() || $cShare->isExpired();
                @endphp
                <div class="ff-list-row" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; box-sizing: border-box;">
                    <div class="ff-list-main" style="flex: 1; min-width: 0; display: flex; align-items: center; gap: 12px;">
                        <span class="ff-tile-icon ff-tile-icon-sm" style="flex-shrink: 0; background: rgba(99,102,241,0.15); color: #6366f1;">
                            <i data-lucide="folder-archive" class="w-[17px] h-[17px]"></i>
                        </span>
                        <div class="ff-min0" style="overflow: hidden; flex: 1;">
                            <div class="shared-title-wrap">
                                <span class="ff-list-title shared-item-title">
                                    {{ $cat ? $cat->title : 'Category Bundle' }}
                                </span>
                                @if ($cat && $cat->is_hidden)
                                    <span class="ff-badge-hidden" style="font-size: 10px; padding: 2px 6px;">🔒 Vault</span>
                                @endif
                                @if ($isLocked)
                                    <span class="ff-badge-hidden" style="font-size: 10px; padding: 2px 6px;">Expired</span>
                                @endif
                            </div>
                            <span class="ff-list-subtitle" style="font-size: 11.5px; color: var(--ff-muted);">
                                Public Bundle · Created {{ $cShare->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>

                    <span class="ff-list-cell ff-hide-mobile" style="width: 140px;">
                        <span class="ff-badge-type" style="background: rgba(99,102,241,0.12); color: #6366f1;">
                            📁 Assets &amp; Links
                        </span>
                    </span>

                    <span class="ff-list-cell ff-hide-mobile" style="width: 170px; font-size: 12px;">
                        <div style="color: var(--ff-text); font-weight: 500;">
                            👁️ {{ $cShare->view_count }} / {{ $cShare->max_views ?? '∞' }} views
                        </div>
                        <div style="color: var(--ff-muted); font-size: 11px;">
                            ⏱️ {{ $cShare->expires_at ? $cShare->expires_at->format('M d, H:i') : 'Never' }}
                        </div>
                    </span>

                    <span class="ff-list-cell ff-hide-mobile" style="width: 170px;">
                        <button type="button" class="ff-hint" onclick="copyShareUrl('{{ $pubUrl }}')" style="background: none; border: none; padding: 0; color: #6366f1; cursor: pointer; display: flex; align-items: center; gap: 4px; font-weight: 600; font-size: 12px;">
                            <i data-lucide="copy" class="w-3.5 h-3.5"></i> Copy Portal URL
                        </button>
                    </span>

                    <div class="shared-row-actions">
                        <button type="button" class="ff-btn" onclick="openEditCategoryShareModal({{ json_encode([
                            'id' => encrypt($cShare->id),
                            'title' => $cat ? $cat->title : 'Category Bundle',
                            'max_views' => $cShare->max_views,
                            'view_count' => $cShare->view_count,
                            'include_files' => (bool)$cShare->include_files,
                            'include_links' => (bool)$cShare->include_links,
                            'is_password_protected' => !empty($cShare->password),
                        ]) }})" style="padding: 6px 10px; font-size: 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;" title="Edit Bundle Settings">
                            <i data-lucide="sliders" class="w-3.5 h-3.5"></i> <span class="btn-label">Settings</span>
                        </button>
                        <button type="button" class="ff-btn is-danger" onclick="revokeUniversalShare('{{ encrypt($cShare->id) }}', this, 'category')" style="padding: 6px 10px; font-size: 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;" title="Revoke Bundle Access">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> <span class="btn-label">Revoke</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="ff-empty">
                    <span class="ff-empty-icon" style="width:64px; height:64px; border-radius:16px;">
                        <i data-lucide="folder-archive" class="w-7 h-7"></i>
                    </span>
                    <div class="ff-section-title" style="font-size:17px;">No active category bundles</div>
                    <p class="ff-empty-text" style="max-width:380px; margin:0;">
                        Shared workspace category portals will appear here.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 6. EDIT FILE SHARE SETTINGS MODAL -->
    <!-- ========================================================================= -->
    <div id="editShareModalOverlay" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0,0,0,0.7); backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 16px;">
        <div style="background: var(--ff-card); color: var(--ff-text); border: 1px solid var(--ff-border); border-radius: 16px; width: 100%; max-width: 520px; box-shadow: 0 25px 50px rgba(0,0,0,0.5); overflow: hidden;">
            <div style="padding: 18px 24px; border-bottom: 1px solid var(--ff-border); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(99,102,241,0.15); color: #6366f1; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="sliders" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Edit File Share Settings</h3>
                        <p id="modalFileName" style="font-size: 12px; color: var(--ff-muted); margin: 0;"></p>
                    </div>
                </div>
                <button type="button" onclick="closeEditModal('editShareModalOverlay')" style="background: none; border: none; font-size: 24px; color: var(--ff-muted); cursor: pointer;">&times;</button>
            </div>

            <form id="editShareForm" style="padding: 22px 24px; display: flex; flex-direction: column; gap: 16px;">
                <input type="hidden" id="editShareId">

                <div class="ff-field">
                    <label class="ff-label" style="font-weight: 600; font-size: 13px;">Share Access Mode</label>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                        <label style="border: 1px solid var(--ff-border); padding: 10px; border-radius: 8px; cursor: pointer; text-align: center; font-size: 12px; font-weight: 600;" id="labelTypePublic">
                            <input type="radio" name="share_type" value="public_link" style="display: none;" onchange="updateShareTypeUI()">
                            🌐 Public Link
                        </label>
                        <label style="border: 1px solid var(--ff-border); padding: 10px; border-radius: 8px; cursor: pointer; text-align: center; font-size: 12px; font-weight: 600;" id="labelTypePrivate">
                            <input type="radio" name="share_type" value="private_user" style="display: none;" onchange="updateShareTypeUI()">
                            👤 Private User
                        </label>
                        <label style="border: 1px solid var(--ff-border); padding: 10px; border-radius: 8px; cursor: pointer; text-align: center; font-size: 12px; font-weight: 600;" id="labelTypeAnon">
                            <input type="radio" name="share_type" value="anonymous_qr" style="display: none;" onchange="updateShareTypeUI()">
                            👁️ Anonymous
                        </label>
                    </div>
                </div>

                <div class="ff-field" id="wrapRecipientEmail" style="display: none;">
                    <label class="ff-label" for="editRecipientEmail">Recipient User Email</label>
                    <input type="email" id="editRecipientEmail" class="ff-input" placeholder="user@example.com">
                </div>

                <div class="ff-split-even">
                    <div class="ff-field">
                        <label class="ff-label" for="editMaxDownloads">Max Allowed Downloads</label>
                        <input type="number" id="editMaxDownloads" class="ff-input" placeholder="e.g. 1 for single-use" min="1">
                    </div>
                    <div class="ff-field" style="display: flex; flex-direction: column; justify-content: flex-end;">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer; margin-bottom: 8px;">
                            <input type="checkbox" id="editResetCount" value="1"> Reset count to 0
                        </label>
                        <span class="ff-hint" id="currentDownloadCountText">Current DLs: 0</span>
                    </div>
                </div>

                <div class="ff-field">
                    <label class="ff-label" for="editExpiryPreset">Link Expiration Window</label>
                    <select id="editExpiryPreset" class="ff-select">
                        <option value="keep">Keep Current Expiration</option>
                        <option value="0">Never (No Expiration)</option>
                        <option value="60">1 Hour</option>
                        <option value="1440">24 Hours</option>
                        <option value="10080">7 Days</option>
                        <option value="43200">30 Days</option>
                    </select>
                </div>

                <div class="ff-field">
                    <label class="ff-label" for="editPasscode">Passcode PIN Protection</label>
                    <input type="password" id="editPasscode" class="ff-input" placeholder="New passcode (leave blank to keep unchanged)">
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #ef4444; margin-top: 6px; cursor: pointer;">
                        <input type="checkbox" id="editClearPasscode" value="1"> Remove passcode protection
                    </label>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px; border-top: 1px solid var(--ff-border); padding-top: 16px;">
                    <button type="button" class="ff-btn" onclick="closeEditModal('editShareModalOverlay')">Cancel</button>
                    <button type="submit" id="btnSaveShareSettings" class="ff-btn ff-btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 7. EDIT LINK SHARE SETTINGS MODAL -->
    <!-- ========================================================================= -->
    <div id="editLinkShareModalOverlay" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0,0,0,0.7); backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 16px;">
        <div style="background: var(--ff-card); color: var(--ff-text); border: 1px solid var(--ff-border); border-radius: 16px; width: 100%; max-width: 500px; box-shadow: 0 25px 50px rgba(0,0,0,0.5); overflow: hidden;">
            <div style="padding: 18px 24px; border-bottom: 1px solid var(--ff-border); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(99,102,241,0.15); color: #6366f1; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="link-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Edit Bookmark Share</h3>
                        <p id="modalLinkTitle" style="font-size: 12px; color: var(--ff-muted); margin: 0;"></p>
                    </div>
                </div>
                <button type="button" onclick="closeEditModal('editLinkShareModalOverlay')" style="background: none; border: none; font-size: 24px; color: var(--ff-muted); cursor: pointer;">&times;</button>
            </div>

            <form id="editLinkShareForm" style="padding: 22px 24px; display: flex; flex-direction: column; gap: 16px;">
                <input type="hidden" id="editLinkShareId">

                <div class="ff-split-even">
                    <div class="ff-field">
                        <label class="ff-label">Max Allowed Clicks</label>
                        <input type="number" id="editLinkMaxClicks" class="ff-input" placeholder="Unlimited" min="1">
                    </div>
                    <div class="ff-field" style="display: flex; flex-direction: column; justify-content: flex-end;">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer; margin-bottom: 8px;">
                            <input type="checkbox" id="editLinkResetClicks" value="1"> Reset click count to 0
                        </label>
                        <span class="ff-hint" id="currentLinkClicksText">Current Clicks: 0</span>
                    </div>
                </div>

                <div class="ff-field">
                    <label class="ff-label">Expiration Window</label>
                    <select id="editLinkExpiryPreset" class="ff-select">
                        <option value="keep">Keep Current Expiration</option>
                        <option value="0">Never (Permanent)</option>
                        <option value="60">1 Hour</option>
                        <option value="1440">24 Hours</option>
                        <option value="10080">7 Days</option>
                        <option value="43200">30 Days</option>
                    </select>
                </div>

                <div class="ff-field">
                    <label class="ff-label">Passcode PIN Protection</label>
                    <input type="password" id="editLinkPasscode" class="ff-input" placeholder="New PIN (leave blank to keep unchanged)">
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #ef4444; margin-top: 6px; cursor: pointer;">
                        <input type="checkbox" id="editLinkClearPasscode" value="1"> Remove passcode protection
                    </label>
                </div>

                <div class="ff-field">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" id="editLinkIsAnonymous" value="1">
                        👤 Anonymous Mode (Hide account info on preview)
                    </label>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px; border-top: 1px solid var(--ff-border); padding-top: 16px;">
                    <button type="button" class="ff-btn" onclick="closeEditModal('editLinkShareModalOverlay')">Cancel</button>
                    <button type="submit" id="btnSaveLinkShareSettings" class="ff-btn ff-btn-primary">Save Link Settings</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 8. EDIT PASSWORD SECRET SHARE SETTINGS MODAL -->
    <!-- ========================================================================= -->
    <div id="editPasswordShareModalOverlay" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0,0,0,0.7); backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 16px;">
        <div style="background: var(--ff-card); color: var(--ff-text); border: 1px solid var(--ff-border); border-radius: 16px; width: 100%; max-width: 500px; box-shadow: 0 25px 50px rgba(0,0,0,0.5); overflow: hidden;">
            <div style="padding: 18px 24px; border-bottom: 1px solid var(--ff-border); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(244,63,94,0.15); color: #f43f5e; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="shield-alert" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Edit Secret Share</h3>
                        <p id="modalPasswordTitle" style="font-size: 12px; color: var(--ff-muted); margin: 0;"></p>
                    </div>
                </div>
                <button type="button" onclick="closeEditModal('editPasswordShareModalOverlay')" style="background: none; border: none; font-size: 24px; color: var(--ff-muted); cursor: pointer;">&times;</button>
            </div>

            <form id="editPasswordShareForm" style="padding: 22px 24px; display: flex; flex-direction: column; gap: 16px;">
                <input type="hidden" id="editPasswordShareId">

                <div class="ff-field">
                    <label class="ff-label">Expiration Window</label>
                    <select id="editPasswordExpiryPreset" class="ff-select">
                        <option value="keep">Keep Current Expiration</option>
                        <option value="60">1 Hour</option>
                        <option value="1440">24 Hours</option>
                        <option value="10080">7 Days</option>
                    </select>
                </div>

                <div class="ff-field">
                    <label class="ff-label">PIN Passcode Protection</label>
                    <input type="password" id="editPasswordPasscode" class="ff-input" placeholder="New PIN passcode (leave blank to keep unchanged)">
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #ef4444; margin-top: 6px; cursor: pointer;">
                        <input type="checkbox" id="editPasswordClearPasscode" value="1"> Remove passcode protection
                    </label>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px; border-top: 1px solid var(--ff-border); padding-top: 16px;">
                    <button type="button" class="ff-btn" onclick="closeEditModal('editPasswordShareModalOverlay')">Cancel</button>
                    <button type="submit" id="btnSavePasswordShareSettings" class="ff-btn ff-btn-primary" style="background:#f43f5e; border-color:#f43f5e;">Save Secret Settings</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 9. EDIT CATEGORY BUNDLE SHARE SETTINGS MODAL -->
    <!-- ========================================================================= -->
    <div id="editCategoryShareModalOverlay" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0,0,0,0.7); backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 16px;">
        <div style="background: var(--ff-card); color: var(--ff-text); border: 1px solid var(--ff-border); border-radius: 16px; width: 100%; max-width: 500px; box-shadow: 0 25px 50px rgba(0,0,0,0.5); overflow: hidden;">
            <div style="padding: 18px 24px; border-bottom: 1px solid var(--ff-border); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(99,102,241,0.15); color: #6366f1; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="folder-archive" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Edit Category Bundle</h3>
                        <p id="modalCategoryTitle" style="font-size: 12px; color: var(--ff-muted); margin: 0;"></p>
                    </div>
                </div>
                <button type="button" onclick="closeEditModal('editCategoryShareModalOverlay')" style="background: none; border: none; font-size: 24px; color: var(--ff-muted); cursor: pointer;">&times;</button>
            </div>

            <form id="editCategoryShareForm" style="padding: 22px 24px; display: flex; flex-direction: column; gap: 16px;">
                <input type="hidden" id="editCategoryShareId">

                <div class="ff-field">
                    <label class="ff-label">Include Assets in Bundle:</label>
                    <div style="display: flex; gap: 16px; margin-top: 4px;">
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; cursor: pointer;">
                            <input type="checkbox" id="editCatIncludeFiles" value="1"> Files
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; cursor: pointer;">
                            <input type="checkbox" id="editCatIncludeLinks" value="1"> Bookmarks
                        </label>
                    </div>
                </div>

                <div class="ff-split-even">
                    <div class="ff-field">
                        <label class="ff-label">Max Allowed Views</label>
                        <input type="number" id="editCatMaxViews" class="ff-input" placeholder="Unlimited" min="1">
                    </div>
                    <div class="ff-field" style="display: flex; flex-direction: column; justify-content: flex-end;">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer; margin-bottom: 8px;">
                            <input type="checkbox" id="editCatResetViews" value="1"> Reset view count to 0
                        </label>
                        <span class="ff-hint" id="currentCatViewsText">Current Views: 0</span>
                    </div>
                </div>

                <div class="ff-field">
                    <label class="ff-label">Expiration Window</label>
                    <select id="editCatExpiryPreset" class="ff-select">
                        <option value="keep">Keep Current Expiration</option>
                        <option value="0">Never (Permanent)</option>
                        <option value="60">1 Hour</option>
                        <option value="1440">24 Hours</option>
                        <option value="10080">7 Days</option>
                        <option value="43200">30 Days</option>
                    </select>
                </div>

                <div class="ff-field">
                    <label class="ff-label">PIN Passcode Protection</label>
                    <input type="password" id="editCatPasscode" class="ff-input" placeholder="New PIN (leave blank to keep unchanged)">
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #ef4444; margin-top: 6px; cursor: pointer;">
                        <input type="checkbox" id="editCatClearPasscode" value="1"> Remove passcode protection
                    </label>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px; border-top: 1px solid var(--ff-border); padding-top: 16px;">
                    <button type="button" class="ff-btn" onclick="closeEditModal('editCategoryShareModalOverlay')">Cancel</button>
                    <button type="submit" id="btnSaveCategoryShareSettings" class="ff-btn ff-btn-primary">Save Bundle Settings</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('push-script')
    <script>
        function switchSharedTab(tab, updateUrl) {
            if (updateUrl === undefined) updateUrl = true;
            
            const tabs = ['with-me', 'by-me', 'links', 'passwords', 'categories'];
            
            localStorage.setItem('ff-shared-tab', tab);

            if (updateUrl) {
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url);
            }

            tabs.forEach(t => {
                const sec = document.getElementById(`section-shared-${t}`);
                const btn = document.getElementById(`tab-btn-${t}`);
                if (sec) sec.style.display = (t === tab) ? 'block' : 'none';
                if (btn) {
                    if (t === tab) {
                        btn.classList.add('is-primary');
                        btn.style.background = '#6366f1';
                        btn.style.color = '#ffffff';
                    } else {
                        btn.classList.remove('is-primary');
                        btn.style.background = 'var(--ff-bg2)';
                        btn.style.color = 'var(--ff-text)';
                    }
                }
            });

            if (window.lucide) window.lucide.createIcons();
        }

        // Restore tab on load
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const savedTab = urlParams.get('tab') || localStorage.getItem('ff-shared-tab') || 'with-me';
            switchSharedTab(savedTab, false);
        });

        function copyShareUrl(url) {
            if (window.ff && typeof window.ff.copy === 'function') {
                window.ff.copy(url, 'Share link copied to clipboard!');
            } else if (window.copyToClipboard) {
                window.copyToClipboard(url, 'Share link copied to clipboard!');
            }
        }

        function closeEditModal(modalId) {
            const el = document.getElementById(modalId);
            if (el) el.style.display = 'none';
        }

        // ----------------------------------------------------
        // 1. FILE SHARE SETTINGS MODAL
        // ----------------------------------------------------
        function openEditFileShareModal(share) {
            document.getElementById('editShareId').value = share.id;
            document.getElementById('modalFileName').textContent = share.file_name;
            document.getElementById('editMaxDownloads').value = share.max_downloads || '';
            document.getElementById('currentDownloadCountText').textContent = `Current DLs: ${share.download_count}`;
            document.getElementById('editResetCount').checked = false;
            document.getElementById('editRecipientEmail').value = share.recipient_email || '';
            document.getElementById('editPasscode').value = '';
            document.getElementById('editClearPasscode').checked = false;
            document.getElementById('editExpiryPreset').value = 'keep';

            const radios = document.getElementsByName('share_type');
            for (const radio of radios) {
                radio.checked = (radio.value === share.share_type);
            }
            updateShareTypeUI();

            document.getElementById('editShareModalOverlay').style.display = 'flex';
            if (window.lucide) window.lucide.createIcons();
        }

        function updateShareTypeUI() {
            const shareType = document.querySelector('input[name="share_type"]:checked')?.value || 'public_link';
            const wrapEmail = document.getElementById('wrapRecipientEmail');
            
            document.getElementById('labelTypePublic').style.borderColor = (shareType === 'public_link') ? '#6366f1' : 'var(--ff-border)';
            document.getElementById('labelTypePrivate').style.borderColor = (shareType === 'private_user') ? '#6366f1' : 'var(--ff-border)';
            document.getElementById('labelTypeAnon').style.borderColor = (shareType === 'anonymous_qr') ? '#6366f1' : 'var(--ff-border)';

            wrapEmail.style.display = (shareType === 'private_user') ? 'block' : 'none';
        }

        document.getElementById('editShareForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('editShareId').value;
            const btn = document.getElementById('btnSaveShareSettings');
            btn.disabled = true;
            btn.innerHTML = 'Saving...';

            const shareType = document.querySelector('input[name="share_type"]:checked').value;
            const maxDownloads = document.getElementById('editMaxDownloads').value;
            const resetCount = document.getElementById('editResetCount').checked ? 1 : 0;
            const recipientEmail = document.getElementById('editRecipientEmail').value;
            const expiryPreset = document.getElementById('editExpiryPreset').value;
            const passcode = document.getElementById('editPasscode').value;
            const clearPasscode = document.getElementById('editClearPasscode').checked ? 1 : 0;
            const isAnonymous = (shareType === 'anonymous_qr') ? 1 : 0;

            const payload = {
                _token: '{{ csrf_token() }}',
                share_type: shareType,
                max_downloads: maxDownloads,
                reset_download_count: resetCount,
                is_anonymous: isAnonymous,
                recipient_email: recipientEmail,
                clear_password: clearPasscode
            };

            if (passcode) payload.password = passcode;
            if (expiryPreset !== 'keep') payload.expires_in_minutes = parseInt(expiryPreset);

            $.ajax({
                url: `/panel/share/update/${id}`,
                type: 'POST',
                data: payload,
                success: function(res) {
                    btn.disabled = false;
                    btn.innerHTML = 'Save Settings';
                    closeEditModal('editShareModalOverlay');
                    if (window.ff && window.ff.toast) window.ff.toast(res.message || 'Share updated!', 'success', 2500);
                    setTimeout(() => location.reload(), 600);
                },
                error: function(err) {
                    btn.disabled = false;
                    btn.innerHTML = 'Save Settings';
                    const msg = err.responseJSON?.message || 'Failed to update share settings';
                    if (window.ff && window.ff.toast) window.ff.toast(msg, 'error', 4000);
                }
            });
        });

        // ----------------------------------------------------
        // 2. LINK SHARE SETTINGS MODAL
        // ----------------------------------------------------
        function openEditLinkShareModal(share) {
            document.getElementById('editLinkShareId').value = share.id;
            document.getElementById('modalLinkTitle').textContent = share.title;
            document.getElementById('editLinkMaxClicks').value = share.max_clicks || '';
            document.getElementById('currentLinkClicksText').textContent = `Current Clicks: ${share.click_count}`;
            document.getElementById('editLinkResetClicks').checked = false;
            document.getElementById('editLinkPasscode').value = '';
            document.getElementById('editLinkClearPasscode').checked = false;
            document.getElementById('editLinkIsAnonymous').checked = share.is_anonymous;
            document.getElementById('editLinkExpiryPreset').value = 'keep';

            document.getElementById('editLinkShareModalOverlay').style.display = 'flex';
            if (window.lucide) window.lucide.createIcons();
        }

        document.getElementById('editLinkShareForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('editLinkShareId').value;
            const btn = document.getElementById('btnSaveLinkShareSettings');
            btn.disabled = true;
            btn.innerHTML = 'Saving...';

            const maxClicks = document.getElementById('editLinkMaxClicks').value;
            const resetClicks = document.getElementById('editLinkResetClicks').checked ? 1 : 0;
            const expiryPreset = document.getElementById('editLinkExpiryPreset').value;
            const passcode = document.getElementById('editLinkPasscode').value;
            const clearPasscode = document.getElementById('editLinkClearPasscode').checked ? 1 : 0;
            const isAnonymous = document.getElementById('editLinkIsAnonymous').checked ? 1 : 0;

            const payload = {
                _token: '{{ csrf_token() }}',
                max_clicks: maxClicks,
                reset_click_count: resetClicks,
                is_anonymous: isAnonymous,
                clear_password: clearPasscode
            };

            if (passcode) payload.password = passcode;
            if (expiryPreset !== 'keep') payload.expires_in_minutes = parseInt(expiryPreset);

            $.ajax({
                url: `/panel/share/link/update/${encodeURIComponent(id)}`,
                type: 'POST',
                data: payload,
                success: function(res) {
                    btn.disabled = false;
                    btn.innerHTML = 'Save Link Settings';
                    closeEditModal('editLinkShareModalOverlay');
                    if (window.ff && window.ff.toast) window.ff.toast(res.message || 'Bookmark share updated!', 'success', 2500);
                    setTimeout(() => location.reload(), 600);
                },
                error: function(err) {
                    btn.disabled = false;
                    btn.innerHTML = 'Save Link Settings';
                    const msg = err.responseJSON?.message || 'Failed to update link settings';
                    if (window.ff && window.ff.toast) window.ff.toast(msg, 'error', 4000);
                }
            });
        });

        // ----------------------------------------------------
        // 3. PASSWORD SECRET SHARE SETTINGS MODAL
        // ----------------------------------------------------
        function openEditPasswordShareModal(share) {
            document.getElementById('editPasswordShareId').value = share.id;
            document.getElementById('modalPasswordTitle').textContent = share.title;
            document.getElementById('editPasswordPasscode').value = '';
            document.getElementById('editPasswordClearPasscode').checked = false;
            document.getElementById('editPasswordExpiryPreset').value = 'keep';

            document.getElementById('editPasswordShareModalOverlay').style.display = 'flex';
            if (window.lucide) window.lucide.createIcons();
        }

        document.getElementById('editPasswordShareForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('editPasswordShareId').value;
            const btn = document.getElementById('btnSavePasswordShareSettings');
            btn.disabled = true;
            btn.innerHTML = 'Saving...';

            const expiryPreset = document.getElementById('editPasswordExpiryPreset').value;
            const passcode = document.getElementById('editPasswordPasscode').value;
            const clearPasscode = document.getElementById('editPasswordClearPasscode').checked ? 1 : 0;

            const payload = {
                _token: '{{ csrf_token() }}',
                clear_password: clearPasscode
            };

            if (passcode) payload.password = passcode;
            if (expiryPreset !== 'keep') payload.expires_in_minutes = parseInt(expiryPreset);

            $.ajax({
                url: `/panel/share/password/update/${encodeURIComponent(id)}`,
                type: 'POST',
                data: payload,
                success: function(res) {
                    btn.disabled = false;
                    btn.innerHTML = 'Save Secret Settings';
                    closeEditModal('editPasswordShareModalOverlay');
                    if (window.ff && window.ff.toast) window.ff.toast(res.message || 'Secret share updated!', 'success', 2500);
                    setTimeout(() => location.reload(), 600);
                },
                error: function(err) {
                    btn.disabled = false;
                    btn.innerHTML = 'Save Secret Settings';
                    const msg = err.responseJSON?.message || 'Failed to update secret settings';
                    if (window.ff && window.ff.toast) window.ff.toast(msg, 'error', 4000);
                }
            });
        });

        // ----------------------------------------------------
        // 4. CATEGORY BUNDLE SHARE SETTINGS MODAL
        // ----------------------------------------------------
        function openEditCategoryShareModal(share) {
            document.getElementById('editCategoryShareId').value = share.id;
            document.getElementById('modalCategoryTitle').textContent = share.title;
            document.getElementById('editCatMaxViews').value = share.max_views || '';
            document.getElementById('currentCatViewsText').textContent = `Current Views: ${share.view_count}`;
            document.getElementById('editCatResetViews').checked = false;
            document.getElementById('editCatIncludeFiles').checked = share.include_files;
            document.getElementById('editCatIncludeLinks').checked = share.include_links;
            document.getElementById('editCatPasscode').value = '';
            document.getElementById('editCatClearPasscode').checked = false;
            document.getElementById('editCatExpiryPreset').value = 'keep';

            document.getElementById('editCategoryShareModalOverlay').style.display = 'flex';
            if (window.lucide) window.lucide.createIcons();
        }

        document.getElementById('editCategoryShareForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('editCategoryShareId').value;
            const btn = document.getElementById('btnSaveCategoryShareSettings');
            btn.disabled = true;
            btn.innerHTML = 'Saving...';

            const maxViews = document.getElementById('editCatMaxViews').value;
            const resetViews = document.getElementById('editCatResetViews').checked ? 1 : 0;
            const incFiles = document.getElementById('editCatIncludeFiles').checked ? 1 : 0;
            const incLinks = document.getElementById('editCatIncludeLinks').checked ? 1 : 0;
            const expiryPreset = document.getElementById('editCatExpiryPreset').value;
            const passcode = document.getElementById('editCatPasscode').value;
            const clearPasscode = document.getElementById('editCatClearPasscode').checked ? 1 : 0;

            const payload = {
                _token: '{{ csrf_token() }}',
                max_views: maxViews,
                reset_view_count: resetViews,
                include_files: incFiles,
                include_links: incLinks,
                clear_password: clearPasscode
            };

            if (passcode) payload.password = passcode;
            if (expiryPreset !== 'keep') payload.expires_in_minutes = parseInt(expiryPreset);

            $.ajax({
                url: `/panel/share/category/update/${encodeURIComponent(id)}`,
                type: 'POST',
                data: payload,
                success: function(res) {
                    btn.disabled = false;
                    btn.innerHTML = 'Save Bundle Settings';
                    closeEditModal('editCategoryShareModalOverlay');
                    if (window.ff && window.ff.toast) window.ff.toast(res.message || 'Category bundle updated!', 'success', 2500);
                    setTimeout(() => location.reload(), 600);
                },
                error: function(err) {
                    btn.disabled = false;
                    btn.innerHTML = 'Save Bundle Settings';
                    const msg = err.responseJSON?.message || 'Failed to update category bundle settings';
                    if (window.ff && window.ff.toast) window.ff.toast(msg, 'error', 4000);
                }
            });
        });

        // ----------------------------------------------------
        // REVOKE HANDLERS
        // ----------------------------------------------------
        async function revokeShareAjax(id, btnEl) {
            const confirmed = await window.ff.confirm({
                title: '🗑️ Revoke Share Access',
                message: 'Are you sure you want to revoke access for this share? Anyone with the link will immediately lose access.',
                confirmText: 'Revoke Access',
                isDanger: true
            });

            if (!confirmed) return;

            const row = btnEl ? btnEl.closest('.ff-list-row') : null;
            if (btnEl) btnEl.disabled = true;

            $.ajax({
                url: `/panel/share/revoke/${id}`,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                headers: { 'Accept': 'application/json' },
                success: function(res) {
                    if (window.ff && window.ff.toast) window.ff.toast(res.info || 'Share access revoked.', 'success', 2500);
                    if (row) {
                        row.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(20px)';
                        setTimeout(() => row.remove(), 250);
                    } else {
                        setTimeout(() => location.reload(), 400);
                    }
                },
                error: function(err) {
                    if (btnEl) btnEl.disabled = false;
                    const msg = err.responseJSON?.info || 'Failed to revoke share.';
                    if (window.ff && window.ff.toast) window.ff.toast(msg, 'error', 4000);
                }
            });
        }

        async function revokeUniversalShare(encryptedId, btnEl, type) {
            const label = type === 'link' ? 'Bookmark link' : (type === 'password' ? 'Password secret' : 'Category bundle');
            const confirmed = await window.ff.confirm({
                title: '🗑️ Revoke Share Access',
                message: `Are you sure you want to revoke access to this ${label}? Anyone with the link will immediately lose access.`,
                confirmText: 'Revoke Access',
                isDanger: true
            });

            if (!confirmed) return;

            const row = btnEl ? btnEl.closest('.ff-list-row') : null;
            if (btnEl) btnEl.disabled = true;

            const routeUrl = `/panel/share/${type}/revoke/${encodeURIComponent(encryptedId)}`;

            $.ajax({
                url: routeUrl,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                headers: { 'Accept': 'application/json' },
                success: function(res) {
                    if (window.ff && window.ff.toast) window.ff.toast(res.message || `${label} share access revoked.`, 'success', 2500);
                    if (row) {
                        row.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(20px)';
                        setTimeout(() => row.remove(), 250);
                    } else {
                        setTimeout(() => location.reload(), 400);
                    }
                },
                error: function(err) {
                    if (btnEl) btnEl.disabled = false;
                    const msg = err.responseJSON?.message || `Failed to revoke ${label} share.`;
                    if (window.ff && window.ff.toast) window.ff.toast(msg, 'error', 4000);
                }
            });
        }

        if (window.ff) window.ff.icons();
    </script>
@endsection
