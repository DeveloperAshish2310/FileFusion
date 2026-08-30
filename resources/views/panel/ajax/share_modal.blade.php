@php
    $fileEncryptedId = isset($file) && $file ? encrypt($file->id) : ($id ?? '');
    $hasPublic = isset($publicShare) && $publicShare;
    $hasAnon = isset($anonShare) && $anonShare;
    $publicUrl = $hasPublic ? appShareUrl('/s/' . $publicShare->share_token) : '';
    $anonUrl = $hasAnon ? appShareUrl('/s/' . $anonShare->share_token) : '';
@endphp

<div class="ff-modal-backdrop ff-share-modal-root" style="position:fixed; inset:0; z-index:100050 !important;" onclick="if (event.target === this) hideeShareModel()">
    <div class="ff-modal" style="width:540px; max-width:94vw; max-height:90vh; overflow-y:auto;">
        
        {{-- Header --}}
        <div class="ff-row-between" style="flex-wrap:nowrap; align-items:flex-start; margin-bottom:14px;">
            <div class="ff-min0">
                <div class="ff-modal-title" style="font-size:18px;">Share File</div>
                <div class="ff-modal-sub ff-truncate" style="max-width:380px;">{{ $name ?? ($file->name ?? 'File') }}</div>
            </div>
            <button type="button" class="ff-modal-close" onclick="hideeShareModel()" aria-label="Close">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>

        {{-- Share Navigation Tabs --}}
        <div class="ff-chip-row" style="margin-bottom:18px; border-bottom:1px solid var(--ff-border); padding-bottom:12px;">
            <button type="button" class="ff-chip is-active" id="tabBtnPrivate" onclick="switchShareTab('private')">
                <i data-lucide="lock" class="w-3.5 h-3.5" style="margin-right:4px;"></i> Private (Users)
            </button>
            <button type="button" class="ff-chip" id="tabBtnPublic" onclick="switchShareTab('public')">
                <i data-lucide="qr-code" class="w-3.5 h-3.5" style="margin-right:4px;"></i> Public QR
            </button>
            <button type="button" class="ff-chip" id="tabBtnAnon" onclick="switchShareTab('anon')">
                <i data-lucide="ghost" class="w-3.5 h-3.5" style="margin-right:4px;"></i> Anonymous QR
            </button>
        </div>

        <input type="hidden" id="shareFileId" value="{{ $fileEncryptedId }}">

        {{-- ==================== TAB 1: PRIVATE SHARING ==================== --}}
        <div id="shareTabPrivate">
            <div class="ff-hint" style="margin-bottom:12px;">
                Share privately with another registered user on File Fusion. Only they can access and download it when logged in.
            </div>

            <form id="privateShareForm" onsubmit="submitPrivateShare(event)" autocomplete="off" style="margin-bottom:18px;">
                <div class="ff-row" style="gap:8px;">
                    <input type="email" name="share_recipient_lookup_email" id="privateEmailInput" class="ff-input" placeholder="Enter recipient email (e.g. user@domain.com)" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other" required>
                    <button type="submit" class="ff-btn ff-btn-primary" style="flex-shrink:0;">Add Access</button>
                </div>
            </form>

            <div class="ff-section-title" style="font-size:13px; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">
                Authorized Recipients (<span id="recipientCount">{{ isset($privateShares) ? $privateShares->count() : 0 }}</span>)
            </div>

            <div id="privateRecipientsList" class="ff-stack-xs" style="max-height:180px; overflow-y:auto; gap:6px;">
                @if (isset($privateShares) && $privateShares->count() > 0)
                    @foreach ($privateShares as $ps)
                        <div class="ff-list-row" id="shareRow{{ $ps->id }}" style="padding:8px 12px; background:var(--ff-bg-2); border-radius:8px;">
                            <div class="ff-min0">
                                <div class="ff-list-title" style="font-size:13.5px;">{{ $ps->recipient->name ?? ($ps->recipient_email ?? 'User') }}</div>
                                <div class="ff-list-subtitle" style="font-size:12px;">{{ $ps->recipient_email }} · shared {{ $ps->created_at->diffForHumans() }}</div>
                            </div>
                            <button type="button" class="ff-btn ff-btn-danger ff-btn-sm" onclick="revokeShareItem({{ $ps->id }})">Revoke</button>
                        </div>
                    @endforeach
                @else
                    <div id="emptyRecipientsNotice" class="ff-hint" style="text-align:center; padding:16px; background:var(--ff-bg-2); border-radius:8px;">
                        No private recipients added yet.
                    </div>
                @endif
            </div>
        </div>

        {{-- ==================== TAB 2: PUBLIC QR SHARING ==================== --}}
        <div id="shareTabPublic" hidden>
            <div class="ff-toggle-row" style="margin-bottom:16px;">
                <div>
                    <div class="ff-toggle-label">Public QR & Link Access</div>
                    <div class="ff-toggle-sub">Anyone with this link or QR code can download</div>
                </div>
                <label class="ff-switch">
                    <input type="checkbox" id="publicAccessToggle" {{ $hasPublic ? 'checked' : '' }} onchange="togglePublicAccess('public_link', this.checked)">
                </label>
            </div>

            <div id="publicShareDetails" {{ $hasPublic ? '' : 'hidden' }}>
                <div style="display:flex; flex-direction:column; align-items:center; margin:16px 0;">
                    <div style="padding:14px; background:white; border:1px solid var(--ff-border); border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                        <div id="qrcodePublic" style="border-radius:6px; overflow:hidden; line-height:0;"></div>
                    </div>
                    <span class="ff-hint" style="margin-top:8px;">Scan with mobile to download directly</span>
                </div>

                <div class="ff-field">
                    <label class="ff-label">Public Link</label>
                    <div class="ff-row" style="gap:8px;">
                        <input type="text" id="publicShareUrl" class="ff-input" value="{{ $publicUrl }}" readonly>
                        <button type="button" class="ff-btn ff-btn-primary" style="flex-shrink:0;" onclick="copyShareText('publicShareUrl', this)">Copy</button>
                    </div>
                </div>

                {{-- Security Controls: Passcode, Expiry, Limit --}}
                <div class="ff-split-even" style="margin-top:14px;">
                    <div class="ff-field">
                        <label class="ff-label">Passcode Protection</label>
                        <input type="password" name="share_passcode_custom" id="publicPasscode" class="ff-input" autocomplete="new-password" data-lpignore="true" data-form-type="other" data-dashlane-ignore="true" placeholder="{{ $hasPublic && $publicShare->isPasswordProtected() ? '•••••• (Protected)' : 'Optional passcode' }}">
                    </div>
                    <div class="ff-field">
                        <label class="ff-label">Expiration</label>
                        <select id="publicExpiry" class="ff-input">
                            <option value="">Never Expires</option>
                            <option value="1" {{ $hasPublic && $publicShare->expires_at && $publicShare->expires_at->diffInHours() <= 1 ? 'selected' : '' }}>1 Hour</option>
                            <option value="24" {{ $hasPublic && $publicShare->expires_at && $publicShare->expires_at->diffInHours() <= 24 ? 'selected' : '' }}>24 Hours</option>
                            <option value="168" {{ $hasPublic && $publicShare->expires_at && $publicShare->expires_at->diffInHours() <= 168 ? 'selected' : '' }}>7 Days</option>
                        </select>
                    </div>
                </div>

                <div class="ff-field" style="margin-top:8px;">
                    <div class="ff-row-between">
                        <label class="ff-label">Max Downloads Cap</label>
                        <span id="publicDownloadProgress" class="ff-hint" style="font-weight:600; color:var(--ff-accent);">
                            @if ($hasPublic && $publicShare->max_downloads)
                                {{ $publicShare->download_count }} of {{ $publicShare->max_downloads }} downloaded
                            @endif
                        </span>
                    </div>
                    <input type="number" id="publicMaxDownloads" class="ff-input" min="1" placeholder="Unlimited" value="{{ $hasPublic ? $publicShare->max_downloads : '' }}">
                </div>

                <div class="ff-row-between" style="margin-top:12px;">
                    <button type="button" class="ff-btn ff-btn-sm" onclick="saveShareConfig('public_link')">Update Security Options</button>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 3: ANONYMOUS QR SHARING ==================== --}}
        <div id="shareTabAnon" hidden>
            <div class="ff-toggle-row" style="margin-bottom:16px;">
                <div>
                    <div class="ff-toggle-label">Anonymous QR Access</div>
                    <div class="ff-toggle-sub">Completely hides uploader identity, email, and metadata</div>
                </div>
                <label class="ff-switch">
                    <input type="checkbox" id="anonAccessToggle" {{ $hasAnon ? 'checked' : '' }} onchange="togglePublicAccess('anonymous_qr', this.checked)">
                </label>
            </div>

            <div id="anonShareDetails" {{ $hasAnon ? '' : 'hidden' }}>
                <div style="display:flex; flex-direction:column; align-items:center; margin:16px 0;">
                    <div style="padding:14px; background:white; border:1px solid var(--ff-border); border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                        <div id="qrcodeAnon" style="border-radius:6px; overflow:hidden; line-height:0;"></div>
                    </div>
                    <span class="ff-hint" style="margin-top:8px;">Anonymous guest download token</span>
                </div>

                <div class="ff-field">
                    <label class="ff-label">Anonymous Link</label>
                    <div class="ff-row" style="gap:8px;">
                        <input type="text" id="anonShareUrl" class="ff-input" value="{{ $anonUrl }}" readonly>
                        <button type="button" class="ff-btn ff-btn-primary" style="flex-shrink:0;" onclick="copyShareText('anonShareUrl', this)">Copy</button>
                    </div>
                </div>

                <div class="ff-field" style="margin-top:12px;">
                    <div class="ff-row-between">
                        <label class="ff-label">Burn After Download (Max Downloads)</label>
                        <span id="anonDownloadProgress" class="ff-hint" style="font-weight:600; color:var(--ff-accent);">
                            @if ($hasAnon && $anonShare->max_downloads)
                                {{ $anonShare->download_count }} of {{ $anonShare->max_downloads }} downloaded
                            @endif
                        </span>
                    </div>
                    <input type="number" id="anonMaxDownloads" class="ff-input" min="1" placeholder="e.g. 1 for one-time transfer" value="{{ $hasAnon ? $anonShare->max_downloads : '1' }}">
                </div>

                <button type="button" class="ff-btn ff-btn-sm" style="margin-top:10px;" onclick="saveShareConfig('anonymous_qr')">Save Anonymous Settings</button>
            </div>
        </div>

    </div>
