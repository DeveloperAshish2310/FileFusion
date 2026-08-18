@extends('layout.backend')

@push('title', 'Global File Vault Manager')

@section('content')
<div class="ff-page">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 class="ff-h1" style="display: flex; align-items: center; gap: 10px;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                Global File Vault Manager
            </h1>
            <p class="ff-sub">Inspect, search, and audit all uploaded files across all user accounts in the system.</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <div class="ff-badge" style="background: rgba(99, 102, 241, 0.12); color: #6366f1; padding: 8px 14px; font-size: 13px; font-weight: 700; border-radius: 8px;">
                Total Files: {{ number_format($totalFileCount) }}
            </div>
            <div class="ff-badge" style="background: rgba(16, 185, 129, 0.12); color: #10b981; padding: 8px 14px; font-size: 13px; font-weight: 700; border-radius: 8px;">
                Total Volume: {{ \App\Models\User::formatBytes($totalFileBytes) }}
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="ff-card" style="padding: 16px; margin-bottom: 24px;">
        <form action="{{ route('panel.admin.files') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 240px; position: relative;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by filename, user email, or name..." class="ff-input" style="width: 100%; height: 40px; padding-left: 36px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; left: 12px; top: 12px; color: var(--ff-muted);"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </div>
            <select name="type" class="ff-select" style="height: 40px; width: 160px;">
                <option value="">All Types</option>
                <option value="image" {{ request('type') == 'image' ? 'selected' : '' }}>Images</option>
                <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>Videos</option>
                <option value="audio" {{ request('type') == 'audio' ? 'selected' : '' }}>Audio</option>
                <option value="pdf" {{ request('type') == 'pdf' ? 'selected' : '' }}>PDF Documents</option>
                <option value="code" {{ request('type') == 'code' ? 'selected' : '' }}>Code / Text</option>
            </select>
            <button type="submit" class="ff-btn is-primary" style="height: 40px; background: #6366f1; border-color: #6366f1;">
                Filter Files
            </button>
            @if(request()->hasAny(['search', 'type']))
                <a href="{{ route('panel.admin.files') }}" class="ff-btn" style="height: 40px; line-height: 24px;">Reset</a>
            @endif
        </form>
    </div>

    <!-- Files Table -->
    <div class="ff-card" style="padding: 0; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="ff-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--ff-bg2); border-bottom: 1px solid var(--ff-border); text-align: left;">
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--ff-muted); text-transform: uppercase;">File Details</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--ff-muted); text-transform: uppercase;">Owner</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--ff-muted); text-transform: uppercase;">Size</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--ff-muted); text-transform: uppercase;">Uploaded At</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--ff-muted); text-transform: uppercase; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($files as $f)
                        <tr style="border-bottom: 1px solid var(--ff-border);">
                            <td style="padding: 14px 16px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(99, 102, 241, 0.12); color: #6366f1; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    </div>
                                    <div style="overflow: hidden; text-overflow: ellipsis; max-width: 280px;">
                                        <div style="font-size: 14px; font-weight: 600; color: var(--ff-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: flex; align-items: center; gap: 6px;">
                                            <span>{{ $f->name }}</span>
                                            @if($f->is_shared || $f->isCurrentlyShared())
                                                <span class="ff-badge" style="background: rgba(99, 102, 241, 0.15); color: #6366f1; border: 1px solid rgba(99, 102, 241, 0.3); padding: 1px 6px; font-size: 10px; font-weight: 700; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;" title="This file is currently shared">
                                                    Shared
                                                </span>
                                            @endif
                                        </div>
                                        <div style="font-size: 11.5px; color: var(--ff-muted);">MIME: {{ $f->type ?? 'unknown' }}</div>
                                    </div>

                                </div>
                            </td>
                            <td style="padding: 14px 16px;">
                                <div style="font-size: 13.5px; font-weight: 600; color: var(--ff-text);">{{ $f->user->name ?? 'System' }}</div>
                                <div style="font-size: 11.5px; color: var(--ff-muted);">{{ $f->user->email ?? 'N/A' }}</div>
                            </td>
                            <td style="padding: 14px 16px; font-size: 13.5px; font-weight: 700; color: var(--ff-text);">
                                {{ \App\Models\User::formatBytes($f->size) }}
                            </td>

                            <td style="padding: 14px 16px; font-size: 13px; color: var(--ff-muted);">
                                {{ $f->created_at ? $f->created_at->format('M d, Y · H:i') : 'N/A' }}
                            </td>
                            <td style="padding: 14px 16px; text-align: right;">
                                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <a href="{{ route('panel.previewFile', $f->id) }}" target="_blank" class="ff-btn" style="padding: 4px 10px; font-size: 12px;">Preview</a>
                                    <a href="{{ route('panel.permanentDeleteFile', $f->id) }}" onclick="return confirm('Permanently delete file {{ addslashes($f->name) }}?');" class="ff-btn" style="padding: 4px 10px; font-size: 12px; background: rgba(239,68,68,0.12); color: #ef4444; border-color: rgba(239,68,68,0.3);">Delete</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 40px; text-align: center; color: var(--ff-muted);">
                                No files found matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($files->hasPages())
            <div style="padding: 16px; border-top: 1px solid var(--ff-border);">
                {{ $files->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
