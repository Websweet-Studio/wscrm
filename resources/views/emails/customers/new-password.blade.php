<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Baru - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .card {
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header-icon {
            width: 64px;
            height: 64px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }
        .header-icon svg {
            width: 32px;
            height: 32px;
            fill: none;
            stroke: white;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .header p {
            margin: 8px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 32px 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 16px;
        }
        .message {
            color: #4b5563;
            margin: 0 0 24px;
            font-size: 15px;
        }
        .credentials-box {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
        }
        .credentials-box-title {
            font-size: 13px;
            font-weight: 600;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 12px;
        }
        .credential-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding:0;
            gap: 6px;
        }
        .credential-item:last-child {
            border-bottom: none;
        }
        .credential-label {
            color: #4b5563;
            font-size: 14px;
            margin-right: 8px;
        }
        .credential-value {
            color: #111827;
            font-size: 14px;
        }
        .warning {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            padding: 16px 20px;
            border-radius: 8px;
            margin: 24px 0;
        }
        .warning-title {
            font-weight: 600;
            color: #dc2626;
            margin: 0 0 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .warning p {
            margin: 0;
            color: #7f1d1d;
            font-size: 14px;
        }
        .cta-section {
            text-align: center;
            margin: 28px 0;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .cta-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
        }
        .divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 28px 0;
        }
        .help-text {
            color: #6b7280;
            font-size: 14px;
            text-align: center;
            margin: 0;
        }
        .help-text a {
            color: #059669;
            text-decoration: none;
            font-weight: 500;
        }
        .help-text a:hover {
            text-decoration: underline;
        }
        .signature {
            color: #4b5563;
            font-size: 14px;
            margin: 24px 0 0;
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e5e7eb;
            padding: 24px 30px;
            text-align: center;
        }
        .footer p {
            margin: 0;
            color: #9ca3af;
            font-size: 13px;
        }
        .footer a {
            color: #6b7280;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <div class="header-icon">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <h1>Password Anda Telah Direset</h1>
                <p>Gunakan password baru untuk masuk ke akun Anda</p>
            </div>

            <div class="content">
                <p class="greeting">Halo, {{ $customer->name }} 👋</p>

                <p class="message">
                    Password akun {{ config('app.name') }} Anda telah berhasil direset. Berikut adalah informasi login terbaru Anda:
                </p>

                <div class="credentials-box">
                    <p class="credentials-box-title">Informasi Login Anda</p>
                    <div class="credential-item">
                        <span class="credential-label">Email</span>
                        <span class="credential-value">{{ $customer->email }}</span>
                    </div>
                    <div class="credential-item">
                        <span class="credential-label">Password Baru</span>
                        <span class="credential-value">{{ $password }}</span>
                    </div>
                </div>

                <div class="warning">
                    <div class="warning-title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        Penting!
                    </div>
                    <p>Segera login dan ganti password ini dengan password pilihan Anda untuk menjaga keamanan akun Anda. Jangan bagikan informasi ini kepada siapa pun.</p>
                </div>

                <div class="cta-section">
                    <a href="{{ url('/login') }}" class="cta-button">Masuk ke Akun</a>
                </div>

                <hr class="divider">

                <p class="help-text">
                    Ada pertanyaan? Hubungi kami kapan saja di
                    <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>
                </p>

                <p class="signature">
                    Salam hangat,<br>
                    <strong>Tim {{ config('app.name') }}</strong>
                </p>
            </div>

            <div class="footer">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Semua hak dilindungi.</p>
            </div>
        </div>
    </div>
</body>
</html>
