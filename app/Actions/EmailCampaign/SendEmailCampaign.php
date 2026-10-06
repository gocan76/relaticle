<?php

declare(strict_types=1);

namespace App\Actions\EmailCampaign;

use App\Enums\EmailCampaignRecipientStatus;
use App\Enums\EmailCampaignStatus;
use App\Jobs\SendCampaignEmailJob;
use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Support\Facades\Bus;

final readonly class SendEmailCampaign
{
    /**
     * Send a campaign. When a user is provided the send is gated by the
     * campaign policy; the scheduler passes null for system-initiated sends.
     */
    public function execute(?User $user, EmailCampaign $campaign): EmailCampaign
    {
        if ($user instanceof User) {
            abort_unless($user->can('send', $campaign), 403);
        }

        if (! in_array($campaign->status, [EmailCampaignStatus::Draft, EmailCampaignStatus::Scheduled], true)) {
            return $campaign;
        }

        abort_if(trim((string) $campaign->subject) === '' || trim((string) $campaign->body) === '', 422, 'Campaign subject and body are required before sending.');

        $recipientIds = $campaign->recipients()
            ->where('status', EmailCampaignRecipientStatus::Pending->value)
            ->whereNotNull('recipient_email')
            ->where('recipient_email', '!=', '')
            ->pluck('id')
            ->all();

        abort_if($recipientIds === [], 422, 'This campaign has no recipients with an email address.');

        $campaign->update([
            'status' => EmailCampaignStatus::Sending->value,
            'sent_at' => null,
        ]);

        $campaignId = $campaign->getKey();

        $jobs = array_map(
            static fn (string $id): SendCampaignEmailJob => new SendCampaignEmailJob($id),
            $recipientIds,
        );

        Bus::batch($jobs)
            ->name("email-campaign:{$campaignId}")
            ->allowFailures()
            ->finally(static function () use ($campaignId): void {
                EmailCampaign::query()->whereKey($campaignId)->update([
                    'status' => EmailCampaignStatus::Sent->value,
                    'sent_at' => now(),
                ]);
            })
            ->dispatch();

        return $campaign->refresh();
    }
}
