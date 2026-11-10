<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Company;
use App\Models\CustomFieldValue;
use Illuminate\Support\Collection;

final readonly class CompanyEmailResolver
{
    /**
     * Resolve the primary email custom-field value of a company.
     */
    public static function resolve(Company $company): ?string
    {
        $value = $company->customFieldValues
            ->firstWhere(fn (CustomFieldValue $fieldValue): bool => isset($fieldValue->getRelations()['customField'])
                && $fieldValue->customField->code === 'email');

        $email = $value?->getValue();

        // The email field can hold multiple addresses; use the first one.
        if ($email instanceof Collection) {
            $email = $email->first();
        }

        return is_string($email) && $email !== '' ? $email : null;
    }
}
