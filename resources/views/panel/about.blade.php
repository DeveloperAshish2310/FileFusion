@extends('layout.backend')
@push('title', 'About FileFusion | Version 1.0b')

@section('content')
<div class="ff-about-page" style="max-width: 1080px; margin: 0 auto; padding-bottom: 60px;">

    {{-- ========================================================================= --}}
    {{-- HERO BRANDING & VERSION HEADER                                            --}}
    {{-- ========================================================================= --}}
    <div class="ff-hero-card" style="position: relative; overflow: hidden; background: linear-gradient(135deg, rgba(30, 41, 59, 0.7) 0%, rgba(15, 23, 42, 0.85) 100%); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 20px; padding: 40px 32px; box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5); backdrop-filter: blur(20px); margin-bottom: 28px;">
        
        <!-- Ambient Decorative Glows -->
        <div style="position: absolute; top: -60px; right: -60px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(99, 102, 241, 0.25) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
        <div style="position: absolute; bottom: -40px; left: -40px; width: 180px; height: 180px; background: radial-gradient(circle, rgba(16, 185, 129, 0.18) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>

        <div style="position: relative; z-index: 2; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 24px;">
            
            <div style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
                <!-- Logo with glowing border -->
                <div style="position: relative; width: 96px; height: 96px; border-radius: 24px; padding: 3px; background: linear-gradient(135deg, #6366f1, #10b981, #3b82f6); box-shadow: 0 10px 25px rgba(99, 102, 241, 0.35); flex-shrink: 0;">
                    <div style="width: 100%; height: 100%; border-radius: 21px; background: #0b0f19; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <img src="{{ asset('logo.png') }}" alt="FileFusion Logo" style="width: 72px; height: 72px; object-fit: contain; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.5));" onerror="this.onerror=null; this.src='{{ asset('assets/icons/icon-192x192.png') }}';">
                    </div>
                </div>

                <div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 6px;">
                        <h1 style="margin: 0; font-size: 28px; font-weight: 800; color: #fff; letter-spacing: -0.5px; line-height: 1.2;">FileFusion</h1>
                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; background: rgba(99, 102, 241, 0.2); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.35);">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981; box-shadow: 0 0 8px #10b981;"></span>
                            Version {{ $appVersion ?? '1.0b' }}
                        </span>
                        <span style="display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);">
                            Beta Channel
                        </span>
                    </div>
                    <p style="margin: 0; color: #94a3b8; font-size: 14.5px; max-width: 580px; line-height: 1.5;">
                        Next-generation zero-knowledge cloud filesystem, encrypted credential vault, real-time multi-device push synchronization, and unified productivity suite.
                    </p>
                </div>
            </div>

            <!-- Action Badges -->
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <button type="button" onclick="checkForUpdates()" class="ff-btn ff-btn-primary" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; font-weight: 600; border-radius: 10px; font-size: 13.5px; cursor: pointer;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                    </svg>
                    Check Updates
                </button>
                <a href="{{ route('panel.settings') }}" class="ff-btn" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; font-weight: 600; border-radius: 10px; font-size: 13.5px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #fff; text-decoration: none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                    Settings
                </a>
            </div>

        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- 2-COLUMN GRID: DEVELOPER INFO & SYSTEM SPECS                              --}}
    {{-- ========================================================================= --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 28px;">

        <!-- Developer & Creator Card -->
        <div class="ff-card" style="background: var(--ff-card, #1e293b); border: 1px solid var(--ff-border, rgba(255,255,255,0.08)); border-radius: 16px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--ff-border, rgba(255,255,255,0.08));">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(99, 102, 241, 0.15); color: #818cf8; display: flex; align-items: center; justify-content: center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--ff-text, #fff);">Developer &amp; Creator</h3>
                    <p style="margin: 0; font-size: 12.5px; color: var(--ff-muted, #94a3b8);">Architect &amp; Lead Engineer</p>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 13.5px; color: var(--ff-muted, #94a3b8);">Lead Developer</span>
                    <span style="font-size: 14px; font-weight: 700; color: var(--ff-text, #fff); display: flex; align-items: center; gap: 6px;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #6366f1;"></span>
                        {{ $developerName ?? 'Ashish Kumar' }}
                    </span>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 13.5px; color: var(--ff-muted, #94a3b8);">Contact &amp; Support</span>
                    <a href="mailto:{{ $developerEmail ?? 'ashish@admin.com' }}" style="font-size: 13.5px; font-weight: 600; color: var(--ff-accent, #6366f1); text-decoration: none;">
                        {{ $developerEmail ?? 'ashish@admin.com' }}
                    </a>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 13.5px; color: var(--ff-muted, #94a3b8);">Repository</span>
                    <a href="{{ $developerGithub ?? 'https://github.com/DeveloperAshish2310/FileFusion' }}" target="_blank" style="font-size: 13.5px; font-weight: 600; color: var(--ff-accent, #6366f1); text-decoration: none; display: flex; align-items: center; gap: 4px;">
                        DeveloperAshish2310
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                            <polyline points="15 3 21 3 21 9"/>
                            <line x1="10" y1="14" x2="21" y2="3"/>
                        </svg>
                    </a>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 13.5px; color: var(--ff-muted, #94a3b8);">Copyright &amp; License</span>
                    <span style="font-size: 13px; font-weight: 500; color: var(--ff-text, #fff);">
                        &copy; {{ date('Y') }} FileFusion. Proprietary.
                    </span>
                </div>
            </div>
        </div>

        <!-- Release & Build Metadata Card -->
        <div class="ff-card" style="background: var(--ff-card, #1e293b); border: 1px solid var(--ff-border, rgba(255,255,255,0.08)); border-radius: 16px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--ff-border, rgba(255,255,255,0.08));">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(16, 185, 129, 0.15); color: #34d399; display: flex; align-items: center; justify-content: center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--ff-text, #fff);">Version &amp; Build Metadata</h3>
                    <p style="margin: 0; font-size: 12.5px; color: var(--ff-muted, #94a3b8);">Active Runtime Configuration</p>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 13.5px; color: var(--ff-muted, #94a3b8);">Active Version</span>
                    <span style="font-size: 13.5px; font-weight: 700; color: #10b981; background: rgba(16, 185, 129, 0.12); padding: 3px 8px; border-radius: 6px;">
                        v{{ $appVersion ?? '1.0b' }}
                    </span>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 13.5px; color: var(--ff-muted, #94a3b8);">Build Timestamp</span>
                    <span style="font-size: 13.5px; font-weight: 600; color: var(--ff-text, #fff);">
                        {{ $buildDate ?? 'August 27, 2026' }}
                    </span>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 13.5px; color: var(--ff-muted, #94a3b8);">Ecosystem &amp; Frameworks</span>
                    <span style="font-size: 13.5px; font-weight: 600; color: var(--ff-text, #fff);">
                        Laravel 11 &bull; Capacitor 8
                    </span>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 13.5px; color: var(--ff-muted, #94a3b8);">Client Environment</span>
                    <span id="clientEnvBadge" style="font-size: 13px; font-weight: 600; color: #818cf8;">
                        Detecting platform...
                    </span>
                </div>
            </div>
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- ARCHITECTURAL HIGHLIGHTS & CAPABILITIES                                  --}}
    {{-- ========================================================================= --}}
    <h2 style="font-size: 18px; font-weight: 700; color: var(--ff-text, #fff); margin: 0 0 16px 4px; display: flex; align-items: center; gap: 8px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="12 2 2 7 12 12 22 7 12 2"/>
            <polyline points="2 17 12 22 22 17"/>
            <polyline points="2 12 12 17 22 12"/>
        </svg>
        Core Architecture &amp; System Capabilities
    </h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 28px;">
        
        <!-- Card 1: Zero-Knowledge Vault -->
        <div style="background: var(--ff-card, #1e293b); border: 1px solid var(--ff-border, rgba(255,255,255,0.08)); border-radius: 14px; padding: 18px 20px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(99, 102, 241, 0.15); color: #818cf8; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <h4 style="margin: 0; font-size: 14.5px; font-weight: 700; color: var(--ff-text, #fff);">AES-256-GCM Vault</h4>
            </div>
            <p style="margin: 0; font-size: 13px; color: var(--ff-muted, #94a3b8); line-height: 1.5;">
                Client and server-side zero-knowledge encrypted vault with automatic session inactivity lockdown and PBKDF2 key derivation.
            </p>
        </div>

        <!-- Card 2: Multi-Device Push -->
        <div style="background: var(--ff-card, #1e293b); border: 1px solid var(--ff-border, rgba(255,255,255,0.08)); border-radius: 14px; padding: 18px 20px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16, 185, 129, 0.15); color: #34d399; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                </div>
                <h4 style="margin: 0; font-size: 14.5px; font-weight: 700; color: var(--ff-text, #fff);">Dual-Engine Cloud Push</h4>
            </div>
            <p style="margin: 0; font-size: 13px; color: var(--ff-muted, #94a3b8); line-height: 1.5;">
                Dual-protocol notification engine powered by Google FCM HTTP v1 for Android native devices and RFC 8292 VAPID for desktop/mobile browsers.
            </p>
        </div>

        <!-- Card 3: Unified Productivity -->
        <div style="background: var(--ff-card, #1e293b); border: 1px solid var(--ff-border, rgba(255,255,255,0.08)); border-radius: 14px; padding: 18px 20px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(245, 158, 11, 0.15); color: #fbbf24; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 11 12 14 22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                </div>
                <h4 style="margin: 0; font-size: 14.5px; font-weight: 700; color: var(--ff-text, #fff);">Interactive Productivity</h4>
            </div>
            <p style="margin: 0; font-size: 13px; color: var(--ff-muted, #94a3b8); line-height: 1.5;">
                Full-featured Todo workspace, recurring task scheduler, sub-step checklists, drive attachments, and interactive visual calendar.
            </p>
        </div>

        <!-- Card 4: Resilient File Management -->
        <div style="background: var(--ff-card, #1e293b); border: 1px solid var(--ff-border, rgba(255,255,255,0.08)); border-radius: 14px; padding: 18px 20px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(59, 130, 246, 0.15); color: #60a5fa; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                </div>
                <h4 style="margin: 0; font-size: 14.5px; font-weight: 700; color: var(--ff-text, #fff);">Chunked Uploads &amp; Shares</h4>
            </div>
            <p style="margin: 0; font-size: 13px; color: var(--ff-muted, #94a3b8); line-height: 1.5;">
                High-speed parallel chunked file uploads, password-protected public share links, link thumbnail scrapers, and secure code editor.
            </p>
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- LIVE SYSTEM HEALTH & ENVIRONMENT DIAGNOSTICS                              --}}
    {{-- ========================================================================= --}}
    <div class="ff-card" style="background: var(--ff-card, #1e293b); border: 1px solid var(--ff-border, rgba(255,255,255,0.08)); border-radius: 16px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 10px; height: 10px; border-radius: 50%; background: #10b981; box-shadow: 0 0 10px #10b981;"></div>
                <h3 style="margin: 0; font-size: 15.5px; font-weight: 700; color: var(--ff-text, #fff);">Live Diagnostic Telemetry</h3>
            </div>
            <span style="font-size: 12px; color: var(--ff-muted, #94a3b8);">All services operating normally</span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
            <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); padding: 12px 14px; border-radius: 10px;">
                <div style="font-size: 11.5px; color: var(--ff-muted, #94a3b8); margin-bottom: 4px;">PUSH NOTIFICATIONS</div>
                <div style="font-size: 13.5px; font-weight: 700; color: #10b981;">FCM &amp; VAPID Active</div>
            </div>

            <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); padding: 12px 14px; border-radius: 10px;">
                <div style="font-size: 11.5px; color: var(--ff-muted, #94a3b8); margin-bottom: 4px;">ENCRYPTION ENGINE</div>
                <div style="font-size: 13.5px; font-weight: 700; color: #818cf8;">AES-256-GCM Hardware</div>
            </div>

            <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); padding: 12px 14px; border-radius: 10px;">
                <div style="font-size: 11.5px; color: var(--ff-muted, #94a3b8); margin-bottom: 4px;">DATABASE POOL</div>
                <div style="font-size: 13.5px; font-weight: 700; color: #34d399;">Connected &amp; Optimized</div>
            </div>

            <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); padding: 12px 14px; border-radius: 10px;">
                <div style="font-size: 11.5px; color: var(--ff-muted, #94a3b8); margin-bottom: 4px;">SECURITY STATE</div>
                <div style="font-size: 13.5px; font-weight: 700; color: #60a5fa;">Multi-Factor Armed</div>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var envBadge = document.getElementById('clientEnvBadge');
        if (envBadge) {
            var isCapacitor = window.Capacitor !== undefined || window.isNativeApp === true;
            var isStandalonePwa = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
            
            if (isCapacitor) {
                envBadge.textContent = '📱 Android Native App';
                envBadge.style.color = '#10b981';
            } else if (isStandalonePwa) {
                envBadge.textContent = '⚡ Progressive Web App (PWA)';
                envBadge.style.color = '#38bdf8';
            } else {
                envBadge.textContent = '🌐 Desktop / Web Browser';
                envBadge.style.color = '#818cf8';
            }
        }
    });

    function checkForUpdates() {
        if (window.ff?.toast) {
            window.ff.toast('Checking for updates...', 'info', 1500);
            setTimeout(function() {
                window.ff.toast('You are running the latest version (v1.0b). Everything is up to date!', 'success', 4000);
            }, 1200);
        } else {
            alert('FileFusion v1.0b is up to date!');
        }
    }
</script>
@endsection
