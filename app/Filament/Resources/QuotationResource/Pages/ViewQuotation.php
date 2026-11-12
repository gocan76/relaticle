<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Enums\QuotationArea;
use App\Enums\QuotationStatus;
use App\Filament\Resources\QuotationResource;
use App\Models\Quotation;
use Filament\Actions\DeleteAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ViewQuotation extends ViewRecord
{
    /** @var class-string<QuotationResource> */
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            QuotationResource::editAction()
                ->icon('heroicon-o-pencil-square')
                ->label(__('filament/resources/quotation.pages.view.actions.edit.label')),
            QuotationResource::sendAction(),
            DeleteAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('filament/resources/quotation.sections.header.title'))
                ->schema([
                    TextEntry::make('number')
                        ->label(__('filament/resources/quotation.fields.number.label')),
                    TextEntry::make('company.name')
                        ->label(__('filament/resources/quotation.fields.company.label')),
                    TextEntry::make('status')
                        ->label(__('filament/resources/quotation.fields.status.label'))
                        ->badge()
                        ->color(fn (QuotationStatus $state): string => $state->getColor()),
                    TextEntry::make('issued_at')
                        ->label(__('filament/resources/quotation.fields.issued_at.label'))
                        ->date(),
                    TextEntry::make('valid_until')
                        ->label(__('filament/resources/quotation.fields.valid_until.label'))
                        ->date(),
                    TextEntry::make('notes')
                        ->label(__('filament/resources/quotation.fields.notes.label'))
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make(__('filament/resources/quotation.sections.fornitura.title'))
                ->schema([
                    RepeatableEntry::make('fornitura_items')
                        ->state(fn (Quotation $record): array => $record->fornitura_items_with_totals)
                        ->hiddenLabel()
                        ->schema([
                            TextEntry::make('description')
                                ->hiddenLabel(),
                            TextEntry::make('unit_label')
                                ->hiddenLabel(),
                            TextEntry::make('quantity')
                                ->hiddenLabel(),
                            TextEntry::make('unit_price')
                                ->hiddenLabel()
                                ->numeric(2),
                            TextEntry::make('line_total')
                                ->hiddenLabel()
                                ->numeric(2),
                        ])
                        ->table([
                            TableColumn::make(__('filament/resources/quotation.fields.fornitura.description')),
                            TableColumn::make(__('filament/resources/quotation.fields.fornitura.unit')),
                            TableColumn::make(__('filament/resources/quotation.fields.fornitura.quantity')),
                            TableColumn::make(__('filament/resources/quotation.fields.fornitura.unit_price')),
                            TableColumn::make(__('filament/resources/quotation.fields.total.label')),
                        ]),
                ]),

            Section::make(__('filament/resources/quotation.sections.areas.title'))
                ->schema([
                    ...collect(QuotationArea::cases())
                        ->map(fn (QuotationArea $area): TextEntry => TextEntry::make($area->column())
                            ->label($area->getLabel())
                            ->numeric(2))
                        ->all(),
                    TextEntry::make('subtotal_amount')
                        ->label(__('filament/resources/quotation.fields.subtotal.label'))
                        ->numeric(2),
                    TextEntry::make('iva_amount')
                        ->label(__('filament/resources/quotation.fields.iva.label'))
                        ->numeric(2),
                    TextEntry::make('total_amount')
                        ->label(__('filament/resources/quotation.fields.total.label'))
                        ->numeric(2)
                        ->weight('bold'),
                ])
                ->columns(2),
        ]);
    }
}
