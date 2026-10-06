<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\EmailCampaign\SendEmailCampaign;
use App\Enums\EmailCampaignStatus;
use App\Models\EmailCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ProcessScheduledCampaigns implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function handle(): void
    {
        $campaigns = EmailCampaign::query()
            ->where('status', EmailCampaignStatus::Scheduled->value)
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($campaigns as $campaign) {
            (new SendEmailCampaign)->execute(null, $campaign);
        }
    }
}
