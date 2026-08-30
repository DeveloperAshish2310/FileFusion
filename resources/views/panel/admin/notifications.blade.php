@extends('layout.backend')
@push('title', 'Mobile & Push Notification Testing Center')

@section('content')
<div class="ff-page">
    {{-- Header --}}
    <div class="ff-admin-header" style="margin-bottom: 24px;">
        <div>
            <h1 class="ff-h1" style="display: flex; align-items: center; gap: 10px;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                Notification Testing Console
            </h1>
            <p class="ff-sub">Test real-time Android native push notifications, local alerts, and system notification channels.</p>
        </div>
        <div class="ff-admin-actions">
            <a href="{{ route('panel.admin.dashboard') }}" class="ff-btn" style="background: var(--ff-card); border-color: var(--ff-border); display: inline-flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Back to Dashboard
            </a>
            <button type="button" id="btn-request-perm" class="ff-btn is-primary" style="background: #6366f1; border-color: #6366f1; display: inline-flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Request Permission
            </button>
        </div>
    </div>

    {{-- System & Environment Diagnostic Banner --}}
    <div class="ff-card" style="padding: 20px; margin-bottom: 24px; border-left: 4px solid #6366f1;">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div id="platform-icon-wrap" style="width: 48px; height: 48px; border-radius: 12px; background: rgba(99, 102, 241, 0.12); color: #6366f1; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 15px; font-weight: 700;" id="platform-name">Detecting Environment...</span>
                        <span id="badge-perm-status" class="ff-badge" style="background: rgba(148, 163, 184, 0.15); color: #94a3b8; font-size: 11px; padding: 3px 8px; border-radius: 6px;">Checking...</span>
                    </div>
                    <p style="margin: 4px 0 0; font-size: 13px; color: var(--ff-muted);" id="platform-sub">Checking Capacitor Native Bridge & Local Notification capabilities.</p>
                </div>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <div style="background: var(--ff-bg-2, #f8fafc); border: 1px solid var(--ff-border, #e2e8f0); padding: 8px 14px; border-radius: 8px; font-size: 12.5px;">
                    <span style="color: var(--ff-muted);">Active Channels:</span>
                    <strong style="color: #6366f1; margin-left: 4px;">Transfers (High)</strong>, <strong style="color: #e0392e; margin-left: 4px;">Security (Max)</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Main 2-Column Section --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 24px; margin-bottom: 24px;">
        
        {{-- Left: Preset Quick-Test Buttons --}}
        <div class="ff-card" style="padding: 24px;">
            <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                1-Click Quick Presets
            </h3>
            <p style="font-size: 13px; color: var(--ff-muted); margin-bottom: 18px;">Instantly trigger real-world application notification scenarios.</p>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                {{-- Preset 1: File Upload Finished --}}
                <div style="background: var(--ff-bg-2, #f8fafc); border: 1px solid var(--ff-border, #e2e8f0); border-radius: 12px; padding: 14px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 38px; height: 38px; border-radius: 8px; background: rgba(16, 185, 129, 0.15); color: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="12" y2="12"/><line x1="15" y1="15" x2="12" y2="12"/></svg>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 600;">File Upload Completed</div>
                            <div style="font-size: 12px; color: var(--ff-muted);">Project_Archive.zip (14.2 MB) encrypted & stored</div>
                        </div>
                    </div>
                    <button type="button" class="ff-btn btn-trigger-preset" data-preset="fileUpload" data-title="📁 File Uploaded" data-body="Project_Archive.zip (14.2 MB) has been encrypted & stored in Vault." data-channel="filefusion_transfers" style="padding: 6px 14px; font-size: 12.5px; font-weight: 600; background: #10b981; color: #fff; border: none; border-radius: 8px;">
                        Test
                    </button>
                </div>

                {{-- Preset 2: Security & 2FA Alert --}}
                <div style="background: var(--ff-bg-2, #f8fafc); border: 1px solid var(--ff-border, #e2e8f0); border-radius: 12px; padding: 14px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 38px; height: 38px; border-radius: 8px; background: rgba(224, 57, 46, 0.15); color: #e0392e; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 600;">Vault Security Alert</div>
                            <div style="font-size: 12px; color: var(--ff-muted);">Master Vault unlocked on this device</div>
                        </div>
                    </div>
                    <button type="button" class="ff-btn btn-trigger-preset" data-preset="vaultUnlocked" data-title="🛡️ Security Alert" data-body="Master Vault unlocked. Session active for 30 mins." data-channel="filefusion_security" style="padding: 6px 14px; font-size: 12.5px; font-weight: 600; background: #e0392e; color: #fff; border: none; border-radius: 8px;">
                        Test
                    </button>
                </div>

                {{-- Preset 3: Storage Quota Warning --}}
                <div style="background: var(--ff-bg-2, #f8fafc); border: 1px solid var(--ff-border, #e2e8f0); border-radius: 12px; padding: 14px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 38px; height: 38px; border-radius: 8px; background: rgba(245, 158, 11, 0.15); color: #f59e0b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 600;">Storage Quota Alert</div>
                            <div style="font-size: 12px; color: var(--ff-muted);">88% of assigned 5.0 GB storage used</div>
                        </div>
                    </div>
                    <button type="button" class="ff-btn btn-trigger-preset" data-preset="storageWarning" data-title="⚠️ Storage Warning" data-body="Storage capacity reached 88% (4.4 GB of 5.0 GB used)." data-channel="filefusion_transfers" style="padding: 6px 14px; font-size: 12.5px; font-weight: 600; background: #f59e0b; color: #fff; border: none; border-radius: 8px;">
                        Test
                    </button>
                </div>

                {{-- Preset 4: Share Sheet Bookmark Captured --}}
                <div style="background: var(--ff-bg-2, #f8fafc); border: 1px solid var(--ff-border, #e2e8f0); border-radius: 12px; padding: 14px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 38px; height: 38px; border-radius: 8px; background: rgba(99, 102, 241, 0.15); color: #6366f1; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 600;">Link Saved via Share Sheet</div>
                            <div style="font-size: 12px; color: var(--ff-muted);">Received from browser intent</div>
                        </div>
                    </div>
                    <button type="button" class="ff-btn btn-trigger-preset" data-preset="linkSaved" data-title="🔗 Link Saved" data-body="Saved 'Laravel Documentation' to your Dev & Cloud category." data-channel="filefusion_transfers" style="padding: 6px 14px; font-size: 12.5px; font-weight: 600; background: #6366f1; color: #fff; border: none; border-radius: 8px;">
                        Test
                    </button>
                </div>

                {{-- Preset 5: Task / Todo Reminder --}}
                <div style="background: var(--ff-bg-2, #f8fafc); border: 1px solid var(--ff-border, #e2e8f0); border-radius: 12px; padding: 14px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 38px; height: 38px; border-radius: 8px; background: rgba(139, 92, 246, 0.15); color: #8b5cf6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 600;">Todo Task Reminder</div>
                            <div style="font-size: 12px; color: var(--ff-muted);">Sprint task due in 30 minutes</div>
                        </div>
                    </div>
                    <button type="button" class="ff-btn btn-trigger-preset" data-preset="taskDue" data-title="📋 Task Reminder" data-body="Action Item: 'Review FileFusion mobile build' is due in 30 mins." data-channel="filefusion_transfers" style="padding: 6px 14px; font-size: 12.5px; font-weight: 600; background: #8b5cf6; color: #fff; border: none; border-radius: 8px;">
                        Test
                    </button>
                </div>
            </div>
        </div>

        {{-- Right: Custom Notification Builder & Delayed Scheduler --}}
        <div class="ff-card" style="padding: 24px;">
            <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                Custom Notification Builder
            </h3>
            <p style="font-size: 13px; color: var(--ff-muted); margin-bottom: 18px;">Craft a custom payload and test delayed push scheduling.</p>

            <form id="form-custom-notif" onsubmit="return false;">
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; margin-bottom: 6px;">Notification Title</label>
                    <input type="text" id="custom-title" class="ff-input" value="🚀 FileFusion System Notification" placeholder="Enter title..." style="width: 100%;" required>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; margin-bottom: 6px;">Notification Message / Body</label>
                    <textarea id="custom-body" class="ff-input" rows="3" placeholder="Enter message body..." style="width: 100%; font-size: 13px; resize: vertical;" required>Your encrypted workspace synchronization has finished successfully.</textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 12.5px; font-weight: 600; margin-bottom: 6px;">Android Channel</label>
                        <select id="custom-channel" class="ff-input" style="width: 100%;">
                            <option value="filefusion_transfers">Transfers (High Importance)</option>
                            <option value="filefusion_security">Security (Max Importance / Heads-up)</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12.5px; font-weight: 600; margin-bottom: 6px;">Delivery Delay</label>
                        <select id="custom-delay" class="ff-input" style="width: 100%;">
                            <option value="0">Instant (0 seconds)</option>
                            <option value="5">5 Seconds Delay (Test Background)</option>
                            <option value="10">10 Seconds Delay</option>
                            <option value="30">30 Seconds Delay</option>
                        </select>
                    </div>
                </div>

                <button type="submit" id="btn-send-custom" class="ff-btn is-primary" style="width: 100%; padding: 12px; font-size: 14px; font-weight: 700; background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff; border: none; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.35); cursor: pointer;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Send Test Notification
                </button>
            </form>

            <div id="countdown-banner" style="display: none; margin-top: 14px; padding: 12px; border-radius: 8px; background: rgba(99, 102, 241, 0.1); border: 1px dashed #6366f1; text-align: center; font-size: 13px; font-weight: 600; color: #6366f1;">
                ⏳ Notification scheduled! Minimize the app now to see it in your Android status bar in <span id="countdown-sec">5</span>s.
            </div>
        </div>
    </div>

    {{-- Registered Cloud Devices (VAPID & FCM) --}}
    <div class="ff-card" style="padding: 24px; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 style="font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; margin: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"/><rect x="2" y="14" width="20" height="8" rx="2" ry="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
                    Multi-Device Cloud Push Registry (VAPID & FCM)
                </h3>
                <p style="font-size: 13px; color: var(--ff-muted); margin: 4px 0 0 0;">Active devices registered to receive background server push when closed or asleep.</p>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <button type="button" id="btn-reregister-device" class="ff-btn" style="padding: 8px 14px; font-size: 13px; font-weight: 600; background: var(--ff-card); color: var(--ff-text); border: 1px solid var(--ff-border); border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                    Re-register Current Device
                </button>
                <button type="button" id="btn-send-cloud-push" class="ff-btn" style="padding: 8px 16px; font-size: 13px; font-weight: 600; background: #6366f1; color: #fff; border: none; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); cursor: pointer;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Send Cloud Push to All My Devices
                </button>
            </div>
        </div>

        @if (isset($devices) && $devices->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 14px;">
                @foreach ($devices as $dev)
                    <div id="device-card-{{ $dev->id }}" style="background: var(--ff-bg-2, #f8fafc); border: 1px solid var(--ff-border, #e2e8f0); border-radius: 12px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                            <div style="width: 40px; height: 40px; border-radius: 10px; background: {{ $dev->is_active ? 'rgba(16, 185, 129, 0.12)' : 'rgba(148, 163, 184, 0.15)' }}; color: {{ $dev->is_active ? '#10b981' : '#94a3b8' }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                @if ($dev->platform === 'android')
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                @elseif ($dev->platform === 'ios' || str_contains(strtolower($dev->device_name), 'iphone') || str_contains(strtolower($dev->device_name), 'ipad'))
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                @else
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                                @endif
                            </div>
                            <div style="min-width: 0;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="font-size: 14px; font-weight: 700; color: var(--ff-text);" class="ff-truncate">{{ $dev->device_name ?: 'Registered Device' }}</span>
                                    <span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: {{ $dev->is_active ? '#10b981' : '#94a3b8' }}; box-shadow: 0 0 6px {{ $dev->is_active ? '#10b981' : 'transparent' }}; flex-shrink: 0;" title="{{ $dev->is_active ? 'Active & Reachable' : 'Inactive' }}"></span>
                                </div>
                                <div style="font-size: 11.5px; color: var(--ff-muted); margin-top: 2px;">
                                    {{ strtoupper($dev->push_type) }} · {{ ucfirst($dev->platform) }} · <span style="color: {{ $dev->is_active ? '#10b981' : 'var(--ff-muted)' }}; font-weight: 600;">{{ $dev->is_active ? 'Active' : 'Inactive' }}</span> ({{ $dev->last_active_at ? $dev->last_active_at->diffForHumans() : 'Recently' }})
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                            <button type="button" class="ff-btn btn-test-single-device" data-device-id="{{ $dev->id }}" data-device-name="{{ $dev->device_name }}" style="padding: 6px 12px; font-size: 12px; font-weight: 600; background: var(--ff-card); border: 1px solid var(--ff-border); border-radius: 8px;">
                                Test Push
                            </button>
                            <button type="button" class="ff-btn btn-delete-device" data-device-id="{{ $dev->id }}" data-device-name="{{ $dev->device_name }}" title="Deregister & Remove Device" style="padding: 6px 8px; font-size: 12px; color: #ef4444; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 8px; cursor: pointer;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="padding: 16px; background: var(--ff-bg-2, #f8fafc); border-radius: 8px; font-size: 13px; color: var(--ff-muted); text-align: center;">
                No external cloud push tokens synced yet. Click <strong>"Re-register Current Device"</strong> or grant permission above to register this device.
            </div>
        @endif
    </div>

    {{-- Live Activity & Dispatch Log Feed --}}
    <div class="ff-card" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                Live Notification Dispatch Log
            </h3>
            <button type="button" id="btn-clear-logs" style="background: none; border: none; color: var(--ff-muted); font-size: 12px; cursor: pointer; text-decoration: underline;">Clear Log</button>
        </div>

        <div id="log-feed" style="max-height: 240px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;">
            <div style="padding: 12px 16px; background: var(--ff-bg-2, #f8fafc); border-radius: 8px; font-size: 12.5px; color: var(--ff-muted); text-align: center;">
                No test notifications dispatched in this session yet. Click any test button above to send.
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function initNotificationTester() {
        const hasAndroid = typeof window.AndroidNative !== 'undefined';
        const isCapacitor = !!(window.Capacitor && window.Capacitor.isNativePlatform && window.Capacitor.isNativePlatform());
        const isNative = hasAndroid || isCapacitor;

        const platformName = document.getElementById('platform-name');
        const platformSub = document.getElementById('platform-sub');
        const badgePerm = document.getElementById('badge-perm-status');
        const logFeed = document.getElementById('log-feed');
        let hasLogged = false;

        // 1. Detect platform & Device Name
        async function updateDiagnosticHeader() {
            let device = 'Web Browser';
            if (window.FileFusionNative && window.FileFusionNative.getDeviceName) {
                device = await window.FileFusionNative.getDeviceName();
            }

            if (isNative) {
                platformName.textContent = `Android App (${device})`;
                platformSub.textContent = `Connected to ${device} with full access to Android NotificationManager.`;
                document.getElementById('platform-icon-wrap').innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>';
            } else {
                platformName.textContent = `Browser (${device})`;
                platformSub.textContent = `Running in browser on ${device}. Notifications use Web Notification API.`;
            }
        }
        updateDiagnosticHeader();

        console.log('[NotificationTester] Initializing with FileFusionNative:', window.FileFusionNative);

        // 2. Check Permission Status
        async function refreshPermissionBadge() {
            if (window.FileFusionNative) {
                console.log('[NotificationTester] Checking permissions...');
                const perm = await window.FileFusionNative.checkPermissions();
                console.log('[NotificationTester] Permission result:', perm);
                if (perm === 'granted') {
                    badgePerm.textContent = 'Permission Granted';
                    badgePerm.style.background = 'rgba(16, 185, 129, 0.15)';
                    badgePerm.style.color = '#10b981';
                } else if (perm === 'denied') {
                    badgePerm.textContent = 'Permission Denied';
                    badgePerm.style.background = 'rgba(239, 68, 68, 0.15)';
                    badgePerm.style.color = '#ef4444';
                } else {
                    badgePerm.textContent = 'Permission Required';
                    badgePerm.style.background = 'rgba(245, 158, 11, 0.15)';
                    badgePerm.style.color = '#f59e0b';
                }
            } else {
                console.warn('[NotificationTester] window.FileFusionNative is undefined');
            }
        }
        refreshPermissionBadge();

        function getApiUrl(path) {
            const cleanPath = path.startsWith('/') ? path : '/' + path;
            const pathname = window.location.pathname;
            const publicIdx = pathname.indexOf('/public');
            const panelIdx = pathname.indexOf('/panel');
            let base = '';
            if (publicIdx !== -1) {
                base = pathname.substring(0, publicIdx + 7);
            } else if (panelIdx > 0) {
                base = pathname.substring(0, panelIdx);
            }
            return base + cleanPath;
        }

        function showToast(msg, type = 'info') {
            if (window.ff && typeof window.ff.toast === 'function') {
                window.ff.toast(msg, type);
            } else if (window.toast && typeof window.toast[type] === 'function') {
                window.toast[type](msg);
            } else if (window.toast && typeof window.toast === 'function') {
                window.toast(msg, type);
            } else {
                console.log('[Toast ' + type + ']: ' + msg);
            }
        }

        // 3. Request Permission Click
        const btnReq = document.getElementById('btn-request-perm');
        if (btnReq) {
            btnReq.onclick = async function () {
                console.log('[NotificationTester] Request permission button clicked');
                btnReq.disabled = true;
                btnReq.textContent = 'Requesting...';
                try {
                    if (window.FileFusionNative) {
                        const result = await window.FileFusionNative.requestPermissions();
                        console.log('[NotificationTester] Request result:', result);
                        await refreshPermissionBadge();
                        showToast('Notification permission: ' + (result || 'requested'), 'info');
                        appendLog('Permission Request', 'Permission prompt result: ' + (result || 'prompted'), 'info');
                    }
                } catch (e) {
                    console.error('[NotificationTester] Permission request error:', e);
                    appendLog('Permission Error', e.message || 'Failed', 'error');
                } finally {
                    btnReq.disabled = false;
                    btnReq.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> Request Permission';
                }
            };
        }

        // 4. Logging Helper
        function appendLog(title, message, status = 'success', delay = 0) {
            if (!hasLogged) {
                logFeed.innerHTML = '';
                hasLogged = true;
            }
            const time = new Date().toLocaleTimeString();
            const row = document.createElement('div');
            row.style.cssText = 'padding: 10px 14px; background: var(--ff-bg-2, #f8fafc); border: 1px solid var(--ff-border, #e2e8f0); border-radius: 8px; display: flex; align-items: center; justify-content: space-between; gap: 12px; font-size: 13px;';
            
            const badgeColor = status === 'success' ? '#10b981' : (status === 'delayed' ? '#6366f1' : '#f59e0b');
            const badgeText = status === 'delayed' ? `Scheduled in ${delay}s` : 'Dispatched';

            row.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-family: monospace; font-size: 11px; color: var(--ff-muted);">${time}</span>
                    <strong style="color: var(--ff-text);">${title}</strong>
                    <span style="color: var(--ff-muted); font-size: 12px;">— ${message}</span>
                </div>
                <span style="font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px; background: ${badgeColor}22; color: ${badgeColor}; flex-shrink: 0;">${badgeText}</span>
            `;
            logFeed.prepend(row);
        }

        const btnClear = document.getElementById('btn-clear-logs');
        if (btnClear) {
            btnClear.onclick = function () {
                logFeed.innerHTML = '<div style="padding: 12px 16px; background: var(--ff-bg-2, #f8fafc); border-radius: 8px; font-size: 12.5px; color: var(--ff-muted); text-align: center;">Log cleared.</div>';
                hasLogged = false;
            };
        }

        // 5. Trigger Presets
        document.querySelectorAll('.btn-trigger-preset').forEach(btn => {
            btn.onclick = async function () {
                const preset = this.dataset.preset;
                const title = this.dataset.title;
                const body = this.dataset.body;
                const channel = this.dataset.channel;

                this.disabled = true;
                this.textContent = 'Sending...';

                try {
                    if (window.FileFusionNative) {
                        if (preset && window.FileFusionNative.notifyPreset) {
                            await window.FileFusionNative.notifyPreset(preset);
                        } else {
                            await window.FileFusionNative.notify({
                                title: title,
                                body: body,
                                channelId: channel,
                                delay: 0
                            });
                        }
                    }
                    appendLog(title, body, 'success', 0);
                    showToast('Notification Dispatched: ' + title, 'success');
                } catch (err) {
                    appendLog(title, 'Failed: ' + err.message, 'error', 0);
                    showToast('Error: ' + err.message, 'error');
                } finally {
                    this.disabled = false;
                    this.textContent = 'Test';
                }
            };
        });

        // 6. Custom Notification Sender & Delayed Countdown
        const btnSendCustom = document.getElementById('btn-send-custom');
        if (btnSendCustom) {
            btnSendCustom.onclick = async function () {
                const title = document.getElementById('custom-title').value.trim();
                const body = document.getElementById('custom-body').value.trim();
                const channel = document.getElementById('custom-channel').value;
                const delay = parseInt(document.getElementById('custom-delay').value, 10);

                if (!title || !body) {
                    showToast('Please enter title and message.', 'warning');
                    return;
                }

                btnSendCustom.disabled = true;

                if (delay > 0) {
                    const countdownBanner = document.getElementById('countdown-banner');
                    const countdownSec = document.getElementById('countdown-sec');
                    countdownBanner.style.display = 'block';
                    let remaining = delay;
                    countdownSec.textContent = remaining;

                    const timer = setInterval(() => {
                        remaining--;
                        countdownSec.textContent = remaining;
                        if (remaining <= 0) {
                            clearInterval(timer);
                            countdownBanner.style.display = 'none';
                            btnSendCustom.disabled = false;
                        }
                    }, 1000);
                }

                try {
                    if (window.FileFusionNative) {
                        await window.FileFusionNative.notify({
                            title: title,
                            body: body,
                            channelId: channel,
                            delay: delay
                        });
                    }
                    appendLog(title, body, delay > 0 ? 'delayed' : 'success', delay);
                    showToast(delay > 0 ? `Notification scheduled in ${delay}s!` : 'Notification sent!', 'success');
                } catch (err) {
                    appendLog(title, 'Error: ' + err.message, 'error', 0);
                    showToast('Error: ' + err.message, 'error');
                } finally {
                    if (delay === 0) {
                        btnSendCustom.disabled = false;
                    }
                }
            };
        }

        // 7. Trigger Server Cloud Push (VAPID / FCM)
        const btnSendCloud = document.getElementById('btn-send-cloud-push');
        if (btnSendCloud) {
            btnSendCloud.onclick = async function () {
                btnSendCloud.disabled = true;
                btnSendCloud.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Dispatching Cloud Push...';

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const endpoint = getApiUrl('/devices/send-test-push');
                    const res = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token || '',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            title: '🛡️ FileFusion Cloud Push',
                            body: 'Live server push received across registered devices via VAPID & FCM.',
                            url: '/panel'
                        })
                    });

                    if (!res.ok) {
                        const errText = await res.text();
                        let parsedErr = `Server returned HTTP ${res.status}`;
                        try {
                            const errJson = JSON.parse(errText);
                            parsedErr = errJson.message || errJson.error || parsedErr;
                        } catch (e) {}
                        appendLog('Cloud Push Error', parsedErr, 'error', 0);
                        showToast(parsedErr, 'error');
                        return;
                    }

                    const data = await res.json();
                    if (data.ok) {
                        const total = data.results?.total || 0;
                        const sent = data.results?.sent || 0;
                        appendLog('Cloud Push Dispatched', `Broadcasted to ${sent}/${total} registered devices.`, sent > 0 ? 'success' : 'error', 0);
                        if (sent > 0) {
                            showToast(`Cloud push dispatched to ${sent} devices!`, 'success');
                        } else {
                            showToast(`Push sent but 0 devices were reachable. Try re-registering below.`, 'warning');
                        }
                    } else {
                        const errMsg = data.error || (data.results?.details?.[0]?.error) || 'Failed to dispatch cloud push';
                        appendLog('Cloud Push Error', errMsg, 'error', 0);
                        showToast(errMsg, 'error');
                    }
                } catch (e) {
                    appendLog('Cloud Push Error', e.message, 'error', 0);
                    showToast('Error: ' + e.message, 'error');
                } finally {
                    btnSendCloud.disabled = false;
                    btnSendCloud.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Send Cloud Push to All My Devices';
                }
            };
        }

        // 8. Individual Single Device Test Button
        document.querySelectorAll('.btn-test-single-device').forEach(btn => {
            btn.onclick = async function () {
                const deviceId = this.dataset.deviceId;
                const deviceName = this.dataset.deviceName;
                this.disabled = true;
                this.textContent = 'Sending...';

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const endpoint = getApiUrl('/devices/send-test-push');
                    const res = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token || '',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            device_id: deviceId,
                            title: '🛡️ FileFusion Device Test',
                            body: `Direct test push successfully received on ${deviceName}.`,
                            url: '/panel'
                        })
                    });

                    if (!res.ok) {
                        const errText = await res.text();
                        let parsedErr = `Server returned HTTP ${res.status}`;
                        try {
                            const errJson = JSON.parse(errText);
                            parsedErr = errJson.message || errJson.error || parsedErr;
                        } catch (e) {}
                        appendLog(`Push Error -> ${deviceName}`, parsedErr, 'error', 0);
                        showToast(parsedErr, 'error');
                        return;
                    }

                    const data = await res.json();
                    if (data.ok) {
                        const detail = data.results?.details?.[0] || {};
                        appendLog(`Push -> ${deviceName}`, `Delivered successfully via ${detail.mode || 'Cloud'}!`, 'success', 0);
                        showToast(`Test push sent to ${deviceName}!`, 'success');
                    } else {
                        const detail = data.results?.details?.[0] || {};
                        const errMsg = data.error || detail.error || 'Delivery failed';
                        appendLog(`Push Error -> ${deviceName}`, errMsg, 'error', 0);
                        showToast(errMsg, 'error');
                    }
                } catch (e) {
                    appendLog(`Push Error -> ${deviceName}`, e.message, 'error', 0);
                    showToast('Error: ' + e.message, 'error');
                } finally {
                    this.disabled = false;
                    this.textContent = 'Test Push';
                }
            };
        });

        // 9. Re-register Current Device Button
        const btnReregister = document.getElementById('btn-reregister-device');
        if (btnReregister) {
            btnReregister.onclick = async function () {
                btnReregister.disabled = true;
                btnReregister.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Re-registering...';

                try {
                    if (window.FileFusionNative && typeof window.FileFusionNative.reregisterPushNotifications === 'function') {
                        await window.FileFusionNative.reregisterPushNotifications();
                        appendLog('Device Re-registered', 'Push credentials synced with server successfully.', 'success', 0);
                        showToast('Device push credentials refreshed & registered!', 'success');
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        showToast('Push registration is not supported on this browser.', 'warning');
                    }
                } catch (err) {
                    appendLog('Re-registration Error', err.message || 'Failed', 'error', 0);
                    showToast('Failed to re-register: ' + (err.message || 'Error'), 'error');
                } finally {
                    btnReregister.disabled = false;
                    btnReregister.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg> Re-register Current Device';
                }
            };
        }

        // 10. Delete / Deregister Device Button
        document.querySelectorAll('.btn-delete-device').forEach(btn => {
            btn.onclick = async function () {
                const deviceId = this.dataset.deviceId;
                const deviceName = this.dataset.deviceName;
                if (!confirm(`Are you sure you want to deregister and remove "${deviceName}"?`)) {
                    return;
                }

                this.disabled = true;
                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const endpoint = getApiUrl(`/devices/${deviceId}`);
                    const res = await fetch(endpoint, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token || '',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await res.json();
                    if (data.ok) {
                        const card = document.getElementById(`device-card-${deviceId}`);
                        if (card) {
                            card.style.transition = 'opacity 0.3s, transform 0.3s';
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.95)';
                            setTimeout(() => card.remove(), 300);
                        }
                        appendLog('Device Deregistered', `Removed ${deviceName} from push registry.`, 'info', 0);
                        showToast(`Device ${deviceName} deregistered.`, 'info');
                    } else {
                        showToast(data.error || 'Failed to delete device', 'error');
                    }
                } catch (e) {
                    showToast('Error: ' + e.message, 'error');
                }
            };
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNotificationTester);
    } else {
        initNotificationTester();
    }
})();
</script>
@endsection

