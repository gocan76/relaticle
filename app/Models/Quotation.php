<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuotationArea;
use App\Enums\QuotationStatus;
use App\Models\Concerns\HasTeam;
use Database\Factories\QuotationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property QuotationStatus $status
 * @property Carbon|null $issued_at
 * @property Carbon|null $valid_until
 * @property-read float $total_amount
 */
#[Fillable([
    'number',
    'company_id',
    'status',
    'issued_at',
    'valid_until',
    'notes',
    'fornitura_amount',
    'produzione_amount',
    'installazione_amount',
    'trasferta_amount',
    'trasporto_amount',
    'team_id',
])]
final class Quotation extends Model
{
    /** @use HasFactory<QuotationFactory> */
    use HasFactory;

    use HasTeam;
    use HasUlids;

    protected function casts(): array
    {
        return [
            'status' => QuotationStatus::class,
            'issued_at' => 'date',
            'valid_until' => 'date',
            'fornitura_amount' => 'decimal:2',
            'produzione_amount' => 'decimal:2',
            'installazione_amount' => 'decimal:2',
            'trasferta_amount' => 'decimal:2',
            'trasporto_amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getTotalAmountAttribute(): float
    {
        return (float) collect(QuotationArea::cases())
            ->sum(fn (QuotationArea $area): float => (float) $this->getAttribute($area->column()));
    }
}
