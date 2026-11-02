<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Actions\Quotation\StoreQuotation;
use App\Filament\Resources\QuotationResource;
use App\Models\Quotation;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Size;

final class ListQuotations extends ListRecords
{
    /** @var class-string<QuotationResource> */
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-o-plus')
                ->size(Size::Small)
                ->using(function (array $data): Quotation {
                    /** @var User $user */
                    $user = auth()->user();

                    return resolve(StoreQuotation::class)->execute($user, $data);
                }),
        ];
    }
}
