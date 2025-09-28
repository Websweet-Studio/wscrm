<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi Login Akun</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2563eb;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8fafc;
            padding: 30px;
            border: 1px solid #e2e8f0;
        }
        .credentials-box {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #2563eb;
            margin: 20px 0;
        }
        .credential-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .credential-item:last-child {
            border-bottom: none;
        }
        .credential-label {
            font-weight: bold;
            color: #4a5568;
        }
        .credential-value {
            font-family: 'Courier New', monospace;
            background-color: #edf2f7;
            padding: 4px 8px;
            border-radius: 4px;
            color: #2d3748;
        }
        .warning {
            background-color: #fef5e7;
            border: 1px solid #f6ad55;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .warning-title {
            font-weight: bold;
            color: #c05621;
            margin-bottom: 8px;
        }
        .login-link {
            background-color: #2563eb;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            display: inline-block;
            margin: 20px 0;
        }
        .footer {
            background-color: #4a5568;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 0 0 8px 8px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Informasi Login Akun Anda</h1>
    </div>

    <div class="content">
        <p>Halo <strong>{{ $user->name }}</strong>,</p>

        <p>Berikut adalah informasi login untuk akun Anda di sistem kami:</p>

        <div class="credentials-box">
            <div class="credential-item">
                <span class="credential-label">Username:</span>
                <span class="credential-value">{{ $user->username }}</span>
            </div>
            <div class="credential-item">
                <span class="credential-label">Email:</span>
                <span class="credential-value">{{ $user->email }}</span>
            </div>
            <div class="credential-item">
                <span class="credential-label">Password Sementara:</span>
                <span class="credential-value">{{ $temporaryPassword }}</span>
            </div>
        </div>

        <div class="warning">
            <div class="warning-title">⚠️ Penting!</div>
            <ul>
                <li>Password ini bersifat sementara dan sangat disarankan untuk segera diganti setelah login pertama</li>
                <li>Jangan bagikan informasi login ini kepada siapa pun</li>
                <li>Anda dapat login menggunakan username atau email</li>
            </ul>
        </div>

        <p>Silakan klik tombol di bawah untuk login ke sistem:</p>

        <a href="{{ url('/login') }}" class="login-link">Login Sekarang</a>

        <p>Jika Anda mengalami kesulitan login, silakan hubungi administrator sistem.</p>

        <p>Terima kasih,<br>
        Tim {{ config('app.name') }}</p>
    </div>

    <div class="footer">
        <p>Email ini dikirim secara otomatis oleh sistem. Mohon tidak membalas email ini.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>