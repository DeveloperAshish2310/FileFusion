<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark" data-accent="terracotta">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Error') | {{ config('app.name', 'FileFusion') }}</title>

    {{-- Instant Theme & Accent Bootstrap --}}
    <script>
        (function() {
            var d = document.documentElement;
            try {
                var theme = localStorage.getItem('ff-theme') || 'dark';
                var accent = localStorage.getItem('ff-accent') || 'terracotta';
                d.dataset.theme = theme;
                d.dataset.accent = accent;
                var s = localStorage.getItem('ff-font-scale');
                if (s) d.style.setProperty('--ff-font-scale', s);
            } catch (e) {
                d.dataset.theme = 'dark';
                d.dataset.accent = 'terracotta';
            }
        })();
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="{{ asset('assets/vendors/js/lucide.min.js') }}"></script>
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <style>
        /* ---------- CSS Variables & Accents ---------- */
        :root,
        [data-accent='terracotta'] {
            --ff-accent: #d97757;
            --ff-accent-2: #b8542e;
            --ff-accent-rgb: 217, 119, 87;
            --ff-accent-glow: rgba(217, 119, 87, 0.45);
        }
        [data-accent='emerald'] {
            --ff-accent: #2f9e6e;
            --ff-accent-2: #1d7350;
            --ff-accent-rgb: 47, 158, 110;
            --ff-accent-glow: rgba(47, 158, 110, 0.45);
        }
        [data-accent='teal'] {
            --ff-accent: #1e9ca8;
            --ff-accent-2: #146e77;
            --ff-accent-rgb: 30, 156, 168;
            --ff-accent-glow: rgba(30, 156, 168, 0.45);
        }
        [data-accent='indigo'] {
            --ff-accent: #5b6fcf;
            --ff-accent-2: #3e4fa3;
            --ff-accent-rgb: 91, 111, 207;
            --ff-accent-glow: rgba(91, 111, 207, 0.45);
        }

        /* Light Theme */
        :root,
        [data-theme='light'] {
            color-scheme: light;
            --ff-bg: oklch(97.5% 0.004 280);
            --ff-bg-radial: radial-gradient(120% 90% at 50% 0%, oklch(99% 0.004 280) 0%, oklch(95.5% 0.006 280) 70%);
            --ff-bg-2: oklch(98.5% 0.004 280);
            --ff-card: #ffffff;
            --ff-card-bg: oklch(98.5% 0.004 280);
            --ff-border: oklch(88% 0.006 280);
            --ff-border-soft: oklch(91% 0.005 280);
            --ff-text: oklch(22% 0.015 280);
            --ff-text-2: oklch(44% 0.015 280);
            --ff-text-soft: oklch(56% 0.012 280);
            --ff-radar-bg: oklch(96% 0.005 280);
            --ff-radar-ring: oklch(87% 0.007 280);
            --ff-node-bg: oklch(95% 0.006 280);
            --ff-terminal-bg: oklch(96% 0.006 280);
            --ff-terminal-bar: oklch(92% 0.008 280);
            --ff-hazard-dark: oklch(92% 0.008 280);
            --ff-badge-text: #ffffff;
            --ff-badge-border: rgba(0,0,0,0.1);
        }

        /* Dark Theme */
        [data-theme='dark'] {
            color-scheme: dark;
            --ff-bg: oklch(13% 0.012 280);
            --ff-bg-radial: radial-gradient(120% 90% at 50% 0%, oklch(17% 0.02 280) 0%, oklch(13% 0.012 280) 62%);
            --ff-bg-2: oklch(17% 0.014 280);
            --ff-card: oklch(19% 0.015 280);
            --ff-card-bg: oklch(20% 0.014 280);
            --ff-border: oklch(28% 0.015 280);
            --ff-border-soft: oklch(24% 0.015 280);
            --ff-text: oklch(96% 0.006 280);
            --ff-text-2: oklch(76% 0.01 280);
            --ff-text-soft: oklch(58% 0.012 280);
            --ff-radar-bg: oklch(16% 0.014 280);
            --ff-radar-ring: oklch(26% 0.015 280);
            --ff-node-bg: oklch(21% 0.015 280);
            --ff-terminal-bg: oklch(16% 0.013 280);
            --ff-terminal-bar: oklch(19% 0.014 280);
            --ff-hazard-dark: oklch(20% 0.014 280);
            --ff-badge-text: #ffffff;
            --ff-badge-border: rgba(255,255,255,0.25);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--ff-bg);
            color: var(--ff-text);
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        .ff-error-wrapper {
            width: 100%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 96px 24px 48px 24px;
            text-align: center;
            position: relative;
            background: var(--ff-bg-radial);
        }

        /* Top Header */
        .ff-top-nav {
            position: absolute;
            top: 28px;
            left: 0;
            right: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            z-index: 20;
        }

        .ff-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--ff-text);
        }

        .ff-brand-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--ff-accent), var(--ff-accent-2));
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px -6px var(--ff-accent-glow);
        }

        .ff-brand-title {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: var(--ff-text);
        }

        .ff-brand-accent {
            color: var(--ff-accent);
        }

        /* Controls / Theme Pill */
        .ff-theme-pills {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ff-theme-btn {
            background: var(--ff-card-bg);
            border: 1px solid var(--ff-border);
            color: var(--ff-text-2);
            padding: 6px 12px;
            border-radius: 9px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.18s ease;
        }

        .ff-theme-btn:hover {
            border-color: var(--ff-accent);
            color: var(--ff-text);
        }

        .ff-accent-dot-btn {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid transparent;
            cursor: pointer;
            transition: transform 0.15s ease;
        }
        .ff-accent-dot-btn:hover {
            transform: scale(1.18);
        }
        .ff-accent-dot-btn.is-active {
            border-color: var(--ff-text);
            box-shadow: 0 0 8px var(--ff-accent-glow);
        }

        /* Buttons */
        .ff-btn-p {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 13px 24px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--ff-accent), var(--ff-accent-2));
            color: #ffffff !important;
            font-size: 14.5px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 10px 26px -8px var(--ff-accent-glow);
            transition: transform 0.16s ease, filter 0.16s ease;
            cursor: pointer;
            border: none;
        }

        .ff-btn-p:hover {
            transform: translateY(-2px);
            filter: brightness(1.08);
        }

        .ff-btn-s {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 13px 24px;
            border-radius: 12px;
            background: var(--ff-card-bg);
            border: 1px solid var(--ff-border);
            color: var(--ff-text) !important;
            font-size: 14.5px;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.16s ease, border-color 0.2s ease, background 0.2s ease;
            cursor: pointer;
        }

        .ff-btn-s:hover {
            transform: translateY(-2px);
            border-color: var(--ff-accent);
            background: color-mix(in srgb, var(--ff-accent) 8%, var(--ff-card-bg));
        }

        /* Typography & Shared Details */
        .ff-error-code {
            font-family: 'Outfit', sans-serif;
            font-size: 76px;
            font-weight: 800;
            letter-spacing: -2px;
            color: var(--ff-text);
            margin-top: 24px;
            line-height: 1;
            animation: ff-fade 0.6s ease 0.05s both;
        }

        .ff-error-tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.3em;
            color: var(--ff-accent);
            margin-top: 14px;
            text-transform: uppercase;
        }

        .ff-error-heading {
            font-family: 'Outfit', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: var(--ff-text);
            margin-top: 12px;
            letter-spacing: -0.6px;
        }

        .ff-error-desc {
            font-size: 15.5px;
            line-height: 1.65;
            color: var(--ff-text-2);
            margin-top: 12px;
            max-width: 460px;
        }

        .ff-error-actions {
            display: flex;
            gap: 12px;
            margin-top: 32px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .ff-error-footer {
            font-size: 12.5px;
            color: var(--ff-text-soft);
            margin-top: 40px;
        }

        .ff-error-footer a {
            color: var(--ff-accent);
            text-decoration: none;
            font-weight: 600;
        }

        .ff-error-footer a:hover {
            text-decoration: underline;
        }

        /* Animations */
        @keyframes ff-fade { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes ff-stag { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes ff-sweep { to { transform: rotate(360deg); } }
        @keyframes ff-ring { 0% { transform: scale(0.55); opacity: 0.75; } 100% { transform: scale(1); opacity: 0; } }
        @keyframes ff-blip { 0%,100% { opacity: 0.25; transform: scale(0.8); } 50% { opacity: 1; transform: scale(1.25); } }
        @keyframes ff-spinCW { to { transform: rotate(360deg); } }
        @keyframes ff-spinCCW { to { transform: rotate(-360deg); } }
        @keyframes ff-pulse { 0%,100% { transform: scale(1); opacity: 0.55; } 50% { transform: scale(1.12); opacity: 0.9; } }
        @keyframes ff-swipe { 0% { transform: translateX(-46px); } 50% { transform: translateX(46px); } 100% { transform: translateX(-46px); } }
        @keyframes ff-stamp { 0% { transform: rotate(-9deg) scale(1.6); opacity: 0; } 60% { transform: rotate(-9deg) scale(0.92); opacity: 1; } 100% { transform: rotate(-9deg) scale(1); opacity: 1; } }
        @keyframes ff-spark { 0%,100% { opacity: 0.2; transform: scale(0.7); } 50% { opacity: 1; transform: scale(1.25); } }
        @keyframes ff-dash { to { stroke-dashoffset: -32; } }
        @keyframes ff-shake { 0%,100% { transform: translateX(0); } 25% { transform: translateX(-3px); } 75% { transform: translateX(3px); } }

        .ff-stag { animation: ff-stag 0.55s cubic-bezier(0.2, 0.7, 0.3, 1) both; }

        @media (max-width: 640px) {
            .ff-top-nav {
                padding: 0 16px;
                top: 16px;
            }
            .ff-error-code {
                font-size: 60px;
            }
            .ff-error-heading {
                font-size: 26px;
            }
            .ff-error-actions {
                flex-direction: column;
                width: 100%;
                max-width: 280px;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="ff-error-wrapper">
        <!-- Top Navigation -->
        <header class="ff-top-nav">
            <a href="{{ url('/') }}" class="ff-brand">
                <div class="ff-brand-icon">
                    <svg width="20" height="20" viewBox="0 0 48 48" fill="none">
                        <path d="M15 6h11l8 8v12a5 5 0 0 1-5 5H15a5 5 0 0 1-5-5V11a5 5 0 0 1 5-5Z" fill="rgba(255,255,255,0.34)"></path>
                        <path d="M22 17h11l8 8v12a5 5 0 0 1-5 5H22a5 5 0 0 1-5-5V22a5 5 0 0 1 5-5Z" fill="#fff"></path>
                        <path d="M23 30h12M23 35h8" stroke="var(--ff-accent-2)" stroke-width="2.4" stroke-linecap="round"></path>
                    </svg>
                </div>
                <span class="ff-brand-title">File<span class="ff-brand-accent">Fusion</span></span>
            </a>

            <div class="ff-theme-pills">
                <!-- Theme Switcher -->
                <button type="button" class="ff-theme-btn" id="ffThemeToggleBtn" title="Toggle Dark / Light Theme" aria-label="Toggle Theme">
                    <i data-lucide="moon" class="w-3.5 h-3.5" id="ffThemeIcon"></i>
                    <span id="ffThemeText">Theme</span>
                </button>

                <!-- Color Accent Dots -->
                <div style="display:inline-flex; align-items:center; gap:5px; background:var(--ff-card-bg); padding:4px 8px; border-radius:10px; border:1px solid var(--ff-border);">
                    <button type="button" class="ff-accent-dot-btn" style="background:#d97757;" data-accent-val="terracotta" title="Terracotta Accent"></button>
                    <button type="button" class="ff-accent-dot-btn" style="background:#2f9e6e;" data-accent-val="emerald" title="Emerald Accent"></button>
                    <button type="button" class="ff-accent-dot-btn" style="background:#1e9ca8;" data-accent-val="teal" title="Teal Accent"></button>
                    <button type="button" class="ff-accent-dot-btn" style="background:#5b6fcf;" data-accent-val="indigo" title="Indigo Accent"></button>
                </div>
            </div>
        </header>

        <!-- Main Content Slot -->
        @yield('content')

        <!-- Footer -->
        <footer class="ff-error-footer ff-stag" style="animation-delay: 0.45s;">
            Need help? <a href="{{ route('contact') }}">Contact support</a> &bull; <a href="{{ url('/') }}">FileFusion Home</a>
        </footer>
    </div>

    <!-- Live Theme & Accent Switcher JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var root = document.documentElement;
            var themeBtn = document.getElementById('ffThemeToggleBtn');
            var themeIcon = document.getElementById('ffThemeIcon');
            var themeText = document.getElementById('ffThemeText');

            function syncUI() {
                var currentTheme = root.dataset.theme || 'dark';
                var currentAccent = root.dataset.accent || 'terracotta';

                if (themeIcon && themeText) {
                    if (currentTheme === 'dark') {
                        themeIcon.setAttribute('data-lucide', 'sun');
                        themeText.textContent = 'Light';
                    } else {
                        themeIcon.setAttribute('data-lucide', 'moon');
                        themeText.textContent = 'Dark';
                    }
                }

                document.querySelectorAll('.ff-accent-dot-btn').forEach(function(btn) {
                    btn.classList.toggle('is-active', btn.getAttribute('data-accent-val') === currentAccent);
                });

                if (window.lucide) {
                    window.lucide.createIcons();
                }
            }

            if (themeBtn) {
                themeBtn.addEventListener('click', function() {
                    var next = root.dataset.theme === 'dark' ? 'light' : 'dark';
                    root.dataset.theme = next;
                    try { localStorage.setItem('ff-theme', next); } catch (e) {}
                    syncUI();
                });
            }

            document.querySelectorAll('.ff-accent-dot-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var nextAccent = this.getAttribute('data-accent-val');
                    root.dataset.accent = nextAccent;
                    try { localStorage.setItem('ff-accent', nextAccent); } catch (e) {}
                    syncUI();
                });
            });

            syncUI();
        });
    </script>
</body>
</html>
