<?php

declare(strict_types=1);

namespace App\Support;

final readonly class EmailCampaignCriteria
{
    /**
     * Normalise the flat campaign form fields into the filter shape that
     * ListCompanies expects (the same shape persisted in filter_criteria).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function fromForm(array $data): array
    {
        $customFields = [];

        if (filled($data['sector'])) {
            $customFields['sector'] = ['eq' => (string) $data['sector']];
        }

        $employees = [];
        if (self::filled($data['employees_min'] ?? null)) {
            $employees['gte'] = (int) $data['employees_min'];
        }
        if (self::filled($data['employees_max'] ?? null)) {
            $employees['lte'] = (int) $data['employees_max'];
        }
        if ($employees !== []) {
            $customFields['employees'] = $employees;
        }

        $revenue = [];
        if (self::filled($data['revenue_min'] ?? null)) {
            $revenue['gte'] = (int) $data['revenue_min'];
        }
        if (self::filled($data['revenue_max'] ?? null)) {
            $revenue['lte'] = (int) $data['revenue_max'];
        }
        if ($revenue !== []) {
            $customFields['revenue'] = $revenue;
        }

        return [
            'name' => self::filled($data['search'] ?? null) ? (string) $data['search'] : null,
            'created_after' => self::filled($data['created_after'] ?? null) ? $data['created_after'] : null,
            'created_before' => self::filled($data['created_before'] ?? null) ? $data['created_before'] : null,
            'custom_fields' => $customFields !== [] ? $customFields : null,
        ];
    }

    /**
     * Map stored criteria back to the flat form fields, so the segment can be
     * round-tripped when editing a campaign.
     *
     * @param  array<string, mixed>|null  $criteria
     * @return array<string, mixed>
     */
    public static function toForm(?array $criteria): array
    {
        if ($criteria === null) {
            return [];
        }

        $customFields = $criteria['custom_fields'] ?? [];

        return [
            'search' => $criteria['name'] ?? null,
            'sector' => $customFields['sector']['eq'] ?? null,
            'employees_min' => $customFields['employees']['gte'] ?? null,
            'employees_max' => $customFields['employees']['lte'] ?? null,
            'revenue_min' => $customFields['revenue']['gte'] ?? null,
            'revenue_max' => $customFields['revenue']['lte'] ?? null,
            'created_after' => $criteria['created_after'] ?? null,
            'created_before' => $criteria['created_before'] ?? null,
        ];
    }

    private static function filled(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }
}
