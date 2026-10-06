<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EmailCampaignStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Sending = 'sending';
    case Sent = 'sent';

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Scheduled => 'warning',
            self::Sending => 'info',
            self::Sent => 'success',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Scheduled => 'Scheduled',
            self::Sending => 'Sending',
            self::Sent => 'Sent',
        };
    }
}
