<?php

declare(strict_types=1);

namespace App\Mail;

use App\Enums\QuotationArea;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class QuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Quotation $quotation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('filament/resources/quotation.actions.send.email_subject', [
                'number' => $this->quotation->number,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quotation',
            with: ['quotation' => $this->quotation],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn (): string => Pdf::loadView('pdf.quotation', [
                    'quotation' => $this->quotation,
                    'areas' => QuotationArea::cases(),
                ])->output(),
                'quotation-'.$this->quotation->number.'.pdf',
            )->withMime('application/pdf'),
        ];
    }
}
