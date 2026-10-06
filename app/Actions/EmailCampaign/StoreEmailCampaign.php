<?php

declare(strict_types=1);

namespace App\Actions\EmailCampaign;

use App\Enums\EmailCampaignStatus;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\User;
use App\Support\EmailCampaignCriteria;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final readonly class StoreEmailCampaign
{
    public function __construct(private ResolveCampaignRecipients $resolver) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data): EmailCampaign
    {
        abort_unless($user->can('create', EmailCampaign::class), 403);

        $attributes = Arr::only($data, [
            'name', 'subject', 'body', 'attachment_path', 'from_email', 'scheduled_at',
        ]);
        $attributes['team_id'] = $user->currentTeam->getKey();
        $attributes['filter_criteria'] = EmailCampaignCriteria::fromForm($data);
        $attributes['status'] = $this->initialStatus($attributes['scheduled_at'] ?? null);

        $criteria = $attributes['filter_criteria'];

        $campaign = DB::transaction(function () use ($user, $attributes, $criteria): EmailCampaign {
            $campaign = EmailCampaign::query()->create($attributes);

            $this->snapshotRecipients($user, $campaign, $criteria);

            return $campaign;
        });

        return $campaign->load('recipients');
    }

    /**
     * @param  array<string, mixed>  $criteria
     */
    private function snapshotRecipients(User $user, EmailCampaign $campaign, array $criteria): void
    {
        $teamId = $user->currentTeam->getKey();

        foreach ($this->resolver->execute($user, $criteria) as $recipient) {
            EmailCampaignRecipient::query()->create([
                'team_id' => $teamId,
                'campaign_id' => $campaign->getKey(),
                'company_id' => $recipient['company']->getKey(),
                'recipient_name' => $recipient['company']->name,
                'recipient_email' => $recipient['email'],
            ]);
        }
    }

    private function initialStatus(mixed $scheduledAt): EmailCampaignStatus
    {
        return $scheduledAt !== null && $scheduledAt !== '' ? EmailCampaignStatus::Scheduled : EmailCampaignStatus::Draft;
    }
}
