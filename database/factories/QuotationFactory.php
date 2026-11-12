<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\QuotationStatus;
use App\Models\Company;
use App\Models\Quotation;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Str;

/**
 * @extends Factory<Quotation>
 */
final class QuotationFactory extends Factory
{
    protected $model = Quotation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => 'Q-'.strtoupper(substr((string) Str::ulid(), 0, 8)),
            'company_id' => Company::factory(),
            'status' => QuotationStatus::Draft->value,
            'fornitura_items' => [],
            'installazione_amount' => $this->faker->randomFloat(2, 0, 100000),
            'trasferta_amount' => $this->faker->randomFloat(2, 0, 100000),
            'trasporto_amount' => $this->faker->randomFloat(2, 0, 100000),
            'team_id' => Team::factory(),
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
            ]);
        }

        return $factory;
    }
}
