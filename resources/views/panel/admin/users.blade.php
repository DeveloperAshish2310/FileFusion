@extends('layout.backend')
@push('title', 'Super Admin - User Management')

@section('content')
<div style="width: 100%; max-width: 100%;">

    {{-- Page Header --}}
    <div class="ff-admin-header">
        <div>
            <h1 class="ff-h1" style="display: flex; align-items: center; gap: 12px; margin-bottom: 4px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(99, 102, 241, 0.15); display: flex; align-items: center; justify-content: center; color: #6366f1;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                User Management Console
            </h1>
            <p class="ff-sub" style="margin: 0;">Manage user accounts, assign custom roles &amp; quotas, approve API access, and test accounts.</p>
        </div>
        <div class="ff-admin-actions">
            <a href="{{ route('api.tester') }}" target="_blank" class="ff-btn" style="background: rgba(99, 102, 241, 0.12); border: 1px solid rgba(99, 102, 241, 0.3); color: #a5b4fc; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; padding: 9px 16px; border-radius: 8px; text-decoration: none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                API Playground
            </a>
            <button type="button" class="ff-btn is-primary" id="openCreateUserModal" style="background: #6366f1; border-color: #6366f1; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; padding: 9px 16px; border-radius: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create New User
            </button>
        </div>
    </div>

    {{-- Top KPI Metric Strip --}}
    <div class="ff-admin-kpi-grid">
        {{-- Card 1: Total Users --}}
        <div class="ff-card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px; border-radius: 12px; border: 1px solid var(--ff-border);">
            <div style="width: 46px; height: 46px; border-radius: 12px; background: rgba(59, 130, 246, 0.12); color: #3b82f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <div style="font-size: 12px; font-weight: 600; color: var(--ff-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Accounts</div>
                <div style="font-size: 22px; font-weight: 800; color: var(--ff-text); margin: 2px 0;">{{ $stats['total_users'] ?? $users->total() }}</div>
                <div style="font-size: 11.5px; color: #10b981; font-weight: 600;">
                    {{ $stats['active_users'] ?? 0 }} Active <span style="color: var(--ff-muted);">•</span> <span style="color: {{ ($stats['suspended_users'] ?? 0) > 0 ? '#ef4444' : 'var(--ff-muted)' }}">{{ $stats['suspended_users'] ?? 0 }} Suspended</span>
                </div>
            </div>
        </div>

        {{-- Card 2: Staff & Admins --}}
        <div class="ff-card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px; border-radius: 12px; border: 1px solid var(--ff-border);">
            <div style="width: 46px; height: 46px; border-radius: 12px; background: rgba(139, 92, 246, 0.12); color: #8b5cf6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div>
                <div style="font-size: 12px; font-weight: 600; color: var(--ff-muted); text-transform: uppercase; letter-spacing: 0.5px;">Staff &amp; Admins</div>
                <div style="font-size: 22px; font-weight: 800; color: var(--ff-text); margin: 2px 0;">{{ $stats['staff_count'] ?? 0 }}</div>
                <div style="font-size: 11.5px; color: var(--ff-muted);">Super Admins, Admins, Managers</div>
            </div>
        </div>

        {{-- Card 3: API Approved Accounts --}}
        <div class="ff-card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px; border-radius: 12px; border: 1px solid var(--ff-border);">
            <div style="width: 46px; height: 46px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6" /><polyline points="8 6 2 12 8 18" /></svg>
            </div>
            <div>
                <div style="font-size: 12px; font-weight: 600; color: var(--ff-muted); text-transform: uppercase; letter-spacing: 0.5px;">API Approved</div>
                <div style="font-size: 22px; font-weight: 800; color: var(--ff-text); margin: 2px 0;">{{ $stats['api_approved_count'] ?? 0 }}</div>
                <div style="font-size: 11.5px; color: #10b981; font-weight: 600;">REST API &amp; MCP Enabled</div>
            </div>
        </div>

        {{-- Card 4: Storage Pool --}}
        <div class="ff-card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px; border-radius: 12px; border: 1px solid var(--ff-border);">
            <div style="width: 46px; height: 46px; border-radius: 12px; background: rgba(245, 158, 11, 0.12); color: #f59e0b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
            </div>
            <div>
                <div style="font-size: 12px; font-weight: 600; color: var(--ff-muted); text-transform: uppercase; letter-spacing: 0.5px;">Storage Pool Used</div>
                <div style="font-size: 22px; font-weight: 800; color: var(--ff-text); margin: 2px 0;">{{ $stats['storage_used_formatted'] ?? '0 B' }}</div>
                <div style="font-size: 11.5px; color: var(--ff-muted);">
                    {{ $stats['storage_percentage'] ?? 0 }}% of {{ $stats['storage_quota_formatted'] ?? '0 B' }} Total
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Search Toolbar --}}
    <div class="ff-card ff-admin-toolbar-card">
        <form method="GET" action="{{ route('panel.admin.users') }}" class="ff-admin-toolbar-form">
            <div class="ff-admin-search-wrap">
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Search by name, email, or username..." class="ff-input" style="width: 100%; height: 40px; padding-left: 36px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; top: 12px; color: var(--ff-muted); pointer-events: none;">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
            <div>
                <select name="status" class="ff-select" onchange="this.form.submit()" style="height: 40px; min-width: 140px;">
                    <option value="">All Statuses</option>
                    <option value="1" {{ (string)$status === '1' ? 'selected' : '' }}>Active Only</option>
                    <option value="0" {{ (string)$status === '0' ? 'selected' : '' }}>Suspended Only</option>
                </select>
            </div>
            <div>
                <select name="role" class="ff-select" onchange="this.form.submit()" style="height: 40px; min-width: 150px;">
                    <option value="">All Roles</option>
                    <option value="super_admin" {{ $role === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="manager" {{ $role === 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="pro_user" {{ $role === 'pro_user' ? 'selected' : '' }}>Pro User</option>
                    <option value="user" {{ $role === 'user' ? 'selected' : '' }}>Standard User</option>
                </select>
            </div>
            <div>
                <select name="api_access" class="ff-select" onchange="this.form.submit()" style="height: 40px; min-width: 160px;">
                    <option value="">All API Statuses</option>
                    <option value="1" {{ (string)($apiAccess ?? '') === '1' ? 'selected' : '' }}>⚡ API Approved</option>
                    <option value="0" {{ (string)($apiAccess ?? '') === '0' ? 'selected' : '' }}>🔒 API Restricted</option>
                </select>
            </div>
            <button type="submit" class="ff-btn ff-btn-primary" style="height: 40px; padding: 0 16px; font-weight: 600;">Filter</button>
            @if (!empty($search) || $status !== null || $role !== null || ($apiAccess ?? null) !== null)
                <a href="{{ route('panel.admin.users') }}" class="ff-btn" style="height: 40px; padding: 0 14px; display: inline-flex; align-items: center; color: var(--ff-muted);">Reset</a>
            @endif
        </form>
    </div>

    {{-- Users Table --}}
    <div class="ff-card" style="border-radius: 12px; border: 1px solid var(--ff-border); overflow: hidden;">
        <div class="ff-admin-table-wrap">
            <table class="ff-admin-table">
                <thead>
                    <tr style="background: var(--ff-surface-subtle); border-bottom: 1px solid var(--ff-border); color: var(--ff-text-2); font-weight: 600;">
                        <th style="padding: 14px 18px;">User Details</th>
                        <th style="padding: 14px 16px;">Role</th>
                        <th style="padding: 14px 16px;">Status</th>
                        <th style="padding: 14px 16px; text-align: center;">API Clearance</th>
                        <th style="padding: 14px 16px; min-width: 190px;">Storage Quota</th>
                        <th style="padding: 14px 16px; text-align: center;">Items</th>
                        <th style="padding: 14px 16px;">Joined</th>
                        <th style="padding: 14px 18px; text-align: right;">Account Actions</th>
                    </tr>
                </thead>
                <tbody id="admin-users-tbody">
                    @forelse ($users as $u)
                        @php
                            $usagePct = $u->getStorageUsagePercentage();
                            $barClass = $usagePct > 90 ? '#ef4444' : ($usagePct > 75 ? '#f59e0b' : '#6366f1');

                            // Role Badges
                            $roleBadges = [
                                'super_admin' => ['bg' => 'rgba(99, 102, 241, 0.15)', 'color' => '#6366f1', 'icon' => '👑', 'label' => 'Super Admin'],
                                'admin'       => ['bg' => 'rgba(236, 72, 153, 0.15)', 'color' => '#ec4899', 'icon' => '⚡', 'label' => 'Admin'],
                                'manager'     => ['bg' => 'rgba(59, 130, 246, 0.15)', 'color' => '#3b82f6', 'icon' => '💼', 'label' => 'Manager'],
                                'pro_user'    => ['bg' => 'rgba(139, 92, 246, 0.15)', 'color' => '#8b5cf6', 'icon' => '⭐', 'label' => 'Pro User'],
                                'user'        => ['bg' => 'var(--ff-input)',           'color' => 'var(--ff-text-2)', 'icon' => '👤', 'label' => 'Standard'],
                            ];
                            $badge = $roleBadges[$u->role] ?? ($u->isSuperAdmin() ? $roleBadges['super_admin'] : $roleBadges['user']);
                            $canApi = $u->canUseApi();
                        @endphp
                        <tr style="border-bottom: 1px solid var(--ff-border); transition: background 0.15s ease;" id="user-row-{{ $u->id }}">
                            {{-- User Details --}}
                            <td style="padding: 14px 18px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    @if ($u->hasAvatar())
                                        <img src="{{ $u->getAvatarUrl() }}" alt="{{ $u->name }}" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 1.5px solid var(--ff-border);">
                                    @else
                                        <div style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; font-weight: 700; font-size: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-weight: 700; color: var(--ff-text); font-size: 13.5px; display: flex; align-items: center; gap: 6px;">
                                            {{ $u->name }}
                                            @if ($u->id === Auth::id())
                                                <span style="font-size: 10px; font-weight: 700; background: rgba(99, 102, 241, 0.15); color: #6366f1; padding: 1px 6px; border-radius: 6px;">YOU</span>
                                            @endif
                                        </div>
                                        <div style="font-size: 12px; color: var(--ff-text-muted); display: flex; align-items: center; gap: 6px; margin-top: 1px;">
                                            <span>{{ $u->email }}</span>
                                            <span>•</span>
                                            <code style="background: var(--ff-input); padding: 1px 5px; border-radius: 4px; font-size: 11px; color: var(--ff-text-2);">{{ '@' . $u->username }}</code>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Role --}}
                            <td style="padding: 14px 16px;">
                                <span style="display: inline-flex; align-items: center; gap: 4px; background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; padding: 4px 9px; border-radius: 6px; font-size: 11.5px; font-weight: 700; letter-spacing: 0.3px; border: 1px solid {{ $badge['color'] }}20;">
                                    <span>{{ $badge['icon'] }}</span>
                                    <span>{{ $badge['label'] }}</span>
                                </span>
                            </td>

                            {{-- Status --}}
                            <td style="padding: 14px 16px;">
                                @if ($u->isActive())
                                    <span style="display: inline-flex; align-items: center; gap: 5px; color: #10b981; font-weight: 700; background: rgba(16, 185, 129, 0.1); padding: 3px 8px; border-radius: 6px; font-size: 11px; border: 1px solid rgba(16, 185, 129, 0.2);">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
                                        ACTIVE
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 5px; color: #ef4444; font-weight: 700; background: rgba(239, 68, 68, 0.1); padding: 3px 8px; border-radius: 6px; font-size: 11px; border: 1px solid rgba(239, 68, 68, 0.2);">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #ef4444;"></span>
                                        SUSPENDED
                                    </span>
                                @endif
                            </td>

                            {{-- API Clearance (Admin Approval Toggle) --}}
                            <td style="padding: 14px 16px; text-align: center;">
                                @if ($u->isSuperAdmin() || $u->role === 'admin')
                                    <span style="display: inline-flex; align-items: center; gap: 5px; background: rgba(99, 102, 241, 0.12); color: #6366f1; padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700;" title="Super Admins and Admins have permanent API access">
                                        <span>⚡ Full API</span>
                                    </span>
                                @else
                                    <button type="button" class="ff-btn btn-toggle-user-api" data-user-id="{{ $u->id }}" data-user-name="{{ $u->name }}" data-current="{{ $canApi ? '1' : '0' }}" style="padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; transition: all 0.2s ease; {{ $canApi ? 'background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);' : 'background: var(--ff-input); color: var(--ff-text-muted); border: 1px solid var(--ff-border);' }}" title="Click to {{ $canApi ? 'Revoke API Access' : 'Approve API Access' }}">
                                        @if ($canApi)
                                            <span style="color: #10b981;">⚡ Granted</span>
                                        @else
                                            <span style="color: var(--ff-text-muted);">🔒 Restricted</span>
                                        @endif
                                    </button>
                                @endif
                            </td>

                            {{-- Storage Usage --}}
                            <td style="padding: 14px 16px;">
                                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 5px;">
                                    <span style="font-weight: 600; color: var(--ff-text);">{{ $u->getStorageUsedFormatted() }}</span>
                                    <span style="color: var(--ff-text-muted); font-size: 11.5px;">{{ $u->getStorageQuotaFormatted() }} ({{ $usagePct }}%)</span>
                                </div>
                                <div style="height: 6px; width: 100%; background: var(--ff-input); border-radius: 3px; overflow: hidden; border: 1px solid var(--ff-border);">
                                    <div style="height: 100%; width: {{ min(100, $usagePct) }}%; background: {{ $barClass }}; border-radius: 3px; transition: width 0.3s ease;"></div>
                                </div>
                            </td>

                            {{-- Items (Files & Links Count) --}}
                            <td style="padding: 14px 16px; text-align: center;">
                                <div style="display: inline-flex; gap: 6px; font-size: 11.5px;">
                                    <span style="background: var(--ff-input); padding: 2px 7px; border-radius: 6px; color: var(--ff-text-2); border: 1px solid var(--ff-border);" title="Files uploaded">📁 {{ $u->files_count ?? 0 }}</span>
                                    <span style="background: var(--ff-input); padding: 2px 7px; border-radius: 6px; color: var(--ff-text-2); border: 1px solid var(--ff-border);" title="Links saved">🔗 {{ $u->links_count ?? 0 }}</span>
                                </div>
                            </td>

                            {{-- Joined --}}
                            <td style="padding: 14px 16px; color: var(--ff-text-muted); font-size: 12px; white-space: nowrap;">
                                {{ $u->created_at ? $u->created_at->format('M d, Y') : 'N/A' }}
                            </td>

                            {{-- Account Actions --}}
                            <td style="padding: 14px 18px; text-align: right;">
                                <div style="display: inline-flex; gap: 5px; align-items: center; justify-content: flex-end; flex-wrap: nowrap;">
                                    {{-- Change Role --}}
                                    <button type="button" class="ff-btn ff-btn-sm" style="padding: 4px 8px; font-size: 11.5px; color: #8b5cf6; background: var(--ff-input); border: 1px solid var(--ff-border);" onclick="openRoleModal('{{ $u->id }}', '{{ addslashes($u->name) }}', '{{ $u->role ?? 'user' }}')" title="Change User Role">
                                        👑 Role
                                    </button>

                                    {{-- Change Quota --}}
                                    <button type="button" class="ff-btn ff-btn-sm" style="padding: 4px 8px; font-size: 11.5px; background: var(--ff-input); border: 1px solid var(--ff-border);" onclick="openQuotaModal('{{ $u->id }}', '{{ addslashes($u->name) }}', '{{ round($u->storage_quota / 1024 / 1024 / 1024, 1) }}')" title="Adjust Storage Quota">
                                        💾 Quota
                                    </button>

                                    {{-- Reset Password --}}
                                    <button type="button" class="ff-btn ff-btn-sm" style="padding: 4px 8px; font-size: 11.5px; color: #f59e0b; background: var(--ff-input); border: 1px solid var(--ff-border);" onclick="openPasswordModal('{{ $u->id }}', '{{ addslashes($u->name) }}')" title="Set / Reset Password">
                                        🔑 Pass
                                    </button>

                                    @if ($u->id !== Auth::id())
                                        {{-- Toggle Status --}}
                                        <form id="status-form-{{ $u->id }}" method="POST" action="{{ route('panel.admin.users.toggleStatus', $u->id) }}" style="display: inline;">
                                            @csrf
                                            <button type="button" class="ff-btn ff-btn-sm ff-admin-status-btn" data-id="{{ $u->id }}" data-action="{{ $u->isActive() ? 'suspend' : 'activate' }}" data-name="{{ $u->name }}" style="padding: 4px 8px; font-size: 11.5px; background: var(--ff-input); border: 1px solid var(--ff-border); color: {{ $u->isActive() ? '#ef4444' : '#10b981' }};" title="{{ $u->isActive() ? 'Suspend Account' : 'Activate Account' }}">
                                                {{ $u->isActive() ? '🚫 Suspend' : '✅ Activate' }}
                                            </button>
                                        </form>

                                        {{-- Impersonate --}}
                                        <a href="{{ route('panel.admin.users.impersonate', $u->id) }}" class="ff-btn ff-btn-sm" style="padding: 4px 8px; font-size: 11.5px; color: #3b82f6; background: var(--ff-input); border: 1px solid var(--ff-border); font-weight: 600;" title="Login As This User">
                                            🎭 Login
                                        </a>

                                        {{-- Delete --}}
                                        <form id="delete-form-{{ $u->id }}" method="POST" action="{{ route('panel.admin.users.delete', $u->id) }}" style="display: inline;">
                                            @csrf
                                            <button type="button" class="ff-btn ff-btn-sm ff-admin-delete-btn" data-id="{{ $u->id }}" data-name="{{ $u->name }}" style="padding: 4px 8px; font-size: 11.5px; color: #ef4444; background: var(--ff-input); border: 1px solid var(--ff-border);" title="Permanently Delete User">
                                                🗑️
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding: 40px; text-align: center; color: var(--ff-text-muted);">
                                <div style="font-size: 15px; font-weight: 600; color: var(--ff-text-2); margin-bottom: 4px;">No Users Found</div>
                                <div style="font-size: 13px;">No user accounts match your current query or filter criteria.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($users->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid var(--ff-border); display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 12.5px; color: var(--ff-text-muted);">
                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} accounts
                </div>
                <div>
                    {{ $users->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ======================== MODAL: CREATE USER ======================== --}}
<div id="createUserModal" class="ff-modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); z-index: 10000; align-items: center; justify-content: center; padding: 16px;">
    <div class="ff-modal" style="width: 520px; max-width: 95%;">
        <div class="ff-row-between" style="margin-bottom: 18px;">
            <div>
                <div class="ff-modal-title" style="display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Provision New User Account
                </div>
                <div class="ff-modal-sub">Create credentials and assign storage &amp; API permissions</div>
            </div>
            <button type="button" class="ff-modal-close" onclick="closeCreateUserModal()" aria-label="Close modal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('panel.admin.users.create') }}">
            @csrf
            <div class="ff-field" style="margin-bottom: 12px;">
                <label class="ff-label">Full Name <span style="color: var(--ff-danger);">*</span></label>
                <input type="text" name="name" required class="ff-input" placeholder="e.g. John Doe">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div class="ff-field">
                    <label class="ff-label">Email Address <span style="color: var(--ff-danger);">*</span></label>
                    <input type="email" name="email" required class="ff-input" placeholder="e.g. john@example.com">
                </div>
                <div class="ff-field">
                    <label class="ff-label">Username <span style="color: var(--ff-danger);">*</span></label>
                    <input type="text" name="username" required class="ff-input" placeholder="e.g. johndoe">
                </div>
            </div>

            <div class="ff-field" style="margin-bottom: 14px;">
                <label class="ff-label">Account Password <span style="color: var(--ff-danger);">*</span></label>
                <input type="password" name="password" required minlength="6" class="ff-input" placeholder="Minimum 6 characters">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div class="ff-field">
                    <label class="ff-label">Storage Quota (GB) <span style="color: var(--ff-danger);">*</span></label>
                    <input type="number" name="quota_gb" id="create_user_quota_gb" value="{{ $roleQuotas['user']['quota_gb'] ?? 25 }}" min="0.01" step="any" required class="ff-input">
                </div>
                <div class="ff-field">
                    <label class="ff-label">Account Role <span style="color: var(--ff-danger);">*</span></label>
                    <select name="role" id="create_user_role_select" class="ff-select">
                        @foreach($roleQuotas as $rKey => $rData)
                            <option value="{{ $rKey }}" data-quota="{{ $rData['quota_gb'] }}" {{ $rKey === 'user' ? 'selected' : '' }}>
                                {{ $rData['label'] }} ({{ $rData['quota_gb'] }} GB)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- API Clearance Checkbox --}}
            <div style="background: var(--ff-surface-subtle); border: 1px solid var(--ff-border); border-radius: 8px; padding: 12px 14px; margin-bottom: 20px;">
                <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="api_access_enabled" value="1" id="create_user_api_access" style="margin-top: 3px; width: 16px; height: 16px; accent-color: #10b981;">
                    <div>
                        <div style="font-size: 13px; font-weight: 700; color: var(--ff-text); display: flex; align-items: center; gap: 6px;">
                            <span>⚡ Grant REST API &amp; Developer Access</span>
                        </div>
                        <div style="font-size: 11.5px; color: var(--ff-text-muted); margin-top: 2px;">
                            Enables this user to generate Personal Access Tokens (PAT) and connect AI clients (MCP).
                        </div>
                    </div>
                </label>
            </div>

            <div class="ff-row" style="gap: 10px; justify-content: flex-end;">
                <button type="button" class="ff-btn" onclick="closeCreateUserModal()">Cancel</button>
                <button type="submit" class="ff-btn ff-btn-primary" style="background: #6366f1; border-color: #6366f1;">Create Account</button>
            </div>
        </form>
    </div>
