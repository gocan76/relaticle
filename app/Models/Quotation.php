<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuotationItemUnit;
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
 * @property Carbon|null $sent_at
 * @property array<int, array{description: string, unit: string|null, quantity: string, unit_price: float}>|null $fornitura_items
 * @property-read float $fornitura_amount
 * @property-read float $subtotal_amount
 * @property-read float $iva_amount
 * @property-read float $total_amount
 */
#[Fillable([
    'number',
    'company_id',
    'status',
    'issued_at',
    'valid_until',
    'notes',
    'sent_at',
    'fornitura_items',
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

    public const float IVA_RATE = 0.22;

    protected function casts(): array
    {
        return [
            'status' => QuotationStatus::class,
            'issued_at' => 'date',
            'valid_until' => 'date',
            'sent_at' => 'datetime',
            'fornitura_items' => 'array',
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

    public function getFornituraAmountAttribute(): float
    {
        return round(array_sum(array_map(
            fn (array $item): float => $this->parseDecimal($item['quantity'] ?? null)
                * $this->parseDecimal($item['unit_price'] ?? null),
            $this->fornitura_items ?? [],
        )), 2);
    }

    public function getSubtotalAmountAttribute(): float
    {
        return round(
            $this->fornitura_amount
            + (float) $this->installazione_amount
            + (float) $this->trasferta_amount
            + (float) $this->trasporto_amount,
            2,
        );
    }

    public function getIvaAmountAttribute(): float
    {
        return round($this->subtotal_amount * self::IVA_RATE, 2);
    }

    public function getTotalAmountAttribute(): float
    {
        return round($this->subtotal_amount + $this->iva_amount, 2);
    }

    /**
     * @return array<int, array{description: string, unit: string|null, unit_label: string|null, quantity: string, unit_price: float, line_total: float}>
     */
    public function getFornituraItemsWithTotalsAttribute(): array
    {
        return array_map(function (array $item): array {
            $unit = $item['unit'] ?? null;
            $unitPrice = $this->parseDecimal($item['unit_price'] ?? null);

            return [
                'description' => (string) ($item['description'] ?? ''),
                'unit' => $unit,
                'unit_label' => QuotationItemUnit::tryFrom((string) $unit)?->getLabel() ?? $unit,
                'quantity' => (string) ($item['quantity'] ?? ''),
                'unit_price' => $unitPrice,
                'line_total' => round($this->parseDecimal($item['quantity'] ?? null) * $unitPrice, 2),
            ];
        }, $this->fornitura_items ?? []);
    }

    private function parseDecimal(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $normalized = str_replace(',', '.', (string) $value);

        return is_numeric($normalized) ? (float) $normalized : 0.0;
    }
}
