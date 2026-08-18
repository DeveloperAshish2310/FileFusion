@if (isset($passwords) && $passwords->count() > 0)
    @foreach ($passwords as $pw)
        <div class="ff-list-row trash-password-card" data-password-id="{{ $pw->id }}"
            data-name="{{ $pw->title }}">

            <div class="ff-list-main">
                <span class="ff-tile-icon ff-tile-icon-sm" style="opacity:.65;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="10" rx="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                </span>
                <div class="ff-min0">
                    <div class="ff-row" style="gap:8px;">
                        <span class="ff-list-title" title="{{ $pw->title }}">{{ $pw->title }}</span>
                        @if ($pw->is_hidden)
                            <span class="ff-badge-hidden">Hidden</span>
                        @endif
                    </div>
                    <div class="ff-list-subtitle">
                        {{ $pw->username ?: 'No username' }} · deleted {{ $pw->deleted_at ? $pw->deleted_at->diffForHumans() : 'recently' }}
                    </div>
                </div>
            </div>

            <button type="button" class="ff-btn ff-btn-sm restore-password">Restore</button>
            <button type="button" class="ff-menu-btn delete-password-permanently" title="Delete permanently">
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
                <rect x="3" y="11" width="18" height="10" rx="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
            </svg>
        </span>
        <span class="ff-empty-text">Trash is empty — deleted credentials will appear here.</span>
    </div>
@endif

<script>
    $(document).on('click', '.restore-password', function(e) {
        e.preventDefault();
        var card = $(this).closest('.trash-password-card');
        var passwordId = card.data('password-id');

        $.ajax({
            url: "{{ route('panel.restorePassword', ':id') }}".replace(':id', passwordId),
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
                    window.ffTrashToast(response.info || 'Credential restored', 'success');
                } else {
                    window.ffTrashToast(response.info || 'Failed to restore credential', 'error');
                }
            },
            error: function() {
                window.ffTrashToast('Failed to restore credential', 'error');
            }
        });
    });

    $(document).on('click', '.delete-password-permanently', async function(e) {
        e.preventDefault();
        const confirmed = await window.ff.confirm({
            title: '🗑️ Permanently Delete Credential',
            message: 'Permanently remove this credential? This action cannot be undone.',
            confirmText: 'Delete Permanently',
            isDanger: true
        });
        if (!confirmed) return;

        var card = $(this).closest('.trash-password-card');
        var passwordId = card.data('password-id');

        $.ajax({
            url: "{{ route('panel.permanentDeletePassword', ':id') }}".replace(':id', passwordId),
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
                    window.ffTrashToast(response.info || 'Credential permanently deleted', 'success');
                } else {
                    window.ffTrashToast(response.info || 'Failed to delete credential', 'error');
                }
            },
            error: function() {
                window.ffTrashToast('Failed to delete credential', 'error');
            }
        });
    });
</script>
