@extends('layout.backend')
@push('title', 'Super Admin - User Management')

@section('content')
<div class="ff-page">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 class="ff-h1" style="display: flex; align-items: center; gap: 10px;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                User Management Console
            </h1>
            <p class="ff-sub">Manage user accounts, assign custom roles & quotas, reset passwords, and test accounts.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="button" class="ff-btn is-primary" id="openCreateUserModal" style="background: #6366f1; border-color: #6366f1; display: inline-flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create New User
            </button>
        </div>
    </div>

    {{-- Filter & Search Toolbar --}}
    <div class="ff-card" style="padding: 16px; margin-bottom: 20px;">
        <form method="GET" action="{{ route('panel.admin.users') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 200px;">
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Search by name, email, or username..." class="ff-input" style="width: 100%;">
            </div>
            <div>
                <select name="status" class="ff-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="1" {{ (string)$status === '1' ? 'selected' : '' }}>Active Only</option>
                    <option value="0" {{ (string)$status === '0' ? 'selected' : '' }}>Suspended Only</option>
                </select>
            </div>
            <div>
                <select name="role" class="ff-select" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    <option value="super_admin" {{ $role === 'super_admin' ? 'selected' : '' }}>Super Admin (5 TB)</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin (1 TB)</option>
                    <option value="manager" {{ $role === 'manager' ? 'selected' : '' }}>Manager (500 GB)</option>
                    <option value="pro_user" {{ $role === 'pro_user' ? 'selected' : '' }}>Pro User (100 GB)</option>
                    <option value="user" {{ $role === 'user' ? 'selected' : '' }}>Standard User (25 GB)</option>
                </select>
            </div>
            <button type="submit" class="ff-btn" style="background: var(--ff-surface);">Filter</button>
            @if (!empty($search) || $status !== null || $role !== null)
                <a href="{{ route('panel.admin.users') }}" class="ff-btn" style="color: var(--ff-muted);">Reset</a>
            @endif
        </form>
    </div>

    {{-- Users Table --}}
    <div class="ff-card">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--ff-border); text-align: left; color: var(--ff-muted);">
                        <th style="padding: 14px 16px;">User Details</th>
                        <th style="padding: 14px 16px;">Role</th>
                        <th style="padding: 14px 16px;">Status</th>
                        <th style="padding: 14px 16px; min-width: 200px;">Storage Usage</th>
                        <th style="padding: 14px 16px;">Files</th>
                        <th style="padding: 14px 16px;">Joined</th>
                        <th style="padding: 14px 16px; text-align: right;">Account Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $u)
                        @php
                            $usagePct = $u->getStorageUsagePercentage();
                            $barClass = $usagePct > 90 ? '#ef4444' : ($usagePct > 75 ? '#f59e0b' : '#6366f1');

                            // Role Badges
                            $roleBadges = [
                                'super_admin' => ['bg' => 'rgba(99, 102, 241, 0.15)', 'color' => '#6366f1', 'label' => 'SUPER ADMIN'],
                                'admin'       => ['bg' => 'rgba(236, 72, 153, 0.15)', 'color' => '#ec4899', 'label' => 'ADMIN'],
                                'manager'     => ['bg' => 'rgba(59, 130, 246, 0.15)', 'color' => '#3b82f6', 'label' => 'MANAGER'],
                                'pro_user'    => ['bg' => 'rgba(139, 92, 246, 0.15)', 'color' => '#8b5cf6', 'label' => 'PRO USER'],
                                'user'        => ['bg' => 'var(--ff-surface)',        'color' => 'var(--ff-muted)', 'label' => 'USER'],
                            ];
                            $badge = $roleBadges[$u->role] ?? ($u->isSuperAdmin() ? $roleBadges['super_admin'] : $roleBadges['user']);
                        @endphp
                        <tr style="border-bottom: 1px solid var(--ff-border-subtle);">
                            <td style="padding: 14px 16px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span class="ff-avatar">{{ strtoupper(substr($u->name, 0, 1)) }}</span>
                                    <div>
                                        <div style="font-weight: 700;">{{ $u->name }}</div>
                                        <div style="font-size: 12px; color: var(--ff-muted);">{{ $u->email }} • <code>{{ '@' . $u->username }}</code></div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 14px 16px;">
                                <span style="background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td style="padding: 14px 16px;">
                                @if ($u->isActive())
                                    <span style="display: inline-flex; align-items: center; gap: 4px; color: #10b981; font-weight: 700; background: rgba(16, 185, 129, 0.1); padding: 3px 8px; border-radius: 4px; font-size: 11px;">
                                        ● ACTIVE
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 4px; color: #ef4444; font-weight: 700; background: rgba(239, 68, 68, 0.1); padding: 3px 8px; border-radius: 4px; font-size: 11px;">
                                        ● SUSPENDED
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 14px 16px;">
                                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                                    <span>{{ $u->getStorageUsedFormatted() }}</span>
                                    <span style="color: var(--ff-muted);">{{ $u->getStorageQuotaFormatted() }} ({{ $usagePct }}%)</span>
                                </div>
                                <div style="height: 6px; width: 100%; background: var(--ff-surface); border-radius: 3px; overflow: hidden;">
                                    <div style="height: 100%; width: {{ min(100, $usagePct) }}%; background: {{ $barClass }}; border-radius: 3px;"></div>
                                </div>
                            </td>
                            <td style="padding: 14px 16px; font-weight: 600;">
                                {{ $u->files_count ?? 0 }}
                            </td>
                            <td style="padding: 14px 16px; color: var(--ff-muted); font-size: 12px;">
                                {{ $u->created_at ? $u->created_at->format('M d, Y') : 'N/A' }}
                            </td>
                            <td style="padding: 14px 16px; text-align: right;">
                                <div style="display: inline-flex; gap: 6px; align-items: center;">
                                    {{-- Change Role Button --}}
                                    <button type="button" class="ff-btn" style="padding: 5px 10px; font-size: 12px; color: #8b5cf6;" onclick="openRoleModal('{{ $u->id }}', '{{ addslashes($u->name) }}', '{{ $u->role ?? 'user' }}')" title="Change User Role">
                                        👑 Role
                                    </button>

                                    {{-- Change Quota Button --}}
                                    <button type="button" class="ff-btn" style="padding: 5px 10px; font-size: 12px;" onclick="openQuotaModal('{{ $u->id }}', '{{ addslashes($u->name) }}', '{{ round($u->storage_quota / 1024 / 1024 / 1024, 1) }}')" title="Change Storage Quota">
                                        💾 Quota
                                    </button>

                                    {{-- Reset Password Button --}}
                                    <button type="button" class="ff-btn" style="padding: 5px 10px; font-size: 12px; color: #f59e0b;" onclick="openPasswordModal('{{ $u->id }}', '{{ addslashes($u->name) }}')" title="Set / Reset Password">
                                        🔑 Pass
                                    </button>

                                    {{-- Actions for other users --}}
                                    @if ($u->id !== Auth::id())
                                        {{-- Toggle Status Button --}}
                                        <form id="status-form-{{ $u->id }}" method="POST" action="{{ route('panel.admin.users.toggleStatus', $u->id) }}" style="display: inline;">
                                            @csrf
                                            <button type="button" class="ff-btn ff-admin-status-btn" data-id="{{ $u->id }}" data-action="{{ $u->isActive() ? 'suspend' : 'activate' }}" data-name="{{ $u->name }}" style="padding: 5px 10px; font-size: 12px; color: {{ $u->isActive() ? '#ef4444' : '#10b981' }};" title="{{ $u->isActive() ? 'Suspend User' : 'Activate User' }}">
                                                {{ $u->isActive() ? '🚫 Suspend' : '✅ Activate' }}
                                            </button>
                                        </form>

                                        {{-- Impersonate Button --}}
                                        <a href="{{ route('panel.admin.users.impersonate', $u->id) }}" class="ff-btn" style="padding: 5px 10px; font-size: 12px; color: #3b82f6; font-weight: 600;" title="Login As This User Instantly">
                                            🎭 Login As
                                        </a>

                                        {{-- Delete Button --}}
                                        <form id="delete-form-{{ $u->id }}" method="POST" action="{{ route('panel.admin.users.delete', $u->id) }}" style="display: inline;">
                                            @csrf
                                            <button type="button" class="ff-btn ff-admin-delete-btn" data-id="{{ $u->id }}" data-name="{{ $u->name }}" style="padding: 5px 10px; font-size: 12px; color: #ef4444;" title="Delete User">
                                                🗑️
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 30px; text-align: center; color: var(--ff-muted);">No users found matching your search.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($users->hasPages())
            <div style="padding: 16px; border-top: 1px solid var(--ff-border);">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<style>
