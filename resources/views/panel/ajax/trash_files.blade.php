@if (isset($files) && $files->count() > 0)
    @foreach ($files as $file)
        @php $icon = getFileIcon($file->extension); @endphp
        <div class="ff-list-row trash-file-card" data-file-id="{{ encrypt($file->id) }}"
            data-name="{{ $file->name }}" data-file-type="{{ $file->type }}">

            <div class="ff-list-main">
                <span class="ff-tile-icon ff-tile-icon-sm" style="opacity:.65;">
                    <i data-lucide="{{ $icon['icon'] }}" class="w-[17px] h-[17px]"></i>
                </span>
                <div class="ff-min0">
                    <div class="ff-list-title" title="{{ $file->name }}">{{ $file->name }}</div>
                    <div class="ff-list-subtitle">
                        Deleted {{ $file->deleted_at->diffForHumans() }} · {{ BytetoSize($file->size) }}
                    </div>
                </div>
            </div>

            <button type="button" class="ff-btn ff-btn-sm restore-file">Restore</button>
            <button type="button" class="ff-menu-btn delete-permanently" title="Delete permanently">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                </svg>
            </button>
        </div>
    @endforeach
@else
    <div class="ff-empty">
        <span class="ff-empty-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6" />
                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
            </svg>
        </span>
        <span class="ff-empty-text">Trash is empty — deleted files will appear here.</span>
    </div>
@endif

<script>
    $(document).on('click', '.restore-file', function(e) {
        e.preventDefault();
        var card = $(this).closest('.trash-file-card');
        var fileId = card.data('file-id');

        $.ajax({
            url: "{{ route('panel.restoreFile', 'FILEID') }}".replace('FILEID', fileId),
            type: 'GET',
            success: function(response) {
                if (response.ok === 1) {
                    card.fadeOut(250, function() {
                        $(this).remove();
                        ffTrashCheckEmpty();
                    });
                    window.ffTrashToast(response.info || 'File restored', 'success');
                } else {
                    window.ffTrashToast(response.info || 'Failed to restore file', 'error');
                }
            },
            error: function() {
                window.ffTrashToast('Failed to restore file', 'error');
            }
        });
    });

    $(document).on('click', '.delete-permanently', async function(e) {
        e.preventDefault();
        const confirmed = await window.ff.confirm({
            title: '🗑️ Permanently Delete File',
            message: 'Permanently remove this file? This action cannot be undone.',
            confirmText: 'Delete Permanently',
            isDanger: true
        });
        if (!confirmed) return;

        var card = $(this).closest('.trash-file-card');
        var fileId = card.data('file-id');

        $.ajax({
            url: "{{ route('panel.permanentDeleteFile', 'FILEID') }}".replace('FILEID', fileId),
            type: 'GET',
            success: function(response) {
                if (response.ok === 1) {
                    card.fadeOut(250, function() {
                        $(this).remove();
                        ffTrashCheckEmpty();
                    });
                    window.ffTrashToast(response.info || 'File permanently deleted', 'success');
                } else {
                    window.ffTrashToast(response.info || 'Failed to delete file', 'error');
                }
            },
            error: function() {
                window.ffTrashToast('Failed to delete file', 'error');
            }
        });
    });
</script>
