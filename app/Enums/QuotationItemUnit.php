<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum QuotationItemUnit: string implements HasLabel
{
    case Linear = 'm';
    case Square = 'm2';

    public function getLabel(): string
    {
        return match ($this) {
            self::Linear => 'm',
            self::Square => 'm²',
        };
    }
}
