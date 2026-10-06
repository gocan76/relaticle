<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Actions\EmailCampaign\SendEmailCampaign;
use App\Actions\EmailCampaign\UpdateEmailCampaign;
use App\Enums\EmailCampaignRecipientStatus;
use App\Enums\EmailCampaignStatus;
use App\Filament\Resources\EmailCampaignResource\Pages\ListEmailCampaigns;
use App\Filament\Resources\EmailCampaignResource\Pages\ViewEmailCampaign;
use App\Models\CustomField;
use App\Models\EmailCampaign;
use App\Models\User;
use App\Support\EmailCampaignCriteria;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class EmailCampaignResource extends Resource
{
    protected static ?string $model = EmailCampaign::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('filament/resources/email_campaign.sections.details.title'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('filament/resources/email_campaign.fields.name.label'))
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                ]),

            Section::make(__('filament/resources/email_campaign.sections.segment.title'))
                ->description(__('filament/resources/email_campaign.sections.segment.description'))
                ->schema([
                    TextInput::make('search')
                        ->label(__('filament/resources/email_campaign.fields.search.label'))
                        ->columnSpanFull(),
                    Select::make('sector')
                        ->label(__('filament/resources/email_campaign.fields.sector.label'))
                        ->options(fn (): array => self::sectorOptions())
                        ->searchable()
                        ->columnSpanFull(),
                    Grid::make()
                        ->columns(2)
                        ->schema([
                            TextInput::make('employees_min')
                                ->label(__('filament/resources/email_campaign.fields.employees_min.label'))
                                ->numeric(),
                            TextInput::make('employees_max')
                                ->label(__('filament/resources/email_campaign.fields.employees_max.label'))
                                ->numeric(),
                            TextInput::make('revenue_min')
                                ->label(__('filament/resources/email_campaign.fields.revenue_min.label'))
                                ->numeric(),
                            TextInput::make('revenue_max')
                                ->label(__('filament/resources/email_campaign.fields.revenue_max.label'))
                                ->numeric(),
                            DatePicker::make('created_after')
                                ->label(__('filament/resources/email_campaign.fields.created_after.label')),
                            DatePicker::make('created_before')
                                ->label(__('filament/resources/email_campaign.fields.created_before.label')),
                        ]),
                ]),

            Section::make(__('filament/resources/email_campaign.sections.message.title'))
                ->schema([
                    TextInput::make('subject')
                        ->label(__('filament/resources/email_campaign.fields.subject.label'))
                        ->maxLength(255)
                        ->columnSpanFull(),
                    RichEditor::make('body')
                        ->label(__('filament/resources/email_campaign.fields.body.label'))
                        ->columnSpanFull(),
                    FileUpload::make('attachment_path')
                        ->label(__('filament/resources/email_campaign.fields.attachment.label'))
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(10240)
                        ->disk('local')
                        ->directory('email-campaigns')
                        ->columnSpanFull(),
                ]),

            Section::make(__('filament/resources/email_campaign.sections.sending.title'))
                ->schema([
                    Grid::make()
                        ->columns(2)
                        ->schema([
                            TextInput::make('from_email')
                                ->label(__('filament/resources/email_campaign.fields.from_email.label'))
                                ->email()
                                ->default(fn (): string => (string) config('mail.from.address')),
                            DateTimePicker::make('scheduled_at')
                                ->label(__('filament/resources/email_campaign.fields.scheduled_at.label'))
                                ->helperText(__('filament/resources/email_campaign.fields.scheduled_at.helper')),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament/resources/email_campaign.fields.name.label'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('filament/resources/email_campaign.fields.status.label'))
                    ->badge()
                    ->color(fn (EmailCampaignStatus $state): string => $state->getColor()),
                TextColumn::make('recipients_with_email_count')
                    ->label(__('filament/resources/email_campaign.fields.recipients.label'))
                    ->sortable(),
                TextColumn::make('recipients_sent_count')
                    ->label(__('filament/resources/email_campaign.fields.sent.label')),
                TextColumn::make('recipients_error_count')
                    ->label(__('filament/resources/email_campaign.fields.errors.label')),
                TextColumn::make('scheduled_at')
                    ->label(__('filament/resources/email_campaign.fields.scheduled_at.label'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('filament/resources/email_campaign.fields.created_at.label'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament/resources/email_campaign.fields.status.label'))
                    ->options(EmailCampaignStatus::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    self::editAction(),
                    self::sendAction(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function editAction(): EditAction
    {
        return EditAction::make()
            ->mutateRecordDataUsing(function (array $data): array {
                $criteria = is_array($data['filter_criteria'] ?? null) ? $data['filter_criteria'] : null;

                return array_merge($data, EmailCampaignCriteria::toForm($criteria));
            })
            ->using(function (EmailCampaign $record, array $data): EmailCampaign {
                /** @var User $user */
                $user = auth()->user();

                return resolve(UpdateEmailCampaign::class)->execute($user, $record, $data);
            });
    }

    public static function sendAction(): Action
    {
        return Action::make('send')
            ->label(__('filament/resources/email_campaign.actions.send.label'))
            ->icon('heroicon-o-paper-airplane')
            ->color('primary')
            ->visible(fn (EmailCampaign $record): bool => in_array($record->status, [EmailCampaignStatus::Draft, EmailCampaignStatus::Scheduled], true))
            ->requiresConfirmation()
            ->modalHeading(__('filament/resources/email_campaign.actions.send.confirm_heading'))
            ->modalDescription(fn (EmailCampaign $record): string => __('filament/resources/email_campaign.actions.send.confirm_description', [
                'count' => $record->recipients()
                    ->whereNotNull('recipient_email')
                    ->where('recipient_email', '!=', '')
                    ->count(),
            ]))
            ->successNotificationTitle(__('filament/resources/email_campaign.actions.send.success'))
            ->action(function (EmailCampaign $record): void {
                /** @var User $user */
                $user = auth()->user();

                resolve(SendEmailCampaign::class)->execute($user, $record);
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailCampaigns::route('/'),
            'view' => ViewEmailCampaign::route('/{record}'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('filament/resources/email_campaign.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/resources/email_campaign.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/resources/email_campaign.navigation_label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount([
                'recipients as recipients_with_email_count' => fn (Builder $query) => $query
                    ->whereNotNull('recipient_email')
                    ->where('recipient_email', '!=', ''),
                'recipients as recipients_sent_count' => fn (Builder $query) => $query
                    ->where('status', EmailCampaignRecipientStatus::Sent->value),
                'recipients as recipients_error_count' => fn (Builder $query) => $query
                    ->where('status', EmailCampaignRecipientStatus::Error->value),
            ]);
    }

    /**
     * @return array<string, string>
     */
    private static function sectorOptions(): array
    {
        $field = CustomField::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', Filament::getTenant()->getKey())
            ->where('entity_type', 'company')
            ->where('code', 'sector')
            ->active()
            ->first();

        return $field?->options->pluck('name', 'id')->all() ?? [];
    }
}
