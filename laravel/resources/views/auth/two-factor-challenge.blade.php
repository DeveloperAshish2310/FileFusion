<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication — {{ config('app.name', 'FileFusion') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        :root {
            --ff-primary: #6366f1;
            --ff-primary-hover: #4f46e5;
            --ff-bg: #0b0f19;
            --ff-surface: #111827;
            --ff-border: #1f2937;
            --ff-text: #f9fafb;
            --ff-muted: #9ca3af;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at top center, rgba(99, 102, 241, 0.15), transparent 70%), var(--ff-bg);
            color: var(--ff-text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .ff-auth-card {
            background: var(--ff-surface);
            border: 1px solid var(--ff-border);
            border-radius: 16px;
            padding: 36px 32px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
            box-sizing: border-box;
        }

        .ff-auth-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .ff-auth-icon {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            color: var(--ff-primary);
        }

        .ff-auth-title {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 8px 0;
        }

        .ff-auth-sub {
            font-size: 13.5px;
            color: var(--ff-muted);
            margin: 0;
            line-height: 1.5;
        }

        .ff-code-input {
            width: 100%;
            height: 52px;
            background: rgba(0, 0, 0, 0.25);
            border: 1.5px solid var(--ff-border);
            border-radius: 10px;
            color: #ffffff;
            font-family: monospace;
            font-size: 24px;
            font-weight: 700;
            text-align: center;
            letter-spacing: 8px;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        .ff-code-input:focus {
            outline: none;
            border-color: var(--ff-primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        }

        .ff-btn-submit {
            width: 100%;
            height: 46px;
            background: var(--ff-primary);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
        }

        .ff-btn-submit:hover {
            background: var(--ff-primary-hover);
            transform: translateY(-1px);
        }

        .ff-auth-links {
            margin-top: 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            text-align: center;
            font-size: 13px;
        }

        .ff-auth-link {
            color: #818cf8;
            text-decoration: none;
            cursor: pointer;
            transition: color 0.15s ease;
            background: none;
            border: none;
            padding: 0;
            font-size: 13px;
        }

        .ff-auth-link:hover {
            color: #c7d2fe;
            text-decoration: underline;
        }

        .ff-alert-box {
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .ff-alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
        }
    </style>
</head>
<body>
    <div class="ff-auth-card">
        <div class="ff-auth-header">
            <div class="ff-auth-icon">
                @if ($twoFactorType === 'email')
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                @else
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                @endif
            </div>
            <h1 class="ff-auth-title">
                @if ($twoFactorType === 'email')
                    Email Security Verification
                @else
                    Two-Factor Authentication
                @endif
            </h1>
            <p class="ff-auth-sub" id="challenge-subtext">
                @if ($twoFactorType === 'email')
                    A 6-digit security code has been sent to your email (<strong>{{ substr($user->email, 0, 3) . '***@' . (explode('@', $user->email)[1] ?? 'email.com') }}</strong>). Please enter it below.
                @elseif ($twoFactorType === 'both')
                    Enter the 6-digit security code from your <strong>Authenticator App</strong> or check your <strong>Email</strong>.
                @else
                    Please enter the 6-digit security code generated by your <strong>Authenticator app</strong> (Google Authenticator, Authy, etc.).
                @endif
            </p>
        </div>

        @if ($errors->any())
            <div class="ff-alert-box">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div id="ajax-alert" style="display: none;"></div>

        <form action="{{ route('two-factor.verify') }}" method="POST" id="form-2fa-challenge">
            @csrf

            <div style="margin-bottom: 8px;">
                <label style="display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--ff-muted); margin-bottom: 8px; text-align: center;" id="label-code-input">
                    Verification Code
                </label>
                <input type="text" name="code" id="code" class="ff-code-input" placeholder="000000" maxlength="9" autofocus autocomplete="one-time-code" required>
            </div>

            <button type="submit" class="ff-btn-submit" id="btn-submit-2fa">
                Verify & Log In →
            </button>
        </form>

        <div class="ff-auth-links">
            @if ($twoFactorType === 'email' || $twoFactorType === 'both')
                <button type="button" class="ff-auth-link" id="btn-resend-email-otp">
                    ✉️ Resend Security Code via Email
                </button>
            @endif

            <button type="button" class="ff-auth-link" id="btn-toggle-recovery">
                🔑 Use an emergency backup recovery code
            </button>

            <a href="{{ route('login') }}" class="ff-auth-link" style="color: var(--ff-muted);">
                ← Cancel and return to log in
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputCode = document.getElementById('code');
            const toggleRecoveryBtn = document.getElementById('btn-toggle-recovery');
            const subtext = document.getElementById('challenge-subtext');
            const labelInput = document.getElementById('label-code-input');
            const resendEmailBtn = document.getElementById('btn-resend-email-otp');
            const alertBox = document.getElementById('ajax-alert');

            let isRecoveryMode = false;

            // Toggle between TOTP Code and Backup Code
            if (toggleRecoveryBtn) {
                toggleRecoveryBtn.addEventListener('click', function() {
                    isRecoveryMode = !isRecoveryMode;
                    if (isRecoveryMode) {
                        subtext.innerText = 'Enter one of your 8-character backup recovery codes generated when you activated 2FA.';
                        labelInput.innerText = 'Recovery Code';
                        inputCode.placeholder = 'xxxx-xxxx';
                        inputCode.maxLength = 12;
                        inputCode.style.letterSpacing = '2px';
                        inputCode.style.fontSize = '18px';
                        toggleRecoveryBtn.innerText = '📱 Use Authenticator app or Email code instead';
                    } else {
                        subtext.innerText = 'Please enter the 6-digit security code from your Authenticator app or email.';
                        labelInput.innerText = 'Verification Code';
                        inputCode.placeholder = '000000';
                        inputCode.maxLength = 6;
                        inputCode.style.letterSpacing = '8px';
                        inputCode.style.fontSize = '24px';
                        toggleRecoveryBtn.innerText = '🔑 Use an emergency backup recovery code';
                    }
                    inputCode.value = '';
                    inputCode.focus();
                });
            }

            // Resend Email OTP with 60-second cooldown
            if (resendEmailBtn) {
                let currentTimer = null;

                function startCooldown(seconds) {
                    if (currentTimer) clearInterval(currentTimer);
                    let countdown = seconds;
                    resendEmailBtn.disabled = true;
                    resendEmailBtn.style.opacity = '0.6';
                    resendEmailBtn.style.cursor = 'not-allowed';
                    resendEmailBtn.innerText = `⏳ Resend code available in ${countdown}s`;

                    currentTimer = setInterval(() => {
                        countdown--;
                        if (countdown > 0) {
                            resendEmailBtn.innerText = `⏳ Resend code available in ${countdown}s`;
                        } else {
                            clearInterval(currentTimer);
                            currentTimer = null;
                            resendEmailBtn.disabled = false;
                            resendEmailBtn.style.opacity = '1';
                            resendEmailBtn.style.cursor = 'pointer';
                            resendEmailBtn.innerText = '✉️ Resend Security Code via Email';
                        }
                    }, 1000);
                }

                // Initial cooldown check from server
                const initialCooldown = {{ (int) ($cooldownRemaining ?? 60) }};
                if (initialCooldown > 0) {
                    startCooldown(initialCooldown);
                }

                resendEmailBtn.addEventListener('click', function() {
                    if (resendEmailBtn.disabled) return;

                    resendEmailBtn.disabled = true;
                    resendEmailBtn.innerText = 'Sending fresh code...';

                    fetch("{{ route('two-factor.email-otp') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ action: 'Account Login' })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (alertBox) {
                            alertBox.style.display = 'block';
                            if (data.ok) {
                                alertBox.className = 'ff-alert-box ff-alert-success';
                                alertBox.innerText = data.message || 'A fresh security code has been sent to your email!';
                            } else {
                                alertBox.className = 'ff-alert-box';
                                alertBox.innerText = data.message || 'Failed to send verification email.';
                            }
                        }

                        startCooldown(data.cooldown || 60);
                    })
                    .catch(err => {
                        if (alertBox) {
                            alertBox.style.display = 'block';
                            alertBox.className = 'ff-alert-box';
                            alertBox.innerText = 'Network error sending code: ' + err.message;
                        }
                        startCooldown(30);
                    });
                });
            }
        });
    </script>
</body>
</html>
