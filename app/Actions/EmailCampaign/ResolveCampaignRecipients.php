<?php

declare(strict_types=1);

namespace App\Actions\EmailCampaign;

use App\Actions\Company\ListCompanies;
use App\Models\Company;
use App\Models\CustomFieldValue;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use LogicException;

final readonly class ResolveCampaignRecipients
{
    public const int MAX_RECIPIENTS = 500;

    /**
     * Resolve the companies matching the given criteria and return each one
     * together with its email custom-field value (null when absent).
     *
     * @param  array<string, mixed>  $criteria
     * @return Collection<int, array{company: Company, email: string|null}>
     */
    public function execute(User $user, array $criteria): Collection
    {
        $paginator = (new ListCompanies)->execute(
            user: $user,
            perPage: self::MAX_RECIPIENTS,
            filters: $criteria,
        );

        // ListCompanies only returns a CursorPaginator when useCursor is true,
        // which we never pass, so the result is always a LengthAwarePaginator.
        throw_unless($paginator instanceof LengthAwarePaginator, LogicException::class, 'Campaign recipients require length-aware pagination.');

        /** @var array<int, array{company: Company, email: string|null}> $recipients */
        $recipients = [];

        foreach ($paginator->items() as $company) {
            $company->loadMissing('customFieldValues.customField');

            $recipients[] = [
                'company' => $company,
                'email' => $this->resolveEmail($company),
            ];
        }

        // @phpstan-ignore return.type (declared and inferred types are identical; flagged only because Larastan stubs Collection's TValue as invariant)
        return collect($recipients);
    }

    private function resolveEmail(Company $company): ?string
    {
        $value = $company->customFieldValues
            ->firstWhere(fn (CustomFieldValue $fieldValue): bool => isset($fieldValue->getRelations()['customField'])
                && $fieldValue->customField->code === 'email');

        $email = $value?->getValue();

        // The email field is multi-value, so getValue() returns a Collection of addresses.
        if ($email instanceof Collection) {
            $email = $email->first();
        }

        return is_string($email) && $email !== '' ? $email : null;
    }
}