</div>

<script>
    (function() {
        var pubUrl = @json($publicUrl);
        var anonUrl = @json($anonUrl);

        if (pubUrl && window.QRCode) {
            renderQr('qrcodePublic', pubUrl);
        }
        if (anonUrl && window.QRCode) {
            renderQr('qrcodeAnon', anonUrl);
        }
        
        // Prevent browser autofill from injecting credentials
        setTimeout(function() {
            var emailInput = document.getElementById('privateEmailInput');
            if (emailInput) emailInput.value = '';
        }, 50);

        if (window.ff) window.ff.icons();
    })();

    function switchShareTab(tab) {
        $('#tabBtnPrivate, #tabBtnPublic, #tabBtnAnon').removeClass('is-active');
        $('#shareTabPrivate, #shareTabPublic, #shareTabAnon').attr('hidden', true);

        if (tab === 'private') {
            $('#tabBtnPrivate').addClass('is-active');
            $('#shareTabPrivate').removeAttr('hidden');
        } else if (tab === 'public') {
            $('#tabBtnPublic').addClass('is-active');
            $('#shareTabPublic').removeAttr('hidden');
            var pubUrl = $('#publicShareUrl').val();
            if (pubUrl) renderQr('qrcodePublic', pubUrl);
        } else if (tab === 'anon') {
            $('#tabBtnAnon').addClass('is-active');
            $('#shareTabAnon').removeAttr('hidden');
            var anonUrl = $('#anonShareUrl').val();
            if (anonUrl) renderQr('qrcodeAnon', anonUrl);
        }
        if (window.ff) window.ff.icons();
    }

    function renderQr(elementId, text) {
        var el = document.getElementById(elementId);
        if (!el || !window.QRCode || !text) return;
        el.innerHTML = '';
        new QRCode(el, {
            text: text,
            width: 156,
            height: 156
        });
    }

    function submitPrivateShare(e) {
        e.preventDefault();
        var fileId = $('#shareFileId').val();
        var email = $('#privateEmailInput').val().trim();
        if (!email) return;

        $.ajax({
            url: "{{ route('panel.share.private') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                file_id: fileId,
                email: email
            },
            success: function(res) {
                if (res.ok === 1) {
                    $('#privateEmailInput').val('');
                    $('#emptyRecipientsNotice').remove();
                    var newHtml = `
                        <div class="ff-list-row" id="shareRow${res.share.id}" style="padding:8px 12px; background:var(--ff-bg-2); border-radius:8px;">
                            <div class="ff-min0">
                                <div class="ff-list-title" style="font-size:13.5px;">${res.share.recipient_name}</div>
                                <div class="ff-list-subtitle" style="font-size:12px;">${res.share.recipient_email} · just now</div>
                            </div>
                            <button type="button" class="ff-btn ff-btn-danger ff-btn-sm" onclick="revokeShareItem(${res.share.id})">Revoke</button>
                        </div>`;
                    $('#privateRecipientsList').prepend(newHtml);
                    var count = parseInt($('#recipientCount').text() || '0') + 1;
                    $('#recipientCount').text(count);
                    window.ff.toast(res.info || 'File shared successfully.', 'success', 2500);
                } else {
                    window.ff.toast(res.info || 'Failed to share file.', 'error', 4000);
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.info || xhr.responseJSON.message : 'Error sharing file.';
                window.ff.toast(msg, 'error', 4000);
            }
        });
    }

    function togglePublicAccess(shareType, isEnabled) {
        var fileId = $('#shareFileId').val();

        $.ajax({
            url: "{{ route('panel.share.public') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                file_id: fileId,
                share_type: shareType,
                is_enabled: isEnabled ? 1 : 0
            },
            success: function(res) {
                if (shareType === 'public_link') {
                    if (isEnabled && res.share_url) {
                        $('#publicShareDetails').removeAttr('hidden');
                        $('#publicShareUrl').val(res.share_url);
                        renderQr('qrcodePublic', res.share_url);
                    } else {
                        $('#publicShareDetails').attr('hidden', true);
                        $('#publicShareUrl').val('');
                    }
                } else if (shareType === 'anonymous_qr') {
                    if (isEnabled && res.share_url) {
                        $('#anonShareDetails').removeAttr('hidden');
                        $('#anonShareUrl').val(res.share_url);
                        renderQr('qrcodeAnon', res.share_url);
                    } else {
                        $('#anonShareDetails').attr('hidden', true);
                        $('#anonShareUrl').val('');
                    }
                }
                window.ff.toast(isEnabled ? 'Public link enabled.' : 'Public link disabled.', 'info', 2000);
            },
            error: function() {
                window.ff.toast('Failed to update share access.', 'error', 4000);
            }
        });
    }

    function saveShareConfig(shareType) {
        var fileId = $('#shareFileId').val();
        var data = {
            _token: '{{ csrf_token() }}',
            file_id: fileId,
            share_type: shareType,
            is_enabled: 1
        };

        if (shareType === 'public_link') {
            data.password = $('#publicPasscode').val();
            data.expiry_hours = $('#publicExpiry').val();
            data.max_downloads = $('#publicMaxDownloads').val();
        } else {
            data.max_downloads = $('#anonMaxDownloads').val();
        }

        $.ajax({
            url: "{{ route('panel.share.public') }}",
            type: 'POST',
            data: data,
            success: function(res) {
                if (res.ok === 1) {
                    if (shareType === 'public_link') {
                        if (res.max_downloads) {
                            $('#publicDownloadProgress').text((res.download_count || 0) + ' of ' + res.max_downloads + ' downloaded');
                        } else {
                            $('#publicDownloadProgress').text('');
                        }
                    } else if (shareType === 'anonymous_qr') {
                        if (res.max_downloads) {
                            $('#anonDownloadProgress').text((res.download_count || 0) + ' of ' + res.max_downloads + ' downloaded');
                        } else {
                            $('#anonDownloadProgress').text('');
                        }
                    }
                    window.ff.toast('Share settings updated successfully.', 'success', 2000);
                }
            },
            error: function() {
                window.ff.toast('Error updating share configuration.', 'error', 4000);
            }
        });
    }

    async function revokeShareItem(shareId) {
        const confirmed = await window.ff.confirm({
            title: '🚫 Revoke Access',
            message: 'Revoke access for this recipient? They will no longer be able to view or download this file.',
            confirmText: 'Revoke Access',
            isDanger: true
        });

        if (!confirmed) return;

        $.ajax({
            url: "{{ url('panel/share/revoke') }}/" + shareId,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.ok === 1) {
                    $('#shareRow' + shareId).fadeOut(200, function() {
                        $(this).remove();
                        var count = Math.max(0, parseInt($('#recipientCount').text() || '1') - 1);
                        $('#recipientCount').text(count);
                    });
                    window.ff.toast('Access revoked.', 'info', 2000);
                }
            },
            error: function() {
                window.ff.toast('Failed to revoke access.', 'error', 4000);
            }
        });
    }

    function copyShareText(inputId, btn) {
        var input = document.getElementById(inputId);
        if (!input) return;
        if (window.ff && typeof window.ff.copy === 'function') {
            window.ff.copy(input.value, 'Copied to clipboard!');
        } else if (window.copyToClipboard) {
            window.copyToClipboard(input.value, 'Copied to clipboard!');
        }
        var orig = btn.textContent;
        btn.textContent = 'Copied!';
        setTimeout(function() { btn.textContent = orig; }, 2000);
    }

    function hideeShareModel() {
        var root = document.querySelector('.ff-share-modal-root');
        if (!root) return;
        var host = root.parentElement;
        if (host) {
            host.hidden = true;
            host.innerHTML = '';
        }
    }
</script>
