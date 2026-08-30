@extends('layout.backend')
@push('title', 'Trash Can — ' . Str::ucfirst($type))

@section('content')
    @php
        $hasItems =
            ($type == 'files' && isset($files) && $files->count() > 0) ||
            ($type == 'links' && isset($links) && $links->count() > 0) ||
            ($type == 'passwords' && isset($passwords) && $passwords->count() > 0);
    @endphp

    <h1 class="ff-h1">Trash Can</h1>
    <p class="ff-sub" style="margin-bottom:20px;">Restore anything you deleted by mistake, or clear it out for good.</p>

    <div class="ff-row-between" style="margin-bottom:20px; gap:12px; align-items:center;">
        <div class="ff-viewtoggle" style="flex-shrink: 0;">
            <a href="{{ route('panel.trashview', 'files') }}"
                class="ff-viewtoggle-btn is-text {{ $type === 'files' ? 'is-active' : '' }}">Files</a>
            <a href="{{ route('panel.trashview', 'links') }}"
                class="ff-viewtoggle-btn is-text {{ $type === 'links' ? 'is-active' : '' }}">Links</a>
            <a href="{{ route('panel.trashview', 'passwords') }}"
                class="ff-viewtoggle-btn is-text {{ $type === 'passwords' ? 'is-active' : '' }}">Passwords</a>
        </div>

        <div class="ff-row" style="gap:10px; flex: 1 1 auto; max-width: 100%; justify-content: flex-end; flex-wrap: wrap;">
            <label class="ff-input-icon" style="min-width:0; flex: 1 1 160px; max-width: 260px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="search" 
                    id="trashSearch" 
                    name="trash_search_query"
                    placeholder="Search {{ $type }}..." 
                    autocomplete="new-password"
                    autocorrect="off"
                    autocapitalize="off"
                    spellcheck="false"
                    data-lpignore="true"
                    data-form-type="other"
                    data-dashlane-ignore="true"
                    readonly
                    onfocus="this.removeAttribute('readonly');">
            </label>

            @if ($hasItems)
                <button type="button" id="emptyTrashBtn" class="ff-btn ff-btn-danger" style="white-space: nowrap; flex-shrink: 0;">Empty Trash</button>
            @endif
        </div>
    </div>

    <div class="ff-list" id="trashList">
        @if ($type == 'links')
            @include('panel.ajax.trash_links')
        @elseif ($type == 'passwords')
            @include('panel.ajax.trash_passwords')
        @else
            @include('panel.ajax.trash_files')
        @endif
    </div>
@endsection

@section('push-script')
    <script>
        window.ffTrashToast = function(message, type) {
            var colors = {
                success: 'oklch(55% 0.15 150)',
                warning: 'oklch(65% 0.16 85)',
                error: 'var(--ff-danger)'
            };
            var el = document.createElement('div');
            el.className = 'ff-toast';
            el.style.background = colors[type] || colors.error;
            el.textContent = message;
            document.body.appendChild(el);
            setTimeout(function() {
                el.style.opacity = '0';
                setTimeout(function() {
                    el.remove();
                }, 300);
            }, type === 'warning' ? 5000 : 3500);
        };

        function ffTrashCheckEmpty() {
            var remaining = document.querySelectorAll('.trash-file-card, .trash-link-card, .trash-password-card').length;
            if (remaining > 0) return;
            var btn = document.getElementById('emptyTrashBtn');
            if (btn) btn.remove();
            document.getElementById('trashList').innerHTML = `
                <div class="ff-empty">
                    <span class="ff-empty-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                        </svg>
                    </span>
                    <span class="ff-empty-text">Trash is empty</span>
                </div>`;
        }

        $(document).ready(function() {
            // -------------------------------------------------- filter this page
            $('#trashSearch').on('input', function() {
                var term = $(this).val().trim().toLowerCase();
                $('.trash-file-card, .trash-link-card, .trash-password-card').each(function() {
                    var name = ($(this).data('name') || '').toString().toLowerCase();
                    $(this).toggle(!term || name.indexOf(term) !== -1);
                });
            });

            // --------------------------------------------------------- empty all
            $('#emptyTrashBtn').on('click', async function(e) {
                e.preventDefault();

                var itemCount = type === 'files' ? $('.trash-file-card').length :
                    (type === 'links' ? $('.trash-link-card').length : $('.trash-password-card').length);
                var emptyUrl = type === 'files' ?
                    '{{ route('panel.emptyTrash') }}' :
                    (type === 'links' ? '{{ route('panel.emptyLinksTrash') }}' : '{{ route('panel.emptyPasswordsTrash') }}');

                const confirmed = await window.ff.confirm({
                    title: '🗑️ Empty Trash',
                    message: `Permanently delete all ${itemCount} ${type} in the trash? This action cannot be undone.`,
                    confirmText: 'Permanently Delete',
                    isDanger: true
                });

                if (!confirmed) return;

                $.ajax({
                    url: emptyUrl,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        type: type
                    },
                    success: function(response) {
                        if (response.ok === 1) {
                            $('.trash-file-card, .trash-link-card, .trash-password-card').remove();
                            ffTrashCheckEmpty();
                            window.ffTrashToast(
                                `${response.deleted_count} ${type} permanently deleted`, 'success');
                        } else if (response.deleted_count > 0) {
                            window.ffTrashToast(
                                `${response.deleted_count} deleted, ${response.failed_count || 0} failed. Reloading…`,
                                'warning');
                            setTimeout(function() {
                                window.location.reload();
                            }, 2000);
                        } else {
                            window.ffTrashToast(response.error || 'Failed to empty trash', 'error');
                        }
                    },
                    error: function() {
                        window.ffTrashToast('Failed to empty trash', 'error');
                    }
                });
            });

            window.ff.icons();
        });
    </script>
@endsection
