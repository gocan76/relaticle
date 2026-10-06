<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EmailCampaignStatus;
use App\Models\Concerns\HasTeam;
use Database\Factories\EmailCampaignFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property EmailCampaignStatus $status
 * @property string|null $subject
 * @property string|null $body
 * @property string|null $attachment_path
 * @property string|null $from_email
 * @property array<string, mixed>|null $filter_criteria
 * @property Carbon|null $scheduled_at
 * @property Carbon|null $sent_at
 * @property-read Collection<int, EmailCampaignRecipient> $recipients
 */
#[Fillable([
    'name',
    'status',
    'subject',
    'body',
    'attachment_path',
    'from_email',
    'filter_criteria',
    'scheduled_at',
    'sent_at',
    'team_id',
])]
final class EmailCampaign extends Model
{
    /** @use HasFactory<EmailCampaignFactory> */
    use HasFactory;

    use HasTeam;
    use HasUlids;

    protected function casts(): array
    {
        return [
            'status' => EmailCampaignStatus::class,
            'filter_criteria' => 'array',
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<EmailCampaignRecipient, $this>
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'campaign_id');
    }
}
