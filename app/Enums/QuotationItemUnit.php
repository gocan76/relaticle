<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum QuotationItemUnit: string implements HasLabel
{
    case Meter = 'm';

    case SquareMeter = 'm2';

    case Piece = 'pz';

    public function getLabel(): string
    {
        return match ($this) {
            self::Meter => 'm',
            self::SquareMeter => 'm²',
            self::Piece => 'n. pz.',
        };
    }
}
