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
                $menuId = 'fmg-' . Str::random(10);
                $icon = getFileIcon($file->extension);
            @endphp

            <div class="ff-tile">
                <span class="ff-tile-select">
                    <input type="checkbox" class="ff-checkbox file-checkbox" data-id="{{ $eid }}">
                </span>

                <div class="ff-quick-row">
                    <button type="button" class="ff-quick-btn previewbtn" data-file="{{ $eid }}" title="Quick In-Browser Preview">
                        <i data-lucide="eye" class="w-[15px] h-[15px]"></i>
                    </button>
                    <a href="{{ route('panel.downloadFile', $eid) }}" class="ff-quick-btn" title="Download">
                        <i data-lucide="download" class="w-[15px] h-[15px]"></i>
                    </a>
                    <button type="button" class="ff-quick-btn sharebtn" data-file="{{ $eid }}" title="Share">
                        <i data-lucide="share" class="w-[15px] h-[15px]"></i>
                    </button>
                    <button type="button" class="ff-quick-btn is-danger deletebtn" data-file="{{ $eid }}"
                        title="Delete">
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
                        <div class="ff-dropdown" data-ff-menu-panel hidden id="{{ $menuId }}">
                            @include('panel.ajax.partials.file_menu', ['file' => $file, 'eid' => $eid])
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
                    @if ($file->is_shared || $file->isCurrentlyShared())
                        <span class="ff-badge" style="background: rgba(99, 102, 241, 0.15); color: #6366f1; border: 1px solid rgba(99, 102, 241, 0.3); padding: 2px 8px; font-size: 11px; font-weight: 700; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;" title="This file is currently shared">
                            <i data-lucide="share-2" class="w-3 h-3"></i> Shared
                        </span>
                    @endif
                </div>
            </div>
        @empty
            @include('panel.ajax.partials.files_empty')
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
                    <input type="checkbox" class="ff-checkbox file-checkbox" data-id="{{ $eid }}">

                    <div class="ff-list-main">
                        <span class="ff-tile-icon ff-tile-icon-sm">
                            <i data-lucide="{{ $icon['icon'] }}" class="w-[17px] h-[17px]"></i>
                        </span>
                        <div class="ff-min0">
                            <div class="ff-list-title" title="{{ $file->name }}">{{ $file->name }}</div>
                            <div class="ff-list-subtitle ff-show-mobile">
                                {{ BytetoSize($file->size) }} ·
                                {{ \Carbon\Carbon::parse($file->updated_at)->format('M d, Y') }}
                            </div>
                        </div>
                    </div>

                    <span class="ff-list-cell ff-hide-mobile" style="width:110px;">{{ BytetoSize($file->size) }}</span>
                    <span class="ff-list-cell ff-hide-mobile" style="width:150px; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                        <span class="ff-badge-type">{{ $fileTypeLabel($file) }}</span>
                        @if ($file->is_shared || $file->isCurrentlyShared())
                            <span class="ff-badge" style="background: rgba(99, 102, 241, 0.15); color: #6366f1; border: 1px solid rgba(99, 102, 241, 0.3); padding: 1px 6px; font-size: 10px; font-weight: 700; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;" title="This file is currently shared">
                                <i data-lucide="share-2" class="w-2.5 h-2.5"></i> Shared
                            </span>
                        @endif
                    </span>
                    <span class="ff-list-cell ff-hide-mobile" style="width:180px;">
                        {{ \Carbon\Carbon::parse($file->updated_at)->format('M d, Y, h:i A') }}
                    </span>


                    <div class="ff-dropdown-wrap" data-ff-menu>
                        <button type="button" class="ff-menu-btn" data-ff-menu-trigger aria-label="File actions">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                        <div class="ff-dropdown" data-ff-menu-panel hidden>
                            @include('panel.ajax.partials.file_menu', ['file' => $file, 'eid' => $eid])
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="ff-pagination">
        {{ $files->links('pagination::tailwind') }}
    </div>
</div>

<script>
    (function() {
        $('.sharebtn').off('click.ff').on('click.ff', function(e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: "{{ route('panel.sharemodal') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: $(this).data('file'),
                    type: 'file'
                },
                success: function(response) {
                    var modal = document.getElementById('sharemodal');
                    modal.hidden = false;
                    modal.innerHTML = response;
                    $(modal).find('script').each(function() {
                        $.globalEval(this.text || this.textContent || this.innerHTML || '');
                    });
                    window.ff.icons();
                },
                error: function() {
                    alert('Could not open the share dialog.');
                }
            });
        });

        $('.deletebtn').off('click.ff').on('click.ff', function(e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: "{{ route('panel.deletemodal') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    file: $(this).data('file')
                },
                success: function(response) {
                    var modal = document.getElementById('deletemodal');
                    modal.hidden = false;
                    modal.innerHTML = response;
                    $(modal).find('script').each(function() {
                        $.globalEval(this.text || this.textContent || this.innerHTML || '');
                    });
                    window.ff.icons();
                },
                error: function() {
                    alert('Could not open the delete dialog.');
                }
            });
        });

        window.ff.icons();
    })();

    async function renameFile(fileId, currentName) {
        var newName = await window.ff.prompt({
            title: '✏️ Rename File',
            message: 'Enter a new name for this file:',
            defaultValue: currentName,
            placeholder: 'example.png, notes.md...',
            confirmText: 'Rename'
        });

        if (newName && newName.trim() !== '' && newName !== currentName) {
            $.ajax({
                type: 'POST',
                url: "{{ route('panel.renameFile') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    fileId: fileId,
                    newName: newName.trim()
                },
                success: function(response) {
                    if (response.ok === 1) {
                        window.ff.toast('File renamed successfully.', 'success', 2000);
                        setTimeout(() => location.reload(), 700);
                    } else {
                        window.ff.toast(response.info || 'Could not rename file.', 'error', 4000);
                    }
                },
                error: function() {
                    window.ff.toast('Failed to rename file. Please try again.', 'error', 4000);
                }
            });
        }
    }

    async function toggleHideFile(fileId) {
        var confirmed = await window.ff.confirm({
            title: '👁️ Hide File',
            message: 'Move this file to your private hidden vault?',
            confirmText: 'Hide File',
            isDanger: false
        });

        if (!confirmed) return;

        $.ajax({
            type: 'POST',
            url: "{{ route('panel.toggleHide') }}",
            data: {
                _token: "{{ csrf_token() }}",
                fileId: fileId
            },
            success: function(response) {
                if (response.ok === 1) {
                    window.ff.toast('File moved to hidden vault.', 'success', 2000);
                    setTimeout(() => location.reload(), 700);
                } else {
                    window.ff.toast(response.info || 'Failed to update file visibility.', 'error', 4000);
                }
            },
            error: function() {
                window.ff.toast('Error updating file visibility. Please try again.', 'error', 4000);
            }
        });
    }
</script>
