<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EmailCampaignRecipientStatus;
use App\Models\Company;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Str;

/**
 * @extends Factory<EmailCampaignRecipient>
 */
final class EmailCampaignRecipientFactory extends Factory
{
    protected $model = EmailCampaignRecipient::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'campaign_id' => EmailCampaign::factory(),
            'company_id' => Company::factory(),
            'recipient_email' => $this->faker->unique()->safeEmail(),
            'recipient_name' => $this->faker->name(),
            'status' => EmailCampaignRecipientStatus::Pending->value,
        ];
    }

    /** @phpstan-return static */
    public function configure(): static
    {
        $factory = $this->sequence(fn (Sequence $sequence): array => [
            'created_at' => now()->subMinutes($sequence->index),
            'updated_at' => now()->subMinutes($sequence->index),
        ]);

        if (config('scribe.generating')) {
            return $factory->state([
                'team_id' => (string) Str::ulid(),
                'campaign_id' => (string) Str::ulid(),
                'company_id' => (string) Str::ulid(),
            ]);
        }

        return $factory;
    }
}
