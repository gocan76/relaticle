<?php

declare(strict_types=1);

namespace App\Filament\Resources\EmailCampaignResource\Pages;

use App\Actions\EmailCampaign\StoreEmailCampaign;
use App\Filament\Resources\EmailCampaignResource;
use App\Models\EmailCampaign;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Size;

final class ListEmailCampaigns extends ListRecords
{
    /** @var class-string<EmailCampaignResource> */
    protected static string $resource = EmailCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-o-plus')
                ->size(Size::Small)
                ->using(function (array $data): EmailCampaign {
                    /** @var User $user */
                    $user = auth()->user();

                    return resolve(StoreEmailCampaign::class)->execute($user, $data);
                }),
        ];
    }
}
