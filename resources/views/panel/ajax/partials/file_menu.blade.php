<button type="button" class="ff-dropdown-item previewbtn" data-file="{{ $eid }}">
    <i data-lucide="scan-eye" class="w-[15px] h-[15px]"></i> Preview (No Download)
</button>
@if ($file->is_editable)
<a href="{{ route('panel.editFile', $eid) }}" class="ff-dropdown-item">
    <i data-lucide="pencil-line" class="w-[15px] h-[15px]"></i> Edit in Editor
</a>
@endif
<button type="button" class="ff-dropdown-item"
    onclick="renameFile('{{ $eid }}', @js($file->name))">
    <i data-lucide="pencil" class="w-[15px] h-[15px]"></i> Rename
</button>
<button type="button" class="ff-dropdown-item" onclick="toggleHideFile('{{ $eid }}')">
    <i data-lucide="{{ $file->is_hidden ? 'eye' : 'eye-off' }}" class="w-[15px] h-[15px]"></i>
    {{ $file->is_hidden ? 'Unhide' : 'Hide' }}
</button>

<div class="ff-dropdown-divider"></div>

<a href="{{ route('panel.downloadFile', $eid) }}" class="ff-dropdown-item ff-download-link" download="{{ $file->name }}" data-filename="{{ $file->name }}">
    <i data-lucide="download" class="w-[15px] h-[15px]"></i> Download
</a>
<button type="button" class="ff-dropdown-item sharebtn" data-file="{{ $eid }}">
    <i data-lucide="share" class="w-[15px] h-[15px]"></i> Share
</button>

<div class="ff-dropdown-divider"></div>

<button type="button" class="ff-dropdown-item is-danger deletebtn" data-file="{{ $eid }}">
    <i data-lucide="trash-2" class="w-[15px] h-[15px]"></i> Delete
</button>
