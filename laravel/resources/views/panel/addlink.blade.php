@extends('layout.backend')
@push('title', 'Add Link')

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
    <style>
        .tagify {
            --tags-border-color: var(--ff-border, #cbd5e1);
            --tags-hover-border-color: var(--ff-accent);
            --tags-focus-border-color: var(--ff-accent);
            background: var(--ff-bg-input, #ffffff);
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.875rem;
            min-height: 42px;
            padding: 3px 6px;
            transition: all 0.2s ease;
            box-shadow: none;
            border: 1px solid var(--ff-border, #cbd5e1);
        }

        /* Light Theme Tag Pill: Entire pill wraps text + x icon inside */
        .tagify__tag {
            display: inline-flex !important;
            align-items: center !important;
            margin: 3px 4px !important;
            padding: 3px 8px !important;
            border-radius: 6px !important;
            background: var(--ff-bg-2, #eef2ff) !important;
            border: 1px solid var(--ff-border, #c7d2fe) !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
            line-height: 1.4 !important;
        }
        .tagify__tag > div {
            background: transparent !important;
            color: var(--ff-text, #1e293b) !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
            font-weight: 500 !important;
        }
        .tagify__tag > div::before {
            display: none !important;
        }
        .tagify__tag__removeBtn {
            position: static !important;
            margin: 0 0 0 6px !important;
            width: 14px !important;
            height: 14px !important;
            line-height: 14px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 50% !important;
            color: #64748b !important;
            opacity: 0.7;
            transition: all 0.15s ease !important;
        }
        .tagify__tag__removeBtn:hover {
            color: #ffffff !important;
            background: #ef4444 !important;
            opacity: 1;
        }

        /* Dark Theme Tag Pill */
        [data-theme="dark"] .tagify {
            background: var(--ff-bg-input, #18181b);
            border: 1px solid var(--ff-border, #27272a);
        }
        [data-theme="dark"] .tagify__tag {
            background: var(--ff-card, #27272a) !important;
            border: 1px solid var(--ff-border, #3f3f46) !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3) !important;
        }
        [data-theme="dark"] .tagify__tag > div {
            color: var(--ff-text, #f1f5f9) !important;
        }
        [data-theme="dark"] .tagify__tag__removeBtn {
            color: #a1a1aa !important;
        }
        [data-theme="dark"] .tagify__tag__removeBtn:hover {
            color: #ffffff !important;
        }

        .tagify.tagify--focus {
            border-color: var(--ff-accent) !important;
            box-shadow: 0 0 0 2px color-mix(in srgb, var(--ff-accent) 25%, transparent) !important;
        }
        .tagify__input {
            color: var(--ff-text-main, inherit) !important;
        }
        .tagify__input::before {
            color: var(--ff-text-muted, #94a3b8) !important;
        }
    </style>
@endsection

@section('content')
    @php
        $isEdit = isset($link);
        $action = $isEdit ? route('panel.updatelink', encrypt($link->id)) : route('panel.addlink');
        $vUrl = old('url', request('prefill_url', $isEdit ? $link->url : ''));
        $vTitle = old('title', request('prefill_name', $isEdit ? $link->title : ''));
        $vDescription = old('description', request('prefill_desc', $isEdit ? $link->description : ''));
        $vThumb = old('thumbnail', $isEdit ? $link->thumbnail : '');
        $vTags = old('categories', $isEdit ? $link->tags : '');
        $vIsNew = (bool) old('isNew', $isEdit && $link->is_new);
        $vIsHidden = (bool) old('isHidden', $isEdit && $link->is_hidden);
        $vShot = (bool) old('makeThumbnailWithBrowershot', $isEdit && $link->makeThumbnailWithBrowershot);
    @endphp

    <div class="ff-breadcrumb">
        <a href="{{ route('panel.linklist') }}">Links</a>
        <span>/</span>
        <span class="is-current">{{ $isEdit ? 'Edit Link' : 'Add Link' }}</span>
    </div>
    <h1 class="ff-h1 ff-h1-sm">{{ $isEdit ? 'Edit Link' : 'Add New Link' }}</h1>
    <p class="ff-sub">{{ $isEdit ? 'Update your link details.' : 'Save a bookmark to your collection.' }}</p>

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

    <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="ff-split">
            <div class="ff-form-card">

                {{-- ------------------------------ The link ------------------------------ --}}
                <div class="ff-section-head">
                    <span class="ff-section-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                        </svg>
                    </span>
                    <div>
                        <div class="ff-section-title">The Link</div>
                        <div class="ff-section-sub">Where it points and what to call it</div>
                    </div>
                </div>

                <div class="ff-field">
                    <label class="ff-label" for="url">URL</label>
                    <input type="url" name="url" id="url" class="ff-input" required
                        placeholder="https://example.com" value="{{ $vUrl }}">
                </div>

                <div class="ff-field">
                    <label class="ff-label" for="title">Title</label>
                    <input type="text" name="title" id="title" class="ff-input" required
                        placeholder="e.g. React documentation" value="{{ $vTitle }}">
                </div>

                <div class="ff-field">
                    <label class="ff-label" for="description">Description</label>
                    <textarea name="description" id="description" rows="3" class="ff-textarea"
                        placeholder="What is this link about?">{{ $vDescription }}</textarea>
                </div>

                {{-- ---------------------------- Organisation ---------------------------- --}}
                <div class="ff-divider"></div>
                <div class="ff-section-head">
                    <span class="ff-section-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" />
                            <line x1="7" y1="7" x2="7.01" y2="7" />
                        </svg>
                    </span>
                    <div>
                        <div class="ff-section-title">Organisation</div>
                        <div class="ff-section-sub">Category and tags to find it later</div>
                    </div>
                </div>

                <div class="ff-split-even">
                    <div class="ff-field">
                        <label class="ff-label" for="category_id">Category</label>
                        <select id="category_id" name="category_id" class="ff-select">
                            <option value="">Select a category</option>
                            @forelse ($categories as $category)
                                <option value="{{ encrypt($category->id) }}"
                                    {{ old('category_id') == encrypt($category->id) || ($isEdit && $link->category_id == $category->id) ? 'selected' : '' }}>
                                    {{ $category->title }}
                                </option>
                            @empty
                                <option value="" disabled>No categories available</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="ff-field">
                        <label class="ff-label" for="categories">Tags</label>
                        <input type="text" name="categories" id="categories" class="ff-input"
                            placeholder="React, JavaScript, Frontend" value="{{ $vTags }}">
                        <span class="ff-hint">Separate tags with commas</span>
                    </div>
                </div>

                {{-- ------------------------------ Thumbnail ------------------------------ --}}
                <div class="ff-divider"></div>
                <div class="ff-section-head">
                    <span class="ff-section-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" />
                            <circle cx="8.5" cy="8.5" r="1.5" />
                            <path d="M21 15l-5-5L5 21" />
                        </svg>
                    </span>
                    <div>
                        <div class="ff-section-title">Thumbnail</div>
                        <div class="ff-section-sub">Upload an image, paste a URL, or capture automatically</div>
                    </div>
                </div>

                {{-- Direct File Upload Dropzone --}}
                <label class="ff-dropzone" id="thumbDropzone" style="cursor:pointer; display:flex; flex-direction:column; align-items:center; justify-content:center; border:2px dashed var(--ff-border, #cbd5e1); border-radius:10px; padding:20px 16px; margin-bottom:14px; background:var(--ff-surface-subtle, rgba(0,0,0,0.02)); transition:all 0.2s ease;">
                    <input type="file" name="thumbnail_file" id="thumbnail_file" accept="image/*" style="display:none;">
                    <span class="ff-dropzone-icon" style="color:var(--ff-accent,#E0392E); margin-bottom:6px;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                    </span>
                    <span class="ff-dropzone-title-sm" id="thumbDropzoneLabel" style="font-size:13px; font-weight:600; color:var(--ff-text-main, #0f172a);">Drop an image here, or click to browse</span>
                    <span class="ff-hint" style="font-size:11.5px; color:var(--ff-text-muted, #64748b); margin-top:2px;">PNG, JPG, WebP, SVG up to 5MB</span>
                </label>

                <div class="ff-field">
                    <label class="ff-label" for="thumbnail">or Thumbnail URL</label>
                    <input type="url" name="thumbnail" id="thumbnail" class="ff-input"
                        placeholder="https://example.com/image.jpg" value="{{ $vThumb }}"
                        {{ $vShot ? 'disabled' : '' }}>
                    <span class="ff-hint">Upload a file, paste an image URL, or use screenshot capture below</span>
                </div>

                <div class="ff-toggle-row">
                    <div>
                        <div class="ff-toggle-label">Capture a screenshot instead</div>
                        <div class="ff-toggle-sub">Only used when the thumbnail upload/URL is left blank</div>
                    </div>
                    <label class="ff-switch">
                        <input type="checkbox" name="makeThumbnailWithBrowershot" value="1"
                            id="makeThumbnailWithBrowershot" {{ $vShot ? 'checked' : '' }}>
                    </label>
                </div>

                {{-- ------------------------------ Visibility ------------------------------ --}}
                <div class="ff-divider"></div>
                <div class="ff-stack-sm">
                    <div class="ff-toggle-row">
                        <div>
                            <div class="ff-toggle-label">Mark as new</div>
                            <div class="ff-toggle-sub">Shows a “New” badge on this link</div>
                        </div>
                        <label class="ff-switch">
                            <input type="checkbox" name="isNew" value="1" id="isNewToggle"
                                {{ $vIsNew ? 'checked' : '' }}>
                        </label>
                    </div>
                    <div class="ff-toggle-row">
                        <div>
                            <div class="ff-toggle-label">Hide link</div>
                            <div class="ff-toggle-sub">Only visible to you, hidden from shares</div>
                        </div>
                        <label class="ff-switch">
                            <input type="checkbox" name="isHidden" value="1" id="isHiddenToggle"
                                {{ $vIsHidden ? 'checked' : '' }}>
                        </label>
                    </div>
                </div>

                <div class="ff-divider"></div>
                <div class="ff-form-actions">
                    <a href="{{ route('panel.linklist') }}" class="ff-btn">Cancel</a>
                    <button type="submit" class="ff-btn ff-btn-primary">
                        {{ $isEdit ? 'Update Link' : 'Add Link' }}
                    </button>
                </div>
            </div>

            {{-- ----------------------------- Live preview ----------------------------- --}}
            <div class="ff-preview-card ff-hide-mobile">
                <div class="ff-preview-label">Live Preview</div>
                <div class="ff-preview-thumb" id="previewThumb">
                    @php
                        $thumbPreviewSrc = null;
                        if ($vThumb) {
                            $thumbPreviewSrc = \Illuminate\Support\Str::startsWith($vThumb, ['http://', 'https://', '//', 'data:'])
                                ? $vThumb
                                : asset($vThumb);
                        }
                    @endphp
                    @if ($thumbPreviewSrc)
                        <img src="{{ $thumbPreviewSrc }}" alt="" style="width:100%; height:100%; object-fit:cover;">
                    @else
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" />
                            <circle cx="8.5" cy="8.5" r="1.5" />
                            <path d="M21 15l-5-5L5 21" />
                        </svg>
                    @endif
                </div>
                <div class="ff-row" style="gap:8px; margin-top:14px;">
                    <div id="previewTitle" style="font-size:15.5px; font-weight:700; color:var(--ff-text);">
                        {{ $vTitle ?: 'Untitled Link' }}
                    </div>
                    <span class="ff-badge-new" id="previewNewBadge" {{ $vIsNew ? '' : 'hidden' }}>New</span>
                </div>
                <div class="ff-truncate" id="previewUrl"
                    style="font-size:12.5px; color:var(--ff-accent); margin-top:4px;">
                    {{ $vUrl ?: 'https://example.com' }}
                </div>
                <div id="previewDescription"
                    style="font-size:13px; color:var(--ff-text-2); margin-top:8px; line-height:1.5;">
                    {{ $vDescription ?: 'Your description will appear here.' }}
                </div>
                <div class="ff-row" style="gap:8px; margin-top:14px;">
                    <span class="ff-badge-hidden" id="previewHiddenBadge" {{ $vIsHidden ? '' : 'hidden' }}>
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
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script>
        (function() {
            var categoriesInput = document.getElementById('categories');
            if (categoriesInput && typeof Tagify !== 'undefined') {
                new Tagify(categoriesInput, {
                    delimiters: ",",
                    trim: true,
                    maxTags: 25,
                    placeholder: 'Type tags & press Comma or Enter',
                    dropdown: {
                        enabled: 0
                    }
                });
            }
            function bindText(inputId, previewId, fallback) {
                var input = document.getElementById(inputId);
                var preview = document.getElementById(previewId);
                input.addEventListener('input', function() {
                    preview.textContent = input.value.trim() || fallback;
                });
            }

            bindText('title', 'previewTitle', 'Untitled Link');
            bindText('url', 'previewUrl', 'https://example.com');
            bindText('description', 'previewDescription', 'Your description will appear here.');

            document.getElementById('isNewToggle').addEventListener('change', function() {
                document.getElementById('previewNewBadge').hidden = !this.checked;
            });
            document.getElementById('isHiddenToggle').addEventListener('change', function() {
                document.getElementById('previewHiddenBadge').hidden = !this.checked;
            });

            var thumbnailFile = document.getElementById('thumbnail_file');
            var thumbDropzoneLabel = document.getElementById('thumbDropzoneLabel');
            var thumbnail = document.getElementById('thumbnail');
            var previewThumb = document.getElementById('previewThumb');

            if (thumbnailFile) {
                thumbnailFile.addEventListener('change', function(e) {
                    var file = e.target.files[0];
                    if (file) {
                        thumbDropzoneLabel.textContent = file.name;
                        var reader = new FileReader();
                        reader.onload = function(evt) {
                            previewThumb.innerHTML = '<img src="' + evt.target.result + '" alt="" style="width:100%; height:100%; object-fit:cover;">';
                        };
                        reader.readAsDataURL(file);
                        if (thumbnail) {
                            thumbnail.value = '';
                        }
                    }
                });
            }

            if (thumbnail) {
                thumbnail.addEventListener('input', function() {
                    var val = this.value.trim();
                    if (!val) return;
                    previewThumb.innerHTML = '<img src="' + val + '" alt="" style="width:100%; height:100%; object-fit:cover;">';
                    if (thumbDropzoneLabel) {
                        thumbDropzoneLabel.textContent = 'Drop an image here, or click to browse';
                    }
                    if (thumbnailFile) {
                        thumbnailFile.value = '';
                    }
                });
            }

            document.getElementById('makeThumbnailWithBrowershot').addEventListener('change', function() {
                if (this.checked) {
                    thumbnail.value = '';
                    thumbnail.disabled = true;
                    thumbnail.placeholder = 'A screenshot will be captured on save…';
                } else {
                    thumbnail.disabled = false;
                    thumbnail.placeholder = 'https://example.com/image.jpg';
                }
            });

            window.ff.icons();
        })();
    </script>
@endsection
