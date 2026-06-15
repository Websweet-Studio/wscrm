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
            margin-right: 8px;
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
        .items-title {
            font-size: 13px;
            font-weight: 600;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 12px;
        }
        .items-count {
            font-weight: 400;
            color: #9ca3af;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin: 0 0 16px;
        }
        .items-table th {
            text-align: left;
            padding: 8px 6px;
            color: #6b7280;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
        }
        .items-table th.text-center {
            text-align: center;
        }
        .items-table th.text-right {
            text-align: right;
        }
        .items-table td {
            padding: 10px 6px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }
        .items-table td.text-center {
            text-align: center;
        }
        .items-table td.text-right {
            text-align: right;
            font-weight: 600;
            white-space: nowrap;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        .row-num {
            width: 30px;
            color: #6b7280;
            font-size: 12px;
        }
        .service-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            margin-right: 8px;
            vertical-align: middle;
            flex-shrink: 0;
        }
        .icon-hosting {
            background-color: #dbeafe;
            color: #1d4ed8;
        }
        .icon-domain {
            background-color: #ede9fe;
            color: #7c3aed;
        }
        .icon-service {
            background-color: #d1fae5;
            color: #047857;
        }
        .service-name {
            font-weight: 600;
            color: #111827;
            font-size: 13px;
        }
        .item-desc {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin: 0;
        }
        .summary-table td {
            padding: 8px 6px;
        }
        .summary-table .label {
            color: #6b7280;
            font-weight: 500;
        }
        .summary-table .label-discount {
            color: #059669;
            font-weight: 500;
        }
        .summary-table .value {
            text-align: right;
            font-weight: 600;
            color: #111827;
        }
        .summary-table .value-discount {
            text-align: right;
            font-weight: 600;
            color: #059669;
        }
        .summary-table .row-discount td {
            border-top: 1px solid #e5e7eb;
        }
        .discount-box {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            padding: 12px 16px;
            margin: 8px 0 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .discount-box-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #047857;
            font-weight: 600;
            font-size: 14px;
        }
        .discount-box-label svg {
            width: 18px;
            height: 18px;
            stroke: #047857;
        }
        .discount-box-value {
            font-weight: 700;
            color: #059669;
            font-size: 16px;
            white-space: nowrap;
        }
        .summary-table .row-total td {
            border-top: 2px solid #111827;
            padding-top: 12px;
        }
        .summary-table .total-label {
            font-weight: 700;
            font-size: 16px;
            color: #111827;
        }
        .summary-table .total-value {
            text-align: right;
            font-weight: 700;
            font-size: 18px;
            color: #059669;
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
            transition: transform 0.2s, box-shadow: 0.2s;
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
                        <span class="invoice-value">{{ $invoice->invoice_number }}</span>
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

                {{-- Order Items --}}
                @if($orderItems->count() > 0)
                @php
                    $subtotal = $orderItems->sum(fn($item) => $item->price * $item->quantity);
                    $discountAmount = $invoice->discount > 0 ? $invoice->discount : ($invoice->order?->discount_amount ?? 0);
                    $finalTotal = $subtotal - $discountAmount;
                    if ($finalTotal < 0) $finalTotal = 0;
                @endphp
                <p class="items-title">
                    Item Pesanan
                    <span class="items-count">({{ $orderItems->count() }} item)</span>
                </p>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th class="row-num">#</th>
                            <th>Layanan</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Harga Satuan</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orderItems as $index => $item)
                        <tr>
                            <td class="row-num">{{ $index + 1 }}</td>
                            <td>
                                @if($item->item_type === 'hosting')
                                    <span class="service-icon icon-hosting">H</span>
                                @elseif($item->item_type === 'domain')
                                    <span class="service-icon icon-domain">D</span>
                                @else
                                    <span class="service-icon icon-service">L</span>
                                @endif
                            </td>
                            <td>
                                <span class="service-name">
                                    @if($item->item_type === 'hosting' && $item->hostingPlan)
                                        {{ $item->hostingPlan->plan_name }}
                                    @elseif($item->item_type === 'domain')
                                        {{ $item->domain_name ?? $invoice->order?->domain_name ?? '-' }}
                                    @elseif($item->servicePlan)
                                        {{ $item->servicePlan->name }}
                                    @else
                                        {{ ucfirst($item->item_type) }}
                                    @endif
                                </span>
                                @if($item->item_type === 'hosting' && $item->hostingPlan)
                                <div class="item-desc">{{ $item->hostingPlan->storage_gb }}GB SSD · {{ $item->hostingPlan->cpu_cores }} Core CPU · {{ $item->hostingPlan->ram_gb }}GB RAM</div>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Summary / Total --}}
                <table class="summary-table">
                    <tbody>
                        <tr>
                            <td class="label" style="width: 80%;">Subtotal</td>
                            <td class="value">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @if($discountAmount > 0)
                        <tr>
                            <td colspan="2">
                                <div class="discount-box">
                                    <span class="discount-box-label">
                                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"></path>
                                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                                        </svg>
                                        Diskon
                                    </span>
                                    <span class="discount-box-value">-Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
                                </div>
                            </td>
                        </tr>
                        @endif
                        <tr class="row-total">
                            <td class="total-label">Total</td>
                            <td class="total-value">Rp {{ number_format($finalTotal, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
                @else
                {{-- No items - show simple total --}}
                @php
                    $discountAmount = $invoice->discount > 0 ? $invoice->discount : ($invoice->order?->discount_amount ?? 0);
                    $finalTotal = $invoice->amount - $discountAmount;
                    if ($finalTotal < 0) $finalTotal = 0;
                @endphp
                <table class="summary-table">
                    <tbody>
                        @if($discountAmount > 0)
                        <tr>
                            <td class="label" style="width: 80%;">Subtotal</td>
                            <td class="value">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div class="discount-box">
                                    <span class="discount-box-label">
                                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"></path>
                                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                                        </svg>
                                        Diskon
                                    </span>
                                    <span class="discount-box-value">-Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
                                </div>
                            </td>
                        </tr>
                        @endif
                        <tr class="row-total">
                            <td class="total-label">Total</td>
                            <td class="total-value">Rp {{ number_format($finalTotal, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
                @endif

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