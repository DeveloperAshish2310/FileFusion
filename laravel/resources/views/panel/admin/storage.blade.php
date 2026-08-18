@extends('layout.backend')

@push('title', 'Storage Pool Analytics & Quotas')

@section('content')
<div class="ff-page">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 class="ff-h1" style="display: flex; align-items: center; gap: 10px;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"/>
                    <line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6" y1="20" x2="6" y2="14"/>
                </svg>
                Storage Pool Analytics & Quotas
            </h1>
            <p class="ff-sub">Global disk usage distribution, top storage consumers ranking, and batch quota controls.</p>
        </div>
    </div>

    <!-- Storage Pool Overview Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="ff-card" style="padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted);">Total Used Pool</span>
            <div style="font-size: 28px; font-weight: 800; color: #10b981; margin-top: 4px;">{{ $stats['total_used_formatted'] }}</div>
            <div style="font-size: 12.5px; color: var(--ff-muted); margin-top: 6px;">Out of {{ $stats['total_quota_formatted'] }} quota</div>
        </div>

        <div class="ff-card" style="padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted);">Images</span>
            <div style="font-size: 24px; font-weight: 800; color: var(--ff-text); margin-top: 4px;">{{ $stats['image_bytes_formatted'] }}</div>
            <div style="font-size: 12.5px; color: var(--ff-muted); margin-top: 6px;">Photos & Graphics</div>
        </div>

        <div class="ff-card" style="padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted);">Videos</span>
            <div style="font-size: 24px; font-weight: 800; color: var(--ff-text); margin-top: 4px;">{{ $stats['video_bytes_formatted'] }}</div>
            <div style="font-size: 12.5px; color: var(--ff-muted); margin-top: 6px;">Clips & Movies</div>
        </div>

        <div class="ff-card" style="padding: 20px;">
            <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--ff-muted);">Documents</span>
            <div style="font-size: 24px; font-weight: 800; color: var(--ff-text); margin-top: 4px;">{{ $stats['doc_bytes_formatted'] }}</div>
            <div style="font-size: 12.5px; color: var(--ff-muted); margin-top: 6px;">PDFs & Text Files</div>
        </div>
    </div>

    <!-- Batch Quota Modifier -->
    <div class="ff-card" style="padding: 20px; margin-bottom: 24px; border-left: 4px solid #6366f1;">
        <h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 700; color: var(--ff-text);">⚡ 1-Click Batch Storage Quota Modifier</h3>
        <p style="margin: 0 0 16px 0; font-size: 13px; color: var(--ff-muted);">Mass update the maximum storage allocation (GB) for all users in the system.</p>
        <form action="{{ route('panel.admin.storage.batchQuota') }}" method="POST" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;" onsubmit="return confirm('Update storage quota for selected target users?');">
            @csrf
            <div style="display: flex; align-items: center; gap: 8px;">
                <label style="font-size: 13px; font-weight: 600; color: var(--ff-text);">New Quota:</label>
                <input type="number" step="any" min="0.01" name="quota_gb" value="25" class="ff-input" style="width: 100px; height: 38px;" required>
                <span style="font-size: 13px; font-weight: 600; color: var(--ff-text);">GB</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <label style="font-size: 13px; font-weight: 600; color: var(--ff-text);">Target Group / Role:</label>
                <select name="target_role" class="ff-select" style="height: 38px; width: 200px;">
                    <option value="user">Standard Users Only</option>
                    <option value="pro_user">Pro Users Only</option>
                    <option value="manager">Managers Only</option>
                    <option value="admin">Administrators</option>
                    <option value="super_admin">Super Admins</option>
                    <option value="all">All Users (Global)</option>
                </select>
            </div>
            <button type="submit" class="ff-btn is-primary" style="height: 38px; background: #6366f1; border-color: #6366f1;">
                Apply Batch Quota
            </button>
        </form>
    </div>

    <!-- Top Storage Users Table -->
    <div class="ff-card" style="padding: 0; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--ff-border); display: flex; align-items: center; justify-content: space-between;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--ff-text);">Top 10 Storage Consumers</h3>
            <a href="{{ route('panel.admin.users') }}" style="font-size: 13px; font-weight: 600; color: #6366f1; text-decoration: none;">View All Users &rarr;</a>
        </div>
        <div style="overflow-x: auto;">
            <table class="ff-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--ff-bg2); border-bottom: 1px solid var(--ff-border); text-align: left;">
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--ff-muted); text-transform: uppercase;">Rank & User</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--ff-muted); text-transform: uppercase;">Storage Used</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--ff-muted); text-transform: uppercase;">Quota Limit</th>
                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: var(--ff-muted); text-transform: uppercase;">Pool Utilization</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topStorageUsers as $idx => $u)
                        @php
                            $usedFormatted = $u->formatBytes($u->storage_used);
                            $quotaFormatted = $u->formatBytes($u->storage_quota);
                            $pct = $u->storage_quota > 0 ? min(100, round(($u->storage_used / $u->storage_quota) * 100, 1)) : 0;
                        @endphp
                        <tr style="border-bottom: 1px solid var(--ff-border);">
                            <td style="padding: 14px 16px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <span style="font-size: 14px; font-weight: 800; color: var(--ff-muted); width: 24px;">#{{ $idx + 1 }}</span>
                                    <div>
                                        <div style="font-size: 14px; font-weight: 600; color: var(--ff-text);">{{ $u->name }}</div>
                                        <div style="font-size: 11.5px; color: var(--ff-muted);">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 14px 16px; font-size: 13.5px; font-weight: 700; color: var(--ff-text);">
                                {{ $usedFormatted }}
                            </td>
                            <td style="padding: 14px 16px; font-size: 13px; color: var(--ff-muted);">
                                {{ $quotaFormatted }}
                            </td>
                            <td style="padding: 14px 16px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="flex: 1; height: 8px; background: var(--ff-bg2); border-radius: 4px; overflow: hidden; max-width: 140px;">
                                        <div style="height: 100%; width: {{ $pct }}%; background: {{ $pct > 85 ? '#ef4444' : '#10b981' }};"></div>
                                    </div>
                                    <span style="font-size: 12.5px; font-weight: 700; color: var(--ff-text);">{{ $pct }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
