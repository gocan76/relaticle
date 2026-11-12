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

test('fornitura amount multiplies pieces by unit price', function () {
    $quotation = Quotation::factory()->create([
        'fornitura_items' => [
            ['description' => 'Pannelli', 'unit' => 'pz', 'quantity' => '10', 'unit_price' => 20],
            ['description' => 'Guide', 'unit' => 'pz', 'quantity' => '5', 'unit_price' => 10.5],
        ],
    ]);

    expect($quotation->fornitura_amount)->toBe(252.5);
});

test('fornitura amount multiplies meter quantity by price', function () {
    $quotation = Quotation::factory()->create([
        'fornitura_items' => [
            ['description' => 'Posa', 'unit' => 'm', 'quantity' => '3', 'unit_price' => 40],
            ['description' => 'Verniciatura', 'unit' => 'm2', 'quantity' => '2', 'unit_price' => 15.5],
        ],
    ]);

    expect($quotation->fornitura_amount)->toBe(151.0);
});

test('quotation total sums fornitura and areas plus 22% VAT', function () {
    $quotation = Quotation::factory()->create([
        'fornitura_items' => [
            ['description' => 'Pannelli', 'unit' => 'pz', 'quantity' => '10', 'unit_price' => 20],
        ],
        'installazione_amount' => 300,
        'trasferta_amount' => 0,
        'trasporto_amount' => 0,
    ]);

    expect($quotation->subtotal_amount)->toBe(500.0)
        ->and($quotation->iva_amount)->toBe(110.0)
        ->and($quotation->total_amount)->toBe(610.0);
});

test('fornitura items expose line totals', function () {
    $quotation = Quotation::factory()->create([
        'fornitura_items' => [
            ['description' => 'Pannelli', 'unit' => 'pz', 'quantity' => '10', 'unit_price' => 20],
            ['description' => 'Posa', 'unit' => 'm', 'unit_price' => 40],
        ],
    ]);

    expect($quotation->fornitura_items_with_totals)->toBe([
        [
            'description' => 'Pannelli',
            'unit' => 'pz',
            'unit_label' => 'n. pz.',
            'quantity' => '10',
            'unit_price' => 20.0,
            'line_total' => 200.0,
        ],
        [
            'description' => 'Posa',
            'unit' => 'm',
            'unit_label' => 'm',
            'quantity' => '3',
            'unit_price' => 40.0,
            'line_total' => 120.0,
        ],
    ]);
});

test('quotation status is cast to enum', function () {
    $quotation = Quotation::factory()->create([
        'status' => 'accepted',
    ]);

    expect($quotation->status)->toBe(QuotationStatus::Accepted);
});
