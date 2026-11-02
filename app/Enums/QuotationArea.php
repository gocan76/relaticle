<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum QuotationArea: string implements HasLabel
{
    case Fornitura = 'fornitura';
    case Produzione = 'produzione';
    case Installazione = 'installazione';
    case Trasferta = 'trasferta';
    case Trasporto = 'trasporto';

    /**
     * The amount column storing this area's value on the quotations table.
     */
    public function column(): string
    {
        return $this->value.'_amount';
    }

    public function getLabel(): string
    {
        return __('filament/resources/quotation.areas.'.$this->value);
    }
}
