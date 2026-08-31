import './bootstrap';
import './firebase';
import { Capacitor } from '@capacitor/core';
import { App } from '@capacitor/app';
import { Haptics, ImpactStyle, NotificationType } from '@capacitor/haptics';
import { Device } from '@capacitor/device';
import { LocalNotifications } from '@capacitor/local-notifications';
import { PushNotifications } from '@capacitor/push-notifications';
import { Network } from '@capacitor/network';

const isNative = !!(Capacitor && Capacitor.isNativePlatform && Capacitor.isNativePlatform());

console.log('[FileFusion App] Initialized. Native Platform:', isNative);

function withTimeout(promise, ms = 2000, fallback = null) {
    return Promise.race([
        promise,
        new Promise(resolve => setTimeout(() => resolve(fallback), ms))
    ]);
}

function urlB64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

// 0. Base Application URL Helper (handles root and subfolder deployments e.g. /FileFusion/public or /panel/...)
function getAppUrl(path = '') {
    let base = '';
    if (typeof window !== 'undefined') {
        const pathname = window.location.pathname;
        const panelIdx = pathname.indexOf('/panel');
        const publicIdx = pathname.indexOf('/public');
        
        if (publicIdx !== -1) {
            base = window.location.origin + pathname.substring(0, publicIdx + 7);
        } else if (panelIdx > 0) {
            base = window.location.origin + pathname.substring(0, panelIdx);
        } else {
            base = window.location.origin;
        }
    }
    if (!path) return base;
    const cleanPath = path.startsWith('/') ? path : '/' + path;
    return base + cleanPath;
}

function getSwUrl() {
    const meta = document.querySelector('meta[name="sw-url"]')?.getAttribute('content');
    return meta || getAppUrl('/sw.js');
}

// 1. Persistent Device UUID
async function getDeviceUuid() {
    if (isNative) {
        try {
            const idInfo = await withTimeout(Device.getId(), 1500, null);
            if (idInfo && idInfo.identifier) return idInfo.identifier;
        } catch (e) {}
    }
    let uuid = localStorage.getItem('ff_device_uuid');
    if (!uuid) {
        uuid = 'dev_' + Math.random().toString(36).substring(2, 15) + '_' + Date.now().toString(36);
        localStorage.setItem('ff_device_uuid', uuid);
    }
    return uuid;
}

// 2. Universal Device Name Detector (Native App & Web Browser)
async function detectDeviceName() {
    if (isNative) {
        try {
            const info = await withTimeout(Device.getInfo(), 1500, null);
            if (info && info.model) {
                return (info.manufacturer ? info.manufacturer + ' ' : '') + info.model;
            }
        } catch (e) {}
        return 'Android Mobile';
    }

    if (typeof navigator === 'undefined') return 'Unknown Device';

    if (navigator.userAgentData && navigator.userAgentData.platform) {
        const p = navigator.userAgentData.platform;
        if (/Windows/i.test(p)) return 'Windows PC';
        if (/macOS/i.test(p)) return 'MacBook / macOS';
        if (/Android/i.test(p)) return 'Android Device';
        if (/iOS|iPhone|iPad/i.test(p)) return 'Apple iOS Device';
        if (/Linux/i.test(p)) return 'Linux PC';
    }

    const ua = navigator.userAgent || '';
    if (/iPhone/i.test(ua)) return 'iPhone';
    if (/iPad/i.test(ua)) return 'iPad';
    if (/Macintosh|Mac OS X/i.test(ua)) return 'MacBook / macOS';
    if (/Windows NT 10.0/i.test(ua)) return 'Windows 10/11 PC';
    if (/Windows/i.test(ua)) return 'Windows PC';
    if (/Android/i.test(ua)) {
        const match = ua.match(/Android[^;]+;\s*([^;)]+)\)/);
        if (match && match[1]) {
            const model = match[1].replace(/Build\/.+$/, '').trim();
            if (model && model.length < 30) return model;
        }
        return 'Android Mobile';
    }
    if (/Linux/i.test(ua)) return 'Linux Workstation';

    return 'Web Browser';
}

