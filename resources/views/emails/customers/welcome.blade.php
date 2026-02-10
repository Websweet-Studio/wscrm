<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang</title>
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
        <h1>Selamat Datang di {{ config('app.name') }}</h1>
    </div>

    <div class="content">
        <p>Halo <strong>{{ $customer->name }}</strong>,</p>

        <p>Terima kasih telah bergabung dengan kami. Kami sangat senang memiliki Anda sebagai pelanggan kami.</p>

        <p>Akun Anda telah berhasil dibuat. Anda sekarang dapat menikmati layanan kami.</p>

        <p>Jika Anda memiliki pertanyaan atau membutuhkan bantuan, jangan ragu untuk menghubungi tim support kami.</p>
        
        <p>Salam hangat,<br>
        Tim {{ config('app.name') }}</p>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </div>
</body>
</html>
