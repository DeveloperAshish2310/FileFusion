/**
 * FileFusion Native Android Bridge (Capacitor.js Integration)
 * Seamlessly integrates Native Android features into Laravel Blade UI
 */
(function () {
    'use strict';

    const isNativeApp = window.Capacitor && window.Capacitor.isNativePlatform();

    console.log('[FileFusion Native Bridge] Initialized. Native platform:', isNativeApp);

    // 1. Android Share Sheet Intent Listener
    window.addEventListener('filefusion:android-share', function (e) {
        const detail = e.detail || {};
        console.log('[FileFusion Native Bridge] Received Android Share Sheet Intent:', detail);

        if (detail.mode === 'text' && detail.text) {
            const urlMatch = detail.text.match(/https?:\/\/[^\s]+/);
            const targetUrl = urlMatch ? urlMatch[0] : detail.text;

            // If already on addlink page, populate field
            const urlInput = document.getElementById('url_field') || document.querySelector('input[name="url"]');
            if (urlInput) {
                urlInput.value = targetUrl;
                urlInput.dispatchEvent(new Event('input', { bubbles: true }));
                urlInput.focus();
            } else {
                // Navigate to addlink with prefilled param
                window.location.href = '/panel/addlink?prefill_url=' + encodeURIComponent(targetUrl);
            }
        } else if (detail.mode === 'file' || detail.mode === 'files') {
            // Navigate to upload page or trigger upload modal
            const uploadBtn = document.querySelector('[data-modal-target="uploadModal"]') || document.querySelector('#upload-btn');
            if (uploadBtn) {
                uploadBtn.click();
            } else if (!window.location.pathname.includes('/panel/uploadfile')) {
                window.location.href = '/panel/uploadfile?intent=share';
            }
        }
    });

    // 2. Connectivity Status Listener
    if (isNativeApp && window.Capacitor.Plugins.Network) {
        const Network = window.Capacitor.Plugins.Network;

        Network.getStatus().then(status => {
            updateNetworkUI(status.connected);
        });

        Network.addListener('networkStatusChange', status => {
            updateNetworkUI(status.connected);
        });
    }

    function updateNetworkUI(isConnected) {
        let banner = document.getElementById('ff-native-offline-banner');
        if (!isConnected) {
            if (!banner) {
                banner = document.createElement('div');
                banner.id = 'ff-native-offline-banner';
                banner.innerHTML = '<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#ef4444;margin-right:8px;"></span> Offline Mode — Viewing cached workspace. Actions will sync when connected.';
                banner.style.cssText = 'position:fixed;top:0;left:0;right:0;z-index:99999;background:rgba(239,68,68,0.95);color:#fff;font-size:12px;font-weight:600;text-align:center;padding:8px 16px;backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(0,0,0,0.15);';
                document.body.appendChild(banner);
            }
            banner.style.display = 'flex';
        } else if (banner) {
            banner.style.display = 'none';
        }
    }

    // 3. Native Local Notifications Initialization
    if (isNativeApp && window.Capacitor.Plugins.LocalNotifications) {
        const LocalNotifications = window.Capacitor.Plugins.LocalNotifications;

        LocalNotifications.createChannel({
            id: 'filefusion_transfers',
            name: 'File Transfers & Uploads',
            description: 'Progress and completion notifications for encrypted uploads',
            importance: 3,
            visibility: 1
        }).catch(err => console.warn('[Native Bridge] Notification channel creation:', err));

        LocalNotifications.createChannel({
            id: 'filefusion_security',
            name: 'Security & Auth Alerts',
            description: 'Two-factor and security notifications',
            importance: 5,
            visibility: 1
        }).catch(err => console.warn('[Native Bridge] Security channel creation:', err));
    }

    // Export global helper for triggers
    window.FileFusionNative = {
        isNative: isNativeApp,
        notify: async function (title, body, channelId = 'filefusion_transfers') {
            if (isNativeApp && window.Capacitor.Plugins.LocalNotifications) {
                try {
                    await window.Capacitor.Plugins.LocalNotifications.schedule({
                        notifications: [
                            {
                                title: title,
                                body: body,
                                id: Math.floor(Date.now() % 100000),
                                channelId: channelId,
                                smallIcon: 'ic_stat_filefusion'
                            }
                        ]
                    });
                } catch (e) {
                    console.error('[Native Bridge] Failed to trigger notification:', e);
                }
            }
        }
    };
})();