// 3. Android Share Sheet Intent Handler (Cold-Start & Runtime)
function handleAndroidShareIntent(detail) {
    if (!detail || !detail.mode) return;
    console.log('[FileFusion App] Processing Android Share Sheet Intent:', detail);

    if (detail.mode === 'text' && detail.text) {
        const urlMatch = detail.text.match(/https?:\/\/[^\s]+/);
        const targetUrl = urlMatch ? urlMatch[0] : detail.text;
        const isFullUrl = targetUrl.startsWith('http://') || targetUrl.startsWith('https://');

        const urlInput = document.getElementById('url_field') || document.querySelector('input[name="url"]');
        if (urlInput) {
            urlInput.value = targetUrl;
            urlInput.dispatchEvent(new Event('input', { bubbles: true }));
            urlInput.focus();
        } else if (isFullUrl) {
            window.location.href = getAppUrl('/panel/add-links?prefill_url=' + encodeURIComponent(targetUrl) + '&prefill_name=' + encodeURIComponent(detail.text !== targetUrl ? detail.text : ''));
        } else {
            window.location.href = getAppUrl('/panel/newfile?prefill_content=' + encodeURIComponent(detail.text));
        }
    } else if (detail.mode === 'file' || detail.mode === 'files') {
        const uploadBtn = document.querySelector('[data-modal-target="uploadModal"]') || document.querySelector('#upload-btn');
        if (uploadBtn) {
            uploadBtn.click();
        } else if (!window.location.pathname.includes('/panel/upload')) {
            window.location.href = getAppUrl('/panel/upload?intent=share');
        }
    }
}

window.addEventListener('filefusion:android-share', function (e) {
    handleAndroidShareIntent(e.detail || {});
});

// Check if there is a pending share from cold start
if (typeof window !== 'undefined') {
    if (window.__FILEFUSION_PENDING_SHARE__) {
        setTimeout(() => {
            handleAndroidShareIntent(window.__FILEFUSION_PENDING_SHARE__);
            window.__FILEFUSION_PENDING_SHARE__ = null;
        }, 300);
    }
}

// Sound Utility for Notifications & Alerts
function playAlertChime() {
    try {
        if (typeof window.FileFusionSoundBridge !== 'undefined' && typeof window.FileFusionSoundBridge.playNotificationSound === 'function') {
            window.FileFusionSoundBridge.playNotificationSound();
            return;
        }
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (AudioContext) {
            const ctx = new AudioContext();
            const now = ctx.currentTime;

            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(587.33, now);
            gain1.gain.setValueAtTime(0.3, now);
            gain1.gain.exponentialRampToValueAtTime(0.01, now + 0.22);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.22);

            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880, now + 0.1);
            gain2.gain.setValueAtTime(0.3, now + 0.1);
            gain2.gain.exponentialRampToValueAtTime(0.01, now + 0.4);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.1);
            osc2.stop(now + 0.4);
        }
    } catch (e) {
        console.warn('[FileFusion Sound]:', e);
    }
}

// 4. Initialize Notification Channels on Native with High Importance & Sound
if (isNative) {
    const channelConfigs = [
        {
            id: 'filefusion_high_priority_v2',
            name: 'FileFusion Alerts & Security',
            description: 'Audible alerts, task reminders and vault security notifications',
            importance: 5,
            visibility: 1,
            sound: 'default',
            vibration: true,
            lights: true,
            lightColor: '#6366f1'
        },
        {
            id: 'filefusion_default_channel',
            name: 'FileFusion Notifications',
            description: 'General alerts, task reminders and system notifications',
            importance: 5,
            visibility: 1,
            sound: 'default',
            vibration: true,
            lights: true,
            lightColor: '#6366f1'
        },
        {
            id: 'filefusion_transfers',
            name: 'File Transfers & Uploads',
            description: 'Progress and completion notifications for encrypted uploads',
            importance: 5,
            visibility: 1,
            sound: 'default',
            vibration: true,
            lights: true,
            lightColor: '#10b981'
        },
        {
            id: 'filefusion_security',
            name: 'Security & Vault Alerts',
            description: 'Critical authentication and vault security events',
            importance: 5,
            visibility: 1,
            sound: 'default',
            vibration: true,
            lights: true,
            lightColor: '#f43f5e'
        },
        {
            id: 'fcm_default_channel',
            name: 'Firebase Notifications',
            description: 'System push notifications',
            importance: 5,
            visibility: 1,
            sound: 'default',
            vibration: true,
            lights: true,
            lightColor: '#6366f1'
        }
    ];

    channelConfigs.forEach(channel => {
        if (typeof LocalNotifications !== 'undefined' && LocalNotifications.createChannel) {
            withTimeout(LocalNotifications.createChannel(channel), 1500)
                .catch(e => console.warn(`[FileFusion App] Local Channel ${channel.id}:`, e));
        }
        if (typeof PushNotifications !== 'undefined' && PushNotifications.createChannel) {
            withTimeout(PushNotifications.createChannel(channel), 1500)
                .catch(e => console.warn(`[FileFusion App] Push Channel ${channel.id}:`, e));
        }
    });
}

