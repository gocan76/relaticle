<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EmailCampaignRecipientStatus;
use App\Models\Concerns\HasTeam;
use Database\Factories\EmailCampaignRecipientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property EmailCampaignRecipientStatus $status
 * @property string|null $recipient_email
 * @property string|null $recipient_name
 * @property string|null $error
 * @property Carbon|null $sent_at
 * @property-read EmailCampaign $campaign
 * @property-read Company|null $company
 */
#[Fillable([
    'team_id',
    'campaign_id',
    'company_id',
    'recipient_email',
    'recipient_name',
    'status',
    'error',
    'sent_at',
])]
final class EmailCampaignRecipient extends Model
{
    /** @use HasFactory<EmailCampaignRecipientFactory> */
    use HasFactory;

    use HasTeam;
    use HasUlids;

    protected function casts(): array
    {
        return [
            'status' => EmailCampaignRecipientStatus::class,
            'sent_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<EmailCampaign, $this>
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
