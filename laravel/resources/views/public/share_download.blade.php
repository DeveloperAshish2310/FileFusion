<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isExpired || $isLimitReached ? 'Link Unavailable' : $file->name }} | {{ config('app.name', 'File Fusion') }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite('resources/css/app.css')
    <script src="{{ asset('assets/vendors/js/lucide.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/js/qrcode.min.js') }}"></script>
    
    <style>
        body {
            background-color: #0d1117;
            color: #f0f6fc;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .download-card {
            background: #161b22;
            border: 1px solid #30363d;
            border-radius: 16px;
            width: 100%;
            max-width: 440px;
            padding: 32px 28px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            text-align: center;
        }
        .file-icon-wrap {
            width: 68px;
            height: 68px;
            border-radius: 16px;
            background: rgba(56, 189, 248, 0.12);
            color: #38bdf8;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
        }
        .file-name {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 6px;
            word-break: break-word;
        }
        .file-meta {
            font-size: 13.5px;
            color: #8b949e;
            margin-bottom: 24px;
        }
        .qr-box {
            background: #ffffff;
            padding: 12px;
            border-radius: 12px;
            display: inline-block;
            margin-bottom: 20px;
        }
        .dl-btn {
            background: #38bdf8;
            color: #0f172a;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            width: 100%;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .dl-btn:hover {
            opacity: 0.9;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 16px;
        }
        .badge-anon {
            background: rgba(168, 85, 247, 0.15);
            color: #c084fc;
            border: 1px solid rgba(168, 85, 247, 0.3);
        }
        .badge-public {
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
        }
        .passcode-input {
            width: 100%;
            padding: 12px 14px;
            background: #0d1117;
            border: 1px solid #30363d;
            border-radius: 8px;
            color: white;
            font-size: 15px;
            box-sizing: border-box;
            margin-bottom: 14px;
            text-align: center;
            letter-spacing: 0.2em;
        }
        .passcode-input:focus {
            outline: none;
            border-color: #38bdf8;
        }
    </style>
</head>
<body>

    <div class="download-card">
        @if ($isExpired)
            <div class="file-icon-wrap" style="background:rgba(239, 68, 68, 0.15); color:#ef4444;">
                <i data-lucide="clock-alert" class="w-8 h-8"></i>
            </div>
            <div class="file-name">Link Expired</div>
            <p class="file-meta">This share link has expired and is no longer available.</p>
        @elseif ($isLimitReached)
            <div class="file-icon-wrap" style="background:rgba(239, 68, 68, 0.15); color:#ef4444;">
                <i data-lucide="flame" class="w-8 h-8"></i>
            </div>
            <div class="file-name">Download Limit Reached</div>
            <p class="file-meta">This link was configured with a maximum download limit which has now been reached.</p>
        @else
            @if ($share->is_anonymous)
                <span class="badge badge-anon">
                    <i data-lucide="ghost" class="w-3.5 h-3.5"></i> Anonymous Transfer
                </span>
            @else
                <span class="badge badge-public">
                    <i data-lucide="globe" class="w-3.5 h-3.5"></i> Public Share
                </span>
            @endif

            <div class="file-icon-wrap">
                <i data-lucide="file" class="w-8 h-8"></i>
            </div>

            <div class="file-name">{{ $file->name }}</div>
            <div class="file-meta">
                {{ BytetoSize($file->size) }}
                @if (!$share->is_anonymous && $ownerName)
                    · Shared by {{ $ownerName }}
                @endif
                @if ($share->max_downloads)
                    <div style="margin-top:6px; font-weight:600; color:#38bdf8; font-size:13px;">
                        <i data-lucide="download" class="w-3.5 h-3.5" style="display:inline; vertical-align:middle; margin-right:2px;"></i>
                        {{ $share->download_count }} of {{ $share->max_downloads }} downloads used
                    </div>
                @endif
            </div>

            <div class="qr-box">
                <div id="dlQrCode"></div>
            </div>

            @if ($isProtected)
                <form action="{{ route('public.share.download', $token) }}" method="POST" style="margin-top:10px;">
                    @csrf
                    <input type="password" name="passcode" class="passcode-input" placeholder="Enter Passcode" required autofocus>
                    <button type="submit" class="dl-btn">
                        <i data-lucide="unlock" class="w-4 h-4"></i> Unlock & Download
                    </button>
                </form>
            @else
                <form action="{{ route('public.share.download', $token) }}" method="POST">
                    @csrf
                    <button type="submit" class="dl-btn">
                        <i data-lucide="download" class="w-4 h-4"></i> Download File
                    </button>
                </form>
            @endif
        @endif

        <div style="margin-top:24px; font-size:12px; color:#6e7681;">
            Powered by {{ config('app.name', 'File Fusion') }}
        </div>
    </div>

    <script>
        (function() {
            if (window.lucide) lucide.createIcons();
            var qr = document.getElementById('dlQrCode');
            if (qr && window.QRCode) {
                new QRCode(qr, {
                    text: window.location.href,
                    width: 140,
                    height: 140,
                    colorDark: '#000000',
                    colorLight: '#ffffff'
                });
            }
        })();
    </script>
</body>
</html>
