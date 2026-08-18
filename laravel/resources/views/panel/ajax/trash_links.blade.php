@if (isset($links) && $links->count() > 0)
    @foreach ($links as $link)
        <div class="ff-list-row trash-link-card" data-link-id="{{ encrypt($link->id) }}"
            data-name="{{ $link->title }}">

            @php
                $thumbSrc = null;
                if ($link->thumbnail) {
                    $thumbSrc = \Illuminate\Support\Str::startsWith($link->thumbnail, ['http://', 'https://', '//', 'data:'])
                        ? $link->thumbnail
                        : asset($link->thumbnail);
                }
            @endphp

            <div class="ff-list-main">
                <span class="ff-thumb-sm" style="opacity:.8;">
                    @if ($thumbSrc)
                        <img src="{{ $thumbSrc }}" alt="{{ $link->title }}" loading="lazy">
                    @else
                        <span class="ff-thumb-sm-label">img</span>
                    @endif
                </span>
                <div class="ff-min0">
                    <div class="ff-row" style="gap:8px;">
                        <span class="ff-list-title" title="{{ $link->title }}">{{ $link->title }}</span>
                        @if ($link->is_starred)
                            <span class="ff-badge-hidden">Starred</span>
                        @endif
                    </div>
                    <div class="ff-list-subtitle" title="{{ $link->url }}">
                        {{ Str::limit($link->url, 48) }} · deleted {{ $link->deleted_at->diffForHumans() }}
                    </div>
                </div>
            </div>

            <button type="button" class="ff-btn ff-btn-sm restore-link">Restore</button>
            <button type="button" class="ff-menu-btn delete-link-permanently" title="Delete permanently">
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
        <span class="ff-empty-text">Trash is empty — deleted links will appear here.</span>
    </div>
@endif

<script>
    $(document).on('click', '.restore-link', function(e) {
        e.preventDefault();
        var card = $(this).closest('.trash-link-card');
        var linkId = card.data('link-id');

        $.ajax({
            url: "{{ route('panel.restoreLink', ':id') }}".replace(':id', linkId),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.ok === 1) {
                    card.fadeOut(250, function() {
                        $(this).remove();
                        ffTrashCheckEmpty();
                    });
                    window.ffTrashToast(response.info || 'Link restored', 'success');
                } else {
                    window.ffTrashToast(response.info || 'Failed to restore link', 'error');
                }
            },
            error: function() {
                window.ffTrashToast('Failed to restore link', 'error');
            }
        });
    });

    $(document).on('click', '.delete-link-permanently', async function(e) {
        e.preventDefault();
        const confirmed = await window.ff.confirm({
            title: '🗑️ Permanently Delete Link',
            message: 'Permanently remove this link? This action cannot be undone.',
            confirmText: 'Delete Permanently',
            isDanger: true
        });
        if (!confirmed) return;

        var card = $(this).closest('.trash-link-card');
        var linkId = card.data('link-id');

        $.ajax({
            url: "{{ route('panel.permanentDeleteLink', ':id') }}".replace(':id', linkId),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.ok === 1) {
                    card.fadeOut(250, function() {
                        $(this).remove();
                        ffTrashCheckEmpty();
                    });
                    window.ffTrashToast(response.info || 'Link permanently deleted', 'success');
                } else {
                    window.ffTrashToast(response.info || 'Failed to delete link', 'error');
                }
            },
            error: function() {
                window.ffTrashToast('Failed to delete link', 'error');
            }
        });
    });
</script>
