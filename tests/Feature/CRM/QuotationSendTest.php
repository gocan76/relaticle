<?php

declare(strict_types=1);

use App\Actions\Quotation\SendQuotation;
use App\Enums\QuotationStatus;
use App\Jobs\SendQuotationEmailJob;
use App\Mail\QuotationMail;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\Quotation;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function (): void {
    $this->user = User::factory()->withTeam()->create();
    $this->team = $this->user->currentTeam;
    Filament::setTenant($this->team);
});

it('marks the quotation as sent and queues the email', function (): void {
    Queue::fake();

    CustomField::factory()->create([
        'tenant_id' => $this->team->getKey(),
        'entity_type' => 'company',
        'code' => 'email',
        'type' => 'email',
    ]);

    $company = Company::factory()->create(['team_id' => $this->team->getKey()]);
    $company->saveCustomFields(['email' => ['info@acme.test']]);

    $quotation = Quotation::factory()->create([
        'team_id' => $this->team->getKey(),
        'company_id' => $company->getKey(),
        'status' => QuotationStatus::Draft,
    ]);

    $result = resolve(SendQuotation::class)->execute($this->user, $quotation);

    expect($result->status)->toBe(QuotationStatus::Sent)
        ->and($result->sent_at)->not->toBeNull();

    Queue::assertPushed(SendQuotationEmailJob::class);
});

it('aborts when the company has no email', function (): void {
    $company = Company::factory()->create(['team_id' => $this->team->getKey()]);

    $quotation = Quotation::factory()->create([
        'team_id' => $this->team->getKey(),
        'company_id' => $company->getKey(),
        'status' => QuotationStatus::Draft,
    ]);

    resolve(SendQuotation::class)->execute($this->user, $quotation);
})->throws(HttpException::class);

it('sends the quotation email to the resolved recipient', function (): void {
    Mail::fake();

    $company = Company::factory()->create(['team_id' => $this->team->getKey()]);

    $quotation = Quotation::factory()->create([
        'team_id' => $this->team->getKey(),
        'company_id' => $company->getKey(),
    ]);

    (new SendQuotationEmailJob($quotation->getKey(), 'info@acme.test'))->handle();

    Mail::assertSent(QuotationMail::class, function (QuotationMail $mail) use ($quotation): bool {
        return $mail->quotation->is($quotation) && $mail->hasTo('info@acme.test');
    });
});

it('denies sending a quotation from another team', function (): void {
    $other = User::factory()->withTeam()->create();

    $company = Company::factory()->create(['team_id' => $other->currentTeam->getKey()]);

    $foreign = Quotation::factory()->create([
        'team_id' => $other->currentTeam->getKey(),
        'company_id' => $company->getKey(),
    ]);

    expect($this->user->can('send', $foreign))->toBeFalse();
});