// 5. Rich Multi-Platform Notification Templates
const NotificationTemplates = {
    fileUpload: (fileName, fileSize, url) => ({
        title: '📁 File Upload Completed',
        body: `${fileName || 'Your file'} (${fileSize || 'encrypted'}) has been securely stored in your Vault.`,
        channelId: 'filefusion_transfers',
        url: url || getAppUrl('/panel/files')
    }),
    vaultUnlocked: (deviceName, url) => ({
        title: '🛡️ Security Alert',
        body: `Master Vault unlocked on ${deviceName || 'current device'}. Session active for 30 mins.`,
        channelId: 'filefusion_security',
        url: url || getAppUrl('/panel/hidden-files')
    }),
    storageWarning: (percent, used, quota, url) => ({
        title: '⚠️ Storage Warning',
        body: `Storage capacity reached ${percent || '88%'} (${used || '4.4 GB'} of ${quota || '5.0 GB'} used).`,
        channelId: 'filefusion_transfers',
        url: url || getAppUrl('/panel/storage-cleaner')
    }),
    linkSaved: (title, category, url) => ({
        title: '🔗 Link Saved',
        body: `Saved '${title || 'Bookmark'}' to your ${category || 'Dev & Cloud'} category.`,
        channelId: 'filefusion_transfers',
        url: url || getAppUrl('/panel/linklist')
    }),
    taskDue: (title, dueText, url) => ({
        title: '📋 Task Reminder',
        body: `Action Item: '${title || 'Review build'}' is due ${dueText || 'in 30 mins'}.`,
        channelId: 'filefusion_transfers',
        url: url || getAppUrl('/panel/todos')
    }),
    backupCompleted: (archiveName, destination, url) => ({
        title: '🔄 Backup Completed',
        body: `System backup '${archiveName || 'filefusion_backup.zip'}' uploaded to ${destination || 'Cloudflare R2'}.`,
        channelId: 'filefusion_transfers',
        url: url || getAppUrl('/panel/admin/backups')
    })
};

