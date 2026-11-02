<?php

declare(strict_types=1);

namespace App\Actions\Quotation;

use App\Models\Company;
use App\Models\Quotation;
use App\Models\User;
use App\Support\TenantFkValidator;
use Illuminate\Support\Arr;

final readonly class UpdateQuotation
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, Quotation $quotation, array $data): Quotation
    {
        abort_unless($user->can('update', $quotation), 403);

        TenantFkValidator::assertOwned($user, $data, ['company_id' => Company::class]);

        $attributes = Arr::only($data, [
            'company_id', 'status', 'issued_at', 'valid_until', 'notes',
            'fornitura_amount', 'produzione_amount', 'installazione_amount',
            'trasferta_amount', 'trasporto_amount',
        ]);

        $quotation->update($attributes);

        return $quotation;
    }
}
