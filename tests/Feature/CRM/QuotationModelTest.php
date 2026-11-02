<?php

declare(strict_types=1);

use App\Enums\QuotationStatus;
use App\Models\Company;
use App\Models\Quotation;
use App\Models\Team;

mutates(Quotation::class);

test('quotation belongs to team', function () {
    $team = Team::factory()->create();
    $quotation = Quotation::factory()->create([
        'team_id' => $team->getKey(),
    ]);

    expect($quotation->team)->toBeInstanceOf(Team::class)
        ->and($quotation->team->getKey())->toBe($team->getKey());
});

test('quotation belongs to company', function () {
    $company = Company::factory()->create();
    $quotation = Quotation::factory()->create([
        'company_id' => $company->getKey(),
    ]);

    expect($quotation->company)->toBeInstanceOf(Company::class)
        ->and($quotation->company->getKey())->toBe($company->getKey());
});

test('quotation total sums all areas', function () {
    $quotation = Quotation::factory()->create([
        'fornitura_amount' => 100,
        'produzione_amount' => 200.5,
        'installazione_amount' => 300,
        'trasferta_amount' => 50.25,
        'trasporto_amount' => 75,
    ]);

    expect($quotation->total_amount)->toBe(725.75);
});

test('quotation status is cast to enum', function () {
    $quotation = Quotation::factory()->create([
        'status' => 'accepted',
    ]);

    expect($quotation->status)->toBe(QuotationStatus::Accepted);
});