.ff-admin-modal-card {
    background: #ffffff;
    color: #0f172a;
    border: 1px solid #e2e8f0;
}
html.dark .ff-admin-modal-card, [data-theme="dark"] .ff-admin-modal-card, body.dark-mode .ff-admin-modal-card {
    background: #181b22;
    color: #f1f5f9;
    border: 1px solid rgba(255, 255, 255, 0.12);
}
</style>

{{-- ======================== MODAL: CREATE USER ======================== --}}
<div id="createUserModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); z-index: 10000; align-items: center; justify-content: center; padding: 16px;">
    <div class="ff-card" style="width: 100%; max-width: 480px; padding: 24px; margin: 0; background: var(--ff-card, #ffffff) !important; color: var(--ff-text) !important; border: 1px solid var(--ff-border); border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--ff-border); padding-bottom: 12px;">
            <h3 style="font-size: 17px; font-weight: 700; color: var(--ff-text); margin: 0;">Provision New User Account</h3>
            <button type="button" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--ff-muted);" onclick="closeCreateUserModal()">✕</button>
        </div>
        <form method="POST" action="{{ route('panel.admin.users.create') }}">
            @csrf
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: var(--ff-text); margin-bottom: 6px;">Full Name</label>
                <input type="text" name="name" required class="ff-input" style="width: 100%; height: 40px;" placeholder="e.g. John Doe">
            </div>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: var(--ff-text); margin-bottom: 6px;">Email Address</label>
                <input type="email" name="email" required class="ff-input" style="width: 100%; height: 40px;" placeholder="e.g. john@example.com">
            </div>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: var(--ff-text); margin-bottom: 6px;">Username</label>
                <input type="text" name="username" required class="ff-input" style="width: 100%; height: 40px;" placeholder="e.g. johndoe">
            </div>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: var(--ff-text); margin-bottom: 6px;">Password</label>
                <input type="password" name="password" required class="ff-input" style="width: 100%; height: 40px;" placeholder="Minimum 6 characters">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--ff-text); margin-bottom: 6px;">Storage Quota (GB)</label>
                    <input type="number" name="quota_gb" id="create_user_quota_gb" value="{{ $roleQuotas['user']['quota_gb'] ?? 25 }}" min="0.01" step="any" required class="ff-input" style="width: 100%; height: 40px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--ff-text); margin-bottom: 6px;">Account Role</label>
                    <select name="role" id="create_user_role_select" class="ff-select" style="width: 100%; height: 40px;">
                        @foreach($roleQuotas as $rKey => $rData)
                            <option value="{{ $rKey }}" data-quota="{{ $rData['quota_gb'] }}" {{ $rKey === 'user' ? 'selected' : '' }}>
                                {{ $rData['label'] }} ({{ $rData['quota_gb'] }} GB)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="ff-btn" onclick="closeCreateUserModal()">Cancel</button>
                <button type="submit" class="ff-btn is-primary" style="background: #6366f1; border-color: #6366f1;">Create User</button>
            </div>
        </form>
    </div>
