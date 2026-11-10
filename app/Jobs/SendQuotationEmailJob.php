<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\QuotationMail;
use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

final class SendQuotationEmailJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function __construct(
        public string $quotationId,
        public string $recipientEmail,
    ) {}

    public function handle(): void
    {
        $quotation = Quotation::query()
            ->with('company')
            ->find($this->quotationId);

        if ($quotation === null) {
            return;
        }

        Mail::to($this->recipientEmail)->send(new QuotationMail($quotation));
    }
}