</div>

{{-- ======================== MODAL: CHANGE ROLE ======================== --}}
<div id="roleModal" class="ff-modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); z-index: 10000; align-items: center; justify-content: center; padding: 16px;">
    <div class="ff-modal" style="width: 460px; max-width: 95%;">
        <div class="ff-row-between" style="margin-bottom: 16px;">
            <div>
                <div class="ff-modal-title">Assign Account Role</div>
                <div class="ff-modal-sub">Update permissions tier for <strong id="roleUserName">User</strong></div>
            </div>
            <button type="button" class="ff-modal-close" onclick="closeRoleModal()" aria-label="Close modal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <form id="roleForm" method="POST" action="">
            @csrf
            <div class="ff-field" style="margin-bottom: 16px;">
                <label class="ff-label">Select Role Tier</label>
                <select name="role" id="roleSelect" class="ff-select">
                    @foreach($roleQuotas as $rKey => $rData)
                        <option value="{{ $rKey }}" data-quota="{{ $rData['quota_gb'] }}">
                            {{ $rData['label'] }} ({{ $rData['quota_gb'] }} GB Default Quota)
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--ff-text); cursor: pointer;">
                    <input type="checkbox" name="sync_quota" value="1" checked style="width: 16px; height: 16px; accent-color: #8b5cf6;">
                    <span>Auto-apply default storage quota for selected role</span>
                </label>
            </div>

            <div class="ff-row" style="gap: 10px; justify-content: flex-end;">
                <button type="button" class="ff-btn" onclick="closeRoleModal()">Cancel</button>
                <button type="submit" class="ff-btn ff-btn-primary" style="background: #8b5cf6; border-color: #8b5cf6;">Update Role</button>
            </div>
        </form>
    </div>
