<?php

declare(strict_types=1);

namespace App\Filament\Resources\EmailCampaignResource\Pages;

use App\Enums\EmailCampaignStatus;
use App\Filament\Resources\EmailCampaignResource;
use App\Filament\Resources\EmailCampaignResource\RelationManagers\RecipientsRelationManager;
use Filament\Actions\DeleteAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ViewEmailCampaign extends ViewRecord
{
    /** @var class-string<EmailCampaignResource> */
    protected static string $resource = EmailCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EmailCampaignResource::editAction()
                ->icon('heroicon-o-pencil-square')
                ->label(__('filament/resources/email_campaign.pages.view.actions.edit.label')),
            EmailCampaignResource::sendAction(),
            DeleteAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make([
                TextEntry::make('name')
                    ->label(__('filament/resources/email_campaign.fields.name.label')),
                TextEntry::make('status')
                    ->label(__('filament/resources/email_campaign.fields.status.label'))
                    ->badge()
                    ->color(fn (EmailCampaignStatus $state): string => $state->getColor()),
                TextEntry::make('subject')
                    ->label(__('filament/resources/email_campaign.fields.subject.label')),
                TextEntry::make('from_email')
                    ->label(__('filament/resources/email_campaign.fields.from_email.label')),
                TextEntry::make('scheduled_at')
                    ->label(__('filament/resources/email_campaign.fields.scheduled_at.label'))
                    ->dateTime(),
                TextEntry::make('sent_at')
                    ->label(__('filament/resources/email_campaign.fields.sent_at.label'))
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->label(__('filament/resources/email_campaign.fields.created_at.label'))
                    ->dateTime(),
            ]),
        ]);
    }

    public function getRelationManagers(): array
    {
        return [
            RecipientsRelationManager::class,
        ];
    }
}