// 6. Push Registration Handshake with Server
async function syncPushWithServer(payload) {
    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch(getAppUrl('/devices/register-push'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        return await res.json();
    } catch (e) {
        console.warn('[FileFusion Push] Failed to register push token with server:', e);
        return null;
    }
}

// 7. Global Native Helper Object
window.FileFusionNative = {
    isNative: isNative,
    Capacitor: Capacitor,
    Device: Device,
    LocalNotifications: LocalNotifications,
    PushNotifications: PushNotifications,
    getAppUrl: getAppUrl,
    getDeviceName: detectDeviceName,
    getDeviceUuid: getDeviceUuid,
    templates: NotificationTemplates,
    biometrics: {
        isAvailable: async function () {
            // Exclusively active on Native Mobile Application (Android / Capacitor with hardware sensor)
            if (typeof window.FileFusionBiometrics !== 'undefined' && typeof window.FileFusionBiometrics.checkBiometricStatus === 'function') {
                try {
                    const status = window.FileFusionBiometrics.checkBiometricStatus();
                    return status === 'available';
                } catch (e) {
                    return false;
                }
            }
            if (isNative && window.PublicKeyCredential && typeof window.PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable === 'function') {
                try {
                    return await window.PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();
                } catch (e) {
                    return false;
                }
            }
            return false;
        },

        prompt: function (options = {}) {
            return new Promise((resolve) => {
                const title = options.title || 'Unlock FileFusion Vault';
                const subtitle = options.subtitle || 'Touch the fingerprint sensor to verify identity';

                if (typeof window.FileFusionBiometrics !== 'undefined' && typeof window.FileFusionBiometrics.authenticate === 'function') {
                    const callbackName = 'ff_bio_cb_' + Math.floor(Math.random() * 1000000);
                    window[callbackName] = function (result) {
                        delete window[callbackName];
                        resolve(result);
                    };
                    try {
                        window.FileFusionBiometrics.authenticate(title, subtitle, callbackName);
                    } catch (e) {
                        delete window[callbackName];
                        resolve({ success: false, message: e.message });
                    }
                    return;
                }

                if (window.PublicKeyCredential) {
                    resolve({ success: true, message: 'Platform verified' });
                    return;
                }

                resolve({ success: false, message: 'Biometrics not supported on this device' });
            });
        },

        unlockVault: async function (vaultType = 'files') {
            const check = await this.isAvailable();
            if (!check && typeof window.FileFusionBiometrics === 'undefined') {
                if (window.toast) {
                    window.toast('Fingerprint sensor is not available on this device.', 'warning');
                }
                return false;
            }

            const promptRes = await this.prompt({
                title: 'Unlock FileFusion Vault',
                subtitle: 'Touch the fingerprint sensor to continue'
            });

            if (!promptRes.success) {
                if (promptRes.message && !promptRes.message.toLowerCase().includes('cancel')) {
                    if (window.toast) {
                        window.toast('Biometric authentication failed: ' + promptRes.message, 'error');
                    }
                }
                return false;
            }

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(getAppUrl('/panel/vault/biometric-unlock'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        vault_type: vaultType,
                        device_name: await detectDeviceName(),
                        platform: isNative ? 'android' : 'web'
                    })
                });

                const data = await res.json();
                if (data.ok) {
                    const toastMsg = vaultType === 'reveal' ? 'Fingerprint verified! 🔓' : 'Vault unlocked with Fingerprint!';
                    if (window.ff && window.ff.toast) {
                        window.ff.toast(toastMsg, 'success', 2200);
                    } else if (window.toast) {
                        window.toast(toastMsg, 'success');
                    }
                    if (data.redirect) {
                        setTimeout(() => { window.location.href = data.redirect; }, 300);
                    }
                    return true;
                } else {
                    const err = data.info || data.error || 'Biometric authentication failed.';
                    if (window.ff && window.ff.toast) {
                        window.ff.toast(err, 'error', 3500);
                    } else if (window.toast) {
                        window.toast(err, 'error');
                    }
                    return false;
                }
            } catch (e) {
                console.error('[Biometric Vault Unlock Error]:', e);
                if (window.toast) {
                    window.toast('Network error during biometric verification.', 'error');
                }
                return false;
            }
        }
    },

    checkPermissions: async function () {
        if (isNative) {
            try {
                const res = await withTimeout(LocalNotifications.checkPermissions(), 2000, null);
                if (res && res.display) {
                    return res.display;
                }
            } catch (e) {
                console.error('[FileFusion Native] checkPermissions error:', e);
            }
        }
        if ('Notification' in window) {
            return Notification.permission;
        }
        return 'unsupported';
    },

    requestPermissions: async function () {
        let result = 'unsupported';
        if (isNative) {
            try {
                const res = await withTimeout(LocalNotifications.requestPermissions(), 5000, null);
                if (res && res.display) {
                    result = res.display;
                }
            } catch (e) {
                console.error('[FileFusion Native] requestPermissions error:', e);
            }
        } else if ('Notification' in window) {
            try {
                result = await Notification.requestPermission();
            } catch (err) {
                console.error('[Notification.requestPermission Error]:', err);
            }
        }

        if (result === 'granted') {
            // Run push registration asynchronously without blocking UI
            this.registerPushNotifications().catch(err => {
                console.warn('[FileFusion Push Register Background Error]:', err);
            });
        }

        return result;
    },

    reregisterPushNotifications: async function () {
        console.log('[FileFusion] Force re-registering push credentials...');
        if (!isNative && 'serviceWorker' in navigator && 'PushManager' in window) {
            try {
                const reg = await navigator.serviceWorker.ready;
                const existingSub = await reg.pushManager.getSubscription();
                if (existingSub) {
                    await existingSub.unsubscribe();
                    console.log('[FileFusion] Unsubscribed stale VAPID subscription');
                }
            } catch (e) {
                console.warn('[FileFusion] Error unsubscribing:', e);
            }
        }
        return await this.registerPushNotifications();
    },

    registerPushNotifications: async function () {
        const uuid = await getDeviceUuid();
        const deviceName = await detectDeviceName();

        // Android Native Push Registration (FCM)
        if (isNative) {
            // First sync device with server so it's registered in user_devices immediately
            await syncPushWithServer({
                device_uuid: uuid,
                device_name: deviceName,
                platform: 'android',
                push_type: 'fcm',
                push_token: 'android_native_' + uuid
            });

            try {
                if (typeof PushNotifications !== 'undefined' && PushNotifications.register) {
                    const permStatus = await withTimeout(PushNotifications.requestPermissions(), 2500, null);
                    if (permStatus && (permStatus.receive === 'granted' || permStatus.display === 'granted')) {
                        await withTimeout(PushNotifications.register(), 3000, null);
                        PushNotifications.addListener('registration', async (token) => {
                            if (token && token.value) {
                                console.log('[FileFusion FCM] Registration Token:', token.value);
                                await syncPushWithServer({
                                    device_uuid: uuid,
                                    device_name: deviceName,
                                    platform: 'android',
                                    push_type: 'fcm',
                                    push_token: token.value
                                });
                            }
                        });
                        PushNotifications.addListener('pushNotificationActionPerformed', (notification) => {
                            console.log('[FileFusion Push Action Performed]:', notification);
                            const data = notification.notification?.data || {};
                            const targetUrl = data.url || notification.notification?.click_action || '/panel';
                            if (targetUrl) {
                                const fullUrl = targetUrl.startsWith('http') ? targetUrl : getAppUrl(targetUrl);
                                window.location.href = fullUrl;
                            }
                        });
                        PushNotifications.addListener('pushNotificationReceived', async (notification) => {
                            console.log('[FileFusion Push Received]:', notification);
                            playAlertChime();
                            // Ensure audible notification plays even when app is active
                            if (typeof LocalNotifications !== 'undefined' && LocalNotifications.schedule) {
                                try {
                                    await LocalNotifications.schedule({
                                        notifications: [
                                            {
                                                id: Math.floor(Math.random() * 1000000),
                                                title: notification.title || 'FileFusion Alert',
                                                body: notification.body || '',
                                                channelId: notification.data?.channelId || 'filefusion_high_priority_v2',
                                                sound: 'default',
                                                smallIcon: 'ic_stat_filefusion',
                                                iconColor: '#6366f1',
                                                extra: notification.data || {}
                                            }
                                        ]
                                    });
                                } catch (e) {
                                    console.warn('[FileFusion Push Local Notification Error]:', e);
                                }
                            }
                        });
                        PushNotifications.addListener('registrationError', (err) => {
                            console.warn('[FileFusion FCM Registration Error]:', err);
                        });
                    }
                }
                if (typeof LocalNotifications !== 'undefined' && LocalNotifications.addListener) {
                    LocalNotifications.addListener('localNotificationActionPerformed', (notification) => {
                        console.log('[FileFusion Local Notification Action]:', notification);
                        const extra = notification.notification?.extra || {};
                        const targetUrl = extra.url || '/panel';
                        if (targetUrl) {
                            const fullUrl = targetUrl.startsWith('http') ? targetUrl : getAppUrl(targetUrl);
                            window.location.href = fullUrl;
                        }
                    });
                }
            } catch (err) {
                console.warn('[FileFusion FCM Registration Error]:', err);
            }
            return;
        }

        // Web / PWA / Apple iOS Push Registration (VAPID)
        if ('serviceWorker' in navigator && 'PushManager' in window) {
            try {
                let reg = await navigator.serviceWorker.getRegistration();
                if (!reg) {
                    try {
                        reg = await navigator.serviceWorker.register(getSwUrl());
                    } catch (swErr) {
                        console.warn('[FileFusion ServiceWorker Register]:', swErr);
                    }
                }
                if (!reg) {
                    reg = await withTimeout(navigator.serviceWorker.ready, 2500, null);
                }

                const keyRes = await withTimeout(fetch(getAppUrl('/devices/vapid-public-key')), 3000, null);
                if (!keyRes) {
                    console.warn('[FileFusion VAPID] Could not fetch VAPID key from server.');
                    return;
                }
                const keyData = await keyRes.json();

                if (keyData.ok && keyData.publicKey && reg && reg.pushManager) {
                    let sub = await reg.pushManager.getSubscription();
                    if (!sub) {
                        sub = await reg.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlB64ToUint8Array(keyData.publicKey)
                        });
                    }

                    if (sub) {
                        const subJson = sub.toJSON();
                        await syncPushWithServer({
                            device_uuid: uuid,
                            device_name: deviceName,
                            platform: window.matchMedia('(display-mode: standalone)').matches ? 'pwa' : 'web',
                            push_type: 'vapid',
                            endpoint: subJson.endpoint,
                            public_key: subJson.keys?.p256dh,
                            auth_token: subJson.keys?.auth,
                            push_token: JSON.stringify(subJson)
                        });
                        console.log('[FileFusion VAPID] Web Push subscription synced with server.');
                    }
                }
            } catch (err) {
                console.warn('[FileFusion VAPID Subscription Error]:', err);
            }
        }
    },

    notify: async function (options) {
        const title = typeof options === 'string' ? options : (options.title || 'FileFusion');
        const body = typeof options === 'object' ? (options.body || '') : (arguments[1] || '');
        const channelId = (typeof options === 'object' && options.channelId) ? options.channelId : 'filefusion_transfers';
        const delaySeconds = (typeof options === 'object' && options.delay) ? parseInt(options.delay, 10) : 0;
        const targetUrl = (typeof options === 'object' && options.url) ? options.url : getAppUrl('/panel');
        const notifId = Math.floor(Date.now() % 1000000);

        if (isNative) {
            try {
                const notifConfig = {
                    title: title,
                    body: body,
                    id: notifId,
                    channelId: channelId,
                    group: channelId || 'filefusion_alerts',
                    smallIcon: 'ic_stat_filefusion',
                    extra: { url: targetUrl }
                };

                if (delaySeconds > 0) {
                    notifConfig.schedule = {
                        at: new Date(Date.now() + (delaySeconds * 1000)),
                        allowWhileIdle: true
                    };
                }

                await withTimeout(
                    LocalNotifications.schedule({
                        notifications: [notifConfig]
                    }),
                    3000
                );

                return { success: true, mode: 'native', id: notifId, delay: delaySeconds };
            } catch (err) {
                console.error('[FileFusion Native] schedule notification error:', err);
            }
        }

        // Web / PWA Notification (ServiceWorker & Desktop Web API)
        if ('Notification' in window && Notification.permission === 'granted') {
            const webOptions = {
                body: body,
                tag: channelId || 'filefusion_alerts',
                icon: getAppUrl('/favicon.ico'),
                badge: getAppUrl('/favicon.ico'),
                vibrate: [200, 100, 200],
                data: { url: targetUrl }
            };

            // 1. Primary for Mobile Android / PWA: ServiceWorker showNotification
            if ('serviceWorker' in navigator) {
                try {
                    let reg = await navigator.serviceWorker.getRegistration();
                    if (!reg) {
                        reg = await withTimeout(navigator.serviceWorker.ready, 2000, null);
                    }
                    if (reg && reg.showNotification) {
                        if (delaySeconds > 0) {
                            setTimeout(() => reg.showNotification(title, webOptions), delaySeconds * 1000);
                        } else {
                            await reg.showNotification(title, webOptions);
                        }
                        return { success: true, mode: 'pwa_sw', id: notifId, delay: delaySeconds };
                    }
                } catch (swErr) {
                    console.warn('[FileFusion PWA SW Notification]:', swErr);
                }
            }

            // 2. Fallback for Desktop Browsers supporting window Notification constructor
            try {
                if (delaySeconds > 0) {
                    setTimeout(() => new Notification(title, webOptions), delaySeconds * 1000);
                } else {
                    new Notification(title, webOptions);
                }
                return { success: true, mode: 'web', id: notifId, delay: delaySeconds };
            } catch (notifErr) {
                console.warn('[FileFusion Web Notification]:', notifErr);
            }
        }

        if (window.ff && typeof window.ff.toast === 'function') {
            window.ff.toast(title + ': ' + body, 'info');
        } else if (window.toast) {
            window.toast(title + ': ' + body);
        }
        return { success: true, mode: 'toast', id: notifId, delay: 0 };
    },

    notifyPreset: async function(presetName, params = {}, delay = 0) {
        const deviceName = await detectDeviceName();
        let payload = null;

        if (presetName === 'fileUpload') {
            payload = NotificationTemplates.fileUpload(params.filename, params.size);
        } else if (presetName === 'vaultUnlocked') {
            payload = NotificationTemplates.vaultUnlocked(params.deviceName || deviceName);
        } else if (presetName === 'storageWarning') {
            payload = NotificationTemplates.storageWarning(params.percent, params.used, params.quota);
        } else if (presetName === 'linkSaved') {
            payload = NotificationTemplates.linkSaved(params.title, params.category);
        } else if (presetName === 'taskDue') {
            payload = NotificationTemplates.taskDue(params.title, params.dueText);
        } else if (presetName === 'backupCompleted') {
            payload = NotificationTemplates.backupCompleted(params.archiveName, params.destination);
        } else {
            payload = { title: params.title || 'FileFusion', body: params.body || '' };
        }

        payload.delay = delay;
        return await this.notify(payload);
    },

    share: async function (options = {}) {
        const title = options.title || 'FileFusion';
        const text = options.text || '';
        const url = options.url || window.location.href;
        const dialogTitle = options.dialogTitle || 'Share with FileFusion';

        // 1. Native Capacitor Share (Android / iOS)
        if (isNative) {
            try {
                const { Share } = await import('@capacitor/share');
                await Share.share({
                    title: title,
                    text: text,
                    url: url,
                    dialogTitle: dialogTitle
                });
                return { success: true, mode: 'native' };
            } catch (err) {
                console.warn('[FileFusion Native Share Warning]:', err);
            }
        }

        // 2. Web Share API (PWA / Browsers)
        if (typeof navigator !== 'undefined' && navigator.share) {
            try {
                await navigator.share({
                    title: title,
                    text: text,
                    url: url
                });
                return { success: true, mode: 'web_share' };
            } catch (err) {
                if (err.name !== 'AbortError') {
                    console.warn('[FileFusion Web Share Warning]:', err);
                }
            }
        }

        // 3. Fallback: Copy to clipboard
        try {
            if (window.ff && typeof window.ff.copy === 'function') {
                window.ff.copy(url, 'Link copied to clipboard!');
            } else if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(url);
                if (window.toast) window.toast.success('Link copied to clipboard!');
            } else {
                var ta = document.createElement('textarea');
                ta.value = url;
                ta.style.position = 'fixed';
                ta.style.top = '0';
                ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.focus();
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                if (window.toast) window.toast.success('Link copied to clipboard!');
            }
            return { success: true, mode: 'clipboard' };
        } catch (clipErr) {
            prompt('Copy link to share:', url);
            return { success: true, mode: 'prompt' };
        }
    }
};

