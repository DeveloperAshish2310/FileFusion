@extends('layout.backend')
@push('title', 'Super Admin Overview')

@section('content')
<div class="ff-page">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <h1 class="ff-h1" style="display: flex; align-items: center; gap: 10px;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                </svg>
                Super Admin Console
            </h1>
            <p class="ff-sub">Global server metrics, storage allocation, and user controls.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('panel.admin.landingPage') }}" class="ff-btn" style="background: #e0392e; color: #ffffff; border-color: #e0392e; display: inline-flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                Edit Landing Page
            </a>

            <a href="{{ route('panel.admin.users') }}" class="ff-btn is-primary" style="background: #6366f1; border-color: #6366f1; display: inline-flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Manage Users
            </a>
        </div>
    </div>


    {{-- Top Metrics Grid --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        {{-- Total Users --}}
        <div class="ff-card" style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px;">Total Users</span>
                    <div style="font-size: 28px; font-weight: 800; margin-top: 6px;">{{ number_format($stats['total_users']) }}</div>
                </div>
                <div style="background: rgba(99, 102, 241, 0.12); color: #6366f1; padding: 10px; border-radius: 10px;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
            </div>
            <div style="margin-top: 12px; font-size: 13px; color: var(--ff-muted); display: flex; gap: 12px;">
                <span style="color: #10b981; font-weight: 600;">● {{ $stats['active_users'] }} Active</span>
                <span style="color: #ef4444; font-weight: 600;">● {{ $stats['suspended_users'] }} Suspended</span>
            </div>
        </div>

        {{-- Total Storage Used --}}
        <div class="ff-card" style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px;">Global Storage</span>
                    <div style="font-size: 28px; font-weight: 800; margin-top: 6px;">{{ $stats['storage_used_formatted'] }}</div>
                </div>
                <div style="background: rgba(16, 185, 129, 0.12); color: #10b981; padding: 10px; border-radius: 10px;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"/><rect x="2" y="14" width="20" height="8" rx="2" ry="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
                </div>
            </div>
            <div style="margin-top: 12px; font-size: 13px; color: var(--ff-muted);">
                Allocated Pool: <strong>{{ $stats['storage_quota_formatted'] }}</strong> ({{ $stats['storage_percentage'] }}% used)
            </div>
        </div>

        {{-- Total Files --}}
        <div class="ff-card" style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px;">Files Stored</span>
                    <div style="font-size: 28px; font-weight: 800; margin-top: 6px;">{{ number_format($stats['total_files']) }}</div>
                </div>
                <div style="background: rgba(59, 130, 246, 0.12); color: #3b82f6; padding: 10px; border-radius: 10px;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
            </div>
            <div style="margin-top: 12px; font-size: 13px; color: var(--ff-muted);">
                Encrypted at Rest (AES-256-GCM)
            </div>
        </div>

        {{-- Server Info --}}
        <div class="ff-card" style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--ff-muted); letter-spacing: 0.5px;">Environment</span>
                    <div style="font-size: 20px; font-weight: 800; margin-top: 8px;">PHP {{ $stats['server_php'] }}</div>
                </div>
                <div style="background: rgba(245, 158, 11, 0.12); color: #f59e0b; padding: 10px; border-radius: 10px;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
            </div>
            <div style="margin-top: 12px; font-size: 13px; color: var(--ff-muted);">
                OS: <strong>{{ $stats['server_os'] }}</strong>
            </div>
        </div>
    </div>

    {{-- File Distribution & Storage Usage --}}
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 24px;">
        {{-- Recent Users --}}
        <div class="ff-card">
            <div class="ff-card-head" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 class="ff-card-title">Recent User Registrations</h3>
                <a href="{{ route('panel.admin.users') }}" class="ff-viewall">View All Users →</a>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--ff-border); text-align: left; color: var(--ff-muted);">
                            <th style="padding: 12px 16px;">User</th>
                            <th style="padding: 12px 16px;">Role</th>
                            <th style="padding: 12px 16px;">Status</th>
                            <th style="padding: 12px 16px;">Storage Used</th>
                            <th style="padding: 12px 16px;">Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stats['recent_users'] as $u)
                            <tr style="border-bottom: 1px solid var(--ff-border-subtle);">
                                <td style="padding: 12px 16px; font-weight: 600;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="ff-avatar" style="width: 28px; height: 28px; font-size: 11px;">{{ strtoupper(substr($u->name, 0, 1)) }}</span>
                                        <div>
                                            <div>{{ $u->name }}</div>
                                            <div style="font-size: 11px; color: var(--ff-muted); font-weight: 400;">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 12px 16px;">
                                    @if ($u->isSuperAdmin())
                                        <span style="background: rgba(99, 102, 241, 0.15); color: #6366f1; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">SUPER ADMIN</span>
                                    @else
                                        <span style="background: var(--ff-surface); color: var(--ff-muted); padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">USER</span>
                                    @endif
                                </td>
                                <td style="padding: 12px 16px;">
                                    @if ($u->isActive())
                                        <span style="color: #10b981; font-weight: 600;">● Active</span>
                                    @else
                                        <span style="color: #ef4444; font-weight: 600;">● Suspended</span>
                                    @endif
                                </td>
                                <td style="padding: 12px 16px;">
                                    {{ $u->getStorageUsedFormatted() }} / {{ $u->getStorageQuotaFormatted() }}
                                </td>
                                <td style="padding: 12px 16px; color: var(--ff-muted);">
                                    {{ $u->created_at ? $u->created_at->diffForHumans() : 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding: 20px; text-align: center; color: var(--ff-muted);">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- File Categories --}}
        <div class="ff-card">
            <div class="ff-card-head">
                <h3 class="ff-card-title">File Types Breakdown</h3>
            </div>
            <div style="padding: 16px; display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px;">
                        <span>📷 Images</span>
                        <strong>{{ $stats['images_count'] }}</strong>
                    </div>
                </div>
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px;">
                        <span>🎬 Videos</span>
                        <strong>{{ $stats['videos_count'] }}</strong>
                    </div>
                </div>
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px;">
                        <span>📄 Documents / PDFs</span>
                        <strong>{{ $stats['documents_count'] }}</strong>
                    </div>
                </div>
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px;">
                        <span>🎵 Audio Files</span>
                        <strong>{{ $stats['audio_count'] }}</strong>
                    </div>
                </div>
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px;">
                        <span>📦 Archives & Other</span>
                        <strong>{{ $stats['others_count'] }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
