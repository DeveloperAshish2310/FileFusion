@extends('errors.layout')

@section('title', '413 - Content Too Large')

@section('content')
    <!-- Graphic / Visual Icon -->
    <div style="position:relative; width:180px; height:180px; display:flex; align-items:center; justify-content:center; animation:ff-fade 0.6s ease both;">
        <div style="position:absolute; inset:0; border-radius:50%; background:radial-gradient(circle, color-mix(in srgb, var(--ff-accent) 25%, transparent) 0%, transparent 70%); border:1px dashed var(--ff-radar-ring); animation:ff-pulse 3s ease-in-out infinite;"></div>
        
        <div style="position:relative; z-index:2; width:90px; height:90px; border-radius:24px; background:linear-gradient(135deg, color-mix(in srgb, var(--ff-accent) 20%, var(--ff-bg-card, #1c1c1f)), var(--ff-bg-card, #1c1c1f)); border:1px solid var(--ff-border); display:flex; flex-direction:column; align-items:center; justify-content:center; box-shadow:0 12px 30px -10px var(--ff-accent-glow);">
            <i data-lucide="hard-drive-upload" style="width:36px; height:36px; color:var(--ff-accent); stroke-width:1.8;"></i>
            <span style="font-size:10px; font-weight:800; color:var(--ff-accent); letter-spacing:0.5px; margin-top:4px;">MAX LIMIT</span>
        </div>

        <div style="position:absolute; right:15px; top:15px; width:32px; height:32px; border-radius:50%; background:#ef4444; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:14px; box-shadow:0 4px 12px rgba(239, 68, 68, 0.4); z-index:3;">
            !
        </div>
    </div>

    <!-- Error Code & Badges -->
    <div class="ff-error-code">413</div>
    <div class="ff-error-tag" style="border-color:rgba(239, 68, 68, 0.3); color:#ef4444; background:rgba(239, 68, 68, 0.08);">Payload Too Large</div>

    <!-- Headings & Copy -->
    <h1 class="ff-error-heading ff-stag" style="animation-delay:0.14s;">Upload Payload Exceeds Server Limit</h1>
    <p class="ff-error-desc ff-stag" style="animation-delay:0.22s;">
        The file or data stream sent in this single request exceeds the server's maximum raw POST threshold (<strong>{{ ini_get('post_max_size') ?: '8M' }}</strong>).
    </p>

    <!-- Technical Specs & Recommended Solution -->
    <div class="ff-stag" style="animation-delay:0.26s; margin:16px auto 24px; max-width:440px; padding:12px 18px; border-radius:12px; background:var(--ff-bg-card, rgba(255,255,255,0.03)); border:1px solid var(--ff-border); text-align:left; font-size:12.5px; color:var(--ff-text-muted);">
        <div style="font-weight:700; color:var(--ff-text-main, #fff); margin-bottom:6px; display:flex; align-items:center; gap:6px;">
            <i data-lucide="zap" style="width:14px; height:14px; color:var(--ff-accent);"></i> Pro Tip: Use Chunked Slicing
        </div>
        <div>
            To upload large media, archives, or high-resolution videos (up to <strong>2 GB</strong> per file), use FileFusion's high-speed <strong>Chunked Uploader</strong> which automatically slices large files into 5MB encrypted chunks.
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="ff-error-actions ff-stag" style="animation-delay:0.32s;">
        <a href="{{ route('panel.uploadfile') }}" class="ff-btn-p">
            <i data-lucide="upload-cloud" class="w-4 h-4"></i> Open Chunked Uploader
        </a>
        <a href="{{ route('panel.dashboard') }}" class="ff-btn-s">
            <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
        </a>
    </div>
@endsection
