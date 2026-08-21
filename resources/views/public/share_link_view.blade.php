<!DOCTYPE html>
<html lang="en" data-accent="indigo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $decryptedTitle }} — Shared Bookmark</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        :root {
            --ff-bg: #090d16;
            --ff-card: rgba(17, 24, 39, 0.85);
            --ff-border: rgba(255, 255, 255, 0.08);
            --ff-text: #f8fafc;
            --ff-muted: #94a3b8;
            --ff-accent: #6366f1;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
            background: var(--ff-bg);
            color: var(--ff-text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: var(--ff-card);
            border: 1px solid var(--ff-border);
            border-radius: 24px;
            padding: 36px 32px;
            max-width: 460px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            backdrop-filter: blur(16px);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .badge {
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
            border: 1px solid rgba(99, 102, 241, 0.3);
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
        }
        .title { font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 8px; line-height: 1.35; }
        .desc { font-size: 13.5px; color: var(--ff-muted); margin-bottom: 20px; line-height: 1.5; }
        .qr-wrap {
            background: #fff;
            padding: 12px;
            border-radius: 16px;
            margin-bottom: 20px;
            display: inline-block;
        }
        .btn-open {
            background: var(--ff-accent);
            color: #fff;
            padding: 14px 28px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(99,102,241,0.4);
            transition: transform 0.15s;
        }
        .btn-open:hover { transform: translateY(-2px); }
        .meta {
            font-size: 12px;
            color: var(--ff-muted);
            margin-top: 16px;
        }
    </style>
</head>
<body>
    <div class="card">
        <span class="badge">
            <i data-lucide="bookmark" style="width: 12px; height: 12px; display: inline-block; vertical-align: middle;"></i>
            Shared Bookmark Link
        </span>
        <h1 class="title">{{ $decryptedTitle }}</h1>
        @if ($decryptedDesc)
            <p class="desc">{{ $decryptedDesc }}</p>
        @endif

        <div class="qr-wrap" id="qrcode"></div>

        <a href="{{ $decryptedUrl }}" target="_blank" rel="noopener noreferrer" class="btn-open">
            Open Link <i data-lucide="external-link" style="width: 18px; height: 18px;"></i>
        </a>

        <div class="meta">
            @if ($share->max_clicks)
                <span>{{ $share->click_count }} of {{ $share->max_clicks }} clicks used</span>
            @else
                <span>Visited {{ $share->click_count }} times</span>
            @endif
        </div>
    </div>

    <script>
        lucide.createIcons();
        new QRCode(document.getElementById("qrcode"), {
            text: "{{ $decryptedUrl }}",
            width: 130,
            height: 130,
            colorDark : "#090d16",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
    </script>
</body>
</html>
