<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EmailCampaignRecipientStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Error = 'error';

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Sent => 'success',
            self::Error => 'danger',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Sent => 'Sent',
            self::Error => 'Error',
        };
    }
}
