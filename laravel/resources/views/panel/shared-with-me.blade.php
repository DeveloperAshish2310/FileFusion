@extends('layout.backend')
@push('title', 'Shared Files Center')

@section('content')
    <div class="ff-row-between" style="align-items:flex-start; margin-bottom:22px;">
        <div>
            <h1 class="ff-h1">Shared Files Center</h1>
            <p class="ff-sub" style="margin-bottom:0;">Manage files shared with your account and files you have shared with others.</p>
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
            <input type="search" name="q" placeholder="Search shared files by name..." value="{{ $searchTerm ?? '' }}" autocomplete="off">
        </form>
    </div>

    <!-- Navigation Tabs -->
    <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid var(--ff-border); padding-bottom: 10px;">
        <button type="button" id="tab-btn-with-me" class="ff-btn is-primary" onclick="switchSharedTab('with-me')" style="font-size: 13.5px; font-weight: 700; border-radius: 8px;">
            📥 Shared With Me ({{ count($sharesWithMe) }})
        </button>
        <button type="button" id="tab-btn-by-me" class="ff-btn" onclick="switchSharedTab('by-me')" style="font-size: 13.5px; font-weight: 700; border-radius: 8px; background: var(--ff-bg2); color: var(--ff-text);">
            📤 Shared By Me ({{ count($sharesByMe) }})
        </button>
    </div>

    <!-- SHARED WITH ME SECTION -->
    <div id="section-shared-with-me">
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
                            <div class="ff-min0" style="overflow: hidden;">
                                <span class="ff-list-title" style="display: block; font-weight: 600; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">{{ $file->name }}</span>
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

                        <div style="width: 130px; display: flex; justify-content: flex-end; flex-shrink: 0;">
                            <a href="{{ route('panel.downloadFile', ['fileid' => encrypt($file->id)]) }}" class="ff-btn is-primary" style="padding: 6px 14px; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; border-radius: 8px;" title="Download File">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i> Download
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

    <!-- SHARED BY ME SECTION -->
    <div id="section-shared-by-me" style="display: none;">
        <div class="ff-list" style="box-sizing: border-box; width: 100%;">
            <div class="ff-list-head ff-hide-mobile" style="display: flex; align-items: center; padding: 12px 16px;">
                <span class="ff-list-col ff-grow" style="flex: 1;">File Name</span>
                <span class="ff-list-col" style="width: 140px;">Share Type</span>
                <span class="ff-list-col" style="width: 180px;">Recipient</span>
                <span class="ff-list-col" style="width: 140px;">Created Date</span>
                <span class="ff-list-col" style="width: 130px; text-align: right;">Action</span>
            </div>

            @forelse ($sharesByMe as $share)
                @php
                    $file = $share->file;
                    $recipient = $share->recipient;
                @endphp
                @if ($file)
                    <div class="ff-list-row" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; box-sizing: border-box;">
                        <div class="ff-list-main" style="flex: 1; min-width: 0; display: flex; align-items: center; gap: 12px;">
                            <span class="ff-tile-icon ff-tile-icon-sm" style="flex-shrink: 0;">
                                <i data-lucide="file" class="w-[17px] h-[17px]"></i>
                            </span>
                            <div class="ff-min0" style="overflow: hidden;">
                                <span class="ff-list-title" style="display: block; font-weight: 600; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">{{ $file->name }}</span>
                                <span class="ff-list-subtitle ff-hide-desktop" style="font-size: 11.5px; color: var(--ff-muted);">{{ BytetoSize($file->size) }} · Type: {{ $share->share_type }}</span>
                            </div>
                        </div>

                        <span class="ff-list-cell ff-hide-mobile" style="width: 140px;">
                            <span class="ff-badge" style="background: rgba(99, 102, 241, 0.15); color: #6366f1; padding: 3px 8px; font-size: 11px; font-weight: 700; border-radius: 6px; text-transform: uppercase;">
                                {{ str_replace('_', ' ', $share->share_type) }}
                            </span>
                        </span>

                        <span class="ff-list-cell ff-hide-mobile" style="width: 180px; color: var(--ff-text);">
                            {{ $recipient->name ?? ($share->recipient_email ?: 'Public Link') }}
                        </span>

                        <span class="ff-list-cell ff-hide-mobile" style="width: 140px; color: var(--ff-muted);">
                            {{ $share->created_at->format('M d, Y') }}
                        </span>

                        <div style="width: 130px; display: flex; justify-content: flex-end; gap: 6px; flex-shrink: 0;">
                            <a href="{{ route('panel.downloadFile', ['fileid' => encrypt($file->id)]) }}" class="ff-btn" style="padding: 6px 10px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; text-decoration: none;" title="Download File">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i> Get
                            </a>
                            <form action="{{ route('panel.share.revoke', $share->id) }}" method="POST" onsubmit="return confirm('Revoke access for this share?');" style="display: inline;">
                                @csrf
                                <button type="submit" class="ff-btn" style="padding: 6px 10px; font-size: 12px; background: rgba(239, 68, 68, 0.12); color: #ef4444; border-color: rgba(239, 68, 68, 0.3);" title="Revoke Share">
                                    Revoke
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @empty
                <div class="ff-empty">
                    <span class="ff-empty-icon" style="width:64px; height:64px; border-radius:16px;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 12v7a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7" />
                            <polyline points="16 6 12 2 8 6" />
                            <line x1="12" y1="2" x2="12" y2="15" />
                        </svg>
                    </span>
                    <div class="ff-section-title" style="font-size:17px;">No files shared by you</div>
                    <p class="ff-empty-text" style="max-width:380px; margin:0;">
                        Share files with friends or team members using private user email or public share links.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@section('push-script')
    <script>
        function switchSharedTab(tab) {
            const secWith = document.getElementById('section-shared-with-me');
            const secBy = document.getElementById('section-shared-by-me');
            const btnWith = document.getElementById('tab-btn-with-me');
            const btnBy = document.getElementById('tab-btn-by-me');

            if (tab === 'with-me') {
                secWith.style.display = 'block';
                secBy.style.display = 'none';

                btnWith.classList.add('is-primary');
                btnWith.style.background = '#6366f1';
                btnWith.style.color = '#ffffff';

                btnBy.classList.remove('is-primary');
                btnBy.style.background = 'var(--ff-bg2)';
                btnBy.style.color = 'var(--ff-text)';
            } else {
                secWith.style.display = 'none';
                secBy.style.display = 'block';

                btnBy.classList.add('is-primary');
                btnBy.style.background = '#6366f1';
                btnBy.style.color = '#ffffff';

                btnWith.classList.remove('is-primary');
                btnWith.style.background = 'var(--ff-bg2)';
                btnWith.style.color = 'var(--ff-text)';
            }
        }

        if (window.ff) window.ff.icons();
    </script>
@endsection