</div>

{{-- ======================== MODAL: RESET PASSWORD ======================== --}}
<div id="passwordModal" class="ff-modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); z-index: 10000; align-items: center; justify-content: center; padding: 16px;">
    <div class="ff-modal" style="width: 440px; max-width: 95%;">
        <div class="ff-row-between" style="margin-bottom: 16px;">
            <div>
                <div class="ff-modal-title" style="color: #f59e0b;">Set / Reset User Password</div>
                <div class="ff-modal-sub">Assign a new password for <strong id="passwordUserName">User</strong></div>
            </div>
            <button type="button" class="ff-modal-close" onclick="closePasswordModal()" aria-label="Close modal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <form id="passwordForm" method="POST" action="">
            @csrf
            <div class="ff-field" style="margin-bottom: 20px;">
                <label class="ff-label">New Password</label>
                <input type="password" name="password" required minlength="6" class="ff-input" placeholder="Enter new password (min 6 chars)">
            </div>

            <div class="ff-row" style="gap: 10px; justify-content: flex-end;">
                <button type="button" class="ff-btn" onclick="closePasswordModal()">Cancel</button>
                <button type="submit" class="ff-btn ff-btn-primary" style="background: #f59e0b; border-color: #f59e0b;">Set Password</button>
            </div>
        </form>
    </div>
