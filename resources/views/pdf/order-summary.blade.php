<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Estimasi Biaya - {{ $branding['app_name'] ?? config('app.name', 'WSCRM') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
            line-height: 1.4;
        }
        
        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 3px solid {{ $branding['primary_color'] ?? '#3b82f6' }};
            padding-bottom: 20px;
        }
        .company-logo {
            font-size: 24px;
            font-weight: bold;
            color: {{ $branding['primary_color'] ?? '#3b82f6' }};
            margin-bottom: 5px;
        }
        .company-info {
            font-size: 13px;
            color: #666;
        }
        .doc-info {
            text-align: right;
        }
        .doc-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .doc-date {
            font-size: 13px;
            color: #666;
        }
        
        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th {
            background-color: #f8f9fa;
            padding: 12px 10px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #dee2e6;
            font-size: 14px;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: top;
        }
        .text-right {
            text-align: right;
        }
        .item-name {
            font-weight: bold;
            margin-bottom: 3px;
        }
        .item-desc {
            font-size: 12px;
            color: #666;
        }
        
        /* Totals */
        .totals {
            width: 250px;
            margin-left: auto;
            margin-top: 20px;
        }
        .totals table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals td {
            padding: 8px 0;
            font-size: 14px;
        }
        .totals .label {
            text-align: right;
            padding-right: 15px;
            color: #666;
        }
        .totals .amount {
            text-align: right;
            font-weight: bold;
        }
        .discount {
            color: #28a745;
        }
        .total-row {
            border-top: 2px solid #dee2e6;
            font-size: 16px;
        }
        .total-row .amount {
            color: {{ $branding['primary_color'] ?? '#3b82f6' }};
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 12px;
            color: #666;
        }
        .terms {
            margin-bottom: 15px;
        }
        .contact {
            text-align: center;
            color: #999;
        }
    </style>
</head>
<body>
    @php $branding = $branding ?? []; @endphp
    <div class="header">
        <div>
            <div class="company-logo">{{ $branding['app_name'] ?? config('app.name', 'WSCRM') }}</div>
            <div class="company-info">
                {{ $branding['company_website'] ?? 'websweetstudio.com' }}<br>
                {{ $branding['company_tagline'] ?? 'Jasa Pembuatan Website Profesional' }}<br>
                {{ $branding['company_email'] ?? 'hello@websweetstudio.com' }}
            </div>
        </div>
        <div class="doc-info">
            <div class="doc-title">ESTIMASI BIAYA</div>
            <div class="doc-date">Tanggal: {{ $date }}</div>
            <div class="doc-date">Jam: {{ $time }}</div>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Layanan</th>
                <th style="width: 20%;" class="text-right">Harga</th>
                <th style="width: 10%;" class="text-right">Qty</th>
                <th style="width: 20%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($calculation['items'] as $item)
            <tr>
                <td>
                    <div class="item-name">{{ $item['name'] }}</div>
                    <div class="item-desc">{{ $item['description'] }}</div>
                </td>
                <td class="text-right">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                <td class="text-right">{{ $item['quantity'] }}</td>
                <td class="text-right">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td class="label">Subtotal:</td>
                <td class="amount">Rp {{ number_format($calculation['subtotal'], 0, ',', '.') }}</td>
            </tr>
            
            @if($calculation['discount_amount'] > 0)
            <tr class="discount">
                <td class="label">
                    Diskon 
                    @if($calculation['discount_type'] === 'percent')
                        ({{ $calculation['discount_percent'] }}%):
                    @else
                        :
                    @endif
                </td>
                <td class="amount">-Rp {{ number_format($calculation['discount_amount'], 0, ',', '.') }}</td>
            </tr>
            @endif
            
            @if($calculation['tax_percent'] > 0)
            <tr>
                <td class="label">Pajak ({{ $calculation['tax_percent'] }}%):</td>
                <td class="amount">Rp {{ number_format($calculation['tax_amount'], 0, ',', '.') }}</td>
            </tr>
            @endif
            
            <tr class="total-row">
                <td class="label">TOTAL:</td>
                <td class="amount">Rp {{ number_format($calculation['grand_total'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <div class="terms">
            <strong>Catatan:</strong><br>
            • Estimasi ini berlaku selama 30 hari<br>
            • Harga sudah termasuk domain dan hosting untuk 1 tahun<br>
            • DP 50% untuk memulai pengerjaan<br>
            • Revisi minor gratis, revisi major dikenakan biaya tambahan
        </div>
        
        @if($banks && count($banks) > 0)
        <div style="margin: 20px 0;">
            <strong>Pembayaran via Transfer Bank:</strong><br>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px; margin-top: 10px;">
                @foreach($banks as $bank)
                <div style="border: 1px solid #dee2e6; padding: 10px; border-radius: 5px; background-color: #f8f9fa;">
                    <div style="font-weight: bold; color: #333; margin-bottom: 3px;">{{ $bank->bank_name }}</div>
                    <div style="font-size: 12px; color: #666;">
                        {{ $bank->account_number }}<br>
                        a.n. {{ $bank->account_name }}
                        @if($bank->admin_fee > 0)
                        <br><span style="color: #dc3545;">Biaya Admin: Rp {{ number_format($bank->admin_fee, 0, ',', '.') }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        
        <div class="contact">
            <strong>{{ $branding['app_name'] ?? config('app.name', 'WSCRM') }}</strong> - {{ $branding['company_tagline'] ?? 'Jasa Pembuatan Website Profesional' }}<br>
            Terima kasih atas kepercayaan Anda!
        </div>
    </div>
</body>
</html>