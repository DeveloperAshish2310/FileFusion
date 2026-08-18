@extends('layout.backend')
@push('title', 'Upload File')
@section('page-width', 'ff-page-narrow')

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
    <h1 class="ff-h1">Upload File</h1>
    <p class="ff-sub">Drag and drop, or browse from your device.</p>

    <div id="dropZone" class="ff-dropzone-lg">
        <span class="ff-dropzone-icon ff-dropzone-icon-lg">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="17 8 12 3 7 8" />
                <line x1="12" y1="3" x2="12" y2="15" />
            </svg>
        </span>
        <div class="ff-dropzone-title" id="dropZoneHeadline">Drag &amp; drop files here</div>
        <div class="ff-hint" style="margin-top:4px;">
            or <span id="fileInput" class="ff-accent-text" style="cursor:pointer; font-weight:600;">click to browse</span>
            from your device
        </div>
        <div class="ff-hint" style="margin-top:14px;">Images, documents, audio, video &amp; archives · up to 2&nbsp;GB per file</div>
    </div>

    <div id="fileList" class="ff-form-card" style="margin-top:20px;" hidden>
        <div class="ff-section-head">
            <span class="ff-section-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="8" y1="6" x2="21" y2="6" />
                    <line x1="8" y1="12" x2="21" y2="12" />
                    <line x1="8" y1="18" x2="21" y2="18" />
                    <line x1="3" y1="6" x2="3.01" y2="6" />
                    <line x1="3" y1="12" x2="3.01" y2="12" />
                    <line x1="3" y1="18" x2="3.01" y2="18" />
                </svg>
            </span>
            <div>
                <div class="ff-section-title" id="queueCountLabel">0 files selected</div>
                <div class="ff-section-sub">Files ready to upload</div>
            </div>
        </div>

        <div id="fileListItems"></div>
    </div>

    {{-- -------------------------------- Details Card -------------------------------- --}}
    <div id="uploadDetailsCard" class="ff-form-card" style="margin-top:20px;">
        <div class="ff-section-head">
            <span class="ff-section-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                </svg>
            </span>
            <div>
                <div class="ff-section-title">Upload Settings</div>
                <div class="ff-section-sub">Applied to every file in this batch</div>
            </div>
        </div>

        <div class="ff-split-even">
            <div class="ff-field">
                <label class="ff-label" for="uploadCategory">Category</label>
                <select id="uploadCategory" class="ff-select">
                    <option value="">Uncategorized</option>
                    @foreach ($categories as $category)
                        <option value="{{ encrypt($category->id) }}">{{ $category->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ff-field">
                <label class="ff-label" for="uploadTags">Tags</label>
                <input type="text" id="uploadTags" name="uploadTags" class="ff-input" placeholder="Type tags & press Enter or Comma">
            </div>
        </div>

        <div class="ff-toggle-row">
            <div>
                <div class="ff-toggle-label">Hide after upload</div>
                <div class="ff-toggle-sub">Only visible behind your vault passcode</div>
            </div>
            <label class="ff-switch">
                <input type="checkbox" id="uploadHidden">
            </label>
        </div>

        <div class="ff-divider"></div>
        <div class="ff-form-actions">
            <button type="button" id="clearQueueButton" class="ff-btn">Clear All</button>
            <button type="button" id="uploadButton" class="ff-btn ff-btn-primary">Upload All</button>
        </div>
    </div>
@endsection

@section('push-script')
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/plupload/3.1.5/moxie.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/plupload/3.1.5/plupload.min.js"
        integrity="sha512-58a28EKvV/15yI9xQa4lozAxbstbQlWGvDPwPTvznkmH1D8iEz0Ylmfytqes2wKCzh6gOci6n+xBsjDtkG/I+g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        let uploadProgress = {};
        let isUploading = false;
        const uploadedFileNames = new Set();

        const dropZone = document.getElementById('dropZone');
        const dropZoneHeadline = document.getElementById('dropZoneHeadline');
        const fileList = document.getElementById('fileList');
        const fileListItems = document.getElementById('fileListItems');
        const uploadButton = document.getElementById('uploadButton');
        const clearQueueButton = document.getElementById('clearQueueButton');
        const queueCountLabel = document.getElementById('queueCountLabel');

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function queueIcon(fileType) {
            if (!fileType) return 'file';
            if (fileType.includes('image')) return 'image';
            if (fileType.includes('video')) return 'video';
            if (fileType.includes('audio')) return 'music';
            if (fileType.includes('pdf') || fileType.includes('document') || fileType.includes('text')) return 'file-text';
            if (fileType.includes('zip') || fileType.includes('compressed')) return 'archive';
            return 'file';
        }

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, function(c) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c];
            });
        }

        function updateFileList() {
            fileListItems.innerHTML = '';
            const files = uploader.files;

            fileList.hidden = files.length === 0;
            queueCountLabel.textContent = files.length + ' file' + (files.length === 1 ? '' : 's') + ' selected';
            uploadButton.textContent = isUploading ? 'Uploading…' : 'Upload ' + (files.length || '');

            files.forEach(function(file) {
                const progress = uploadProgress[file.name] || 0;
                const isComplete = progress === 100;

                const row = document.createElement('div');
                row.className = 'ff-row';
                row.style.padding = '10px 0';
                row.innerHTML = `
                    <span class="ff-tile-icon ff-tile-icon-sm" style="width:38px;height:38px;border-radius:10px;">
                        <i data-lucide="${queueIcon(file.type)}" class="w-[18px] h-[18px]"></i>
                    </span>
                    <div class="ff-grow">
                        <div class="ff-row-between" style="gap:10px; flex-wrap:nowrap;">
                            <span class="ff-list-title">${escapeHtml(file.name)}</span>
                            <span class="ff-hint" style="flex-shrink:0;">${formatFileSize(file.size)}</span>
                        </div>
                        <div class="ff-progress ff-progress-sm" style="margin-top:8px;">
                            <div class="ff-progress-bar" style="width:${isComplete ? 100 : progress}%"></div>
                        </div>
                        ${isComplete ? '<div class="ff-hint ff-accent-text" style="margin-top:6px;font-weight:600;">Upload complete</div>' : ''}
                    </div>
                    <button type="button" class="ff-menu-btn delete-file" aria-label="Remove from queue">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                `;

                row.querySelector('.delete-file').addEventListener('click', function() {
                    uploader.removeFile(file);
                    delete uploadProgress[file.name];
                    uploadedFileNames.delete(file.name);
                    updateFileList();
                });

                fileListItems.appendChild(row);
            });

            window.ff.icons();
        }

        var uploader = new plupload.Uploader({
            runtimes: 'html5,html4',
            browse_button: 'fileInput',
            drop_element: 'dropZone',
            chunk_size: '{{ config('panel.upload.chunk_size', '5mb') }}',
            url: "{{ route('panel.uploadaction') }}",
            max_retries: 3,
            multipart: true,
            file_data_name: 'file',
            multipart_params: {
                '_token': '{{ csrf_token() }}'
            },
            filters: {
                max_file_size: 2147483648,
                mime_types: [{
                        title: "Image files",
                        extensions: "jpg,gif,png,jpeg,webp,svg,bmp,avif"
                    },
                    {
                        title: "Zip files",
                        extensions: "zip,rar,7z,tar,gz,tar.gz,tar.bz2"
                    },
                    {
                        title: "Document files",
                        extensions: "pdf,doc,docx,xls,xlsx,ppt,pptx,txt"
                    },
                    {
                        title: "Video files",
                        extensions: "mp4,avi,mov,flv,wmv,webm,mkv"
                    },
                    {
                        title: "Audio files",
                        extensions: "mp3,wav,ogg,flac,aac"
                    },
                    {
                        title: "All files",
                        extensions: "*"
                    }
                ]
            },
            init: {
                PostInit: function() {
                    updateFileList();
                },
                FilesAdded: function() {
                    updateFileList();
                },
                ChunkUploaded: function(up, file, response) {
                    try {
                        const res = JSON.parse(response.response);
                        if (res.ok === false || res.ok === 0 || res.error) {
                            console.error('Chunk upload failed:', res.info || res.error);
                            return false;
                        }
                    } catch (e) {
                        console.error('Failed to parse chunk response:', e);
                        return false;
                    }
                },
                UploadProgress: function(up, file) {
                    uploadProgress[file.name] = file.percent;
                    updateFileList();
                },
                FileUploaded: function(up, file, response) {
                    try {
                        const res = JSON.parse(response.response);
                        if (res.ok !== false && res.ok !== 0 && !res.error) {
                            uploadProgress[file.name] = 100;
                            uploadedFileNames.add(file.name);
                        } else {
                            console.error('File upload validation failed:', res.info || res.error);
                        }
                    } catch (e) {
                        console.error('Failed to parse file response:', e);
                    }
                    updateFileList();
                },
                UploadComplete: function() {
                    isUploading = false;
                    uploadButton.disabled = false;
                    updateFileList();
                },
                Error: function(up, err) {
                    console.error('Upload error:', err.message);
                    window.ff.toast('Upload error: ' + err.message, 'error', 4000);
                    isUploading = false;
                    uploadButton.disabled = false;
                    updateFileList();
                }
            }
        });

        uploader.init();

        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropZone.classList.add('is-dragging');
            dropZoneHeadline.textContent = 'Drop it right here';
        });

        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            dropZone.classList.remove('is-dragging');
            dropZoneHeadline.textContent = 'Drag & drop files here';
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropZone.classList.remove('is-dragging');
            dropZoneHeadline.textContent = 'Drag & drop files here';
            updateFileList();
        });

        // Initialize Tagify on Tags input (Allow spaces in tags, separate only by comma or Enter)
        const uploadTagsInput = document.getElementById('uploadTags');
        let tagifyInstance = null;
        if (uploadTagsInput && typeof Tagify !== 'undefined') {
            tagifyInstance = new Tagify(uploadTagsInput, {
                delimiters: ",",
                trim: true,
                maxTags: 25,
                placeholder: 'Type tags & press Comma or Enter',
                dropdown: {
                    enabled: 0
                }
            });
        }

        clearQueueButton.addEventListener('click', function() {
            uploader.files.slice().forEach(function(file) {
                uploader.removeFile(file);
            });
            uploadProgress = {};
            uploadedFileNames.clear();
            if (tagifyInstance) {
                tagifyInstance.removeAllTags();
            }
            updateFileList();
        });

        uploadButton.addEventListener('click', function() {
            if (isUploading) return;

            const pendingFiles = uploader.files.filter(function(file) {
                return !uploadedFileNames.has(file.name);
            });
            if (pendingFiles.length === 0) {
                window.ff.toast('No new files to upload.', 'info', 2500);
                return;
            }

            // Extract tags from Tagify or fallback input
            let tagString = '';
            if (tagifyInstance && Array.isArray(tagifyInstance.value)) {
                tagString = tagifyInstance.value.map(function(t) { return t.value; }).join(', ');
            } else if (uploadTagsInput) {
                tagString = uploadTagsInput.value.trim();
            }

            // Send the Details panel alongside every chunk so the upload handler
            // can start honouring them without any front-end change.
            uploader.settings.multipart_params = Object.assign({}, uploader.settings.multipart_params, {
                category_id: document.getElementById('uploadCategory').value || '',
                tags: tagString,
                is_hidden: document.getElementById('uploadHidden').checked ? 1 : 0
            });

            isUploading = true;
            uploadButton.disabled = true;
            uploadButton.textContent = 'Uploading…';
            uploader.start();
        });

        window.ff.icons();
    </script>
@endsection
