<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 14px;
            line-height: 1.4;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        .main-table {
            width: 100%;
            border: none;
        }
        .main-table td {
            padding: 10px;
            vertical-align: top;
            border: none;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
        }
        .header-table td {
            padding: 10px;
            border: none;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .info-box {
            background: #ffffff;
            padding: 0;
            border-radius: 0;
            margin: 5px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th {
            background: #333;
            color: white;
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .items-table td {
            padding: 12px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        .items-table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .totals-table {
            width: 300px;
            float: right;
            margin-top: 20px;
        }
        .totals-table td {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .total-row {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-paid { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-overdue { background: #f8d7da; color: #721c24; }
        .status-cancelled { background: #e9ecef; color: #6c757d; }
        .bank-info {
            margin-top: 30px;
            background: #f0f8ff;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <div class="invoice-title">FAKTUR</div>
                <div style="font-size: 16px; margin-bottom: 10px;">{{ $invoice->invoice_number }}</div>
                <div class="status-badge status-{{ $invoice->status }}">
                    @if($invoice->status === 'paid') LUNAS
                    @elseif($invoice->status === 'pending') MENUNGGU
                    @elseif($invoice->status === 'overdue') TERLAMBAT
                    @else {{ strtoupper($invoice->status) }}
                    @endif
                </div>
            </td>
            <td style="width: 40%; text-align: right;">
                @php
                    $companyName = $branding->get('app_name', 'WSCRM');
                    $companyEmail = $branding->get('company_email');
                    $companyPhone = $branding->get('company_phone');
                    $companyWhatsapp = $branding->get('company_whatsapp');
                    $companyAddress = $branding->get('company_address');
                    $appLogo = $branding->get('app_logo');
                @endphp
                @if($appLogo)
                    @php
                        $logoRelative = str_replace('/storage/', '', $appLogo);
                        $logoPath = \Illuminate\Support\Facades\Storage::disk('public')->path($logoRelative);
                    @endphp
                    <img src="{{ $logoPath }}" alt="{{ $companyName }}" style="max-height: 60px; margin-bottom: 5px;">
                @else
                    <div style="font-size: 18px; font-weight: bold; margin-bottom: 5px;">{{ $companyName }}</div>
                @endif
                @if($companyEmail)
                    <div style="margin-bottom: 5px;">{{ $companyEmail }}</div>
                @endif
                @if($companyPhone)
                    <div style="margin-bottom: 5px;">{{ $companyPhone }}</div>
                @endif
                @if($companyWhatsapp && $companyWhatsapp !== $companyPhone)
                    <div>WA: {{ $companyWhatsapp }}</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Customer and Invoice Details -->
    <table class="main-table">
        <tr>
            <td style="width: 50%;">
                <h4 style="color: #666; margin: 0 0 8px 0; font-size: 14px;">TAGIHAN UNTUK:</h4>
                <div class="info-box">
                    <strong>{{ $invoice->customer->name }}</strong><br>
                    {{ $invoice->customer->email }}<br>
                    @if($invoice->customer->phone)
                        Tel: {{ $invoice->customer->phone }}<br>
                    @endif
                    @if($invoice->customer->address)
                        {{ $invoice->customer->address }}<br>
                    @endif
                    @if($invoice->customer->city)
                        {{ $invoice->customer->city }}<br>
                    @endif
                    @if($invoice->customer->country)
                        {{ $invoice->customer->country }}
                    @endif
                </div>
            </td>
            <td style="width: 50%;">
                <h4 style="color: #666; margin: 0 0 8px 0; font-size: 14px;">DETAIL FAKTUR:</h4>
                <div class="info-box">
                    <strong>Tanggal Faktur:</strong> {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d M Y') }}<br>
                    <strong>Jatuh Tempo:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}<br>
                    <strong>Jenis Faktur:</strong> {{ $invoice->invoice_type === 'setup' ? 'Setup' : 'Perpanjangan' }}<br>
                    <strong>Siklus Tagihan:</strong> 
                    @if($invoice->billing_cycle === 'monthly') Bulanan
                    @elseif($invoice->billing_cycle === 'quarterly') Triwulan
                    @elseif($invoice->billing_cycle === 'semi_annually') 6 Bulan
                    @elseif($invoice->billing_cycle === 'annually') Tahunan
                    @else {{ ucfirst($invoice->billing_cycle) }}
                    @endif<br>
                    @if($invoice->order)
                        <strong>Order:</strong> #{{ $invoice->order->id }}<br>
                        @if($invoice->order->domain_name)
                            <strong>Domain:</strong> {{ $invoice->order->domain_name }}<br>
                        @endif
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Layanan</th>
                <th>Deskripsi</th>
                <th>Qty</th>
                <th class="text-right">Harga</th>
            </tr>
        </thead>
        <tbody>
            @if($invoice->order && $invoice->order->orderItems)
                @foreach($invoice->order->orderItems as $item)
                    <tr>
                        <td>
                            @if($item->item_type === 'hosting') Hosting
                            @elseif($item->item_type === 'domain') Domain
                            @elseif($item->item_type === 'service') Layanan
                            @elseif($item->item_type === 'app') Aplikasi
                            @elseif($item->item_type === 'web') Website
                            @elseif($item->item_type === 'maintenance') Maintenance
                            @else {{ ucfirst($item->item_type) }}
                            @endif
                        </td>
                        <td>
                            @if($item->domain_name)
                                {{ $item->domain_name }}
                            @else
                                @if($item->item_type === 'hosting') Layanan Hosting
                                @elseif($item->item_type === 'domain') Registrasi Domain
                                @elseif($item->item_type === 'service') Layanan Tambahan
                                @elseif($item->item_type === 'app') Pengembangan Aplikasi
                                @elseif($item->item_type === 'web') Pengembangan Website
                                @elseif($item->item_type === 'maintenance') Layanan Maintenance
                                @else Layanan {{ ucfirst($item->item_type) }}
                                @endif
                            @endif
                        </td>
                        <td>{{ $item->quantity ?? 1 }}</td>
                        <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td>{{ $invoice->invoice_type === 'setup' ? 'Setup' : 'Perpanjangan' }}</td>
                    <td>
                        @if($invoice->notes)
                            {{ $invoice->notes }}
                        @else
                            Layanan {{ $invoice->invoice_type === 'setup' ? 'Setup' : 'Perpanjangan' }}
                        @endif
                    </td>
                    <td>1</td>
                    <td class="text-right">Rp {{ number_format($invoice->amount + ($invoice->discount ?? 0), 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Totals -->
    <div style="clear: both;">
        <table class="totals-table">
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($invoice->amount + ($invoice->discount ?? 0), 0, ',', '.') }}</strong></td>
            </tr>
            @if($invoice->discount && $invoice->discount > 0)
                <tr>
                    <td><strong>Diskon:</strong></td>
                    <td class="text-right" style="color: #28a745;"><strong>-Rp {{ number_format($invoice->discount, 0, ',', '.') }}</strong></td>
                </tr>
            @endif
            <tr class="total-row">
                <td><strong>TOTAL:</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($invoice->amount, 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    @if($invoice->status === 'paid' && $invoice->paid_at)
        <div style="margin-top: 20px; padding: 15px; background: #d4edda; border-radius: 5px; color: #155724; clear: both;">
            <strong>✓ LUNAS</strong> - Dibayar pada {{ \Carbon\Carbon::parse($invoice->paid_at)->format('d M Y H:i') }}
            @if($invoice->payment_method)
                via {{ $invoice->payment_method }}
            @endif
        </div>
    @elseif($invoice->status !== 'paid')
        <!-- Bank Payment Information -->
        @if($paymentAccounts->isNotEmpty())
            <div class="bank-info" style="clear: both;">
                <h3 style="margin: 0 0 10px 0; color: #007bff;">INFORMASI PEMBAYARAN</h3>
                <table style="width: 100%; border: none;">
                    @foreach($paymentAccounts->chunk(2) as $chunk)
                        <tr>
                            @foreach($chunk as $account)
                                <td style="width: {{ 100 / min(2, $chunk->count()) }}%; border: none; padding: 0; padding-right: 20px;">
                                    @if($account->type === 'bank')
                                        <strong>{{ $account->name ?: 'Bank' }}</strong><br>
                                        No. Rek: {{ $account->account_number }}<br>
                                        A/n: {{ $account->account_name }}
                                    @elseif($account->type === 'ewallet')
                                        <strong>{{ $account->name ?: 'E-Wallet' }}</strong><br>
                                        No: {{ $account->account_number }}<br>
                                        A/n: {{ $account->account_name }}
                                    @elseif($account->type === 'qris')
                                        <strong>{{ $account->name ?: 'QRIS' }}</strong><br>
                                        @if($account->qris_image_path)
                                            @php
                                                $qrisRelative = str_replace('/storage/', '', $account->qris_image_path);
                                                $qrisPath = \Illuminate\Support\Facades\Storage::disk('public')->path($qrisRelative);
                                            @endphp
                                            <img src="{{ $qrisPath }}" alt="QRIS" style="max-width: 150px; max-height: 150px;">
                                        @endif
                                        @if($account->account_name)
                                            <br>A/n: {{ $account->account_name }}
                                        @endif
                                    @endif
                                </td>
                            @endforeach
                            @for($i = $chunk->count(); $i < 2; $i++)
                                <td style="border: none; padding: 0;"></td>
                            @endfor
                        </tr>
                    @endforeach
                </table>
                @php $primaryPhone = $branding->get('company_whatsapp') ?: $branding->get('company_phone'); @endphp
                @if($primaryPhone)
                    <p style="margin: 10px 0 0 0; font-size: 12px; color: #666;">
                        Silakan transfer sesuai nominal dan konfirmasi pembayaran ke WhatsApp: {{ $primaryPhone }}
                    </p>
                @endif
            </div>
        @elseif($invoice->paymentAccount)
            <div class="bank-info" style="clear: both;">
                <h3 style="margin: 0 0 10px 0; color: #007bff;">INFORMASI PEMBAYARAN</h3>
                @if($invoice->paymentAccount->type === 'bank')
                    <strong>{{ $invoice->paymentAccount->name ?: 'Bank' }}</strong><br>
                    No. Rek: {{ $invoice->paymentAccount->account_number }}<br>
                    A/n: {{ $invoice->paymentAccount->account_name }}
                @elseif($invoice->paymentAccount->type === 'ewallet')
                    <strong>{{ $invoice->paymentAccount->name ?: 'E-Wallet' }}</strong><br>
                    No: {{ $invoice->paymentAccount->account_number }}<br>
                    A/n: {{ $invoice->paymentAccount->account_name }}
                @elseif($invoice->paymentAccount->type === 'qris')
                    <strong>{{ $invoice->paymentAccount->name ?: 'QRIS' }}</strong><br>
                    @if($invoice->paymentAccount->qris_image_path)
                        @php
                            $qrisRel = str_replace('/storage/', '', $invoice->paymentAccount->qris_image_path);
                            $qrisP = \Illuminate\Support\Facades\Storage::disk('public')->path($qrisRel);
                        @endphp
                        <img src="{{ $qrisP }}" alt="QRIS" style="max-width: 150px; max-height: 150px;">
                    @endif
                    @if($invoice->paymentAccount->account_name)
                        <br>A/n: {{ $invoice->paymentAccount->account_name }}
                    @endif
                @endif
                @php $priPhone = $branding->get('company_whatsapp') ?: $branding->get('company_phone'); @endphp
                @if($priPhone)
                    <p style="margin: 10px 0 0 0; font-size: 12px; color: #666;">
                        Silakan transfer sesuai nominal dan konfirmasi pembayaran ke WhatsApp: {{ $priPhone }}
                    </p>
                @endif
            </div>
        @endif
    @endif

    @if($invoice->notes)
        <div style="margin-top: 30px; clear: both;">
            <h4 style="color: #666; margin: 0 0 8px 0; font-size: 14px;">Catatan:</h4>
            <div class="info-box">
                {{ $invoice->notes }}
            </div>
        </div>
    @endif

    <div class="footer" style="clear: both;">
        <p><strong>Terima kasih atas kepercayaan Anda!</strong></p>
        <p>Untuk pertanyaan mengenai faktur ini, silakan hubungi kami:</p>
        @php
            $footerParts = [];
            if ($companyEmail) $footerParts[] = 'Email: ' . $companyEmail;
            $priNo = $branding->get('company_whatsapp') ?: $companyPhone;
            if ($priNo) $footerParts[] = 'WhatsApp: ' . $priNo;
            if ($companyPhone && $companyPhone !== $priNo) $footerParts[] = 'Tel: ' . $companyPhone;
        @endphp
        @if(count($footerParts))
            <p>{{ implode(' | ', $footerParts) }}</p>
        @endif
        @if($companyAddress)
            <p>{{ $companyAddress }}</p>
        @endif
        <p style="margin-top: 20px; font-size: 10px;">
            Faktur ini dibuat secara otomatis pada {{ now()->format('d M Y H:i:s') }} WIB
        </p>
    </div>
</body>
</html>