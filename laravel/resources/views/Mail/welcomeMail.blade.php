<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Welcome & Verify Your Email</title>
</head>

<body
    style="margin: 0; padding: 0; background-color: #e0f2fe; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    <!-- Container -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background-color: #e0f2fe; padding: 40px 20px;">
        <tr>
            <td align="center">
                <!-- Main Card -->
                <table width="600" cellpadding="0" cellspacing="0" border="0"
                    style="background-color: #ffffff; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); max-width: 600px;">
                    <tr>
                        <td style="padding: 40px 40px 30px 40px; text-align: center;">
                            <!-- Logo/Icon -->
                            <div style="margin-bottom: 20px;">
                                <div
                                    style="display: inline-block; width: 64px; height: 64px; background: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%); border-radius: 50%; padding: 16px; margin-bottom: 16px; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                        viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M21.75 9v.906a2.25 2.25 0 01-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 001.183 1.981l6.478 3.488m8.839 2.51l-4.66-2.51m0 0l-1.023-.55a2.25 2.25 0 00-2.134 0l-1.023.55m0 0l-4.661 2.51m16.5 1.615a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V8.25a2.25 2.25 0 012.25-2.25h15a2.25 2.25 0 012.25 2.25v7.5z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Header -->
                            <h1 style="margin: 0 0 24px 0; font-size: 28px; font-weight: 700; color: #1e293b;">
                                {{ config('app.name', 'FileSystem') }}</h1>

                            <!-- Welcome Message -->
                            <h2 style="margin: 0 0 8px 0; font-size: 24px; font-weight: 600; color: #334155;">Welcome,
                                {{ $user->name }}!</h2>
                            <p style="margin: 0 0 32px 0; font-size: 16px; line-height: 24px; color: #64748b;">Thank you
                                for choosing {{ config('app.name', 'FileSystem') }}! We're excited to have you. Just one
                                more step to secure your account.</p>

                            <!-- Verification Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="margin-bottom: 24px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $verificationUrl }}"
                                            style="display: inline-block; padding: 14px 28px; background: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%); color: #ffffff; text-decoration: none; font-weight: 700; font-size: 16px; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);">
                                            Verify Email Address
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Expiry Info -->
                            <p style="margin: 0 0 16px 0; font-size: 14px; color: #64748b;">This verification link will
                                expire in 60 minutes.</p>

                            <!-- Alternative Link -->
                            <p style="margin: 0; font-size: 12px; color: #94a3b8; line-height: 18px;">
                                If the button doesn't work, copy and paste this link into your browser:<br>
                                <span style="color: #2563eb; word-break: break-all;">{{ $verificationUrl }}</span>
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 32px 40px; border-top: 1px solid #e2e8f0; text-align: center;">
                            <p style="margin: 0 0 8px 0; font-size: 12px; color: #94a3b8;">
                                If you have any questions, contact our support team at
                                <a href="mailto:support@theashishkumar.in"
                                    style="color: #dc2626; text-decoration: none;">support@theashishkumar.in</a>
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #94a3b8;">
                                &copy; {{ date('Y') }} {{ config('app.name', 'FileSystem') }}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
