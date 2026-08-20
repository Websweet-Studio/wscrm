<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
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
            padding: 32px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
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
        .body {
            color: #4b5563;
            font-size: 15px;
            line-height: 1.7;
        }
        .body h1 { font-size: 20px; color: #111827; margin: 20px 0 10px; }
        .body h2 { font-size: 17px; color: #111827; margin: 18px 0 8px; }
        .body h3 { font-size: 15px; color: #111827; margin: 16px 0 6px; }
        .body p { margin: 0 0 12px; }
        .body ul, .body ol { margin: 0 0 12px; padding-left: 22px; }
        .body li { margin-bottom: 4px; }
        .body a { color: #d97706; text-decoration: none; font-weight: 500; }
        .body a:hover { text-decoration: underline; }
        .body strong { color: #111827; }
        .body blockquote {
            border-left: 3px solid #f59e0b;
            margin: 12px 0;
            padding: 8px 16px;
            background: #fffbeb;
            color: #78350f;
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
            color: #d97706;
            text-decoration: none;
            font-weight: 500;
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
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <h1>{{ $subjectLine }}</h1>
            </div>

            <div class="content">
                <p class="greeting">Halo, {{ $customer->name }} 👋</p>

                <div class="body">
                    {!! $body !!}
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
