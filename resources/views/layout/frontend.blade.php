<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@stack('title', 'FileFusion') | {{ config('app.name', 'FileFusion') }}</title>
    @vite('resources/css/app.css')
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <meta name="theme-color" content="#E0392E">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'FileFusion') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    <style>
        :root {
            --ff-bg: #FAFAFA;
            --ff-bg2: #F4F4F6;
            --ff-nav-bg: rgba(250, 250, 250, 0.88);
            --ff-text: #1E1E24;
            --ff-text-secondary: #585866;
            --ff-text-soft: #848494;
            --ff-border: #E2E2E6;
            --ff-accent: #E0392E;
            --ff-accent2: #B3271F;
            --ff-accent-grad: linear-gradient(135deg, #E0392E, #B3271F);
            --ff-icon-bg: rgba(224, 57, 46, 0.12);
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: var(--ff-bg);
            color: var(--ff-text);
            -webkit-font-smoothing: antialiased;
        }

        .font-outfit {
            font-family: 'Outfit', sans-serif;
        }

        .ff-glass-nav {
            position: sticky;
            top: 0;
            z-index: 50;
            background: var(--ff-nav-bg);
            border-bottom: 1px solid var(--ff-border);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .ff-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border-radius: 11px;
            background: var(--ff-accent-grad);
            color: #ffffff;
            font-weight: 700;
            font-size: 14.5px;
            text-decoration: none;
            box-shadow: 0 8px 20px -8px var(--ff-accent);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .ff-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px -8px var(--ff-accent);
            color: #ffffff;
        }

        .ff-card {
            background: #ffffff;
            border: 1px solid var(--ff-border);
            border-radius: 16px;
            padding: 26px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .ff-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px -12px rgba(0,0,0,0.08);
        }

        @keyframes ff-fade {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-ff-fade {
            animation: ff-fade 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>

    @yield('css')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body class="min-h-screen flex flex-col">

    <div class="app flex-grow">
        @yield('content')
    </div>

    @yield('push-script')
    <script>
        $(document).ready(function() {
            $('#mobile-menu-button').on('click', function(e) {
                e.stopPropagation();
                $('#mobile-menu').slideToggle(200);
            });
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#mobile-menu, #mobile-menu-button').length) {
                    $('#mobile-menu').slideUp(150);
                }
            });
        });

        // Register PWA Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').catch(function() {});
            });
        }
    </script>
</body>

</html>

