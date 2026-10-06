<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class CampaignEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public EmailCampaign $campaign,
        public EmailCampaignRecipient $recipient,
    ) {}

    public function envelope(): Envelope
    {
        $envelope = new Envelope(
            subject: $this->substitute($this->campaign->subject ?? ''),
        );

        if ($this->campaign->from_email !== null && $this->campaign->from_email !== '') {
            $envelope->from($this->campaign->from_email);
        }

        return $envelope;
    }

    public function content(): Content
    {
        return new Content(htmlString: $this->substitute($this->campaign->body ?? ''));
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if ($this->campaign->attachment_path === null || $this->campaign->attachment_path === '') {
            return [];
        }

        return [
            Attachment::fromStorageDisk('local', $this->campaign->attachment_path),
        ];
    }

    private function substitute(string $text): string
    {
        return str_replace(
            ['{company_name}', '{recipient_email}'],
            [(string) $this->recipient->recipient_name, (string) $this->recipient->recipient_email],
            $text,
        );
    }
}
