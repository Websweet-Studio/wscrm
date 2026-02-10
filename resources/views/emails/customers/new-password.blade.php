<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Baru Anda</title>
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
        <h1>Password Baru Akun Anda</h1>
    </div>

    <div class="content">
        <p>Halo <strong>{{ $customer->name }}</strong>,</p>

        <p>Sesuai permintaan Anda (atau admin), password akun Anda telah direset. Berikut adalah password baru Anda:</p>

        <div class="credentials-box">
            <div class="credential-item">
                <span class="credential-label">Email:</span>
                <span class="credential-value">{{ $customer->email }}</span>
            </div>
            <div class="credential-item">
                <span class="credential-label">Password Baru:</span>
                <span class="credential-value">{{ $password }}</span>
            </div>
        </div>

        <div class="warning">
            <div class="warning-title">Penting!</div>
            <p>Harap segera login dan ganti password ini dengan password pilihan Anda demi keamanan akun Anda.</p>
        </div>
        
        <p>Salam hangat,<br>
        Tim {{ config('app.name') }}</p>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </div>
</body>
</html>