</div>

{{-- ======================== MODAL: CHANGE ROLE ======================== --}}
<div id="roleModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); z-index: 10000; align-items: center; justify-content: center; padding: 16px;">
    <div class="ff-card" style="width: 100%; max-width: 460px; padding: 24px; margin: 0; background: var(--ff-card, #ffffff) !important; color: var(--ff-text) !important; border: 1px solid var(--ff-border); border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--ff-border); padding-bottom: 12px;">
            <h3 style="font-size: 17px; font-weight: 700; color: var(--ff-text); margin: 0;">Assign Account Role &amp; Capabilities</h3>
            <button type="button" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--ff-muted);" onclick="closeRoleModal()">✕</button>
        </div>
        <p style="font-size: 13px; color: var(--ff-text-2, var(--ff-muted)); margin-bottom: 16px;">
            Select role tier for <strong id="roleUserName" style="color: var(--ff-text);">User</strong>:
        </p>
        <form id="roleForm" method="POST" action="">
            @csrf
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: var(--ff-text); margin-bottom: 8px;">Select Role:</label>
                <select name="role" id="roleSelect" class="ff-select" style="width: 100%; height: 40px;">
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
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="ff-btn" onclick="closeRoleModal()">Cancel</button>
                <button type="submit" class="ff-btn is-primary" style="background: #8b5cf6; border-color: #8b5cf6;">Update Role</button>
            </div>
        </form>
    </div>
