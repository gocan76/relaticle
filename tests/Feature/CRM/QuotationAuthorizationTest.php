<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Quotation;
use App\Models\User;
use App\Policies\QuotationPolicy;

mutates(QuotationPolicy::class, User::class);

it('allows team members to view, update and delete quotations', function (): void {
    $owner = User::factory()->withTeam()->create();
    $team = $owner->currentTeam;

    $company = Company::factory()->create(['team_id' => $team->getKey()]);
    $quotation = Quotation::factory()->create([
        'team_id' => $team->getKey(),
        'company_id' => $company->getKey(),
    ]);

    $actor = User::query()->findOrFail($owner->getKey());

    expect($actor->can('view', $quotation))->toBeTrue()
        ->and($actor->can('update', $quotation))->toBeTrue()
        ->and($actor->can('delete', $quotation))->toBeTrue();
});

it('denies a quotation belonging to another team', function (): void {
    $owner = User::factory()->withTeam()->create();
    $other = User::factory()->withTeam()->create();

    $company = Company::factory()->create(['team_id' => $other->currentTeam->getKey()]);
    $foreign = Quotation::factory()->create([
        'team_id' => $other->currentTeam->getKey(),
        'company_id' => $company->getKey(),
    ]);

    $actor = User::query()->findOrFail($owner->getKey());

    expect($actor->can('view', $foreign))->toBeFalse()
        ->and($actor->can('update', $foreign))->toBeFalse()
        ->and($actor->can('delete', $foreign))->toBeFalse();
});

it('requires a current team to list or create quotations', function (): void {
    $user = User::factory()->create();

    expect($user->can('viewAny', Quotation::class))->toBeFalse()
        ->and($user->can('create', Quotation::class))->toBeFalse();
});
