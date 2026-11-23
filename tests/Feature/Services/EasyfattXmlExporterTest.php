<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Quotation;
use App\Models\User;
use App\Services\EasyfattXmlExporter;

beforeEach(function (): void {
    $this->user = User::factory()->withTeam()->create();
    $this->team = $this->user->currentTeam;
});

it('generates an Easyfatt preventivo document', function (): void {
    $company = Company::factory()->for($this->team)->create([
        'name' => 'Acme Srl',
        'partita_iva' => 'IT12345678901',
        'address' => 'Via Roma 1',
        'city' => 'Milano',
        'postal_code' => '20100',
        'province' => 'MI',
        'country' => 'Italia',
    ]);

    $quotation = Quotation::factory()->for($company)->for($this->team)->create([
        'number' => 'Q-0001',
        'issued_at' => '2026-11-23',
        'payment_name' => 'Bonifico bancario',
        'fornitura_items' => [
            ['description' => 'Cavo elettrico', 'unit' => 'm', 'quantity' => '10', 'unit_price' => 2.5],
        ],
        'installazione_amount' => 100,
        'trasferta_amount' => 0,
        'trasporto_amount' => 0,
    ]);

    $xml = resolve(EasyfattXmlExporter::class)->generate(collect([$quotation]));
    $document = simplexml_load_string($xml)->Documents->Document;

    expect($document)->toBeInstanceOf(SimpleXMLElement::class)
        ->and((string) $document->DocumentType)->toBe('Q')
        ->and((string) $document->Date)->toBe('2026-11-23')
        ->and((string) $document->CustomerName)->toBe('Acme Srl')
        ->and((string) $document->CustomerVatCode)->toBe('IT12345678901')
        ->and((string) $document->CustomerAddress)->toBe('Via Roma 1')
        ->and((string) $document->CustomerCity)->toBe('Milano')
        ->and((string) $document->CustomerPostcode)->toBe('20100')
        ->and((string) $document->CustomerProvince)->toBe('MI')
        ->and((string) $document->CustomerCountry)->toBe('Italia')
        ->and((string) $document->PaymentName)->toBe('Bonifico bancario');

    $rows = $document->Rows->Row;

    expect((string) $rows[0]->Description)->toBe('Cavo elettrico')
        ->and((string) $rows[0]->Qty)->toBe('10')
        ->and((string) $rows[0]->Um)->toBe('m')
        ->and((string) $rows[0]->Price)->toBe('2.50')
        ->and((string) $rows[0]->VatCode)->toBe('22')
        ->and((string) $rows[0]->VatCode['Perc'])->toBe('22')
        ->and((string) $rows[1]->Description)->toBe('Installazione')
        ->and((string) $rows[1]->Qty)->toBe('1')
        ->and((string) $rows[1]->Price)->toBe('100.00');
});

it('preserves a non-numeric number as a custom field', function (): void {
    $company = Company::factory()->for($this->team)->create([
        'name' => 'Acme Srl',
        'partita_iva' => 'IT12345678901',
    ]);

    $quotation = Quotation::factory()->for($company)->for($this->team)->create([
        'number' => 'Q-ABC123',
        'fornitura_items' => [],
        'installazione_amount' => 0,
        'trasferta_amount' => 0,
        'trasporto_amount' => 0,
    ]);

    $xml = resolve(EasyfattXmlExporter::class)->generate(collect([$quotation]));
    $document = simplexml_load_string($xml)->Documents->Document;

    expect(isset($document->Number))->toBeFalse()
        ->and((string) $document->CustomField1)->toBe('Q-ABC123');
});

it('omits empty customer fields and rows', function (): void {
    $company = Company::factory()->for($this->team)->create([
        'name' => 'Acme Srl',
        'partita_iva' => null,
    ]);

    $quotation = Quotation::factory()->for($company)->for($this->team)->create([
        'fornitura_items' => [],
        'installazione_amount' => 0,
        'trasferta_amount' => 0,
        'trasporto_amount' => 0,
    ]);

    $xml = resolve(EasyfattXmlExporter::class)->generate(collect([$quotation]));
    $document = simplexml_load_string($xml)->Documents->Document;

    expect(isset($document->CustomerAddress))->toBeFalse()
        ->and(isset($document->CustomerVatCode))->toBeFalse()
        ->and(isset($document->PaymentName))->toBeFalse()
        ->and(isset($document->Rows))->toBeFalse();
});
