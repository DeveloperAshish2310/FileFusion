@extends('layout.backend')

@push('title', 'Security & Activity Audit Trail')

@section('content')
<div class="ff-page">
    <div class="ff-admin-header">
        <div>
            <h1 class="ff-h1" style="display: flex; align-items: center; gap: 10px;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--ff-accent)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    <polyline points="9 12 11 14 15 10"></polyline>
                </svg>
                Security &amp; Activity Audit Trail
            </h1>
            <p class="ff-sub">Comprehensive forensics log: client IP addresses, action types, timestamps, HTTP endpoints, and security traces.</p>
        </div>
        <div class="ff-admin-actions">
            <a href="{{ route('panel.admin.activityLogs.export', request()->query()) }}" class="ff-btn" style="display: inline-flex; align-items: center; gap: 6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export CSV
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="ff-admin-kpi-grid">
        <div class="ff-card" style="padding: 16px; display: flex; align-items: center; gap: 14px;">
            <span class="ff-section-icon" style="background: var(--ff-icon-bg); color: var(--ff-accent); width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            </span>
            <div>
                <div style="font-size: 20px; font-weight: 800; color: var(--ff-text); font-family: var(--ff-font-display);">{{ number_format($stats['total_logs']) }}</div>
                <div style="font-size: 12px; color: var(--ff-text-2);">Total Audit Events</div>
            </div>
        </div>

        <div class="ff-card" style="padding: 16px; display: flex; align-items: center; gap: 14px;">
            <span class="ff-section-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981; width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
            </span>
            <div>
                <div style="font-size: 20px; font-weight: 800; color: var(--ff-text); font-family: var(--ff-font-display);">{{ number_format($stats['today_logs']) }}</div>
                <div style="font-size: 12px; color: var(--ff-text-2);">Today's Activity</div>
            </div>
        </div>

        <div class="ff-card" style="padding: 16px; display: flex; align-items: center; gap: 14px;">
            <span class="ff-section-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            </span>
            <div>
                <div style="font-size: 20px; font-weight: 800; color: var(--ff-text); font-family: var(--ff-font-display);">{{ number_format($stats['warnings']) }}</div>
                <div style="font-size: 12px; color: var(--ff-text-2);">Security Warnings</div>
            </div>
        </div>

        <div class="ff-card" style="padding: 16px; display: flex; align-items: center; gap: 14px;">
            <span class="ff-section-icon" style="background: rgba(239, 68, 68, 0.15); color: var(--ff-danger); width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            </span>
            <div>
                <div style="font-size: 20px; font-weight: 800; color: var(--ff-text); font-family: var(--ff-font-display);">{{ number_format($stats['dangers']) }}</div>
                <div style="font-size: 12px; color: var(--ff-text-2);">Critical Alerts</div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="ff-card ff-admin-toolbar-card">
        <form action="{{ route('panel.admin.activityLogs') }}" method="GET" class="ff-admin-toolbar-form">
            <div class="ff-admin-search-wrap">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by IP, description, action, email..." class="ff-input" style="width: 100%; height: 40px; padding-left: 36px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; left: 12px; top: 12px; color: var(--ff-text-soft);"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </div>

            <select name="action" class="ff-select" style="height: 40px; width: 170px;">
                <option value="all">All Action Categories</option>
                <option value="auth" {{ request('action') === 'auth' ? 'selected' : '' }}>🔐 Authentication</option>
                <option value="2fa" {{ request('action') === '2fa' ? 'selected' : '' }}>📱 Two-Factor (2FA)</option>
                <option value="vault" {{ request('action') === 'vault' ? 'selected' : '' }}>🔒 Hidden Vaults</option>
                <option value="password" {{ request('action') === 'password' ? 'selected' : '' }}>🔑 Password Reveals</option>
                <option value="file" {{ request('action') === 'file' ? 'selected' : '' }}>📁 Files Operations</option>
                <option value="link" {{ request('action') === 'link' ? 'selected' : '' }}>🔗 Links Operations</option>
                <option value="admin" {{ request('action') === 'admin' ? 'selected' : '' }}>⚡ Super Admin</option>
                <option value="security" {{ request('action') === 'security' ? 'selected' : '' }}>🛡️ Key Rotation</option>
            </select>

            <select name="status" class="ff-select" style="height: 40px; width: 140px;">
                <option value="all">All Statuses</option>
                <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>✓ Success</option>
                <option value="warning" {{ request('status') === 'warning' ? 'selected' : '' }}>⚠️ Warning</option>
                <option value="danger" {{ request('status') === 'danger' ? 'selected' : '' }}>✗ Danger</option>
                <option value="info" {{ request('status') === 'info' ? 'selected' : '' }}>ℹ️ Info</option>
            </select>

            <button type="submit" class="ff-btn ff-btn-primary" style="height: 40px;">
                Filter Logs
            </button>
            @if(request()->hasAny(['search', 'action', 'status', 'ip', 'user_id']))
                <a href="{{ route('panel.admin.activityLogs') }}" class="ff-btn" style="height: 40px; line-height: 24px;">Reset</a>
            @endif
        </form>
    </div>

    <!-- Logs Table -->
    <div class="ff-card" style="padding: 0; overflow: hidden; margin-bottom: 24px;">
        <div class="ff-admin-table-wrap">
            <table class="ff-table" style="width: 100%; min-width: 1260px; border-collapse: collapse; table-layout: fixed;">
                <colgroup>
                    <col style="width: 150px;">
                    <col style="width: 235px;">
                    <col style="width: 345px;">
                    <col style="width: 180px;">
                    <col style="width: 125px;">
                    <col style="width: 145px;">
                    <col style="width: 80px;">
                </colgroup>
                <thead>
                    <tr style="background: var(--ff-bg-2); border-bottom: 1px solid var(--ff-border); text-align: left;">
                        <th style="padding: 12px 16px; font-size: 11.5px; font-weight: 700; color: var(--ff-text-soft); text-transform: uppercase;">Timestamp</th>
                        <th style="padding: 12px 16px; font-size: 11.5px; font-weight: 700; color: var(--ff-text-soft); text-transform: uppercase;">Action</th>
                        <th style="padding: 12px 16px; font-size: 11.5px; font-weight: 700; color: var(--ff-text-soft); text-transform: uppercase;">Description</th>
                        <th style="padding: 12px 16px; font-size: 11.5px; font-weight: 700; color: var(--ff-text-soft); text-transform: uppercase;">User</th>
                        <th style="padding: 12px 16px; font-size: 11.5px; font-weight: 700; color: var(--ff-text-soft); text-transform: uppercase;">Client IP</th>
                        <th style="padding: 12px 16px; font-size: 11.5px; font-weight: 700; color: var(--ff-text-soft); text-transform: uppercase;">Device &amp; OS</th>
                        <th style="padding: 12px 16px; font-size: 11.5px; font-weight: 700; color: var(--ff-text-soft); text-transform: uppercase; text-align: right;">Trace</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $desc = $log->description;
                            if (preg_match('/eyJ[a-zA-Z0-9_\-\+\/=]+/', $desc, $matches)) {
                                try {
                                    $decryptedPart = \App\Helpers\Encryptor::decrypt($matches[0]);
                                    if ($decryptedPart) {
                                        $desc = str_replace($matches[0], $decryptedPart, $desc);
                                    }
                                } catch (\Exception $e) {}
                            }
                        @endphp
                        <tr style="border-bottom: 1px solid var(--ff-border); transition: background 0.15s ease;">
                            <td style="padding: 12px 16px; font-size: 12px; color: var(--ff-text-2); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <div style="font-weight: 600; color: var(--ff-text);">{{ $log->created_at->diffForHumans() }}</div>
                                <div style="font-size: 11px; color: var(--ff-text-soft);">{{ $log->created_at->format('d M Y, H:i:s') }}</div>
                            </td>

                            <td style="padding: 12px 16px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                @php
                                    $statusStyle = match($log->status) {
                                        'success' => 'background: rgba(16, 185, 129, 0.12); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.25);',
                                        'warning' => 'background: rgba(245, 158, 11, 0.12); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.25);',
                                        'danger' => 'background: var(--ff-danger-soft); color: var(--ff-danger); border: 1px solid var(--ff-danger-border);',
                                        default => 'background: var(--ff-bg-2); color: var(--ff-text-2); border: 1px solid var(--ff-border);',
                                    };
                                @endphp
                                <span title="{{ $log->action }}" style="display: inline-block; max-width: 100%; overflow: hidden; text-overflow: ellipsis; vertical-align: middle; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-family: var(--ff-font-mono); font-weight: 700; {{ $statusStyle }}">
                                    {{ $log->action }}
                                </span>
                            </td>

                            <td style="padding: 12px 16px; font-size: 13px; color: var(--ff-text); line-height: 1.4; word-break: break-word; overflow-wrap: anywhere;">
                                <div>{{ $desc }}</div>
                                @if($log->method && $log->url)
                                    <div style="font-size: 11px; color: var(--ff-text-soft); font-family: var(--ff-font-mono); margin-top: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <span style="font-weight: 700;">{{ $log->method }}</span> {{ $log->url }}
                                    </div>
                                @endif
                            </td>

                            <td style="padding: 12px 16px; font-size: 12.5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                @if($log->user)
                                    <a href="{{ route('panel.admin.activityLogs', ['user_id' => $log->user->id]) }}" style="font-weight: 600; color: var(--ff-accent); text-decoration: none; display: block; overflow: hidden; text-overflow: ellipsis;" title="{{ $log->user->name }}">
                                        {{ $log->user->name }}
                                    </a>
                                    <div style="font-size: 11px; color: var(--ff-text-soft); overflow: hidden; text-overflow: ellipsis;" title="{{ $log->user->email }}">{{ $log->user->email }}</div>
                                @elseif($log->user_email)
                                    <span style="color: var(--ff-text); display: block; overflow: hidden; text-overflow: ellipsis;" title="{{ $log->user_email }}">{{ $log->user_email }}</span>
                                    <div style="font-size: 11px; color: var(--ff-text-soft);">Guest / Target</div>
                                @else
                                    <span style="color: var(--ff-text-soft);">System / Guest</span>
                                @endif
                            </td>

                            <td style="padding: 12px 16px; font-size: 12px; font-family: var(--ff-font-mono); white-space: nowrap;">
                                <a href="{{ route('panel.admin.activityLogs', ['ip' => $log->ip_address]) }}" style="color: var(--ff-text); text-decoration: none; font-weight: 600;" title="Filter by this IP">
                                    {{ $log->ip_address ?: '—' }}
                                </a>
                            </td>

                            <td style="padding: 12px 16px; font-size: 12px; color: var(--ff-text-2); white-space: nowrap;">
                                <div style="font-weight: 600; color: var(--ff-text);">{{ $log->device ?: 'Desktop' }} • {{ $log->os ?: 'OS' }}</div>
                                <div style="font-size: 11px; color: var(--ff-text-soft);">{{ $log->browser ?: 'Browser' }}</div>
                            </td>

                            <td style="padding: 12px 16px; text-align: right; white-space: nowrap;">
                                <button type="button" class="ff-btn ff-btn-sm btn-inspect-trace"
                                    data-id="{{ $log->id }}"
                                    data-action="{{ $log->action }}"
                                    data-desc="{{ $desc }}"
                                    data-status="{{ $log->status }}"
                                    data-ip="{{ $log->ip_address }}"
                                    data-time="{{ $log->created_at->format('d M Y, H:i:s T') }}"
                                    data-user="{{ $log->user ? $log->user->email : ($log->user_email ?: 'Guest / System') }}"
                                    data-method="{{ $log->method }}"
                                    data-url="{{ $log->url }}"
                                    data-ua="{{ $log->user_agent }}"
                                    data-context="{{ json_encode($log->context) }}">
                                    🔍 Trace
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 40px 16px; text-align: center; color: var(--ff-text-soft);">
                                <div style="font-size: 16px; font-weight: 700; margin-bottom: 4px;">No audit logs found</div>
                                <p style="font-size: 13px; margin: 0;">Try adjusting your search terms or filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div style="padding: 16px; border-top: 1px solid var(--ff-border);">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Trace Inspection Modal --}}