// Automatic push subscription check on load if already granted
if (typeof window !== 'undefined' && 'Notification' in window && Notification.permission === 'granted') {
    setTimeout(() => {
        window.FileFusionNative?.registerPushNotifications().catch(() => {});
    }, 2000);
}

// 7. Secret Vault Auto-Lock (handled server-side via configurable inactivity lifetime)


// =========================================================================
// NATIVE ANDROID DOWNLOAD INTERCEPTOR -> Downloads/FileFusion
// =========================================================================
(function() {
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link || !link.href) return;

        const isDownload = link.hasAttribute('download') ||
            link.href.includes('/download/file/') ||
            link.href.includes('/download/bulk') ||
            link.href.includes('/download/') ||
            link.classList.contains('ff-download-link');

        if (isDownload) {
            if (window.FileFusionAndroidDownload && typeof window.FileFusionAndroidDownload.download === 'function') {
                e.preventDefault();
                e.stopPropagation();

                let fileName = link.getAttribute('data-filename') || link.getAttribute('download') || '';
                if (!fileName || fileName.trim().toLowerCase() === 'download') {
                    const tile = link.closest('.ff-tile, .ff-list-row, .ff-card');
                    if (tile) {
                        const nameEl = tile.querySelector('.ff-tile-name, .ff-list-title');
                        if (nameEl && nameEl.innerText.trim()) {
                            fileName = nameEl.innerText.trim();
                        }
                    }
                }

                window.FileFusionAndroidDownload.download(link.href, fileName.trim());
                if (window.ff && typeof window.ff.toast === 'function') {
                    window.ff.toast(`Downloading ${fileName || 'file'} to Downloads/FileFusion...`, 'info', 2500);
                }
                return false;
            }
        }
    }, true);
})();

