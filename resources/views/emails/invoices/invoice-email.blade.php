<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tagihan {{ $invoice->invoice_number }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background-color: #e0e0e0; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center" style="padding: 32px 16px;">
                <table width="580" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border: 1px solid #cccccc;">
                    {{-- Logo / App Name Header (mengikuti branding) --}}
                    <tr>
                        <td style="padding: 24px 32px 16px; border-bottom: 3px solid #000000;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="width: 60%;">
                                        @php
                                            $companyName = $branding->get('app_name', config('app.name', 'WSCRM'));
                                            $appLogo = $branding->get('app_logo');
                                        @endphp
                                        @if($appLogo)
                                            <img src="{{ \Illuminate\Support\Facades\URL::to('/storage/' . (str_replace('/storage/', '', $appLogo))) }}" alt="{{ $companyName }}" style="max-height: 60px;">
                                        @else
                                            <span style="font-size: 20px; font-weight: 700; color: #000000;">{{ $companyName }}</span>
                                        @endif
                                    </td>
                                    <td style="width: 40%; text-align: right;">
                                        @php
                                            $primaryColor = $branding->get('primary_color', '#3b82f6');
                                        @endphp
                                        <span style="display: inline-block; width: 16px; height: 16px; border-radius: 50%; background-color: {{ $primaryColor }};"></span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Title --}}
                    <tr>
                        <td style="padding: 24px 32px 12px;">
                            <h1 style="font-size: 20px; font-weight: 700; color: #000000; margin: 0;">TAGIHAN</h1>
                            <p style="font-size: 14px; color: #555555; margin: 4px 0 0;">{{ $invoice->invoice_number }}</p>
                        </td>
                    </tr>

                    {{-- Greeting --}}
                    <tr>
                        <td style="padding: 8px 32px 16px;">
                            <p style="font-size: 15px; color: #333333; margin: 0;">
                                Kepada Yth. <strong style="color: #000000;">{{ $invoice->customer->name }}</strong>,
                            </p>
                            <p style="font-size: 14px; color: #555555; margin: 8px 0 0; line-height: 1.6;">
                                Berikut adalah rincian tagihan Anda. Mohon lakukan pembayaran sebelum tanggal jatuh tempo.
                            </p>
                        </td>
                    </tr>

                    {{-- Invoice Details --}}
                    <tr>
                        <td style="padding: 0 32px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="width: 50%; vertical-align: top;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr><td style="padding: 5px 0; font-size: 13px; color: #666666; width: 110px;">No. Tagihan</td><td style="padding: 5px 0; font-size: 13px; color: #000000;">: {{ $invoice->invoice_number }}</td></tr>
                                            <tr><td style="padding: 5px 0; font-size: 13px; color: #666666;">Jenis</td><td style="padding: 5px 0; font-size: 13px; color: #000000;">: {{ match($invoice->invoice_type) {
                                                'setup' => 'Pembukaan',
                                                'renewal' => 'Perpanjangan',
                                                'upgrade' => 'Upgrade',
                                                'downgrade' => 'Downgrade',
                                                'topup' => 'Topup Kredit AI',
                                                default => $invoice->invoice_type,
                                            } }}</td></tr>
                                            <tr><td style="padding: 5px 0; font-size: 13px; color: #666666;">Siklus</td><td style="padding: 5px 0; font-size: 13px; color: #000000;">: {{ match($invoice->billing_cycle) {
                                                'monthly' => 'Bulanan',
                                                'quarterly' => 'Triwulan',
                                                'semi_annually' => '6 Bulanan',
                                                'annually' => 'Tahunan',
                                                default => $invoice->billing_cycle,
                                            } }}</td></tr>
                                            @if($invoice->order)
                                            <tr><td style="padding: 5px 0; font-size: 13px; color: #666666;">Layanan</td><td style="padding: 5px 0; font-size: 13px; color: #000000;">: {{ $invoice->order->domain_name ?? '-' }}</td></tr>
                                            @endif
                                        </table>
                                    </td>
                                    <td style="width: 50%; vertical-align: top; text-align: right;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr><td style="padding: 5px 0; font-size: 13px; color: #666666; width: 100px;">Tgl Terbit</td><td style="padding: 5px 0; font-size: 13px; color: #000000;">: {{ $invoice->issue_date->format('d M Y') }}</td></tr>
                                            <tr><td style="padding: 5px 0; font-size: 13px; color: #666666;">Jatuh Tempo</td><td style="padding: 5px 0; font-size: 13px; color: #000000; font-weight: 700;">: {{ $invoice->due_date->format('d M Y') }}</td></tr>
                                            <tr><td style="padding: 5px 0; font-size: 13px; color: #666666;">Status</td><td style="padding: 5px 0; font-size: 13px; color: #000000;">: <strong>{{ $invoice->status === 'paid' ? 'LUNAS' : 'BELUM DIBAYAR' }}</strong></td></tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Items Table --}}
                    @if($orderItems->count() > 0)
                        @php
                            $subtotal = $orderItems->sum(fn($item) => $item->price * $item->quantity);
                            $discountAmount = $invoice->discount > 0 ? $invoice->discount : ($invoice->order?->discount_amount ?? 0);
                            $finalTotal = $subtotal - $discountAmount;
                            if ($finalTotal < 0) $finalTotal = 0;
                        @endphp
                    <tr>
                        <td style="padding: 0 32px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding: 8px 10px; font-size: 12px; font-weight: 700; color: #000000; background-color: #f0f0f0; border-top: 2px solid #000000; border-bottom: 2px solid #000000;">ITEM</td>
                                    <td style="padding: 8px 10px; font-size: 12px; font-weight: 700; color: #000000; background-color: #f0f0f0; border-top: 2px solid #000000; border-bottom: 2px solid #000000; text-align: center; width: 50px;">QTY</td>
                                    <td style="padding: 8px 10px; font-size: 12px; font-weight: 700; color: #000000; background-color: #f0f0f0; border-top: 2px solid #000000; border-bottom: 2px solid #000000; text-align: right; width: 120px;">HARGA</td>
                                    <td style="padding: 8px 10px; font-size: 12px; font-weight: 700; color: #000000; background-color: #f0f0f0; border-top: 2px solid #000000; border-bottom: 2px solid #000000; text-align: right; width: 130px;">TOTAL</td>
                                </tr>
                                @foreach($orderItems as $item)
                                <tr>
                                    <td style="padding: 10px 10px; font-size: 13px; color: #000000; border-bottom: 1px solid #dddddd;">
                                        <strong>
                                            @if($item->item_type === 'hosting' && $item->hostingPlan)
                                                {{ $item->hostingPlan->plan_name }}
                                            @elseif($item->item_type === 'domain')
                                                {{ $item->domain_name ?? $invoice->order?->domain_name ?? '-' }}
                                            @elseif($item->servicePlan)
                                                {{ $item->servicePlan->name }}
                                            @else
                                                {{ ucfirst($item->item_type) }}
                                            @endif
                                        </strong>
                                        @if($item->item_type === 'hosting' && $item->hostingPlan)
                                            <br><span style="font-size: 12px; color: #888888;">{{ $item->hostingPlan->storage_gb }}GB SSD &middot; {{ $item->hostingPlan->cpu_cores }} Core CPU &middot; {{ $item->hostingPlan->ram_gb }}GB RAM</span>
                                        @endif
                                    </td>
                                    <td style="padding: 10px 10px; font-size: 13px; color: #000000; border-bottom: 1px solid #dddddd; text-align: center;">{{ $item->quantity }}</td>
                                    <td style="padding: 10px 10px; font-size: 13px; color: #000000; border-bottom: 1px solid #dddddd; text-align: right;">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td style="padding: 10px 10px; font-size: 13px; color: #000000; border-bottom: 1px solid #dddddd; text-align: right;">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </table>

                            {{-- Summary --}}
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 12px;">
                                <tr>
                                    <td style="padding: 6px 10px; font-size: 13px; color: #555555; text-align: right; width: 70%;">Subtotal</td>
                                    <td style="padding: 6px 10px; font-size: 13px; color: #000000; font-weight: 600; text-align: right;">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @if($discountAmount > 0)
                                <tr>
                                    <td style="padding: 6px 10px; font-size: 13px; color: #555555; text-align: right;">Diskon</td>
                                    <td style="padding: 6px 10px; font-size: 13px; color: #000000; font-weight: 600; text-align: right; border-bottom: 1px solid #cccccc;">-Rp {{ number_format($discountAmount, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding: 10px 10px; font-size: 16px; font-weight: 700; color: #000000; text-align: right; border-top: 2px solid #000000;">TOTAL</td>
                                    <td style="padding: 10px 10px; font-size: 16px; font-weight: 700; color: #000000; text-align: right; border-top: 2px solid #000000;">Rp {{ number_format($finalTotal, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @else
                        @php
                            $discountAmount = $invoice->discount > 0 ? $invoice->discount : ($invoice->order?->discount_amount ?? 0);
                            $finalTotal = $invoice->amount - $discountAmount;
                            if ($finalTotal < 0) $finalTotal = 0;
                        @endphp
                    <tr>
                        <td style="padding: 0 32px 24px;">
                            @if($invoice->invoice_type === 'topup' && $invoice->aiPackage)
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 12px;">
                                <tr>
                                    <td style="padding: 10px; font-size: 13px; color: #000000; border: 1px solid #dddddd;">
                                        <strong>{{ $invoice->aiPackage->name }}</strong>
                                        <br><span style="font-size: 12px; color: #888888;">{{ number_format($invoice->aiPackage->credits, 0, ',', '.') }} kredit AI</span>
                                    </td>
                                    <td style="padding: 10px; font-size: 13px; color: #000000; border: 1px solid #dddddd; text-align: right; width: 120px;">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                            @endif
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                @if($discountAmount > 0)
                                <tr>
                                    <td style="padding: 6px 10px; font-size: 13px; color: #555555; text-align: right;">Subtotal</td>
                                    <td style="padding: 6px 10px; font-size: 13px; color: #000000; font-weight: 600; text-align: right;">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 10px; font-size: 13px; color: #555555; text-align: right;">Diskon</td>
                                    <td style="padding: 6px 10px; font-size: 13px; color: #000000; font-weight: 600; text-align: right; border-bottom: 1px solid #cccccc;">-Rp {{ number_format($discountAmount, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding: 10px 10px; font-size: 16px; font-weight: 700; color: #000000; text-align: right; border-top: 2px solid #000000;">TOTAL</td>
                                    <td style="padding: 10px 10px; font-size: 16px; font-weight: 700; color: #000000; text-align: right; border-top: 2px solid #000000;">Rp {{ number_format($finalTotal, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif

                    {{-- Payment Information (mengikuti setting akun pembayaran) --}}
                    @if($invoice->status !== 'paid' && $paymentAccounts->isNotEmpty())
                    <tr>
                        <td style="padding: 0 32px 24px;">
                            <h3 style="margin: 0 0 10px 0; color: #007bff; font-size: 16px;">INFORMASI PEMBAYARAN</h3>
                            <table style="width: 100%; border-collapse: collapse;">
                                @foreach($paymentAccounts->chunk(2) as $chunk)
                                    <tr>
                                        @foreach($chunk as $account)
                                            <td style="width: {{ 100 / min(2, $chunk->count()) }}%; padding: 0; padding-right: 20px; vertical-align: top;">
                                                @if($account->type === 'bank')
                                                    <strong>{{ $account->name ?: 'Bank' }}</strong><br>
                                                    <span style="font-size: 13px; color: #555;">No. Rek: {{ $account->account_number }}</span><br>
                                                    <span style="font-size: 13px; color: #555;">A/n: {{ $account->account_name }}</span>
                                                @elseif($account->type === 'ewallet')
                                                    <strong>{{ $account->name ?: 'E-Wallet' }}</strong><br>
                                                    <span style="font-size: 13px; color: #555;">No: {{ $account->account_number }}</span><br>
                                                    <span style="font-size: 13px; color: #555;">A/n: {{ $account->account_name }}</span>
                                                @elseif($account->type === 'qris')
                                                    <strong>{{ $account->name ?: 'QRIS' }}</strong><br>
                                                    @if($account->qris_image_path)
                                                        <img src="{{ \Illuminate\Support\Facades\URL::to('/storage/' . (str_replace('/storage/', '', $account->qris_image_path))) }}" alt="QRIS" style="max-width: 150px; max-height: 150px;">
                                                    @endif
                                                    @if($account->account_name)
                                                        <br><span style="font-size: 13px; color: #555;">A/n: {{ $account->account_name }}</span>
                                                    @endif
                                                @endif
                                            </td>
                                        @endforeach
                                        @for($i = $chunk->count(); $i < 2; $i++)
                                            <td style="padding: 0;"></td>
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
                        </td>
                    </tr>
                    @endif

                    {{-- CTA (hanya bila belum lunas) --}}
                    @if($invoice->status !== 'paid')
                    <tr>
                        <td align="center" style="padding: 8px 32px 24px;">
                            <a href="{{ url('/customer/invoices/' . $invoice->id) }}" style="display: inline-block; background-color: {{ $branding->get('primary_color', '#000000') }}; color: #ffffff; text-decoration: none; padding: 12px 36px; font-size: 14px; font-weight: 700;">
                                BAYAR TAGIHAN
                            </a>
                        </td>
                    </tr>
                    @endif

                    {{-- Footer --}}
                    <tr>
                        <td style="padding: 20px 32px; background-color: #f5f5f5; border-top: 1px solid #dddddd;">
                            <p style="font-size: 13px; color: #666666; margin: 0; line-height: 1.6;">
                                @php
                                    $footerParts = [];
                                    $companyEmail = $branding->get('company_email');
                                    $companyPhone = $branding->get('company_phone');
                                    $companyWhatsapp = $branding->get('company_whatsapp');
                                    if ($companyEmail) $footerParts[] = 'Email: ' . $companyEmail;
                                    $priNo = $companyWhatsapp ?: $companyPhone;
                                    if ($priNo) $footerParts[] = 'WhatsApp: ' . $priNo;
                                    if ($companyPhone && $companyPhone !== $priNo) $footerParts[] = 'Tel: ' . $companyPhone;
                                @endphp
                                @if(count($footerParts))
                                    Hubungi kami: {{ implode(' | ', $footerParts) }}
                                @else
                                    Ada pertanyaan? Hubungi kami.
                                @endif
                            </p>
                            <p style="font-size: 12px; color: #999999; margin: 16px 0 0;">
                                {{ $branding->get('app_name', config('app.name', 'WSCRM')) }} &mdash; {{ date('Y') }}
                            </p>
                            @if($branding->get('footer_text'))
                                <p style="font-size: 11px; color: #999999; margin: 4px 0 0;">{{ $branding->get('footer_text') }}</p>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