</div>

{{-- ======================== MODAL: CHANGE QUOTA ======================== --}}
<div id="quotaModal" class="ff-modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); z-index: 10000; align-items: center; justify-content: center; padding: 16px;">
    <div class="ff-modal" style="width: 460px; max-width: 95%;">
        <div class="ff-row-between" style="margin-bottom: 16px;">
            <div>
                <div class="ff-modal-title">Adjust Storage Limit</div>
                <div class="ff-modal-sub">Change disk capacity for <strong id="quotaUserName">User</strong></div>
            </div>
            <button type="button" class="ff-modal-close" onclick="closeQuotaModal()" aria-label="Close modal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <form id="quotaForm" method="POST" action="">
            @csrf
            <div class="ff-field" style="margin-bottom: 14px;">
                <label class="ff-label">Quick Preset Tiers:</label>
                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px;">
                    @foreach($roleQuotas as $rKey => $rData)
                        <button type="button" class="ff-btn ff-btn-sm" onclick="setPreset({{ $rData['quota_gb'] }})">{{ $rData['quota_gb'] }} GB</button>
                    @endforeach
                </div>
                <label class="ff-label">Or Custom Storage (GB):</label>
                <input type="number" name="quota_gb" id="quotaInput" required min="0.01" step="any" class="ff-input">
            </div>

            <div class="ff-row" style="gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="ff-btn" onclick="closeQuotaModal()">Cancel</button>
                <button type="submit" class="ff-btn ff-btn-primary" style="background: #6366f1; border-color: #6366f1;">Save Quota</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Create User Modal Handlers
    document.getElementById('openCreateUserModal').addEventListener('click', function() {
        document.getElementById('createUserModal').style.display = 'flex';
    });
    function closeCreateUserModal() {
        document.getElementById('createUserModal').style.display = 'none';
    }

    // Dynamic role quota & API toggle in Create User modal
    const createUserRoleSelect = document.getElementById('create_user_role_select');
    const createUserQuotaInput = document.getElementById('create_user_quota_gb');
    const createUserApiChk = document.getElementById('create_user_api_access');
    if (createUserRoleSelect) {
        createUserRoleSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            const quota = opt ? opt.getAttribute('data-quota') : null;
            if (quota && createUserQuotaInput) {
                createUserQuotaInput.value = quota;
            }
            if (createUserApiChk) {
                if (this.value === 'super_admin' || this.value === 'admin') {
                    createUserApiChk.checked = true;
                }
            }
        });
    }

    // Role Modal Handlers
    function openRoleModal(userId, userName, currentRole) {
        document.getElementById('roleUserName').textContent = userName;
        document.getElementById('roleSelect').value = currentRole || 'user';
        document.getElementById('roleForm').action = "{{ url('/panel/admin/users/role') }}/" + userId;
        document.getElementById('roleModal').style.display = 'flex';
    }
    function closeRoleModal() {
        document.getElementById('roleModal').style.display = 'none';
    }

    // Password Reset Modal Handlers
    function openPasswordModal(userId, userName) {
        document.getElementById('passwordUserName').textContent = userName;
        document.getElementById('passwordForm').action = "{{ url('/panel/admin/users/reset-password') }}/" + userId;
        document.getElementById('passwordModal').style.display = 'flex';
    }
    function closePasswordModal() {
        document.getElementById('passwordModal').style.display = 'none';
    }

    // Quota Modal Handlers
    function openQuotaModal(userId, userName, currentQuotaGB) {
        document.getElementById('quotaUserName').textContent = userName;
        document.getElementById('quotaInput').value = currentQuotaGB;
        document.getElementById('quotaForm').action = "{{ url('/panel/admin/users/quota') }}/" + userId;
        document.getElementById('quotaModal').style.display = 'flex';
    }
    function closeQuotaModal() {
        document.getElementById('quotaModal').style.display = 'none';
    }
    function setPreset(gb) {
        document.getElementById('quotaInput').value = gb;
    }

    // AJAX API Access Toggle
    document.querySelectorAll('.btn-toggle-user-api').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');
            const current = this.getAttribute('data-current') === '1';
            const actionText = current ? 'revoke API access from' : 'grant API & developer access to';

            if (!confirm(`Are you sure you want to ${actionText} ${userName}?`)) {
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span>Updating...</span>';

            fetch(`{{ url('/panel/admin/users/toggle-api') }}/${userId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                if (data.ok || data.success) {
                    const isAllowed = data.api_access_enabled;
                    btn.setAttribute('data-current', isAllowed ? '1' : '0');
                    if (isAllowed) {
                        btn.style.background = 'rgba(16, 185, 129, 0.15)';
                        btn.style.color = '#10b981';
                        btn.style.borderColor = 'rgba(16, 185, 129, 0.3)';
                        btn.innerHTML = '<span style="color: #10b981;">⚡ Granted</span>';
                    } else {
                        btn.style.background = 'var(--ff-input)';
                        btn.style.color = 'var(--ff-text-muted)';
                        btn.style.borderColor = 'var(--ff-border)';
                        btn.innerHTML = '<span style="color: var(--ff-text-muted);">🔒 Restricted</span>';
                    }
                    if (window.ff && window.ff.toast) {
                        window.ff.toast(data.info || 'API access updated.', 'success');
                    } else {
                        alert(data.info || 'API access updated.');
                    }
                } else {
                    alert(data.info || 'Failed to update API access.');
                }
            })
            .catch(err => {
                btn.disabled = false;
                alert('Error: ' + err.message);
            });
        });
    });

    $(document).on('click', '.ff-admin-status-btn', async function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const action = $(this).data('action');
        const name = $(this).data('name') || 'this user';
        const isSuspend = action === 'suspend';

        const confirmed = await window.ff.confirm({
            title: isSuspend ? '🚫 Suspend User' : '✅ Activate User',
            message: isSuspend ? `Suspend account for "${name}"? They will not be able to log in.` : `Re-activate account for "${name}"?`,
            confirmText: isSuspend ? 'Suspend User' : 'Activate User',
            isDanger: isSuspend
        });

        if (confirmed) {
            document.getElementById('status-form-' + id).submit();
        }
    });

    $(document).on('click', '.ff-admin-delete-btn', async function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const name = $(this).data('name') || 'this user';

        const confirmed = await window.ff.confirm({
            title: '🗑️ Permanently Delete User',
            message: `Permanently delete user "${name}" along with all their files and vaults? This action cannot be undone.`,
            confirmText: 'Delete User & Data',
            isDanger: true
        });

        if (confirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
</script>
@endsection
