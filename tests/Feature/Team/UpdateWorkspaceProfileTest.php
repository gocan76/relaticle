<?php

declare(strict_types=1);

use App\Actions\Team\UpdateWorkspaceProfile;
use App\Enums\QuotationArea;
use App\Models\Company;
use App\Models\Quotation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

beforeEach(function (): void {
    $this->user = User::factory()->withTeam()->create();
    $this->team = $this->user->currentTeam;

    $this->actingAs($this->user);
});

it('updates the workspace business profile', function (): void {
    $data = [
        'partita_iva' => '12345678901',
        'rea' => 'MI-1234567',
        'address' => 'Via Roma 1',
        'city' => 'Milano',
        'postal_code' => '20100',
        'province' => 'MI',
        'country' => 'IT',
        'phone' => '02 1234567',
        'email' => 'info@example.test',
        'pec' => 'pec@example.test',
        'logo' => 'team-logos/logo.png',
        'bank_details' => 'UNICREDIT IT 57 U 02008 33680 000107478057',
    ];

    $team = resolve(UpdateWorkspaceProfile::class)->execute($this->user, $this->team, $data);

    $team->refresh();

    expect($team->partita_iva)->toBe('12345678901')
        ->and($team->rea)->toBe('MI-1234567')
        ->and($team->address)->toBe('Via Roma 1')
        ->and($team->city)->toBe('Milano')
        ->and($team->postal_code)->toBe('20100')
        ->and($team->province)->toBe('MI')
        ->and($team->country)->toBe('IT')
        ->and($team->phone)->toBe('02 1234567')
        ->and($team->email)->toBe('info@example.test')
        ->and($team->pec)->toBe('pec@example.test')
        ->and($team->logo)->toBe('team-logos/logo.png')
        ->and($team->bank_details)->toBe('UNICREDIT IT 57 U 02008 33680 000107478057');
});

it('denies updating another team', function (): void {
    $otherTeam = Team::factory()->create();

    expect(fn () => resolve(UpdateWorkspaceProfile::class)->execute($this->user, $otherTeam, ['city' => 'Roma']))
        ->toThrow(AuthorizationException::class);
});

it('renders the workspace business info in the quotation pdf', function (): void {
    $this->team->update([
        'name' => 'Casaviva SRL',
        'partita_iva' => '12345678901',
        'rea' => 'MI-1234567',
        'address' => 'Via Roma 1',
        'city' => 'Milano',
        'postal_code' => '20100',
        'province' => 'MI',
        'phone' => '02 1234567',
        'email' => 'info@casaviva.test',
        'pec' => 'pec@casaviva.test',
        'bank_details' => 'UNICREDIT IT 57 U 02008 33680 000107478057',
    ]);

    $company = Company::factory()->create([
        'team_id' => $this->team->getKey(),
        'name' => 'Cliente Demo SRL',
        'partita_iva' => '00209560994',
        'address' => 'Via per Santa Vittoria 5',
        'city' => 'Sestri Levante',
        'postal_code' => '16039',
        'province' => 'GE',
        'country' => 'Italia',
    ]);

    $quotation = Quotation::factory()->create([
        'team_id' => $this->team->getKey(),
        'company_id' => $company->getKey(),
        'number' => 'Q-0001',
        'payment_name' => Quotation::PAYMENT_METHOD_DEPOSIT_50,
        'fornitura_items' => [
            ['description' => 'Pannelli', 'unit' => 'pz', 'quantity' => '10', 'unit_price' => 20],
        ],
        'installazione_amount' => 0,
        'trasferta_amount' => 0,
        'trasporto_amount' => 0,
    ]);

    $html = view('pdf.quotation', [
        'quotation' => $quotation->loadMissing(['company', 'team']),
        'areas' => QuotationArea::cases(),
    ])->render();

    expect($html)->toContain('Preventivo Q-0001')
        ->toContain('Decorrenza:')
        ->toContain("Validità dell'offerta 30 gg")
        ->toContain('Casaviva SRL')
        ->toContain('Via Roma 1 Milano 20100 (MI)')
        ->toContain('Email: info@casaviva.test')
        ->toContain('PEC: pec@casaviva.test')
        ->toContain('Tel. 02 1234567')
        ->toContain('C.F./P.Iva 12345678901')
        ->toContain('REA MI-1234567')
        ->toContain('Destinatario')
        ->toContain('Cliente Demo SRL')
        ->toContain('Via per Santa Vittoria 5')
        ->toContain('16039 Sestri Levante (GE)')
        ->toContain('Italia')
        ->toContain('P.Iva 00209560994')
        ->toContain('Modalità di pagamento:')
        ->toContain('Acconto 50% alla conferma - saldo alla consegna B.B.')
        ->toContain('Acconto (50%):')
        ->toContain('122.00')
        ->toContain('Coordinate bancarie:')
        ->toContain('UNICREDIT IT 57 U 02008 33680 000107478057');
});
