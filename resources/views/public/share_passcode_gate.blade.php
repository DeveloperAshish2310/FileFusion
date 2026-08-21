<!DOCTYPE html>
<html lang="en" data-accent="indigo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passcode Protected — FileFusion</title>
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
            padding: 36px 32px;
            max-width: 420px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            backdrop-filter: blur(16px);
        }
        .icon-wrap {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: rgba(245, 158, 11, 0.12);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
        }
        h1 { font-size: 19px; font-weight: 700; margin-bottom: 8px; color: #fff; }
        p { font-size: 13.5px; color: var(--ff-muted); line-height: 1.5; margin-bottom: 22px; }
        .input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
        }
        .input {
            flex: 1;
            background: rgba(0,0,0,0.35);
            border: 1px solid var(--ff-border);
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14px;
            color: #fff;
            outline: none;
            text-align: center;
            letter-spacing: 2px;
        }
        .input:focus { border-color: var(--ff-accent); }
        .btn {
            background: var(--ff-accent);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .alert {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 16px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <i data-lucide="lock" style="width: 30px; height: 30px;"></i>
        </div>
        <h1>Passcode Protected</h1>
        <p>This {{ $shareType ?? 'content' }} requires a private passcode PIN to view.</p>

        @if (session('error'))
            <div class="alert">{{ session('error') }}</div>
        @endif

        <form action="{{ $actionUrl }}" method="POST">
            @csrf
            <div class="input-group">
                <input type="password" name="passcode" class="input" placeholder="Enter PIN Passcode" autofocus required>
            </div>
            <button type="submit" class="btn">
                <i data-lucide="unlock" style="width: 16px; height: 16px;"></i> Unlock &amp; Access
            </button>
        </form>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
