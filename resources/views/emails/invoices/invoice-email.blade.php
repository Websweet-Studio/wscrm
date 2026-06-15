<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan {{ $invoice->invoice_number }} - {{ config('app.name') }}</title>
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
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
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
        .invoice-box {
            background-color: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
        }
        .invoice-box-title {
            font-size: 13px;
            font-weight: 600;
            color: #4338ca;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 12px;
        }
        .invoice-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #c7d2fe;
            gap: 16px;
        }
        .invoice-item:last-child {
            border-bottom: none;
        }
        .invoice-label {
            color: #4b5563;
            font-size: 14px;
        }
        .invoice-value {
            font-weight: 600;
            color: #111827;
            font-size: 14px;
        }
        .invoice-value.highlight {
            font-size: 18px;
            color: #4338ca;
        }
        .invoice-value.mono {
            font-family: 'Courier New', monospace;
            background-color: #ffffff;
            padding: 4px 10px;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .cta-section {
            text-align: center;
            margin: 28px 0;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .cta-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
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
            color: #4f46e5;
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
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <div class="header-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
                <h1>Tagihan Anda</h1>
                <p>{{ $invoice->invoice_number }}</p>
            </div>

            <div class="content">
                <p class="greeting">Halo, {{ $invoice->customer->name }} 👋</p>

                <p class="message">
                    Berikut adalah tagihan Anda dari {{ config('app.name') }}. Silakan lakukan pembayaran sebelum jatuh tempo.
                </p>

                <div class="invoice-box">
                    <p class="invoice-box-title">Detail Tagihan</p>
                    <div class="invoice-item">
                        <span class="invoice-label">No. Tagihan</span>
                        <span class="invoice-value mono">{{ $invoice->invoice_number }}</span>
                    </div>
                    <div class="invoice-item">
                        <span class="invoice-label">Jenis</span>
                        <span class="invoice-value">{{ $invoice->invoice_type === 'setup' ? 'Pembukaan' : 'Perpanjangan' }}</span>
                    </div>
                    <div class="invoice-item">
                        <span class="invoice-label">Siklus</span>
                        <span class="invoice-value">{{ match($invoice->billing_cycle) {
                            'monthly' => 'Bulanan',
                            'quarterly' => 'Triwulan',
                            'semi_annually' => '6 Bulanan',
                            'annually' => 'Tahunan',
                            default => $invoice->billing_cycle,
                        } }}</span>
                    </div>
                    @if($invoice->order)
                    <div class="invoice-item">
                        <span class="invoice-label">Layanan</span>
                        <span class="invoice-value">{{ $invoice->order->domain_name ?? '-' }}</span>
                    </div>
                    @endif
                    <div class="invoice-item">
                        <span class="invoice-label">Tanggal Terbit</span>
                        <span class="invoice-value">{{ $invoice->issue_date->format('d M Y') }}</span>
                    </div>
                    <div class="invoice-item">
                        <span class="invoice-label">Jatuh Tempo</span>
                        <span class="invoice-value" style="color: #dc2626;">{{ $invoice->due_date->format('d M Y') }}</span>
                    </div>
                    <div class="invoice-item">
                        <span class="invoice-label">Status</span>
                        <span class="invoice-value">
                            <span class="status-badge status-pending">Belum Dibayar</span>
                        </span>
                    </div>
                </div>

                {{-- Total Box --}}
                <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; margin: 0 0 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #4b5563; font-size: 14px;">Total yang harus dibayar</span>
                        <span style="font-size: 24px; font-weight: 700; color: #059669;">
                            Rp {{ number_format($invoice->amount - $invoice->discount, 0, ',', '.') }}
                        </span>
                    </div>
                    @if($invoice->discount > 0)
                    <div style="margin-top: 8px; text-align: right;">
                        <span style="font-size: 13px; color: #6b7280;">
                            Subtotal: Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                            · Diskon: -Rp {{ number_format($invoice->discount, 0, ',', '.') }}
                        </span>
                    </div>
                    @endif
                </div>

                <div class="cta-section">
                    <a href="{{ url('/customer/invoices/' . $invoice->id) }}" class="cta-button">Lihat & Bayar Tagihan</a>
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
