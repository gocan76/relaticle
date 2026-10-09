<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\EmailCampaignRecipientStatus;
use App\Mail\CampaignEmail;
use App\Models\EmailCampaignRecipient;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class SendCampaignEmailJob implements ShouldQueue
{
    use Batchable, Dispatchable, Queueable, SerializesModels;

    public function __construct(public string $recipientId) {}

    public function handle(): void
    {
        $recipient = EmailCampaignRecipient::query()
            ->with('campaign')
            ->find($this->recipientId);

        if ($recipient === null) {
            return;
        }

        if (! is_string($recipient->recipient_email) || $recipient->recipient_email === '') {
            return;
        }

        try {
            Mail::to($recipient->recipient_email)->send(new CampaignEmail($recipient->campaign, $recipient));

            $recipient->update([
                'status' => EmailCampaignRecipientStatus::Sent->value,
                'sent_at' => now(),
                'error' => null,
            ]);
        } catch (Throwable $e) {
            report($e);

            $recipient->update([
                'status' => EmailCampaignRecipientStatus::Error->value,
                'error' => mb_substr($e->getMessage(), 0, 500),
            ]);
        }
    }
}