<div id="modal-trace-inspect" class="ff-modal-backdrop" hidden>
    <div class="ff-modal" style="width: 580px; max-width: 95%;">
        <div class="ff-row-between" style="margin-bottom: 16px;">
            <div>
                <div class="ff-modal-title">🔍 Forensics &amp; Audit Trace</div>
                <div class="ff-modal-sub" id="trace-modal-sub">Event details and request payload</div>
            </div>
            <button type="button" class="ff-modal-close" id="btn-close-trace" aria-label="Close modal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 18px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; background: var(--ff-bg-2); padding: 12px; border-radius: 10px; border: 1px solid var(--ff-border); font-size: 12.5px;">
                <div>
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--ff-text-soft);">Action</div>
                    <code id="trace-action" style="font-weight: 700; color: var(--ff-accent);"></code>
                </div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--ff-text-soft);">Timestamp</div>
                    <span id="trace-time" style="font-weight: 600; color: var(--ff-text);"></span>
                </div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--ff-text-soft);">Client IP</div>
                    <code id="trace-ip" style="font-weight: 700; color: var(--ff-text);"></code>
                </div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--ff-text-soft);">User</div>
                    <span id="trace-user" style="font-weight: 600; color: var(--ff-text);"></span>
                </div>
            </div>

            <div>
                <label class="ff-label" style="font-size: 11.5px;">Description</label>
                <div id="trace-description" style="padding: 10px 12px; border-radius: 8px; background: var(--ff-bg-2); border: 1px solid var(--ff-border); font-size: 13px; color: var(--ff-text); line-height: 1.4;"></div>
            </div>

            <div>
                <label class="ff-label" style="font-size: 11.5px;">HTTP Endpoint</label>
                <div style="padding: 8px 12px; border-radius: 8px; background: var(--ff-bg-2); border: 1px solid var(--ff-border); font-family: var(--ff-font-mono); font-size: 12px; color: var(--ff-text);">
                    <span id="trace-method" style="font-weight: 800; color: var(--ff-accent);"></span> <span id="trace-url"></span>
                </div>
            </div>

            <div>
                <label class="ff-label" style="font-size: 11.5px;">User-Agent Header</label>
                <div id="trace-ua" style="padding: 8px 12px; border-radius: 8px; background: var(--ff-bg-2); border: 1px solid var(--ff-border); font-family: var(--ff-font-mono); font-size: 11px; color: var(--ff-text-2); word-break: break-all;"></div>
            </div>

            <div>
                <label class="ff-label" style="font-size: 11.5px;">Structured Context Metadata (JSON)</label>
                <pre id="trace-context" style="margin: 0; padding: 10px 12px; border-radius: 8px; background: var(--ff-bg-2); border: 1px solid var(--ff-border); font-family: var(--ff-font-mono); font-size: 11.5px; color: var(--ff-accent); max-height: 140px; overflow-y: auto;"></pre>
            </div>
        </div>

        <div class="ff-row" style="justify-content: flex-end;">
            <button type="button" class="ff-btn ff-btn-primary" id="btn-done-trace">Close</button>
        </div>
    </div>
