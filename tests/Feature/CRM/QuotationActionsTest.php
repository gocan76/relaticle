<?php

declare(strict_types=1);

use App\Actions\Quotation\StoreQuotation;
use App\Actions\Quotation\UpdateQuotation;
use App\Enums\QuotationStatus;
use App\Models\Company;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Validation\ValidationException;

mutates(Quotation::class);

test('store quotation generates a number and assigns the team', function (): void {
    $user = User::factory()->withTeam()->create();
    $company = Company::factory()->create(['team_id' => $user->currentTeam->getKey()]);

    $quotation = resolve(StoreQuotation::class)->execute($user, [
        'company_id' => $company->getKey(),
        'status' => QuotationStatus::Draft,
        'fornitura_items' => [
            ['description' => 'Pannelli', 'unit' => 'm2', 'quantity' => '10', 'unit_price' => 20],
            ['description' => 'Guide', 'unit' => 'm', 'quantity' => '5', 'unit_price' => 10],
        ],
        'installazione_amount' => 300,
        'trasferta_amount' => 0,
        'trasporto_amount' => 0,
    ]);

    expect($quotation->team_id)->toBe($user->currentTeam->getKey())
        ->and($quotation->number)->toStartWith('Q-')
        ->and($quotation->fornitura_amount)->toBe(250.0)
        ->and($quotation->subtotal_amount)->toBe(550.0)
        ->and($quotation->iva_amount)->toBe(121.0)
        ->and($quotation->total_amount)->toBe(671.0);
});

test('store quotation rejects a company from another team', function (): void {
    $user = User::factory()->withTeam()->create();
    $foreignCompany = Company::factory()->create();

    resolve(StoreQuotation::class)->execute($user, [
        'company_id' => $foreignCompany->getKey(),
    ]);
})->throws(ValidationException::class);

test('update quotation changes items and status', function (): void {
    $user = User::factory()->withTeam()->create();
    $company = Company::factory()->create(['team_id' => $user->currentTeam->getKey()]);
    $quotation = Quotation::factory()->create([
        'team_id' => $user->currentTeam->getKey(),
        'company_id' => $company->getKey(),
    ]);

    $updated = resolve(UpdateQuotation::class)->execute($user, $quotation, [
        'company_id' => $company->getKey(),
        'status' => QuotationStatus::Sent,
        'fornitura_items' => [
            ['description' => 'Viti', 'unit' => 'm', 'quantity' => '100', 'unit_price' => 0.5],
        ],
    ]);

    expect($updated->status)->toBe(QuotationStatus::Sent)
        ->and($updated->fornitura_amount)->toBe(50.0);
});