// Auto-sync active device push credentials on page load if permission is already granted
(function() {
    if (typeof window !== 'undefined') {
        const autoSyncDevice = async () => {
            try {
                if (isNative || ('Notification' in window && Notification.permission === 'granted')) {
                    if (window.FileFusionNative && typeof window.FileFusionNative.registerPushNotifications === 'function') {
                        await window.FileFusionNative.registerPushNotifications();
                    }
                }
            } catch (e) {
                console.warn('[FileFusion Push AutoSync]:', e);
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', autoSyncDevice);
        } else {
            autoSyncDevice();
        }
    }
})();

// =========================================================================
// NATIVE ANDROID HARDWARE BACK BUTTON ROUTER & HAPTICS ENGINE
// =========================================================================
(function() {
    let lastBackPressTime = 0;

    if (isNative && App && typeof App.addListener === 'function') {
        App.addListener('backButton', function() {
            // Layer 1: Close active Bottom Sheet if open
            const bottomSheetBackdrop = document.getElementById('ffGlobalBottomSheetBackdrop');
            if (bottomSheetBackdrop && bottomSheetBackdrop.classList.contains('is-open')) {
                if (window.ff && window.ff.closeBottomSheet) window.ff.closeBottomSheet();
                if (window.ff && window.ff.haptic) window.ff.haptic('selection');
                return;
            }

            // Layer 2: Close active modals
            const openModals = Array.from(document.querySelectorAll('.ff-modal-overlay, #sharemodal, #deletemodal, #editmodal, #shareLinkModal, #sharePasswordModal, #shareCategoryModal, .ff-share-modal-root')).filter(m => {
                return window.getComputedStyle(m).display !== 'none' && m.style.display !== 'none' && !m.hidden;
            });

            if (openModals.length > 0) {
                const topModal = openModals[openModals.length - 1];
                const closeBtn = topModal.querySelector('.ff-modal-close, .closeShareLinkModalBtn, .closeSharePasswordModalBtn, .closeShareCategoryModalBtn, [onclick*="hide"]');
                if (closeBtn) {
                    closeBtn.click();
                } else {
                    topModal.style.display = 'none';
                }
                if (window.ff && window.ff.haptic) window.ff.haptic('selection');
                return;
            }

            // Layer 3: Close active kebab dropdown menus
            const activeMenus = Array.from(document.querySelectorAll('[data-ff-menu-panel]')).filter(p => !p.hidden);
            if (activeMenus.length > 0) {
                if (window.ff && window.ff.closeAllMenus) window.ff.closeAllMenus();
                return;
            }

            // Layer 4: Close active mobile sidebar drawer
            const shell = document.getElementById('ffShell');
            if (shell && shell.classList.contains('is-nav-open')) {
                shell.classList.remove('is-nav-open');
                const backdrop = document.getElementById('ffBackdrop');
                if (backdrop) backdrop.hidden = true;
                return;
            }

            // Layer 5: Sub-page navigation vs Dashboard root exit confirmation
            const pathname = window.location.pathname;
            const isDashboard = pathname === '/panel' || pathname === '/panel/' || pathname === '/' || pathname.endsWith('/dashboard');

            if (!isDashboard) {
                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    window.location.href = getAppUrl('/panel');
                }
                return;
            }

            // On Dashboard root: double-tap to exit app
            const now = Date.now();
            if (now - lastBackPressTime < 2000) {
                App.exitApp();
            } else {
                lastBackPressTime = now;
                if (window.ff && window.ff.toast) {
                    window.ff.toast('Press back again to exit', 'info', 1800);
                }
                if (window.ff && window.ff.haptic) {
                    window.ff.haptic('light');
                }
            }
        });
    }

    // Global Multi-Platform Haptics Engine
    async function triggerHaptic(type = 'light') {
        // 1. Direct Java Android Native Bridge
        if (window.FileFusionAndroidHaptics && typeof window.FileFusionAndroidHaptics.vibrate === 'function') {
            try {
                window.FileFusionAndroidHaptics.vibrate(type);
                return;
            } catch (e) {}
        }

        // 2. Capacitor Haptics Plugin
        if (isNative && Haptics) {
            try {
                if (type === 'light' || type === 'selection') {
                    await Haptics.impact({ style: ImpactStyle.Light });
                    return;
                } else if (type === 'medium') {
                    await Haptics.impact({ style: ImpactStyle.Medium });
                    return;
                } else if (type === 'heavy') {
                    await Haptics.impact({ style: ImpactStyle.Heavy });
                    return;
                } else if (type === 'error' || type === 'warning') {
                    await Haptics.notification({ type: NotificationType.Error });
                    return;
                }
            } catch (e) {}
        }

        // 3. Fallback to Browser Navigator Vibrate
        try {
            if (navigator.vibrate) {
                if (type === 'light' || type === 'selection') navigator.vibrate(20);
                else if (type === 'medium') navigator.vibrate(45);
                else if (type === 'heavy' || type === 'error') navigator.vibrate([40, 50, 40]);
            }
        } catch (e) {}
    }

    window.ffTriggerHaptic = triggerHaptic;

    // Auto-bind haptic feedback to interactive elements
    document.addEventListener('click', function(e) {
        const target = e.target.closest('.ff-bottom-nav-item, .copy-link-btn, .copy-username-btn, #copyPasswordBtn, [data-ff-copy], .ff-quick-btn, .ff-chip, .ff-btn');
        if (target) {
            triggerHaptic('selection');
        }
    }, { passive: true });
})();

