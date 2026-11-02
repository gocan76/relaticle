<?php

declare(strict_types=1);

namespace App\Actions\Quotation;

use App\Models\Company;
use App\Models\Quotation;
use App\Models\User;
use App\Support\TenantFkValidator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final readonly class StoreQuotation
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data): Quotation
    {
        abort_unless($user->can('create', Quotation::class), 403);

        TenantFkValidator::assertOwned($user, $data, ['company_id' => Company::class]);

        $attributes = Arr::only($data, [
            'company_id', 'status', 'issued_at', 'valid_until', 'notes',
            'fornitura_amount', 'produzione_amount', 'installazione_amount',
            'trasferta_amount', 'trasporto_amount',
        ]);
        $attributes['team_id'] = $user->currentTeam->getKey();
        $attributes['number'] = 'Q-'.strtoupper(substr((string) Str::ulid(), 0, 8));

        return Quotation::query()->create($attributes);
    }
}
