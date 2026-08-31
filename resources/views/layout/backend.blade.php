<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@stack('title') | {{ config('app.name', 'File Fusion') }}</title>

    {{-- Theme & Sidebar bootstrap — runs before paint so there is no layout shift or light/dark flash --}}
    <script>
        (function() {
            var d = document.documentElement;
            try {
                d.dataset.theme = localStorage.getItem('ff-theme') || 'light';
                d.dataset.accent = localStorage.getItem('ff-accent') || 'terracotta';
                var s = localStorage.getItem('ff-font-scale');
                if (s) d.style.setProperty('--ff-font-scale', s);

                var side = localStorage.getItem('ff-sidebar-collapsed');
                var w = window.innerWidth;
                if (side === 'collapsed' || side === 'true' || (side === null && w >= 901 && w <= 1280)) {
                    d.classList.add('ff-is-sidebar-collapsed');
                }
            } catch (e) {
                d.dataset.theme = 'light';
                d.dataset.accent = 'terracotta';
            }
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <meta name="app-url" content="{{ url('/') }}">
    <meta name="sw-url" content="{{ asset('sw.js') }}">
    <meta name="theme-color" content="#E0392E">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'FileFusion') }}">
    @yield('css')

    <script src="{{ asset('assets/vendors/js/lucide.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/js/alpinejs.min.js') }}" defer></script>
    <script src="{{ asset('assets/vendors/js/qrcode.min.js') }}"></script>

    {{-- window.ff must exist before any page markup runs — AJAX partials call it inline --}}
    <script>
        window.ff = (function() {
            var root = document.documentElement;

            function setTheme(next) {
                root.dataset.theme = next;
                try {
                    localStorage.setItem('ff-theme', next);
                } catch (e) {}
            }

            function setAccent(next) {
                root.dataset.accent = next;
                try {
                    localStorage.setItem('ff-accent', next);
                } catch (e) {}
            }

            function setFontScale(next) {
                root.style.setProperty('--ff-font-scale', next);
                try {
                    localStorage.setItem('ff-font-scale', next);
                } catch (e) {}
            }

            function closeAllMenus(except) {
                document.querySelectorAll('[data-ff-menu-panel]').forEach(function(panel) {
                    if (panel !== except) {
                        panel.hidden = true;
                        var tile = panel.closest('.ff-tile, .ff-card, .ff-list-row');
                        if (tile) tile.classList.remove('has-active-menu');
                    }
                });
            }

            function icons() {
                if (window.lucide) window.lucide.createIcons();
            }

            // =========================================================================
            // FF GLOBAL TOAST NOTIFICATION ENGINE
            // =========================================================================
            function toast(message, type = 'success', duration = 3000) {
                var container = document.getElementById('ffGlobalToastContainer');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'ffGlobalToastContainer';
                    container.style.cssText = 'position:fixed; top:24px; right:24px; z-index:9999999; display:flex; flex-direction:column; gap:10px; pointer-events:none; max-width:400px; width:calc(100% - 48px);';
                    document.body.appendChild(container);
                }

                var item = document.createElement('div');
                item.style.cssText = 'pointer-events:auto; padding:12px 18px; border-radius:10px; background:rgba(24,24,27,0.96); backdrop-filter:blur(16px); border:1px solid rgba(255,255,255,0.12); box-shadow:0 12px 30px rgba(0,0,0,0.6); display:flex; align-items:center; gap:12px; color:#fff; font-size:13.5px; font-weight:500; transform:translateX(120%); opacity:0; transition:all 0.35s cubic-bezier(0.16,1,0.3,1);';

                var icon = '✅';
                var borderColor = '#10b981';

                if (type === 'error') {
                    icon = '❌';
                    borderColor = '#ef4444';
                    item.style.background = 'rgba(40,15,15,0.96)';
                } else if (type === 'warning') {
                    icon = '⚠️';
                    borderColor = '#f59e0b';
                } else if (type === 'info') {
                    icon = 'ℹ️';
                    borderColor = '#6366f1';
                }

                item.style.borderLeft = '4px solid ' + borderColor;
                item.innerHTML = '<span style="font-size:18px; flex-shrink:0;">' + icon + '</span><div style="flex:1; line-height:1.4;">' + message + '</div>';

                container.appendChild(item);

                requestAnimationFrame(function() {
                    item.style.transform = 'translateX(0)';
                    item.style.opacity = '1';
                });

                setTimeout(function() {
                    item.style.transform = 'translateX(120%)';
                    item.style.opacity = '0';
                    setTimeout(function() { item.remove(); }, 400);
                }, duration);
            }

            // =========================================================================
            // FF GLOBAL MODAL & PROMPT ENGINE (REPLACES PROMPT / CONFIRM / ALERT)
            // =========================================================================
            function modal(options) {
                return new Promise(function(resolve) {
                    var title = options.title || 'Confirm Action';
                    var message = options.message || 'Are you sure you want to proceed?';
                    var isPrompt = options.isPrompt || false;
                    var defaultValue = options.defaultValue || '';
                    var placeholder = options.placeholder || 'Enter value...';
                    var confirmText = options.confirmText || (isPrompt ? 'Save' : 'Confirm');
                    var cancelText = options.cancelText || 'Cancel';
                    var isDanger = options.isDanger || false;

                    var overlay = document.createElement('div');
                    overlay.style.cssText = 'position:fixed; inset:0; z-index:9999998; background:rgba(0,0,0,0.65); display:flex; align-items:center; justify-content:center; padding:16px; opacity:0; transition:opacity 0.2s ease;';

                    var card = document.createElement('div');
                    card.style.cssText = 'background:var(--ff-card, #ffffff); color:var(--ff-text, #0f172a); border:1px solid var(--ff-border, #e2e8f0); border-radius:14px; width:100%; max-width:440px; box-shadow:0 20px 40px rgba(0,0,0,0.3); overflow:hidden; transform:scale(0.95); transition:transform 0.2s cubic-bezier(0.16,1,0.3,1); display:flex; flex-direction:column;';

                    var btnConfirmBg = isDanger ? '#ef4444' : 'var(--ff-accent, #6366f1)';

                    card.innerHTML = `
                        <div style="padding: 18px 22px; border-bottom: 1px solid var(--ff-border, #e2e8f0); display: flex; align-items: center; justify-content: space-between;">
                            <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--ff-text, #0f172a); display: flex; align-items: center; gap: 8px;">
                                ${title}
                            </h3>
                            <button type="button" class="ff-modal-close" style="background: none; border: none; color: var(--ff-muted, #94a3b8); cursor: pointer; font-size: 20px; line-height: 1; padding: 2px;">&times;</button>
                        </div>
                        <div style="padding: 18px 22px; display: flex; flex-direction: column; gap: 12px;">
                            <p style="margin: 0; font-size: 13.5px; color: var(--ff-text-2, var(--ff-muted, #64748b)); line-height: 1.5;">${message}</p>
                            ${isPrompt ? `<input type="text" class="ff-modal-input ff-input" value="${defaultValue.replace(/"/g, '&quot;')}" placeholder="${placeholder}" style="width: 100%; height: 42px; font-size: 13.5px; box-sizing: border-box;">` : ''}
                        </div>
                        <div style="padding: 12px 22px; background: var(--ff-bg-2, var(--ff-bg2, #f8fafc)); border-top: 1px solid var(--ff-border, #e2e8f0); display: flex; justify-content: flex-end; gap: 10px;">
                            <button type="button" class="ff-btn ff-modal-cancel" style="padding: 7px 14px; font-size: 12.5px; font-weight: 600; border-radius: 7px; background: transparent; color: var(--ff-text, #334155); border: 1px solid var(--ff-border, #cbd5e1);">${cancelText}</button>
                            <button type="button" class="ff-btn ff-modal-confirm" style="padding: 7px 16px; font-size: 12.5px; font-weight: 600; border-radius: 7px; background: ${btnConfirmBg}; color: #fff; border: 1px solid ${btnConfirmBg};">${confirmText}</button>
                        </div>
                    `;

                    overlay.appendChild(card);
                    document.body.appendChild(overlay);

                    var input = card.querySelector('.ff-modal-input');
                    var confirmBtn = card.querySelector('.ff-modal-confirm');
                    var cancelBtn = card.querySelector('.ff-modal-cancel');
                    var closeBtn = card.querySelector('.ff-modal-close');

                    requestAnimationFrame(function() {
                        overlay.style.opacity = '1';
                        card.style.transform = 'scale(1)';
                        if (input) {
                            input.focus();
                            input.select();
                        }
                    });

                    function close(result) {
                        overlay.style.opacity = '0';
                        card.style.transform = 'scale(0.95)';
                        setTimeout(function() {
                            overlay.remove();
                            resolve(result);
                        }, 200);
                    }

                    confirmBtn.addEventListener('click', function() {
                        if (isPrompt) {
                            close(input ? input.value.trim() : '');
                        } else {
                            close(true);
                        }
                    });

                    cancelBtn.addEventListener('click', function() {
                        close(isPrompt ? null : false);
                    });

                    closeBtn.addEventListener('click', function() {
                        close(isPrompt ? null : false);
                    });

                    overlay.addEventListener('click', function(e) {
                        if (e.target === overlay) close(isPrompt ? null : false);
                    });

                    if (input) {
                        input.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                confirmBtn.click();
                            } else if (e.key === 'Escape') {
                                cancelBtn.click();
                            }
                        });
                    }
                });
            }

            function confirmModal(options) {
                if (typeof options === 'string') options = { message: options };
                options.isPrompt = false;
                return modal(options);
            }

            function promptModal(options) {
                if (typeof options === 'string') options = { message: options };
                options.isPrompt = true;
                return modal(options);
            }

            // =========================================================================
            // FF SMART CLIPBOARD API & FULLSCREEN UTILITIES
            // =========================================================================
            function fallbackCopy(text, message) {
                if (!text && text !== '0') return false;
                var ta = document.createElement('textarea');
                ta.value = String(text);
                ta.style.position = 'fixed';
                ta.style.top = '0';
                ta.style.left = '0';
                ta.style.width = '2em';
                ta.style.height = '2em';
                ta.style.padding = '0';
                ta.style.border = 'none';
                ta.style.outline = 'none';
                ta.style.boxShadow = 'none';
                ta.style.background = 'transparent';
                ta.style.opacity = '0.01';
                ta.style.fontSize = '16px';
                document.body.appendChild(ta);
                ta.focus({ preventScroll: true });
                ta.select();
                ta.setSelectionRange(0, 999999);
                var successful = false;
                try {
                    successful = document.execCommand('copy');
                } catch (err) {
                    successful = false;
                }
                if (document.body.contains(ta)) {
                    document.body.removeChild(ta);
                }
                if (successful) {
                    toast(message || 'Copied to clipboard! 📋', 'success');
                    haptic('selection');
                    return true;
                } else {
                    if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                        navigator.clipboard.writeText(String(text)).then(function() {
                            toast(message || 'Copied to clipboard! 📋', 'success');
                            haptic('selection');
                        }).catch(function() {
                            toast('Failed to copy to clipboard', 'error');
                        });
                        return true;
                    }
                    toast('Failed to copy to clipboard', 'error');
                    return false;
                }
            }

            function copy(text, message) {
                if (!text && text !== '0') return Promise.resolve(false);
                var str = String(text);

                // 1. Direct Native Android Java Clipboard Bridge (100% reliable in Capacitor WebView & LAN IP)
                try {
                    if (window.FileFusionAndroidClipboard && typeof window.FileFusionAndroidClipboard.copy === 'function') {
                        var nativeOk = window.FileFusionAndroidClipboard.copy(str);
                        if (nativeOk) {
                            toast(message || 'Copied to clipboard!', 'success');
                            haptic('selection');
                            return Promise.resolve(true);
                        }
                    }
                } catch(e) {}

                // 1.5 Capacitor Native Clipboard Plugin Bridge
                try {
                    if (window.Capacitor && window.Capacitor.Plugins && window.Capacitor.Plugins.Clipboard) {
                        return window.Capacitor.Plugins.Clipboard.write({ string: str }).then(function() {
                            toast(message || 'Copied to clipboard!', 'success');
                            haptic('selection');
                            return true;
                        }).catch(function() {
                            return fallbackCopy(str, message);
                        });
                    }
                } catch(e) {}

                // 2. Modern Web Clipboard API (in Secure Contexts)
                if (navigator.clipboard && window.isSecureContext && typeof navigator.clipboard.writeText === 'function') {
                    return navigator.clipboard.writeText(str).then(function() {
                        toast(message || 'Copied to clipboard!', 'success');
                        haptic('selection');
                        return true;
                    }).catch(function() {
                        return fallbackCopy(str, message);
                    });
                } else {
                    return Promise.resolve(fallbackCopy(str, message));
                }
            }

            // Safe polyfill so views calling navigator.clipboard.writeText never throw unhandled TypeError
            try {
                if (!window.navigator.clipboard) {
                    window.navigator.clipboard = {
                        writeText: function(text) {
                            return new Promise(function(resolve, reject) {
                                var ok = fallbackCopy(text);
                                if (ok) resolve(); else reject(new Error('Copy failed'));
                            });
                        }
                    };
                }
            } catch(e) {}

            window.copyToClipboard = copy;
            window.ffCopy = copy;

            function toggleFullscreen(elem) {
                elem = elem || document.documentElement;
                if (!document.fullscreenElement) {
                    if (elem.requestFullscreen) elem.requestFullscreen();
                    else if (elem.webkitRequestFullscreen) elem.webkitRequestFullscreen();
                } else {
                    if (document.exitFullscreen) document.exitFullscreen();
                    else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
                }
            }

            var deferredInstallPrompt = null;
            window.addEventListener('beforeinstallprompt', function(e) {
                e.preventDefault();
                deferredInstallPrompt = e;
                document.querySelectorAll('[data-ff-pwa-install]').forEach(function(btn) {
                    btn.style.display = '';
                });
            });

            function installPwa() {
                if (deferredInstallPrompt) {
                    deferredInstallPrompt.prompt();
                    deferredInstallPrompt.userChoice.then(function(choiceResult) {
                        if (choiceResult.outcome === 'accepted') {
                            toast('FileFusion installed successfully!', 'success');
                        }
                        deferredInstallPrompt = null;
                    });
                } else {
                    toast('FileFusion is already installed or install prompt is unavailable.', 'info');
                }
            }

            function haptic(type = 'light') {
                if (window.ffTriggerHaptic) {
                    window.ffTriggerHaptic(type);
                    return;
                }
                if (window.FileFusionAndroidHaptics && typeof window.FileFusionAndroidHaptics.vibrate === 'function') {
                    try {
                        window.FileFusionAndroidHaptics.vibrate(type);
                        return;
                    } catch (e) {}
                }
                try {
                    if (navigator.vibrate) {
                        if (type === 'light' || type === 'selection') navigator.vibrate(20);
                        else if (type === 'medium') navigator.vibrate(45);
                        else if (type === 'heavy' || type === 'error') navigator.vibrate([40, 50, 40]);
                    }
                } catch (e) {}
            }

            function openBottomSheet(opts) {
                var backdrop = document.getElementById('ffGlobalBottomSheetBackdrop');
                var titleEl = document.getElementById('ffBottomSheetTitle');
                var subEl = document.getElementById('ffBottomSheetSubtitle');
                var actionsEl = document.getElementById('ffBottomSheetActions');
                if (!backdrop || !actionsEl) return;

                if (titleEl) titleEl.textContent = opts.title || 'Actions';
                if (subEl) subEl.textContent = opts.subtitle || '';
                actionsEl.innerHTML = '';

                (opts.actions || []).forEach(function(act) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'ff-bottom-sheet-btn ' + (act.isDanger ? 'is-danger' : '');
                    var iconHtml = act.icon ? `<i data-lucide="${act.icon}"></i>` : '';
                    btn.innerHTML = `${iconHtml}<span>${act.label || 'Action'}</span>`;
                    btn.addEventListener('click', function(e) {
                        closeBottomSheet();
                        haptic('selection');
                        if (typeof act.onClick === 'function') act.onClick(e);
                    });
                    actionsEl.appendChild(btn);
                });

                backdrop.classList.add('is-open');
                document.body.style.overflow = 'hidden';
                haptic('light');
                icons();
            }

            function closeBottomSheet(e) {
                var backdrop = document.getElementById('ffGlobalBottomSheetBackdrop');
                if (backdrop) {
                    backdrop.classList.remove('is-open');
                    document.body.style.overflow = '';
                }
            }

            return {
                setTheme: setTheme,
                setAccent: setAccent,
                setFontScale: setFontScale,
                closeAllMenus: closeAllMenus,
                icons: icons,
                toast: toast,
                modal: modal,
                confirm: confirmModal,
                installPwa: installPwa,
                startLoading: startLoading,
                stopLoading: stopLoading,
                copy: copy,
                copyToClipboard: copy,
                fallbackCopy: fallbackCopy,
                haptic: haptic,
                openBottomSheet: openBottomSheet,
                closeBottomSheet: closeBottomSheet,
                theme: function() {
                    return root.dataset.theme;
                },
                accent: function() {
                    return root.dataset.accent;
                }
            };

            function startLoading() {
                var bar = document.getElementById('ffPageProgress');
                if (bar) {
                    bar.classList.add('is-loading');
                    bar.style.width = '35%';
                    setTimeout(function() { if (bar && bar.classList.contains('is-loading')) bar.style.width = '70%'; }, 150);
                    setTimeout(function() { if (bar && bar.classList.contains('is-loading')) bar.style.width = '90%'; }, 400);
                }
            }

            function stopLoading() {
                var bar = document.getElementById('ffPageProgress');
                if (bar) {
                    bar.style.width = '100%';
                    setTimeout(function() {
                        if (bar) {
                            bar.classList.remove('is-loading');
                            bar.style.width = '0%';
                        }
                    }, 250);
                }
            }

            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                stopLoading();
            } else {
                document.addEventListener('DOMContentLoaded', stopLoading);
            }

            window.addEventListener('load', stopLoading);
            window.addEventListener('pageshow', stopLoading);

            // Universal Delegated Copy Button Handler across the entire platform
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('[data-ff-copy], [data-copy], .js-copy-url-btn, .copy-link-url-btn, .copy-link-btn, .copy-username-btn, .copy-password-btn, .copy-btn, .copybtn, .js-copy-hash, .js-copy-btn, #copyPasswordBtn, #copyShareLinkResultBtn, #copySharePasswordResultBtn, #copyShareCategoryResultBtn');
                if (!btn) return;
                
                // If it's a delete or non-copy element, ignore
                if (btn.classList.contains('deletebtn') || btn.classList.contains('ff-link-delete-btn') || btn.classList.contains('is-danger')) return;
                
                // If it's an input field button with sibling target or ID
                var text = btn.getAttribute('data-ff-copy') || 
                           btn.getAttribute('data-copy') || 
                           btn.getAttribute('data-url') || 
                           btn.getAttribute('data-username') ||
                           btn.getAttribute('data-hash') || 
                           btn.getAttribute('data-value') || 
                           btn.getAttribute('data-text') || 
                           btn.getAttribute('data-token');

                if (!text) {
                    if (btn.id === 'copyShareLinkResultBtn') {
                        var inp = document.getElementById('shareLinkResultUrl');
                        if (inp) text = inp.value;
                    } else if (btn.id === 'copySharePasswordResultBtn') {
                        var inp = document.getElementById('sharePasswordResultUrl');
                        if (inp) text = inp.value;
                    } else if (btn.id === 'copyShareCategoryResultBtn') {
                        var inp = document.getElementById('shareCategoryResultUrl');
                        if (inp) text = inp.value;
                    } else if (btn.id === 'copyPasswordBtn') {
                        var inp = document.getElementById('rawPasswordInput') || document.getElementById('revealedPasswordValue');
                        if (inp) text = inp.value || inp.textContent;
                    }
                }

                if (text) {
                    e.preventDefault();
                    e.stopPropagation();
                    var msg = btn.getAttribute('data-copy-msg') || (btn.classList.contains('js-copy-url-btn') || btn.classList.contains('copy-link-url-btn') ? 'Link copied to clipboard! 📋' : 'Copied to clipboard! 📋');
                    copy(text, msg);

                    // Micro-interaction: temporarily show check icon
                    var icon = btn.querySelector('i, svg');
                    if (icon) {
                        var origIcon = icon.getAttribute('data-lucide') || 'copy';
                        icon.setAttribute('data-lucide', 'check');
                        if (window.lucide) {
                            window.lucide.createIcons({ root: btn });
                        } else if (window.ff && window.ff.icons) {
                            window.ff.icons();
                        }
                        setTimeout(function() {
                            icon.setAttribute('data-lucide', origIcon);
                            if (window.lucide) {
                                window.lucide.createIcons({ root: btn });
                            } else if (window.ff && window.ff.icons) {
                                window.ff.icons();
                            }
                        }, 1800);
                    }
                }
            }, true);

            // Intercept internal link navigation to show non-blocking progress bar
            document.addEventListener('click', function(e) {
                var link = e.target.closest('a');
                if (link && link.href && !link.target && !link.hasAttribute('download') && link.href.startsWith(window.location.origin) && !link.href.includes('#') && !link.getAttribute('href').startsWith('javascript:') && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
                    startLoading();
                }
            });

            // Intercept standard form submissions
            document.addEventListener('submit', function(e) {
                if (!e.defaultPrevented && !e.target.classList.contains('no-loader')) {
                    startLoading();
                }
            });
        })();
    </script>
</head>

<body>
    <!-- Global Non-Blocking Top Progress Bar -->
    <div id="ffPageProgress"></div>

    @php
        $ffUser = Auth::user();
        $ffName = $ffUser->nickname ?? $ffUser->name ?? $ffUser->username ?? 'Account';
        $ffInitial = function_exists('getInitials') ? getInitials($ffName) : strtoupper(substr($ffName, 0, 1));
    @endphp

    <div class="ff-shell" id="ffShell">

        <div class="ff-backdrop" id="ffBackdrop" hidden></div>

        @include('panel.includes.sidebar')

        <div class="ff-main">

            @if (session()->has('impersonator_admin_id'))
                <div style="background: linear-gradient(90deg, #f59e0b, #d97706); color: #000; padding: 10px 20px; font-size: 13px; font-weight: 600; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>You are currently impersonating <strong>{{ Auth::user()->name }} ({{ Auth::user()->email }})</strong> as Super Admin <strong>{{ session('impersonator_admin_name') }}</strong>.</span>
                    </div>
                    <a href="{{ route('panel.admin.stopImpersonation') }}" style="background: #000; color: #fff; padding: 5px 14px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 700; transition: opacity 0.2s;">Exit Impersonation</a>
                </div>
            @endif

            {{-- ------------------------------- Top bar ------------------------------- --}}
            <header class="ff-topbar">
                <div class="ff-topbar-left">
                    <button type="button" class="ff-burger" id="ffBurger" aria-label="Open navigation">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round">
                            <line x1="3" y1="6" x2="21" y2="6" />
                            <line x1="3" y1="12" x2="21" y2="12" />
                            <line x1="3" y1="18" x2="21" y2="18" />
                        </svg>
                    </button>

                    <button type="button" class="ff-icon-btn ff-hide-mobile" id="ffDesktopSidebarToggle" aria-label="Toggle sidebar width" title="Toggle Sidebar" style="display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:8px; border:1px solid var(--ff-border); background:var(--ff-input); color:var(--ff-text-2); cursor:pointer;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <line x1="9" y1="3" x2="9" y2="21"/>
                        </svg>
                    </button>

                    <form action="{{ route('panel.globalSearch') }}" method="GET" class="ff-topbar-search">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="7" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        <input type="search" name="q" id="ffGlobalSearch" value="{{ request('q') }}"
                            placeholder="Search files, links… (Ctrl+K)" autocomplete="off">
                    </form>
                </div>

                <div class="ff-topbar-right">
                    <button type="button" class="ff-icon-btn" id="ffThemeToggle" aria-label="Toggle dark mode">
                        <svg class="ff-icon-sun" width="17" height="17" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="5" />
                            <line x1="12" y1="1" x2="12" y2="3" />
                            <line x1="12" y1="21" x2="12" y2="23" />
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                            <line x1="1" y1="12" x2="3" y2="12" />
                            <line x1="21" y1="12" x2="23" y2="12" />
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
                        </svg>
                        <svg class="ff-icon-moon" width="17" height="17" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                        </svg>
                    </button>

                    <div class="ff-topbar-sep"></div>

                    <div class="ff-dropdown-wrap" data-ff-menu>
                        <button type="button" class="ff-row" data-ff-menu-trigger
                            style="background:none;border:none;cursor:pointer;gap:10px;padding:0;">
                            @if ($ffUser && $ffUser->hasAvatar())
                                <img src="{{ route('panel.user.avatar', $ffUser->id) }}" alt="{{ $ffName }}" class="ff-avatar" style="object-fit:cover; border-radius:50%;">
                            @else
                                <span class="ff-avatar">{{ $ffInitial }}</span>
                            @endif
                            <span class="ff-username">{{ $ffName }}</span>
                        </button>
                        <div class="ff-dropdown" data-ff-menu-panel hidden>
                            @if (Auth::user() && Auth::user()->isSuperAdmin())
                                <div style="padding: 6px 12px; font-size: 11px; font-weight: 700; color: #6366f1; text-transform: uppercase; letter-spacing: 0.5px;">Super Admin</div>
                                <a href="{{ route('panel.admin.dashboard') }}" class="ff-dropdown-item">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Admin Overview
                                </a>
                                <a href="{{ route('panel.admin.users') }}" class="ff-dropdown-item">
                                    <i data-lucide="users" class="w-4 h-4"></i> User Manager
                                </a>
                                <a href="{{ route('panel.admin.landingPage') }}" class="ff-dropdown-item">
                                    <i data-lucide="layout-template" class="w-4 h-4"></i> Landing Page CMS
                                </a>
                                <a href="{{ route('panel.admin.files') }}" class="ff-dropdown-item">
                                    <i data-lucide="folder" class="w-4 h-4"></i> Global Files
                                </a>
                                <a href="{{ route('panel.admin.links') }}" class="ff-dropdown-item">
                                    <i data-lucide="link" class="w-4 h-4"></i> Global Links
                                </a>
                                <a href="{{ route('panel.admin.storage') }}" class="ff-dropdown-item">
                                    <i data-lucide="bar-chart-2" class="w-4 h-4"></i> Storage Analytics
                                </a>
                                <a href="{{ route('panel.admin.settings') }}" class="ff-dropdown-item">
                                    <i data-lucide="settings" class="w-4 h-4"></i> System Settings
                                </a>
                                <a href="{{ route('panel.admin.notifications') }}" class="ff-dropdown-item">
                                    <i data-lucide="bell" class="w-4 h-4"></i> Notification Tester
                                </a>
                                <div class="ff-dropdown-divider"></div>
                            @endif

                            <a href="{{ route('panel.profile') }}" class="ff-dropdown-item">
                                <i data-lucide="user" class="w-4 h-4"></i> My Profile
                            </a>
                            <a href="{{ route('panel.settings') }}#two-factor-section" class="ff-dropdown-item">
                                <i data-lucide="shield-check" class="w-4 h-4"></i> Security &amp; 2FA
                            </a>
                            <a href="{{ route('panel.settings') }}#vault-password-section" class="ff-dropdown-item">
                                <i data-lucide="lock" class="w-4 h-4"></i> Vault &amp; Passcode
                            </a>
                            <a href="{{ route('panel.settings') }}#change-password-section" class="ff-dropdown-item">
                                <i data-lucide="key" class="w-4 h-4"></i> Change Password
                            </a>
                            <div class="ff-dropdown-divider"></div>
                            <a href="{{ route('logout') }}" class="ff-dropdown-item is-danger">
                                <i data-lucide="log-out" class="w-4 h-4"></i> Logout
                            </a>
                        </div>

                    </div>
                </div>
            </header>

            {{-- ------------------------------- Content ------------------------------- --}}
            <main class="ff-content">
                <div class="@yield('page-width', 'ff-page')">
                    @if (session('success'))
                        <div class="ff-alert is-success">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="ff-alert is-error">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    {{-- Native Mobile Bottom Navigation & Action Sheets --}}
    @include('layout.partials.mobile_bottom_nav')

    <script>
        (function() {
            var shell = document.getElementById('ffShell');
            var backdrop = document.getElementById('ffBackdrop');

            function openNav() {
                shell.classList.add('is-nav-open');
                backdrop.hidden = false;
            }

            function closeNav() {
                shell.classList.remove('is-nav-open');
                backdrop.hidden = true;
            }

            document.getElementById('ffBurger').addEventListener('click', openNav);
            backdrop.addEventListener('click', closeNav);
            document.addEventListener('click', function(e) {
                if (e.target.closest('[data-ff-nav-close]')) closeNav();
            });

            // Desktop Sidebar Minimize/Expand Toggle
            var desktopSidebarBtn = document.getElementById('ffDesktopSidebarToggle');
            if (desktopSidebarBtn) {
                // Sync shell class with html root bootstrap
                if (document.documentElement.classList.contains('ff-is-sidebar-collapsed')) {
                    shell.classList.add('is-sidebar-collapsed');
                }

                desktopSidebarBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var isCurrentlyCollapsed = document.documentElement.classList.contains('ff-is-sidebar-collapsed') || shell.classList.contains('is-sidebar-collapsed');
                    
                    if (isCurrentlyCollapsed) {
                        document.documentElement.classList.remove('ff-is-sidebar-collapsed');
                        shell.classList.remove('is-sidebar-collapsed');
                        localStorage.setItem('ff-sidebar-collapsed', 'expanded');
                    } else {
                        document.documentElement.classList.add('ff-is-sidebar-collapsed');
                        shell.classList.add('is-sidebar-collapsed');
                        localStorage.setItem('ff-sidebar-collapsed', 'collapsed');
                    }
                });
            }

            // Dark mode
            var toggle = document.getElementById('ffThemeToggle');

            function paintToggle() {
                var dark = document.documentElement.dataset.theme === 'dark';
                toggle.querySelector('.ff-icon-sun').style.display = dark ? '' : 'none';
                toggle.querySelector('.ff-icon-moon').style.display = dark ? 'none' : '';
            }
            paintToggle();
            toggle.addEventListener('click', function() {
                window.ff.setTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark');
                paintToggle();
                document.dispatchEvent(new CustomEvent('ff:theme-change'));
            });

            // Generic dropdown menus: [data-ff-menu] > [data-ff-menu-trigger] + [data-ff-menu-panel]
            document.addEventListener('click', function(e) {
                var trigger = e.target.closest('[data-ff-menu-trigger]');
                if (trigger) {
                    e.preventDefault();
                    e.stopPropagation();
                    var wrap = trigger.closest('[data-ff-menu]');
                    var panel = wrap.querySelector('[data-ff-menu-panel]');
                    var tile = trigger.closest('.ff-tile, .ff-card, .ff-list-row');
                    var willOpen = panel.hidden;
                    window.ff.closeAllMenus(panel);
                    panel.hidden = !willOpen;
                    if (tile) {
                        tile.classList.toggle('has-active-menu', willOpen);
                    }
                    return;
                }
                // If clicking an action item inside dropdown menu, close all menus
                var menuItem = e.target.closest('[data-ff-menu-panel] button, [data-ff-menu-panel] a, [data-ff-menu-panel] .ff-dropdown-item');
                if (menuItem) {
                    window.ff.closeAllMenus();
                    return;
                }
                if (!e.target.closest('[data-ff-menu-panel]')) window.ff.closeAllMenus();
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') window.ff.closeAllMenus();
            });

            // Ctrl/Cmd + K focuses global search
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    var input = document.getElementById('ffGlobalSearch');
                    if (input) input.focus();
                }
            });

            window.ff.icons();

            // Register PWA Service Worker
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(function() {});
                });
            }
        })();
    </script>

    <!-- FileFusion Native Android Bridge (Direct Java Bridge & Capacitor) -->
    <script src="{{ asset('assets/js/native-bridge.js') }}"></script>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var msg = {!! json_encode(session('success')) !!};
                if (window.ff && window.ff.toast) window.ff.toast(msg, 'success');
                if (window.FileFusionNative && window.FileFusionNative.notify) {
                    window.FileFusionNative.notify({ title: '✅ Success', body: msg });
                }
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var msg = {!! json_encode(session('error')) !!};
                if (window.ff && window.ff.toast) window.ff.toast(msg, 'error');
                if (window.FileFusionNative && window.FileFusionNative.notify) {
                    window.FileFusionNative.notify({ title: '❌ Security Alert', body: msg, channelId: 'filefusion_security' });
                }
            });
        </script>
    @endif

    @yield('push-script')
    @stack('scripts')
</body>

</html>
