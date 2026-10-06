<?php

declare(strict_types=1);

namespace App\Filament\Resources\EmailCampaignResource\RelationManagers;

use App\Enums\EmailCampaignRecipientStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class RecipientsRelationManager extends RelationManager
{
    protected static string $relationship = 'recipients';

    protected static ?string $modelLabel = null;

    public static function getModelLabel(): string
    {
        return __('filament/resources/email_campaign.relation_managers.recipients.model_label');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('recipient_name')
            ->columns([
                TextColumn::make('recipient_name')
                    ->label(__('filament/resources/email_campaign.fields.recipient_name.label'))
                    ->searchable(),
                TextColumn::make('recipient_email')
                    ->label(__('filament/resources/email_campaign.fields.recipient_email.label'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('filament/resources/email_campaign.fields.recipient_status.label'))
                    ->badge()
                    ->color(fn (EmailCampaignRecipientStatus $state): string => $state->getColor()),
                TextColumn::make('sent_at')
                    ->label(__('filament/resources/email_campaign.fields.sent_at.label'))
                    ->dateTime()
                    ->toggleable(),
                TextColumn::make('error')
                    ->label(__('filament/resources/email_campaign.fields.error.label'))
                    ->limit(60)
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'asc');
    }
}
