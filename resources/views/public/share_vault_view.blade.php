<!DOCTYPE html>
<html lang="en" data-accent="indigo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zero-Knowledge Secret Vault — FileFusion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --ff-bg: #090d16;
            --ff-card: rgba(17, 24, 39, 0.88);
            --ff-border: rgba(255, 255, 255, 0.08);
            --ff-text: #f8fafc;
            --ff-muted: #94a3b8;
            --ff-accent: #f43f5e;
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
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            backdrop-filter: blur(16px);
        }
        .icon-wrap {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            background: rgba(244, 63, 94, 0.12);
            color: #f43f5e;
            border: 1px solid rgba(244, 63, 94, 0.3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
        }
        h1 { font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 8px; }
        p { font-size: 13.5px; color: var(--ff-muted); line-height: 1.5; margin-bottom: 20px; }
        .burn-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(244, 63, 94, 0.15);
            color: #fda4af;
            border: 1px solid rgba(244, 63, 94, 0.3);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .btn-reveal {
            background: #f43f5e;
            color: #fff;
            padding: 14px 28px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 10px 25px rgba(244,63,94,0.4);
            transition: transform 0.15s;
        }
        .btn-reveal:hover { transform: translateY(-2px); }
        .secret-box {
            display: none;
            text-align: left;
            margin-top: 24px;
            background: rgba(0,0,0,0.4);
            border: 1px solid var(--ff-border);
            border-radius: 16px;
            padding: 20px;
        }
        .field-row {
            margin-bottom: 12px;
        }
        .field-row:last-child { margin-bottom: 0; }
        .field-label { font-size: 11px; text-transform: uppercase; color: var(--ff-muted); font-weight: 700; margin-bottom: 4px; }
        .field-val {
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--ff-border);
            border-radius: 8px;
            padding: 10px 14px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13.5px;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-copy {
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
        }
        .btn-copy:hover { color: #fff; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <i data-lucide="shield-alert" style="width: 32px; height: 32px;"></i>
        </div>
        <h1>Zero-Knowledge Encrypted Secret</h1>
        <p>A secure password credential was shared with you. Decryption happens on-demand.</p>

        @if ($share->burn_after_reading)
            <div class="burn-badge">
                <i data-lucide="flame" style="width: 14px; height: 14px;"></i> Self-Destructs (Burns) Immediately On Read
            </div>
        @endif

        @if ($share->isPasswordProtected())
            <div style="margin-bottom: 16px;">
                <input type="password" id="pinInput" placeholder="Enter PIN Passcode" style="width: 100%; background: rgba(0,0,0,0.3); border: 1px solid var(--ff-border); border-radius: 12px; padding: 12px; color: #fff; text-align: center; font-size: 14px; outline: none;">
            </div>
        @endif

        <button class="btn-reveal" id="btnReveal" onclick="revealSecret()">
            <i data-lucide="eye" style="width: 18px; height: 18px;"></i> Reveal &amp; Decrypt Secret
        </button>

        <div class="secret-box" id="secretBox">
            <div class="field-row">
                <div class="field-label">Title</div>
                <div class="field-val" id="valTitle">-</div>
            </div>
            <div class="field-row">
                <div class="field-label">Username / Account</div>
                <div class="field-val">
                    <span id="valUser">-</span>
                    <button class="btn-copy" onclick="copyVal('valUser')"><i data-lucide="copy" style="width: 15px; height: 15px;"></i></button>
                </div>
            </div>
            <div class="field-row">
                <div class="field-label">Password / Secret</div>
                <div class="field-val" style="color: #34d399;">
                    <span id="valPass">-</span>
                    <button class="btn-copy" onclick="copyVal('valPass')"><i data-lucide="copy" style="width: 15px; height: 15px;"></i></button>
                </div>
            </div>
            <div class="field-row" id="rowUrl" style="display: none;">
                <div class="field-label">Login URL</div>
                <div class="field-val"><a href="#" id="valUrl" target="_blank" style="color: #38bdf8; text-decoration: none;">-</a></div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        async function revealSecret() {
            const btn = document.getElementById('btnReveal');
            const pin = document.getElementById('pinInput')?.value || '';

            btn.disabled = true;
            btn.innerHTML = `<i data-lucide="loader-2" class="spin"></i> Decrypting...`;
            lucide.createIcons();

            try {
                const res = await fetch("{{ url('/s/v/' . $share->share_token . '/reveal') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ passcode: pin })
                });

                const data = await res.json();
                if (data.ok && data.payload) {
                    btn.style.display = 'none';
                    document.getElementById('secretBox').style.display = 'block';
                    document.getElementById('valTitle').textContent = data.payload.title || 'Untitled Secret';
                    document.getElementById('valUser').textContent = data.payload.username || 'N/A';
                    document.getElementById('valPass').textContent = data.payload.password || '';

                    if (data.payload.url) {
                        document.getElementById('rowUrl').style.display = 'block';
                        const a = document.getElementById('valUrl');
                        a.textContent = data.payload.url;
                        a.href = data.payload.url;
                    }
                    lucide.createIcons();
                } else {
                    alert(data.info || 'Decryption failed.');
                    btn.disabled = false;
                    btn.innerHTML = `<i data-lucide="eye"></i> Reveal & Decrypt Secret`;
                    lucide.createIcons();
                }
            } catch (e) {
                alert('Connection error.');
                btn.disabled = false;
            }
        }

        function copyVal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const txt = el.textContent || el.value;
            var ta = document.createElement('textarea');
            ta.value = String(txt);
            ta.setAttribute('readonly', '');
            ta.style.position = 'fixed';
            ta.style.top = '0';
            ta.style.left = '-9999px';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            document.execCommand('copy');
            if (document.body.contains(ta)) document.body.removeChild(ta);
            if (navigator.clipboard && window.isSecureContext) {
                try { navigator.clipboard.writeText(txt); } catch(e) {}
            }
            alert('Copied to clipboard!');
        }
    </script>
</body>
</html>
