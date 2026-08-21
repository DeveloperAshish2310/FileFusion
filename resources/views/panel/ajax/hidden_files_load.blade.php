@php
    $fileTypeLabel = function ($file) {
        $mime = (string) ($file->type ?? '');
        if (str_starts_with($mime, 'image/')) {
            return 'Image';
        }
        if (str_starts_with($mime, 'video/')) {
            return 'Video';
        }
        if (str_starts_with($mime, 'audio/')) {
            return 'Audio';
        }
        $ext = strtolower($file->extension ?? '');
        if (in_array($ext, ['txt', 'md', 'json', 'log'])) {
            return 'Text';
        }
        if (in_array($ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'rtf'])) {
            return 'Documents';
        }
        return $ext ? strtoupper($ext) : 'Other';
    };
@endphp

<div data-ff-view-target="files" data-ff-view="grid">

    {{-- ================================ Grid view ================================ --}}
    <div class="ff-view-grid ff-grid-files">
        @forelse ($files as $file)
            @php
                $eid = encrypt($file->id);
                $icon = getFileIcon($file->extension);
            @endphp

            <div class="ff-tile">
                <span class="ff-tile-select">
                    <input type="checkbox" class="ff-checkbox hidden-file-checkbox" data-id="{{ $eid }}">
                </span>

                <div class="ff-quick-row">
                    <button type="button" class="ff-quick-btn previewbtn" data-file="{{ $eid }}" title="Quick Preview">
                        <i data-lucide="eye" class="w-[15px] h-[15px]"></i>
                    </button>
                    <a href="{{ route('panel.downloadFile', $eid) }}" class="ff-quick-btn" title="Download">
                        <i data-lucide="download" class="w-[15px] h-[15px]"></i>
                    </a>
                    <button type="button" class="ff-quick-btn toggle-hide-btn" data-file="{{ $eid }}" title="Unhide File">
                        <i data-lucide="eye-off" class="w-[15px] h-[15px]"></i>
                    </button>
                    <button type="button" class="ff-quick-btn is-danger deletebtn" data-file="{{ $eid }}" title="Delete">
                        <i data-lucide="trash-2" class="w-[15px] h-[15px]"></i>
                    </button>
                </div>

                <div class="ff-row-between" style="margin-top:22px; align-items:flex-start; flex-wrap:nowrap;">
                    <span class="ff-tile-icon">
                        <i data-lucide="{{ $icon['icon'] }}" class="w-5 h-5"></i>
                    </span>

                    <div class="ff-dropdown-wrap" data-ff-menu>
                        <button type="button" class="ff-menu-btn" data-ff-menu-trigger aria-label="File actions">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                        <div class="ff-dropdown" data-ff-menu-panel hidden>
                            <button type="button" class="ff-dropdown-item previewbtn" data-file="{{ $eid }}">
                                <i data-lucide="eye" class="w-[15px] h-[15px]"></i> Quick Preview
                            </button>
                            <a href="{{ route('panel.editFile', $eid) }}" class="ff-dropdown-item">
                                <i data-lucide="file-text" class="w-[15px] h-[15px]"></i> Edit / View
                            </a>
                            <button type="button" class="ff-dropdown-item"
                                onclick="renameFile('{{ $eid }}', @js($file->name))">
                                <i data-lucide="pencil" class="w-[15px] h-[15px]"></i> Rename
                            </button>
                            <button type="button" class="ff-dropdown-item toggle-hide-btn" data-file="{{ $eid }}" onclick="toggleHideFile('{{ $eid }}')">
                                <i data-lucide="eye-off" class="w-[15px] h-[15px]"></i> Unhide
                            </button>

                            <div class="ff-dropdown-divider"></div>

                            <a href="{{ route('panel.downloadFile', $eid) }}" class="ff-dropdown-item">
                                <i data-lucide="download" class="w-[15px] h-[15px]"></i> Download
                            </a>
                            <button type="button" class="ff-dropdown-item is-danger deletebtn" data-file="{{ $eid }}">
                                <i data-lucide="trash-2" class="w-[15px] h-[15px]"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>

                <div class="ff-tile-name" style="margin-top:14px;" title="{{ $file->name }}">{{ $file->name }}</div>
                <div class="ff-tile-meta">
                    <span>{{ BytetoSize($file->size) }}</span>
                    <span class="ff-dot"></span>
                    <span>{{ \Carbon\Carbon::parse($file->updated_at)->format('M d, Y, h:i A') }}</span>
                </div>
                <div style="margin-top:12px; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                    <span class="ff-badge-type">{{ $fileTypeLabel($file) }}</span>
                    <span class="ff-badge-hidden">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.7 18.7 0 0 1 5.06-5.94" />
                            <line x1="1" y1="1" x2="23" y2="23" />
                        </svg>
                        Hidden
                    </span>
                </div>
            </div>
        @empty
            <div class="ff-empty" style="grid-column:1/-1;">
                <span class="ff-empty-icon" style="width:64px; height:64px; border-radius:16px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.7 18.7 0 0 1 5.06-5.94" />
                        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                        <line x1="1" y1="1" x2="23" y2="23" />
                    </svg>
                </span>
                <div class="ff-section-title" style="font-size:17px;">No hidden files</div>
                <p class="ff-empty-text" style="max-width:380px; margin:0;">
                    Files you mark as hidden will show up here, behind your vault passcode.
                </p>
                <a href="{{ route('panel.filelist') }}" class="ff-btn" style="margin-top:6px;">Back to Files</a>
            </div>
        @endforelse
    </div>

    {{-- ================================ List view ================================ --}}
    @if ($files->count())
        <div class="ff-view-list ff-list">
            <div class="ff-list-head ff-hide-mobile">
                <span class="ff-list-col" style="width:20px;"></span>
                <span class="ff-list-col ff-grow">Name</span>
                <span class="ff-list-col" style="width:110px;">Size</span>
                <span class="ff-list-col" style="width:150px;">Type</span>
                <span class="ff-list-col" style="width:180px;">Modified</span>
                <span class="ff-list-col" style="width:40px;"></span>
            </div>

            @foreach ($files as $file)
                @php
                    $eid = encrypt($file->id);
                    $icon = getFileIcon($file->extension);
                @endphp

                <div class="ff-list-row">
                    <input type="checkbox" class="ff-checkbox hidden-file-checkbox" data-id="{{ $eid }}">

                    <div class="ff-list-main">
                        <span class="ff-tile-icon ff-tile-icon-sm">
                            <i data-lucide="{{ $icon['icon'] }}" class="w-4 h-4"></i>
                        </span>
                        <div class="ff-min0">
                            <span class="ff-list-title">{{ $file->name }}</span>
                            <div class="ff-list-subtitle ff-show-mobile">
                                {{ BytetoSize($file->size) }} · {{ $fileTypeLabel($file) }}
                            </div>
                        </div>
                    </div>

                    <span class="ff-list-cell ff-hide-mobile" style="width:110px;">{{ BytetoSize($file->size) }}</span>
                    <span class="ff-list-cell ff-hide-mobile" style="width:150px;">
                        <span class="ff-badge-type">{{ $fileTypeLabel($file) }}</span>
                        <span class="ff-badge-hidden" style="margin-left:4px;">Hidden</span>
                    </span>
                    <span class="ff-list-cell ff-hide-mobile"
                        style="width:180px;">{{ \Carbon\Carbon::parse($file->updated_at)->format('M d, Y, h:i A') }}</span>

                    <div class="ff-dropdown-wrap" data-ff-menu style="width:40px; text-align:right;">
                        <button type="button" class="ff-menu-btn" data-ff-menu-trigger aria-label="Actions">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                        <div class="ff-dropdown" data-ff-menu-panel hidden>
                            <button type="button" class="ff-dropdown-item previewbtn" data-file="{{ $eid }}">
                                <i data-lucide="eye" class="w-[15px] h-[15px]"></i> Quick Preview
                            </button>
                            <a href="{{ route('panel.editFile', $eid) }}" class="ff-dropdown-item">
                                <i data-lucide="file-text" class="w-[15px] h-[15px]"></i> Edit / View
                            </a>
                            <button type="button" class="ff-dropdown-item toggle-hide-btn" data-file="{{ $eid }}" onclick="toggleHideFile('{{ $eid }}')">
                                <i data-lucide="eye-off" class="w-[15px] h-[15px]"></i> Unhide
                            </button>
                            <div class="ff-dropdown-divider"></div>
                            <a href="{{ route('panel.downloadFile', $eid) }}" class="ff-dropdown-item">
                                <i data-lucide="download" class="w-[15px] h-[15px]"></i> Download
                            </a>
                            <button type="button" class="ff-dropdown-item is-danger deletebtn" data-file="{{ $eid }}">
                                <i data-lucide="trash-2" class="w-[15px] h-[15px]"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($files->hasPages())
        <div class="ff-pagination">
            {{ $files->links('pagination::tailwind') }}
        </div>
    @endif
</div>
