<!DOCTYPE html>
<html lang="en" data-accent="indigo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $cat->title }} — Shared Category Portal</title>
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
            padding: 40px 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .header-card {
            background: var(--ff-card);
            border: 1px solid var(--ff-border);
            border-radius: 24px;
            padding: 32px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            backdrop-filter: blur(16px);
        }
        .cat-title { font-size: 24px; font-weight: 800; color: #fff; margin-bottom: 4px; }
        .cat-subtitle { font-size: 13.5px; color: var(--ff-muted); }
        .section-card {
            background: var(--ff-card);
            border: 1px solid var(--ff-border);
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 20px;
            backdrop-filter: blur(16px);
        }
        .section-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #fff;
        }
        .asset-list { display: flex; flex-direction: column; gap: 10px; }
        .asset-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--ff-border);
            border-radius: 12px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background 0.15s;
        }
        .asset-item:hover { background: rgba(255, 255, 255, 0.06); }
        .asset-name { font-weight: 600; font-size: 14px; color: #fff; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .btn-action {
            background: rgba(255,255,255,0.08);
            color: #fff;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-action:hover { background: var(--ff-accent); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-card">
            <div>
                <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--ff-accent); letter-spacing: 0.5px;">
                    Shared Workspace Category
                </span>
                <h1 class="cat-title">{{ $cat->title }}</h1>
                <p class="cat-subtitle">Curated bundle of workspace assets and links</p>
            </div>
            <i data-lucide="folder-archive" style="width: 48px; height: 48px; color: var(--ff-accent);"></i>
        </div>

        @if ($share->include_files && count($files) > 0)
            <div class="section-card">
                <div class="section-title">
                    <i data-lucide="file-text" style="color: #38bdf8;"></i> Files ({{ count($files) }})
                </div>
                <div class="asset-list">
                    @foreach ($files as $f)
                        <div class="asset-item">
                            <span class="asset-name">
                                <i data-lucide="file" style="width: 16px; height: 16px; color: #94a3b8;"></i>
                                {{ $f->name }}
                            </span>
                            <span style="font-size: 12px; color: var(--ff-muted);">
                                {{ round($f->size / 1024, 1) }} KB
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($share->include_links && count($links) > 0)
            <div class="section-card">
                <div class="section-title">
                    <i data-lucide="bookmark" style="color: #a855f7;"></i> Bookmark Links ({{ count($links) }})
                </div>
                <div class="asset-list">
                    @foreach ($links as $l)
                        <div class="asset-item">
                            <a href="{{ $l->url }}" target="_blank" class="asset-name">
                                <i data-lucide="external-link" style="width: 16px; height: 16px; color: #a855f7;"></i>
                                {{ $l->title ?: $l->url }}
                            </a>
                            <a href="{{ $l->url }}" target="_blank" class="btn-action">
                                Open ↗
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
