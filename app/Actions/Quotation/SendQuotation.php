<?php

declare(strict_types=1);

namespace App\Actions\Quotation;

use App\Enums\QuotationStatus;
use App\Jobs\SendQuotationEmailJob;
use App\Models\Quotation;
use App\Models\User;
use App\Support\CompanyEmailResolver;

final readonly class SendQuotation
{
    public function execute(User $user, Quotation $quotation): Quotation
    {
        abort_unless($user->can('send', $quotation), 403);

        if ($quotation->status !== QuotationStatus::Draft) {
            return $quotation;
        }

        $quotation->loadMissing('company.customFieldValues.customField');

        $email = $quotation->company === null
            ? null
            : CompanyEmailResolver::resolve($quotation->company);

        abort_if(! is_string($email) || $email === '', 422, __('filament/resources/quotation.actions.send.no_email'));

        $quotation->update([
            'status' => QuotationStatus::Sent,
            'sent_at' => now(),
        ]);

        SendQuotationEmailJob::dispatch($quotation->getKey(), $email);

        return $quotation->refresh();
    }
}
