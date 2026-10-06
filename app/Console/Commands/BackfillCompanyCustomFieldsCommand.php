<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\CustomFields\CreateDefaultCustomField;
use App\Enums\CustomFields\CompanyField as CompanyCustomField;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\Team;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Relaticle\CustomFields\Models\Scopes\CustomFieldsActivableScope;
use Relaticle\CustomFields\Services\TenantContextService;

#[Description('Create the default Company custom fields for existing teams')]
#[Signature('custom-fields:backfill-company-fields
                            {--team= : Specific team ID to backfill (optional)}
                            {--dry-run : Show what would be created without making changes}')]
final class BackfillCompanyCustomFieldsCommand extends Command
{
    /** @var list<CompanyCustomField> */
    private const array FIELDS = [
        CompanyCustomField::ADDRESS,
        CompanyCustomField::EMAIL,
        CompanyCustomField::PHONE,
        CompanyCustomField::SECTOR,
        CompanyCustomField::EMPLOYEES,
        CompanyCustomField::REVENUE,
    ];

    public function __construct(private readonly CreateDefaultCustomField $createDefaultCustomField)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $specificTeam = $this->option('team');

        $teams = Team::query();

        if ($specificTeam) {
            $teams->whereKey($specificTeam);
        }

        $created = 0;
        $skipped = 0;
        $previousTenantId = TenantContextService::getCurrentTenantId();

        try {
            foreach ($teams->get() as $team) {
                $teamId = (string) $team->getKey();
                TenantContextService::setTenantId($teamId);

                foreach (self::FIELDS as $enum) {
                    $exists = CustomField::query()
                        ->withoutGlobalScope(CustomFieldsActivableScope::class)
                        ->where('tenant_id', $teamId)
                        ->where('entity_type', Company::class)
                        ->where('code', $enum->value)
                        ->exists();

                    if ($exists) {
                        $skipped++;

                        continue;
                    }

                    if ($dryRun) {
                        $this->line("Would create \"{$enum->value}\" for team {$teamId}");
                        $created++;

                        continue;
                    }

                    DB::transaction(function () use ($teamId, $enum): void {
                        $this->createDefaultCustomField->execute($teamId, Company::class, $enum);
                    });

                    $this->line("Created \"{$enum->value}\" for team {$teamId}");
                    $created++;
                }
            }
        } finally {
            TenantContextService::setTenantId($previousTenantId);
        }

        $verb = $dryRun ? 'would be created' : 'created';
        $this->info("Backfill complete: {$created} field(s) {$verb}, {$skipped} already existed.");

        return self::SUCCESS;
    }
}
