@extends('layout.backend')
@push('title', 'Add Password')

@section('content')
    @php
        $isEdit = isset($password);
        $action = $isEdit ? route('panel.updatepassword', $password->id) : route('panel.addpassword');
        $vTitle = old('title', $isEdit ? $password->title : '');
        $vUsername = old('username', $isEdit ? $password->username : '');
        $vUrl = old('url', $isEdit ? $password->url : '');
        $vPassword = old('password', $isEdit ? $password->password : '');
        $vNotes = old('notes', $isEdit ? $password->notes : '');
        $vHidden = (bool) old('isHidden', $isEdit && $password->is_hidden);
        $vFields = $isEdit && !empty($password->auth_fields) ? $password->auth_fields : [['label' => '', 'value' => '']];
    @endphp

    <div class="ff-breadcrumb">
        <a href="{{ route('panel.passwords') }}">Passwords</a>
        <span>/</span>
        <span class="is-current">{{ $isEdit ? 'Edit Credential' : 'Add Password' }}</span>
    </div>
    <h1 class="ff-h1 ff-h1-sm">{{ $isEdit ? 'Edit Credential' : 'Add Password / API Key' }}</h1>
    <p class="ff-sub">Store a credential securely in your vault.</p>

    @if ($errors->any())
        <div class="ff-alert is-error">
            <i data-lucide="alert-circle" class="w-4 h-4"></i>
            <div>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <form action="{{ $action }}" method="POST" autocomplete="off">
        @csrf

        <div class="ff-split">
            <div class="ff-form-card">

                {{-- ------------------------- Credential details ------------------------- --}}
                <div class="ff-section-head">
                    <span class="ff-section-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="10" rx="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </span>
                    <div>
                        <div class="ff-section-title">Credential Details</div>
                        <div class="ff-section-sub">Name it so you can find it later</div>
                    </div>
                </div>

                <div class="ff-field">
                    <label class="ff-label" for="pwTitle">Title / Name</label>
                    <input type="text" name="title" id="pwTitle" class="ff-input" required
                        placeholder="e.g. GitHub Account" value="{{ $vTitle }}">
                </div>

                <div class="ff-split-even">
                    <div class="ff-field">
                        <label class="ff-label" for="pwUsername">Username / Email</label>
                        <input type="text" name="username" id="pwUsername" class="ff-input" placeholder="username"
                            autocomplete="off" value="{{ $vUsername }}">
                    </div>
                    <div class="ff-field">
                        <label class="ff-label" for="pwUrl">Website URL</label>
                        <input type="url" name="url" id="pwUrl" class="ff-input"
                            placeholder="https://example.com" value="{{ $vUrl }}">
                    </div>
                </div>

                {{-- ------------------------------- Secrets ------------------------------- --}}
                <div class="ff-divider"></div>
                <div class="ff-section-head">
                    <span class="ff-section-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3" />
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
                        </svg>
                    </span>
                    <div>
                        <div class="ff-section-title">Secrets</div>
                        <div class="ff-section-sub">Masked by default — will be stored encrypted</div>
                    </div>
                </div>

                <div class="ff-field">
                    <label class="ff-label" for="pwSecret">Password</label>
                    <div class="ff-reveal-wrap">
                        <input type="password" name="password" id="pwSecret" class="ff-input has-reveal"
                            placeholder="Enter password" autocomplete="new-password" value="{{ $vPassword }}">
                        <button type="button" class="ff-reveal-btn" data-reveal="#pwSecret" aria-label="Reveal password">
                            <i data-lucide="eye" class="w-[17px] h-[17px]"></i>
                        </button>
                    </div>
                </div>

                <div class="ff-field">
                    <div class="ff-row-between">
                        <label class="ff-label">Other Auth Fields</label>
                        <span class="ff-hint">Secret, Token, Keygen, etc.</span>
                    </div>

                    <div id="authFields" class="ff-stack-xs" style="margin-top:4px; gap:10px;">
                        @foreach ($vFields as $i => $field)
                            <div class="ff-auth-row">
                                <input type="text" name="auth_labels[]" class="ff-input ff-auth-label"
                                    placeholder="Field name (e.g. Secret)" value="{{ $field['label'] ?? '' }}">
                                <div class="ff-reveal-wrap ff-grow">
                                    <input type="password" name="auth_values[]"
                                        class="ff-input has-reveal ff-mono ff-auth-value" placeholder="Value"
                                        autocomplete="new-password" value="{{ $field['value'] ?? '' }}">
                                    <button type="button" class="ff-reveal-btn" aria-label="Reveal value">
                                        <i data-lucide="eye" class="w-[17px] h-[17px]"></i>
                                    </button>
                                </div>
                                <button type="button" class="ff-menu-btn ff-auth-remove" aria-label="Remove field">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round">
                                        <line x1="18" y1="6" x2="6" y2="18" />
                                        <line x1="6" y1="6" x2="18" y2="18" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" id="addAuthField" class="ff-add-field-btn">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        Add another field
                    </button>
                </div>

                <div class="ff-field">
                    <label class="ff-label" for="pwNotes">Notes</label>
                    <textarea name="notes" id="pwNotes" rows="3" class="ff-textarea"
                        placeholder="Optional notes">{{ $vNotes }}</textarea>
                </div>

                {{-- ------------------------------ Visibility ------------------------------ --}}
                <div class="ff-divider"></div>
                <div class="ff-toggle-row">
                    <div>
                        <div class="ff-toggle-label">Hide credential</div>
                        <div class="ff-toggle-sub">Requires your vault password to view</div>
                    </div>
                    <label class="ff-switch">
                        <input type="checkbox" name="isHidden" value="1" id="pwHiddenToggle"
                            {{ $vHidden ? 'checked' : '' }}>
                    </label>
                </div>

                <div class="ff-divider"></div>
                <div class="ff-form-actions">
                    <a href="{{ route('panel.passwords') }}" class="ff-btn">Cancel</a>
                    <button type="submit" class="ff-btn ff-btn-primary">
                        {{ $isEdit ? 'Save Changes' : 'Save Credential' }}
                    </button>
                </div>
            </div>

            {{-- ----------------------------- Live preview ----------------------------- --}}
            <div class="ff-preview-card ff-hide-mobile">
                <div class="ff-preview-label">Live Preview</div>
                <div class="ff-preview-thumb">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="10" rx="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                </div>
                <div id="pwPreviewTitle" style="font-size:15.5px; font-weight:700; color:var(--ff-text); margin-top:14px;">
                    {{ $vTitle ?: 'Untitled Credential' }}
                </div>
                <div id="pwPreviewUsername" style="font-size:13px; color:var(--ff-text-2); margin-top:6px;">
                    {{ $vUsername ?: 'username' }}
                </div>
                <div class="ff-row" style="gap:8px; margin-top:14px;">
                    <span class="ff-badge-type">Password</span>
                    <span class="ff-badge-hidden" id="pwPreviewHidden" {{ $vHidden ? '' : 'hidden' }}>
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.7 18.7 0 0 1 5.06-5.94" />
                            <line x1="1" y1="1" x2="23" y2="23" />
                        </svg>
                        Hidden
                    </span>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('push-script')
    <script>
        (function() {
            // ------------------------------------------------------ reveal buttons
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('.ff-reveal-btn');
                if (!btn) return;

                var input = btn.parentElement.querySelector('input');
                if (!input) return;

                var showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                btn.querySelector('i').setAttribute('data-lucide', showing ? 'eye' : 'eye-off');
                window.ff.icons();
            });

            // -------------------------------------------------- dynamic auth fields
            var wrap = document.getElementById('authFields');

            function rowTemplate() {
                var row = document.createElement('div');
                row.className = 'ff-auth-row';
                row.innerHTML = `
                    <input type="text" name="auth_labels[]" class="ff-input ff-auth-label" placeholder="Field name (e.g. Secret)">
                    <div class="ff-reveal-wrap ff-grow">
                        <input type="password" name="auth_values[]" class="ff-input has-reveal ff-mono ff-auth-value" placeholder="Value" autocomplete="new-password">
                        <button type="button" class="ff-reveal-btn" aria-label="Reveal value">
                            <i data-lucide="eye" class="w-[17px] h-[17px]"></i>
                        </button>
                    </div>
                    <button type="button" class="ff-menu-btn ff-auth-remove" aria-label="Remove field">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>`;
                return row;
            }

            document.getElementById('addAuthField').addEventListener('click', function() {
                wrap.appendChild(rowTemplate());
                window.ff.icons();
            });

            wrap.addEventListener('click', function(e) {
                var remove = e.target.closest('.ff-auth-remove');
                if (!remove) return;

                var rows = wrap.querySelectorAll('.ff-auth-row');
                if (rows.length === 1) {
                    // Keep one row, just clear it
                    rows[0].querySelectorAll('input').forEach(function(i) {
                        i.value = '';
                    });
                    return;
                }
                remove.closest('.ff-auth-row').remove();
            });

            // ------------------------------------------------------- live preview
            function bind(inputId, previewId, fallback) {
                var input = document.getElementById(inputId);
                var preview = document.getElementById(previewId);
                input.addEventListener('input', function() {
                    preview.textContent = input.value.trim() || fallback;
                });
            }

            bind('pwTitle', 'pwPreviewTitle', 'Untitled Credential');
            bind('pwUsername', 'pwPreviewUsername', 'username');

            document.getElementById('pwHiddenToggle').addEventListener('change', function() {
                document.getElementById('pwPreviewHidden').hidden = !this.checked;
            });

            window.ff.icons();
        })();
    </script>
@endsection
