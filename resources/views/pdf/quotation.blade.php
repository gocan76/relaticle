<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('filament/resources/quotation.navigation_label') }} {{ $quotation->number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #111827; font-size: 13px; }
        h1 { font-size: 20px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { padding: 6px 8px; text-align: left; vertical-align: top; }
        .header th { width: 30%; color: #6b7280; font-weight: normal; }
        .areas th, .areas td, .fornitura th, .fornitura td { border-bottom: 1px solid #e5e7eb; }
        .amount { text-align: right; }
        .total td { font-weight: bold; border-top: 2px solid #111827; }
        .terms { margin-top: 24px; font-size: 8px; line-height: 1.35; color: #4b5563; }
        .terms p { margin: 0 0 4px; }
        .terms strong { color: #111827; }
    </style>
</head>
<body>
    <h1>{{ __('filament/resources/quotation.sections.header.title') }} {{ $quotation->number }}</h1>

    <table class="header">
        <tr>
            <th>{{ __('filament/resources/quotation.fields.company.label') }}</th>
            <td>{{ $quotation->company?->name }}</td>
        </tr>
        <tr>
            <th>{{ __('filament/resources/quotation.fields.issued_at.label') }}</th>
            <td>{{ $quotation->issued_at?->toDateString() }}</td>
        </tr>
        <tr>
            <th>{{ __('filament/resources/quotation.fields.valid_until.label') }}</th>
            <td>{{ $quotation->valid_until?->toDateString() }}</td>
        </tr>
    </table>

    @if (count($quotation->fornitura_items_with_totals))
        <table class="fornitura">
            <thead>
                <tr>
                    <th>{{ __('filament/resources/quotation.sections.fornitura.title') }}</th>
                    <th>{{ __('filament/resources/quotation.fields.fornitura.unit') }}</th>
                    <th>{{ __('filament/resources/quotation.fields.fornitura.quantity') }}</th>
                    <th class="amount">{{ __('filament/resources/quotation.fields.fornitura.unit_price') }}</th>
                    <th class="amount">{{ __('filament/resources/quotation.fields.total.label') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quotation->fornitura_items_with_totals as $item)
                    <tr>
                        <td>{{ $item['description'] }}</td>
                        <td>{{ $item['unit_label'] }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td class="amount">{{ number_format($item['unit_price'], 2) }} &euro;</td>
                        <td class="amount">{{ number_format($item['line_total'], 2) }} &euro;</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <table class="areas">
        <tbody>
            @foreach ($areas as $area)
                <tr>
                    <td>{{ $area->getLabel() }}</td>
                    <td class="amount">{{ number_format((float) $quotation->{$area->column()}, 2) }} &euro;</td>
                </tr>
            @endforeach
            <tr>
                <td>{{ __('filament/resources/quotation.fields.subtotal.label') }}</td>
                <td class="amount">{{ number_format($quotation->subtotal_amount, 2) }} &euro;</td>
            </tr>
            <tr>
                <td>{{ __('filament/resources/quotation.fields.iva.label') }}</td>
                <td class="amount">{{ number_format($quotation->iva_amount, 2) }} &euro;</td>
            </tr>
            <tr class="total">
                <td>{{ __('filament/resources/quotation.fields.total.label') }}</td>
                <td class="amount">{{ number_format($quotation->total_amount, 2) }} &euro;</td>
            </tr>
        </tbody>
    </table>

    @if ($quotation->notes)
        <p>{{ $quotation->notes }}</p>
    @endif

    <div class="terms">
        <p><strong>Nota:</strong> Il presente preventivo include la presentazione di numero 2 proposte grafiche.</p>
        <p><strong>Misure e Rilievi:</strong> Il preventivo è formulato sulle misure fornite dal cliente. Eventuali rilievi tecnici in loco, se richiesti, faranno fede per il conteggio definitivo dei materiali e della posa.</p>
        <p><strong>Stato delle Superfici:</strong> Le superfici di applicazione devono presentarsi lisce, integre, asciutte e prive di vernici sfoglianti, siliconi o residui. È inclusa la sola sgrassatura pre-posa; la rimozione di vecchie pellicole o residui di colla sarà quantificata a parte. Non si risponde di distacchi dovuti all'inidoneità o al degrado del supporto.</p>
        <p><strong>Logistica e Condizioni Meteo:</strong> Si presuppone un accesso libero e sicuro all'area di lavoro in orario diurno feriale. Eventuali noleggi di piattaforme aeree/trabattelli sono esclusi se non espressamente indicati. Per le pose in esterno, l'intervento è subordinato a condizioni meteo favorevoli (temperature tra +10°C e +30°C).</p>
        <p><strong>Garanzia e Rimozione:</strong> La durata del materiale segue le specifiche del produttore per esposizione verticale. La futura rimozione su pareti verniciate o cartongesso può comportare il distacco della pittura sottostante, evento non imputabile al fornitore.</p>
        <p><strong>Validità e Pagamenti:</strong> Offerta valida 30 giorni. Acconto del 50% all'accettazione, saldo a fine lavori.</p>
    </div>
</body>
</html>
