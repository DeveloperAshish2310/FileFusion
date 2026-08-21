<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isExpired || $isLimitReached ? 'Link Unavailable' : $file->name }} | {{ config('app.name', 'File Fusion') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <script src="{{ asset('assets/vendors/js/lucide.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/js/qrcode.min.js') }}"></script>
    
    <!-- Dynamic Theme & Accent Bootstrap -->
    <script>
        (function() {
            var d = document.documentElement;
            try {
                var accent = localStorage.getItem('ff-accent') || 'terracotta';
                d.dataset.accent = accent;
            } catch (e) {
                d.dataset.accent = 'terracotta';
            }
        })();
    </script>
    
    <style>
        :root,
        [data-accent='terracotta'] {
            --ff-accent: #d97757;
            --ff-accent-2: #b8542e;
            --accent-rgb: 217, 119, 87;
            --accent-glow: rgba(217, 119, 87, 0.35);
        }
        [data-accent='emerald'] {
            --ff-accent: #2f9e6e;
            --ff-accent-2: #1d7350;
            --accent-rgb: 47, 158, 110;
            --accent-glow: rgba(47, 158, 110, 0.35);
        }
        [data-accent='teal'] {
            --ff-accent: #1e9ca8;
            --ff-accent-2: #146e77;
            --accent-rgb: 30, 156, 168;
            --accent-glow: rgba(30, 156, 168, 0.35);
        }
        [data-accent='indigo'] {
            --ff-accent: #5b6fcf;
            --ff-accent-2: #3e4fa3;
            --accent-rgb: 91, 111, 207;
            --accent-glow: rgba(91, 111, 207, 0.35);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #080b12;
            background-image: 
                radial-gradient(circle at 50% 15%, rgba(var(--accent-rgb), 0.12) 0%, transparent 60%),
                radial-gradient(circle at 50% 85%, rgba(15, 23, 42, 0.8) 0%, transparent 70%);
            color: #f1f5f9;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
            -webkit-font-smoothing: antialiased;
        }

        .share-container {
            width: 100%;
            max-width: 440px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Glassmorphic Card matching exact design */
        .download-card {
            background: #111522;
            background: linear-gradient(180deg, rgba(20, 26, 42, 0.9) 0%, rgba(14, 18, 30, 0.95) 100%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            width: 100%;
            padding: 36px 32px 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7), 0 0 40px rgba(var(--accent-rgb), 0.08);
            backdrop-filter: blur(16px);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            position: relative;
            overflow: hidden;
        }

        .download-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
        }

        /* Top Scope Pill */
        .badge-scope {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            background: rgba(var(--accent-rgb), 0.1);
            color: var(--ff-accent);
            border: 1px solid rgba(var(--accent-rgb), 0.25);
            letter-spacing: 0.02em;
        }

        /* Icon Container */
        .file-icon-wrap {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            background: rgba(var(--accent-rgb), 0.15);
            color: var(--ff-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            margin-top: 4px;
        }

        .file-icon-wrap.is-danger {
            background: rgba(244, 63, 94, 0.15);
            color: #f43f5e;
        }

        /* File Titles */
        .file-name {
            font-family: 'Outfit', 'Inter', sans-serif;
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.01em;
            word-break: break-word;
            line-height: 1.25;
            margin-top: 2px;
        }

        .file-size {
            font-size: 14px;
            font-weight: 500;
            color: #94a3b8;
            margin-top: -8px;
        }

        /* Usage / Download Count Pill */
        .downloads-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 12.5px;
            color: #cbd5e1;
            font-weight: 500;
        }

        /* QR Code Container */
        .qr-wrapper {
            margin: 4px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .qr-card {
            background: #ffffff;
            padding: 12px;
            border-radius: 16px;
            display: inline-flex;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            transition: transform 0.2s ease;
        }

        .qr-card:hover {
            transform: scale(1.02);
        }

        .qr-caption {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
        }

        /* Passcode Input Group */
        .form-wrap {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 6px;
        }

        .passcode-field {
            position: relative;
            width: 100%;
        }

        .passcode-field i, 
        .passcode-field svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            width: 16px;
            height: 16px;
        }

        .passcode-input {
            width: 100%;
            height: 48px;
            padding: 0 16px 0 42px;
            background: rgba(8, 11, 18, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #ffffff;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.2s ease;
        }

        .passcode-input:focus {
            outline: none;
            border-color: var(--ff-accent);
            box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.2);
            background: rgba(8, 11, 18, 0.95);
        }

        .passcode-input::placeholder {
            color: #475569;
        }

        /* Action Button */
        .btn-download {
            width: 100%;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--ff-accent), var(--ff-accent-2));
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 6px 20px var(--accent-glow);
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-download:hover {
            box-shadow: 0 8px 25px var(--accent-glow);
            transform: translateY(-1px);
            opacity: 0.96;
        }

        .btn-download:active {
            transform: translateY(1px);
        }

        /* Alert / Error Banner */
        .alert-error {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #fca5a5;
            font-size: 13px;
            padding: 8px 14px;
            border-radius: 10px;
            width: 100%;
            display: flex;
            align-items: center;
            gap: 8px;
            text-align: left;
        }

        /* Footer */
        .card-footer {
            margin-top: 10px;
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .card-footer strong {
            color: #94a3b8;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="share-container">

        <div class="download-card">

            {{-- 1. STATE: Expired or Limit Reached --}}
            @if ($isExpired || $isLimitReached)
                
                <div class="file-icon-wrap is-danger" style="margin-top: 10px;">
                    <i data-lucide="{{ $isExpired ? 'clock-alert' : 'flame' }}" style="width: 28px; height: 28px;"></i>
                </div>

                <div class="file-name">
                    {{ $isExpired ? 'Link Expired' : 'Download Limit Reached' }}
                </div>

                <p style="font-size: 14px; color: #94a3b8; line-height: 1.5; max-width: 320px;">
                    {{ $isExpired 
                        ? 'This secure share link has passed its expiration window and is no longer available.' 
                        : 'This link was configured with a maximum download limit which has now been reached.' 
                    }}
                </p>

            {{-- 2. STATE: Active Valid Download --}}
            @else

                {{-- Scope Badge --}}
                <div class="badge-scope">
                    <i data-lucide="{{ $share->is_anonymous ? 'eye' : 'globe' }}" style="width: 14px; height: 14px;"></i>
                    <span>{{ $share->is_anonymous ? 'Anonymous Transfer' : 'Public Share' }}</span>
                </div>

                {{-- File Icon --}}
                <div class="file-icon-wrap">
                    <i data-lucide="file-text" style="width: 26px; height: 26px;"></i>
                </div>

                {{-- File Name & Size --}}
                <div class="file-name">{{ $file->name }}</div>
                <div class="file-size">{{ BytetoSize($file->size) }}</div>

                {{-- Download Limits & Expiry Pill --}}
                @if ($share->max_downloads || $share->expires_at)
                    <div class="downloads-pill">
                        <i data-lucide="download" style="width: 13px; height: 13px; color: var(--ff-accent);"></i>
                        <span>
                            @if ($share->max_downloads)
                                {{ $share->download_count }} of {{ $share->max_downloads }} downloads used
                            @elseif ($share->expires_at)
                                Expires {{ $share->expires_at->diffForHumans() }}
                            @endif
                        </span>
                    </div>
                @endif

                {{-- QR Code Scan Section --}}
                <div class="qr-wrapper">
                    <div class="qr-card">
                        <div id="dlQrCode"></div>
                    </div>
                    <span class="qr-caption">Scan to open on another device</span>
                </div>

                {{-- Error Message on Failed Passcode --}}
                @if (session('error'))
                    <div class="alert-error">
                        <i data-lucide="alert-circle" style="width: 16px; height: 16px; flex-shrink: 0;"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                {{-- Form Actions (Protected with Passcode or Instant Download) --}}
                @if ($isProtected)
                    <form class="form-wrap" action="{{ route('public.share.download', $token) }}" method="POST">
                        @csrf
                        <div class="passcode-field">
                            <i data-lucide="lock"></i>
                            <input type="password" name="passcode" class="passcode-input" placeholder="Enter passcode" required autofocus autocomplete="off">
                        </div>
                        <button type="submit" class="btn-download">
                            <i data-lucide="lock" style="width: 16px; height: 16px;"></i> Unlock &amp; Download
                        </button>
                    </form>
                @else
                    <form class="form-wrap" action="{{ route('public.share.download', $token) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-download">
                            <i data-lucide="download" style="width: 16px; height: 16px;"></i> Download File
                        </button>
                    </form>
                @endif

            @endif

            {{-- Footer Branding --}}
            <div class="card-footer">
                <span>Powered by</span>
                <strong>FileFusion</strong>
            </div>

        </div>

    </div>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) {
                lucide.createIcons();
            }

            var qrElement = document.getElementById('dlQrCode');
            if (qrElement && window.QRCode) {
                new QRCode(qrElement, {
                    text: window.location.href,
                    width: 120,
                    height: 120,
                    colorDark: '#0b0f1d',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            }
        });
    </script>
</body>
</html>