</div>

{{-- ======================== MODAL: RESET PASSWORD ======================== --}}
<div id="passwordModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); z-index: 10000; align-items: center; justify-content: center; padding: 16px;">
    <div class="ff-card" style="width: 100%; max-width: 440px; padding: 24px; margin: 0; background: var(--ff-card, #ffffff) !important; color: var(--ff-text) !important; border: 1px solid var(--ff-border); border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--ff-border); padding-bottom: 12px;">
            <h3 style="font-size: 17px; font-weight: 700; color: var(--ff-text); margin: 0;">Set / Reset User Password</h3>
            <button type="button" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--ff-muted);" onclick="closePasswordModal()">✕</button>
        </div>
        <p style="font-size: 13px; color: var(--ff-text-2, var(--ff-muted)); margin-bottom: 16px;">
            Enter a new password for <strong id="passwordUserName" style="color: var(--ff-text);">User</strong>:
        </p>
        <form id="passwordForm" method="POST" action="">
            @csrf
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: var(--ff-text); margin-bottom: 6px;">New Password</label>
                <input type="password" name="password" required minlength="6" class="ff-input" style="width: 100%; height: 40px;" placeholder="Enter new password (min 6 chars)">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="ff-btn" onclick="closePasswordModal()">Cancel</button>
                <button type="submit" class="ff-btn is-primary" style="background: #f59e0b; border-color: #f59e0b;">Set Password</button>
            </div>
        </form>
    </div>
</div>

{{-- ======================== MODAL: CHANGE QUOTA ======================== --}}
<div id="quotaModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); z-index: 10000; align-items: center; justify-content: center; padding: 16px;">
    <div class="ff-card" style="width: 100%; max-width: 440px; padding: 24px; margin: 0; background: var(--ff-card, #ffffff) !important; color: var(--ff-text) !important; border: 1px solid var(--ff-border); border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--ff-border); padding-bottom: 12px;">
            <h3 style="font-size: 17px; font-weight: 700; color: var(--ff-text); margin: 0;">Change Storage Quota</h3>
            <button type="button" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--ff-muted);" onclick="closeQuotaModal()">✕</button>
        </div>
        <p style="font-size: 13px; color: var(--ff-text-2, var(--ff-muted)); margin-bottom: 16px;">
            Adjusting storage limit for <strong id="quotaUserName" style="color: var(--ff-text);">User</strong>.
        </p>
        <form id="quotaForm" method="POST" action="">
            @csrf
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: var(--ff-text); margin-bottom: 6px;">Select Quick Preset:</label>
                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px;">
                    @foreach($roleQuotas as $rKey => $rData)
                        <button type="button" class="ff-btn" style="padding: 4px 10px; font-size: 12px;" onclick="setPreset({{ $rData['quota_gb'] }})">{{ $rData['quota_gb'] }} GB</button>
                    @endforeach
                </div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: var(--ff-text); margin-bottom: 6px;">Or Enter Custom GB:</label>
                <input type="number" name="quota_gb" id="quotaInput" required min="0.01" step="any" class="ff-input" style="width: 100%; height: 40px;">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="ff-btn" onclick="closeQuotaModal()">Cancel</button>
                <button type="submit" class="ff-btn is-primary" style="background: #6366f1; border-color: #6366f1;">Save Quota</button>
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

    // Dynamic role quota synchronization in Create User modal
    const createUserRoleSelect = document.getElementById('create_user_role_select');
    const createUserQuotaInput = document.getElementById('create_user_quota_gb');
    if (createUserRoleSelect && createUserQuotaInput) {
        createUserRoleSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            const quota = opt ? opt.getAttribute('data-quota') : null;
            if (quota) {
                createUserQuotaInput.value = quota;
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
