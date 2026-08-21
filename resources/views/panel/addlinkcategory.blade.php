@extends('layout.backend')
@push('title', 'Add Category')

@section('content')
    @php
        $isEdit = isset($category);
        $url = $isEdit ? route('panel.categories.update', $category->id) : route('panel.categories.store');
        $currentType = old('type', $category->type ?? request()->query('type', 'links'));
        $currentTitle = old('title', $category->title ?? '');
        $currentDescription = old('description', $category->description ?? '');
        $currentTags = $isEdit && is_array($category->categories)
            ? implode(', ', $category->categories)
            : old('categories', '');
        $isNew = (bool) old('is_new', $category->is_new ?? false);
        $isHidden = (bool) old('is_hidden', $category->is_hidden ?? false);
        $thumbUrl = old('thumbnail_url', $category->thumbnail ?? '');
    @endphp

    <div class="ff-breadcrumb">
        <a href="{{ route('panel.categories.index') }}">Categories</a>
        <span>/</span>
        <span class="is-current">{{ $isEdit ? 'Edit Category' : 'Add Category' }}</span>
    </div>
    <h1 class="ff-h1 ff-h1-sm">{{ $isEdit ? 'Edit Category' : 'Add New Category' }}</h1>
    <p class="ff-sub">Organize your links, files, and tasks into a collection.</p>

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

    <form action="{{ $url }}" method="POST" enctype="multipart/form-data" id="categoryForm">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="ff-split">
            <div class="ff-form-card">

                {{-- ----------------------------- Basic info ----------------------------- --}}
                <div class="ff-section-head">
                    <span class="ff-section-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                        </svg>
                    </span>
                    <div>
                        <div class="ff-section-title">Basic Info</div>
                        <div class="ff-section-sub">Name and describe this category</div>
                    </div>
                </div>

                <div class="ff-field">
                    <label class="ff-label" for="title">Title</label>
                    <input type="text" name="title" id="title" class="ff-input" required
                        placeholder="e.g. Work Projects or React Resources" value="{{ $currentTitle }}">
                </div>

                <div class="ff-field">
                    <label class="ff-label" for="description">Description</label>
                    <textarea name="description" id="description" rows="3" class="ff-textarea"
                        placeholder="What kind of items live in this collection?">{{ $currentDescription }}</textarea>
                </div>

                <div class="ff-field">
                    <label class="ff-label">Type</label>
                    <input type="hidden" name="type" id="typeInput" value="{{ $currentType }}">
                    <div class="ff-row" style="gap:10px; flex-wrap:wrap;">
                        <button type="button" class="ff-type-btn {{ $currentType === 'links' ? 'is-active' : '' }}"
                            data-type="links">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                            </svg>
                            Links
                        </button>
                        <button type="button" class="ff-type-btn {{ $currentType === 'files' ? 'is-active' : '' }}"
                            data-type="files">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                            </svg>
                            Files
                        </button>
                        <button type="button" class="ff-type-btn {{ in_array($currentType, ['tasks', 'todos']) ? 'is-active' : '' }}"
                            data-type="tasks">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 11l3 3L22 4" />
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                            </svg>
                            Tasks
                        </button>
                        <button type="button" class="ff-type-btn {{ $currentType === 'both' ? 'is-active' : '' }}"
                            data-type="both">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7" rx="1.5" />
                                <rect x="14" y="14" width="7" height="7" rx="1.5" />
                                <path d="M14 7h7" />
                                <path d="M3 17h7" />
                            </svg>
                            Both (Links & Files)
                        </button>
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
                        <div class="ff-section-sub">Upload an image or paste a URL — either works</div>
                    </div>
                </div>

                <label class="ff-dropzone" id="thumbDropzone">
                    <input type="file" name="thumbnail_file" id="thumbnail_file" accept="image/*" hidden>
                    <span class="ff-dropzone-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                    </span>
                    <span class="ff-dropzone-title-sm" id="thumbDropzoneLabel">Drop an image, or click to browse</span>
                    <span class="ff-hint">PNG, JPG up to 5MB</span>
                </label>

                <div class="ff-field">
                    <label class="ff-label" for="thumbnail">or Thumbnail URL</label>
                    <input type="url" name="thumbnail_url" id="thumbnail" class="ff-input"
                        placeholder="https://example.com/image.jpg" value="{{ $thumbUrl }}">
                    <span class="ff-hint">Upload a file <em>or</em> paste a URL — not both.</span>
                </div>

                {{-- --------------------------- Tags & visibility --------------------------- --}}
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
                        <div class="ff-section-title">Tags &amp; Visibility</div>
                        <div class="ff-section-sub">Help yourself find it later, or keep it private</div>
                    </div>
                </div>

                <div class="ff-field">
                    <label class="ff-label" for="categories">Tags</label>
                    <input type="text" name="categories" id="categories" class="ff-input"
                        placeholder="React, JavaScript, Frontend" value="{{ $currentTags }}">
                    <span class="ff-hint">Separate tags with commas</span>
                </div>

                <div class="ff-stack-sm">
                    <div class="ff-toggle-row">
                        <div>
                            <div class="ff-toggle-label">Mark as new</div>
                            <div class="ff-toggle-sub">Shows a “New” badge on this category</div>
                        </div>
                        <label class="ff-switch">
                            <input type="checkbox" name="is_new" value="1" id="isNewToggle"
                                {{ $isNew ? 'checked' : '' }}>
                        </label>
                    </div>
                    <div class="ff-toggle-row">
                        <div>
                            <div class="ff-toggle-label">Hide category</div>
                            <div class="ff-toggle-sub">Only visible to you, hidden from shares</div>
                        </div>
                        <label class="ff-switch">
                            <input type="checkbox" name="is_hidden" value="1" id="isHiddenToggle"
                                {{ $isHidden ? 'checked' : '' }}>
                        </label>
                    </div>
                </div>

                <div class="ff-divider"></div>
                <div class="ff-form-actions">
                    <a href="{{ route('panel.categories.index') }}" class="ff-btn">Cancel</a>
                    <button type="submit" class="ff-btn ff-btn-primary">
                        {{ $isEdit ? 'Save Changes' : 'Add Category' }}
                    </button>
                </div>
            </div>

            {{-- ----------------------------- Live preview ----------------------------- --}}
            <div class="ff-preview-card ff-hide-mobile">
                <div class="ff-preview-label">Live Preview</div>
                <div class="ff-preview-thumb" id="previewThumb">
                    @if ($thumbUrl)
                        <img src="{{ $thumbUrl }}" alt="">
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
                        {{ $currentTitle ?: 'Untitled Category' }}
                    </div>
                    <span class="ff-badge-new" id="previewNewBadge" {{ $isNew ? '' : 'hidden' }}>New</span>
                </div>
                <div id="previewDescription" style="font-size:13px; color:var(--ff-text-2); margin-top:6px; line-height:1.5;">
                    {{ $currentDescription ?: 'Your description will appear here.' }}
                </div>
                <div class="ff-row" style="gap:8px; margin-top:14px;">
                    <span class="ff-badge-type" id="previewType">{{ ucfirst($currentType) }}</span>
                    <span class="ff-badge-hidden" id="previewHiddenBadge" {{ $isHidden ? '' : 'hidden' }}>
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
            var typeInput = document.getElementById('typeInput');
            var previewType = document.getElementById('previewType');

            document.querySelectorAll('.ff-type-btn[data-type]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.ff-type-btn[data-type]').forEach(function(b) {
                        b.classList.remove('is-active');
                    });
                    btn.classList.add('is-active');
                    typeInput.value = btn.dataset.type;
                    previewType.textContent = btn.dataset.type.charAt(0).toUpperCase() + btn.dataset.type.slice(1);
                });
            });

            var title = document.getElementById('title');
            var previewTitle = document.getElementById('previewTitle');
            title.addEventListener('input', function() {
                previewTitle.textContent = title.value.trim() || 'Untitled Category';
            });

            var description = document.getElementById('description');
            var previewDescription = document.getElementById('previewDescription');
            description.addEventListener('input', function() {
                previewDescription.textContent = description.value.trim() || 'Your description will appear here.';
            });

            document.getElementById('isNewToggle').addEventListener('change', function() {
                document.getElementById('previewNewBadge').hidden = !this.checked;
            });
            document.getElementById('isHiddenToggle').addEventListener('change', function() {
                document.getElementById('previewHiddenBadge').hidden = !this.checked;
            });

            // Thumbnail preview: URL field or picked file
            var previewThumb = document.getElementById('previewThumb');

            function showThumb(src) {
                previewThumb.innerHTML = '';
                var img = document.createElement('img');
                img.src = src;
                img.alt = '';
                previewThumb.appendChild(img);
            }

            document.getElementById('thumbnail').addEventListener('change', function() {
                if (this.value.trim()) showThumb(this.value.trim());
            });

            var fileField = document.getElementById('thumbnail_file');
            var dropzone = document.getElementById('thumbDropzone');
            var dropzoneLabel = document.getElementById('thumbDropzoneLabel');

            fileField.addEventListener('change', function() {
                if (!this.files || !this.files[0]) return;
                dropzoneLabel.textContent = this.files[0].name;
                showThumb(URL.createObjectURL(this.files[0]));
            });

            ['dragover', 'dragenter'].forEach(function(evt) {
                dropzone.addEventListener(evt, function(e) {
                    e.preventDefault();
                    dropzone.classList.add('is-dragging');
                });
            });
            ['dragleave', 'drop'].forEach(function(evt) {
                dropzone.addEventListener(evt, function(e) {
                    e.preventDefault();
                    dropzone.classList.remove('is-dragging');
                });
            });
            dropzone.addEventListener('drop', function(e) {
                if (e.dataTransfer.files && e.dataTransfer.files.length) {
                    fileField.files = e.dataTransfer.files;
                    fileField.dispatchEvent(new Event('change'));
                }
            });

            window.ff.icons();
        })();
    </script>
@endsection
