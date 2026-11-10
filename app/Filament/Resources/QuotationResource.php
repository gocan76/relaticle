<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Actions\Quotation\SendQuotation;
use App\Actions\Quotation\UpdateQuotation;
use App\Enums\QuotationArea;
use App\Enums\QuotationStatus;
use App\Filament\Resources\QuotationResource\Pages\ListQuotations;
use App\Filament\Resources\QuotationResource\Pages\ViewQuotation;
use App\Models\Quotation;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class QuotationResource extends Resource
{
    protected static ?string $model = Quotation::class;

    protected static ?string $recordTitleAttribute = 'number';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-currency-euro';

    protected static ?int $navigationSort = 21;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('filament/resources/quotation.sections.header.title'))
                ->schema([
                    TextInput::make('number')
                        ->label(__('filament/resources/quotation.fields.number.label'))
                        ->disabled()
                        ->visible(fn (?Quotation $record): bool => $record !== null),
                    Select::make('company_id')
                        ->label(__('filament/resources/quotation.fields.company.label'))
                        ->relationship('company', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('status')
                        ->label(__('filament/resources/quotation.fields.status.label'))
                        ->options(QuotationStatus::class)
                        ->default(QuotationStatus::Draft)
                        ->required(),
                    DatePicker::make('issued_at')
                        ->label(__('filament/resources/quotation.fields.issued_at.label')),
                    DatePicker::make('valid_until')
                        ->label(__('filament/resources/quotation.fields.valid_until.label')),
                    Textarea::make('notes')
                        ->label(__('filament/resources/quotation.fields.notes.label'))
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make(__('filament/resources/quotation.sections.areas.title'))
                ->schema(self::areaFields())
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label(__('filament/resources/quotation.fields.number.label'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label(__('filament/resources/quotation.fields.company.label'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('filament/resources/quotation.fields.status.label'))
                    ->badge()
                    ->color(fn (QuotationStatus $state): string => $state->getColor()),
                TextColumn::make('total_amount')
                    ->label(__('filament/resources/quotation.fields.total.label'))
                    ->numeric(2),
                TextColumn::make('issued_at')
                    ->label(__('filament/resources/quotation.fields.issued_at.label'))
                    ->date()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('filament/resources/quotation.fields.created_at.label'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament/resources/quotation.fields.status.label'))
                    ->options(QuotationStatus::class),
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
            ->using(function (Quotation $record, array $data): Quotation {
                /** @var User $user */
                $user = auth()->user();

                return resolve(UpdateQuotation::class)->execute($user, $record, $data);
            });
    }

    public static function sendAction(): Action
    {
        return Action::make('send')
            ->label(__('filament/resources/quotation.actions.send.label'))
            ->icon('heroicon-o-paper-airplane')
            ->color('primary')
            ->visible(fn (Quotation $record): bool => $record->status === QuotationStatus::Draft)
            ->requiresConfirmation()
            ->modalHeading(__('filament/resources/quotation.actions.send.confirm_heading'))
            ->modalDescription(fn (Quotation $record): string => __('filament/resources/quotation.actions.send.confirm_description', [
                'company' => $record->company?->name ?? '',
            ]))
            ->successNotificationTitle(__('filament/resources/quotation.actions.send.success'))
            ->action(function (Quotation $record): void {
                /** @var User $user */
                $user = auth()->user();

                resolve(SendQuotation::class)->execute($user, $record);
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuotations::route('/'),
            'view' => ViewQuotation::route('/{record}'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('filament/resources/quotation.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/resources/quotation.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/resources/quotation.navigation_label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('company');
    }

    /**
     * @return list<TextInput>
     */
    private static function areaFields(): array
    {
        return collect(QuotationArea::cases())
            ->map(fn (QuotationArea $area): TextInput => TextInput::make($area->column())
                ->label($area->getLabel())
                ->numeric()
                ->prefix('€')
                ->default(0)
            )
            ->all();
    }
}
