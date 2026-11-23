<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Actions\Quotation\SendQuotation;
use App\Actions\Quotation\UpdateQuotation;
use App\Enums\QuotationArea;
use App\Enums\QuotationItemUnit;
use App\Enums\QuotationStatus;
use App\Filament\Resources\QuotationResource\Pages\ListQuotations;
use App\Filament\Resources\QuotationResource\Pages\ViewQuotation;
use App\Models\Quotation;
use App\Models\User;
use App\Services\EasyfattXmlExporter;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
                    Select::make('payment_name')
                        ->label(__('filament/resources/quotation.fields.payment_name.label'))
                        ->options([
                            'Bonifico bancario' => 'Bonifico bancario',
                            'Contanti' => 'Contanti',
                            'Rimessa diretta' => 'Rimessa diretta',
                            'Ricevuta bancaria' => 'Ricevuta bancaria',
                            'Assegno' => 'Assegno',
                            'Carta di credito' => 'Carta di credito',
                            'Bollettino postale' => 'Bollettino postale',
                            'RID' => 'RID',
                        ])
                        ->searchable()
                        ->nullable(),
                    DatePicker::make('issued_at')
                        ->label(__('filament/resources/quotation.fields.issued_at.label')),
                    DatePicker::make('valid_until')
                        ->label(__('filament/resources/quotation.fields.valid_until.label')),
                    Textarea::make('notes')
                        ->label(__('filament/resources/quotation.fields.notes.label'))
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make(__('filament/resources/quotation.sections.fornitura.title'))
                ->schema([
                    self::fornituraRepeater(),
                ]),

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
                self::previewAction(),
                self::exportEasyfattAction(),
                ActionGroup::make([
                    ViewAction::make(),
                    self::editAction(),
                    self::sendAction(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::exportEasyfattBulkAction(),
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

    public static function previewAction(): Action
    {
        return Action::make('preview')
            ->label(__('filament/resources/quotation.actions.preview.label'))
            ->icon('heroicon-o-eye')
            ->tooltip(__('filament/resources/quotation.actions.preview.label'))
            ->action(function (Quotation $record): StreamedResponse {
                /** @var User $user */
                $user = auth()->user();

                abort_unless($user->can('view', $record), 403);

                $pdf = Pdf::loadView('pdf.quotation', [
                    'quotation' => $record->loadMissing(['company', 'team']),
                    'areas' => QuotationArea::cases(),
                ])->output();

                return response()->streamDownload(
                    function () use ($pdf): void {
                        echo $pdf;
                    },
                    'quotation-'.$record->number.'.pdf',
                    ['Content-Type' => 'application/pdf'],
                    'inline',
                );
            });
    }

    public static function exportEasyfattAction(): Action
    {
        return Action::make('exportEasyfatt')
            ->label(__('filament/resources/quotation.actions.export_easyfatt.label'))
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->tooltip(__('filament/resources/quotation.actions.export_easyfatt.label'))
            ->action(function (Quotation $record): StreamedResponse {
                $xml = resolve(EasyfattXmlExporter::class)->generate(collect([$record]));

                return response()->streamDownload(
                    function () use ($xml): void {
                        echo $xml;
                    },
                    self::exportFilename($record),
                    ['Content-Type' => 'application/xml; charset=UTF-8'],
                );
            });
    }

    public static function exportEasyfattBulkAction(): BulkAction
    {
        return BulkAction::make('exportEasyfatt')
            ->label(__('filament/resources/quotation.actions.export_easyfatt.label'))
            ->icon('heroicon-o-arrow-down-tray')
            ->action(function (Collection $records): StreamedResponse {
                $xml = resolve(EasyfattXmlExporter::class)->generate($records);

                return response()->streamDownload(
                    function () use ($xml): void {
                        echo $xml;
                    },
                    'easyfatt-'.now()->format('Ymd-His').'.xml',
                    ['Content-Type' => 'application/xml; charset=UTF-8'],
                );
            });
    }

    private static function exportFilename(Quotation $record): string
    {
        $number = $record->getAttribute('number');

        return 'preventivo-'.Str::slug(is_string($number) && $number !== '' ? $number : (string) $record->getKey()).'.xml';
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

    private static function fornituraRepeater(): Repeater
    {
        return Repeater::make('fornitura_items')
            ->hiddenLabel()
            ->schema([
                TextInput::make('description')
                    ->label(__('filament/resources/quotation.fields.fornitura.description'))
                    ->required()
                    ->columnSpanFull(),
                Select::make('unit')
                    ->label(__('filament/resources/quotation.fields.fornitura.unit'))
                    ->options(QuotationItemUnit::class)
                    ->required()
                    ->live(),
                TextInput::make('quantity')
                    ->label(fn (Get $get): string => match ($get('unit')) {
                        QuotationItemUnit::Meter => __('filament/resources/quotation.fields.fornitura.quantity_per_meter'),
                        QuotationItemUnit::SquareMeter => __('filament/resources/quotation.fields.fornitura.quantity_per_square_meter'),
                        default => __('filament/resources/quotation.fields.fornitura.quantity'),
                    })
                    ->required(),
                TextInput::make('unit_price')
                    ->label(fn (Get $get): string => match ($get('unit')) {
                        QuotationItemUnit::Meter => __('filament/resources/quotation.fields.fornitura.price_per_meter'),
                        QuotationItemUnit::SquareMeter => __('filament/resources/quotation.fields.fornitura.price_per_square_meter'),
                        default => __('filament/resources/quotation.fields.fornitura.unit_price'),
                    })
                    ->numeric()
                    ->prefix('€')
                    ->required(),
            ])
            ->columns(3)
            ->addActionLabel(__('filament/resources/quotation.actions.add_fornitura_item'))
            ->reorderable()
            ->collapsible();
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
