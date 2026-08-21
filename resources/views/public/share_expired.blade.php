<!DOCTYPE html>
<html lang="en" data-accent="indigo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Link Expired' }} — FileFusion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
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
            border-radius: 20px;
            padding: 40px 32px;
            max-width: 440px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            backdrop-filter: blur(16px);
        }
        .icon-wrap {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: rgba(239, 68, 68, 0.12);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        h1 { font-size: 20px; font-weight: 700; margin-bottom: 10px; color: #fff; }
        p { font-size: 14px; color: var(--ff-muted); line-height: 1.6; margin-bottom: 24px; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: rgba(255,255,255,0.06);
            color: #fff;
            border: 1px solid var(--ff-border);
            border-radius: 12px;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn:hover { background: rgba(255,255,255,0.12); }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <i data-lucide="flame" style="width: 36px; height: 36px;"></i>
        </div>
        <h1>{{ $title ?? 'Link Expired' }}</h1>
        <p>{{ $message ?? 'This share link has expired or reached its maximum allowed access count.' }}</p>
        <a href="/" class="btn">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Return to FileFusion
        </a>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
