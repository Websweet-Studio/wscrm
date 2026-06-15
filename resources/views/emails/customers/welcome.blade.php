<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di {{ config('app.name') }}</title>
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
        .account-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
        }
        .account-box-title {
            font-size: 13px;
            font-weight: 600;
            color: #15803d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 12px;
        }
        .account-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #d1fae5;
            gap: 16px;
        }
        .account-item:last-child {
            border-bottom: none;
        }
        .account-label {
            color: #4b5563;
            font-size: 14px;
            margin-right: 8px;
        }
        .account-value {
            font-weight: 600;
            color: #111827;
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
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <h1>Selamat Datang di {{ config('app.name') }}!</h1>
                <p>Akun Anda telah berhasil dibuat</p>
            </div>

            <div class="content">
                <p class="greeting">Halo, {{ $customer->name }} 👋</p>

                <p class="message">
                    Terima kasih telah bergabung dengan {{ config('app.name') }}. Kami sangat senang menyambut Anda sebagai bagian dari komunitas kami. Dengan akun ini, Anda dapat mengakses semua layanan yang kami sediakan.
                </p>

                <div class="account-box">
                    <p class="account-box-title">Informasi Akun Anda</p>
                    <div class="account-item">
                        <span class="account-label">Nama</span>
                        <span class="account-value">{{ $customer->name }}</span>
                    </div>
                    <div class="account-item">
                        <span class="account-label">Email</span>
                        <span class="account-value">{{ $customer->email }}</span>
                    </div>
                    @if(!empty($customer->username))
                    <div class="account-item">
                        <span class="account-label">Username</span>
                        <span class="account-value">{{ $customer->username }}</span>
                    </div>
                    @endif
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
