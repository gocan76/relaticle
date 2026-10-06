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

final readonly class UpdateEmailCampaign
{
    public function __construct(private ResolveCampaignRecipients $resolver) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, EmailCampaign $campaign, array $data): EmailCampaign
    {
        abort_unless($user->can('update', $campaign), 403);

        $attributes = Arr::only($data, [
            'name', 'subject', 'body', 'attachment_path', 'from_email', 'scheduled_at',
        ]);
        $attributes['filter_criteria'] = EmailCampaignCriteria::fromForm($data);

        $editable = in_array($campaign->status, [EmailCampaignStatus::Draft, EmailCampaignStatus::Scheduled], true);

        $criteria = $attributes['filter_criteria'];

        $campaign = DB::transaction(function () use ($user, $campaign, $attributes, $criteria, $editable): EmailCampaign {
            $campaign->update($attributes);

            if ($editable) {
                $campaign->recipients()->delete();
                $this->snapshotRecipients($user, $campaign, $criteria);
            }

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
}