</div>
@endsection

@section('push-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modal-trace-inspect');
        const btnClose = document.getElementById('btn-close-trace');
        const btnDone = document.getElementById('btn-done-trace');

        function closeModal() {
            modal.hidden = true;
        }

        if (btnClose) btnClose.addEventListener('click', closeModal);
        if (btnDone) btnDone.addEventListener('click', closeModal);
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeModal();
            });
        }

        document.querySelectorAll('.btn-inspect-trace').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('trace-action').textContent = btn.dataset.action || '—';
                document.getElementById('trace-time').textContent = btn.dataset.time || '—';
                document.getElementById('trace-ip').textContent = btn.dataset.ip || '—';
                document.getElementById('trace-user').textContent = btn.dataset.user || '—';
                document.getElementById('trace-description').textContent = btn.dataset.desc || '—';
                document.getElementById('trace-method').textContent = (btn.dataset.method || 'GET') + ' ';
                document.getElementById('trace-url').textContent = btn.dataset.url || '—';
                document.getElementById('trace-ua').textContent = btn.dataset.ua || 'None provided';

                try {
                    const ctx = JSON.parse(btn.dataset.context || 'null');
                    document.getElementById('trace-context').textContent = ctx ? JSON.stringify(ctx, null, 2) : 'No extra metadata';
                } catch (e) {
                    document.getElementById('trace-context').textContent = btn.dataset.context || 'None';
                }

                modal.hidden = false;
                window.ff.icons();
            });
        });

        window.ff.icons();
    });
</script>
@endsection
