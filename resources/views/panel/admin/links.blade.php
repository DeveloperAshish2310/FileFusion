@extends('layout.backend')

@push('title', 'Global Link Audit Console')

@section('content')
<div class="ff-page">
    <div class="ff-admin-header">
        <div>
            <h1 class="ff-h1" style="display: flex; align-items: center; gap: 10px;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                </svg>
                Global Link Audit Console
            </h1>
            <p class="ff-sub">Search, test, and audit all saved URLs and web bookmarks across all accounts in the system.</p>
        </div>
        <div class="ff-admin-actions">
            <div class="ff-badge" style="background: rgba(99, 102, 241, 0.12); color: #6366f1; padding: 8px 14px; font-size: 13px; font-weight: 700; border-radius: 8px;">
                Total Saved Links: {{ number_format($totalLinkCount) }}
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="ff-card ff-admin-toolbar-card">
        <form action="{{ route('panel.admin.links') }}" method="GET" class="ff-admin-toolbar-form">
            <div class="ff-admin-search-wrap">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by link title, URL, domain, or owner..." class="ff-input" style="width: 100%; height: 40px; padding-left: 36px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; left: 12px; top: 12px; color: var(--ff-muted);"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </div>
            <button type="submit" class="ff-btn is-primary" style="height: 40px; background: #6366f1; border-color: #6366f1;">
                Search Links
            </button>
            @if(request()->has('search'))
                <a href="{{ route('panel.admin.links') }}" class="ff-btn" style="height: 40px; line-height: 24px;">Clear Search</a>
            @endif
        </form>
    </div>

    <!-- Links Table -->
    <div class="ff-card" style="padding: 0; overflow: hidden;">
        <div class="ff-admin-table-wrap">
            <table class="ff-admin-table">
                <thead>
                    <tr style="background: var(--ff-bg2); border-bottom: 1px solid var(--ff-border); text-align: left;">
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--ff-muted); text-transform: uppercase;">Website & URL</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--ff-muted); text-transform: uppercase;">Owner</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--ff-muted); text-transform: uppercase;">Category</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--ff-muted); text-transform: uppercase;">Saved At</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--ff-muted); text-transform: uppercase; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($links as $l)
                        <tr style="border-bottom: 1px solid var(--ff-border);">
                            <td style="padding: 14px 16px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(99, 102, 241, 0.12); color: #6366f1; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                    </div>
                                    <div style="overflow: hidden; max-width: 380px;">
                                        <div style="font-size: 14px; font-weight: 600; color: var(--ff-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $l->title ?: ($l->url ?: 'Untitled Link') }}</div>
                                        <a href="{{ $l->url }}" target="_blank" rel="noopener" style="font-size: 11.5px; color: #6366f1; text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;">{{ $l->url }}</a>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 14px 16px;">
                                <div style="font-size: 13.5px; font-weight: 600; color: var(--ff-text);">{{ $l->user->name ?? 'System' }}</div>
                                <div style="font-size: 11.5px; color: var(--ff-muted);">{{ $l->user->email ?? 'N/A' }}</div>
                            </td>
                            <td style="padding: 14px 16px;">
                                <span class="ff-badge" style="background: var(--ff-bg2); color: var(--ff-text); padding: 4px 10px; font-size: 12px; border-radius: 6px; border: 1px solid var(--ff-border);">
                                    {{ $l->category->title ?? ($l->category->name ?? 'Uncategorized') }}
                                </span>
                            </td>
                            <td style="padding: 14px 16px; font-size: 13px; color: var(--ff-muted);">
                                {{ $l->created_at ? $l->created_at->format('M d, Y') : 'N/A' }}
                            </td>
                            <td style="padding: 14px 16px; text-align: right;">
                                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <a href="{{ $l->url }}" target="_blank" rel="noopener" class="ff-btn" style="padding: 4px 10px; font-size: 12px;">Visit URL</a>
                                    <form action="{{ route('panel.permanentDeleteLink', $l->id) }}" method="POST" onsubmit="return confirm('Delete link {{ addslashes($l->title ?: $l->url) }}?');" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="ff-btn" style="padding: 4px 10px; font-size: 12px; background: rgba(239,68,68,0.12); color: #ef4444; border-color: rgba(239,68,68,0.3);">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 40px; text-align: center; color: var(--ff-muted);">
                                No links found matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($links->hasPages())
            <div style="padding: 16px; border-top: 1px solid var(--ff-border);">
                {{ $links->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
