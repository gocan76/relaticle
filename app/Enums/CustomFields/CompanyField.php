<?php

declare(strict_types=1);

namespace App\Enums\CustomFields;

use App\Enums\CustomFieldType;

enum CompanyField: string
{
    use CustomFieldTrait;

    /**
     * Ideal Customer Profile: Indicates whether the company is the most suitable customer for you
     */
    case ICP = 'icp';

    /**
     * Domains: Website domains of the company (system field)
     */
    case DOMAINS = 'domains';

    /**
     * LinkedIn: The LinkedIn profile URL of the company
     */
    case LINKEDIN = 'linkedin';

    /**
     * Address: Street address of the company
     */
    case ADDRESS = 'address';

    /**
     * Email: Primary contact email of the company
     */
    case EMAIL = 'email';

    /**
     * Phone: Primary phone number of the company
     */
    case PHONE = 'phone';

    /**
     * Sector: Industry sector the company operates in
     */
    case SECTOR = 'sector';

    /**
     * Employees: Number of employees at the company
     */
    case EMPLOYEES = 'employees';

    /**
     * Revenue: Annual revenue of the company
     */
    case REVENUE = 'revenue';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::ICP => 'ICP',
            self::DOMAINS => 'Domains',
            self::LINKEDIN => 'LinkedIn',
            self::ADDRESS => 'Address',
            self::EMAIL => 'Email',
            self::PHONE => 'Phone',
            self::SECTOR => 'Sector',
            self::EMPLOYEES => 'Employees',
            self::REVENUE => 'Revenue',
        };
    }

    public function getFieldType(): string
    {
        return match ($this) {
            self::ICP => CustomFieldType::TOGGLE->value,
            self::DOMAINS, self::LINKEDIN => CustomFieldType::LINK->value,
            self::ADDRESS => CustomFieldType::TEXT->value,
            self::EMAIL => CustomFieldType::EMAIL->value,
            self::PHONE => CustomFieldType::PHONE->value,
            self::SECTOR => CustomFieldType::SELECT->value,
            self::EMPLOYEES => CustomFieldType::NUMBER->value,
            self::REVENUE => CustomFieldType::CURRENCY->value,
        };
    }

    public function isSystemDefined(): bool
    {
        return match ($this) {
            self::DOMAINS => true,
            default => false,
        };
    }

    public function isListToggleableHidden(): bool
    {
        return match ($this) {
            self::ICP, self::DOMAINS, self::EMAIL, self::SECTOR => false,
            default => true,
        };
    }

    /**
     * @return array<int|string, string>|null
     */
    public function getOptions(): ?array
    {
        return match ($this) {
            self::SECTOR => [
                'Technology',
                'Professional Services',
                'Manufacturing',
                'Retail',
                'Healthcare',
                'Financial Services',
                'Real Estate',
                'Education',
                'Marketing & Advertising',
                'Construction',
                'Transportation & Logistics',
                'Nonprofit',
                'Other',
            ],
            default => null,
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::ICP => 'Indicates whether this company is an Ideal Customer Profile',
            self::DOMAINS => 'Website domains of the company (e.g., example.com)',
            self::LINKEDIN => 'URL to the company\'s LinkedIn profile',
            self::ADDRESS => 'Street address of the company',
            self::EMAIL => 'Primary contact email of the company',
            self::PHONE => 'Primary phone number of the company',
            self::SECTOR => 'Industry sector the company operates in',
            self::EMPLOYEES => 'Number of employees at the company',
            self::REVENUE => 'Annual revenue of the company',
        };
    }

    public function allowsMultipleValues(): bool
    {
        return match ($this) {
            self::DOMAINS => true,
            default => false,
        };
    }

    public function isUniquePerEntityType(): bool
    {
        return match ($this) {
            self::DOMAINS => true,
            default => false,
        };
    }
}
