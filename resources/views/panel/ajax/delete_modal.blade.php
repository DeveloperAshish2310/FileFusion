<div class="ff-modal-backdrop ff-delete-modal-root" style="position:fixed; inset:0; z-index:100050 !important;" onclick="if (event.target === this) hideeDeleteModel()">
    <div class="ff-modal ff-modal-sm">
        <div class="ff-row-between" style="flex-wrap:nowrap; align-items:flex-start;">
            <div class="ff-modal-title">Delete file</div>
            <button type="button" class="ff-modal-close" onclick="hideeDeleteModel()" aria-label="Close">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>

        <div style="text-align:center; padding:20px 0 4px;">
            <span class="ff-danger-icon" style="width:48px; height:48px; border-radius:13px; margin:0 auto 14px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
            </span>
            <div class="ff-section-title" style="font-size:16px;">Move this file to the Trash Can?</div>
            <p class="ff-hint" style="margin:8px 0 0; line-height:1.5;">
                “{{ $filename ?? 'this file' }}” will be recoverable from the Trash Can until you empty it.
            </p>
        </div>

        <div class="ff-divider" style="margin:20px 0 0;"></div>
        <div class="ff-form-actions" style="margin-top:18px;">
            <button type="button" class="ff-btn" onclick="hideeDeleteModel()">Cancel</button>
            <button type="button" class="ff-btn ff-btn-danger" onclick="handleDelete()">Delete</button>
        </div>
    </div>
</div>

<script>
    (function() {
        if (window.ff) window.ff.icons();
    })();

    function hideeDeleteModel() {
        var root = document.querySelector('.ff-delete-modal-root');
        if (!root) return;
        var host = root.parentElement;
        if (host) {
            host.hidden = true;
            host.innerHTML = '';
        }
    }

    function handleDelete() {
        $.ajax({
            type: "GET",
            url: "{{ route('panel.deletefile', encrypt($fileId)) }}",
            data: {
                '_token': "{{ csrf_token() }}",
                'ref_id': "{{ encrypt($fileId) }}"
            },
            success: function(response) {
                if (response.ok) {
                    hideeDeleteModel();
                    window.ff.toast('File moved to trash.', 'success', 2000);
                    setTimeout(() => location.reload(), 600);
                } else {
                    window.ff.toast(response.info || 'Could not delete file.', 'error', 4000);
                }
            },
            error: function(error) {
                console.log(error);
                window.ff.toast('Could not delete the file. Please try again.', 'error', 4000);
            }
        });
    }
</script>
