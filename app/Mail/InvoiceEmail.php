<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\PaymentAccount;
use App\Models\BrandingSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceEmail extends Mailable
{
  use Queueable, SerializesModels;

  public function __construct(
    public Invoice $invoice
  ) {}

  public function envelope(): Envelope
  {
    return new Envelope(
      subject: 'Tagihan ' . $this->invoice->invoice_number . ' - ' . config('app.name'),
    );
  }

  public function content(): Content
  {
    $this->invoice->loadMissing(['aiPackage', 'order.orderItems' => function ($q) {
      $q->with(['hostingPlan', 'domainPrice', 'servicePlan']);
    }]);

    return new Content(
      view: 'emails.invoices.invoice-email',
      with: [
        'orderItems' => $this->invoice->order?->orderItems()->with(['hostingPlan', 'domainPrice', 'servicePlan'])->get() ?? collect(),
        'branding' => BrandingSetting::getAllActive()->pluck('value', 'key'),
        'paymentAccounts' => PaymentAccount::active()->orderBy('sort')->get(),
      ],
    );
  }

  public function attachments(): array
  {
    return [];
  }
}
