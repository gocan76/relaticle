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
        .doc-header { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .doc-header td { padding: 0; }
        .doc-info { vertical-align: bottom; font-size: 13px; line-height: 1.4; }
        .sender-text { vertical-align: bottom; text-align: right; font-size: 11px; line-height: 1.25; padding-left: 12px; }
        .sender-text .name { font-size: 26px; font-weight: bold; line-height: 1.2; }
        .logo-cell { vertical-align: middle; padding-left: 16px; }
        .logo { max-height: 70px; max-width: 160px; }
    </style>
</head>
<body>
    <table class="doc-header">
        <tr>
            <td class="doc-info" width="40%" valign="bottom">
                Preventivo {{ $quotation->number }}<br>
                Decorrenza: {{ $quotation->issued_at?->format('d/m/Y') }}<br>
                Validità dell'offerta 30 gg
            </td>
            <td class="sender-text" width="45%" valign="bottom">
                <div class="name">{{ $quotation->team?->name }}</div>
                @php
                    $teamAddress = trim(implode(' ', array_filter([
                        $quotation->team?->address,
                        $quotation->team?->city,
                        $quotation->team?->postal_code,
                        $quotation->team?->province ? '('.$quotation->team->province.')' : null,
                    ])));
                @endphp
                @if ($teamAddress !== '')
                    <div>{{ $teamAddress }}</div>
                @endif
                @if (filled($quotation->team?->email) || filled($quotation->team?->pec))
                    <div>
                        @if (filled($quotation->team?->email))
                            Email: {{ $quotation->team->email }}
                        @endif
                        @if (filled($quotation->team?->email) && filled($quotation->team?->pec))
                            &nbsp;&nbsp;
                        @endif
                        @if (filled($quotation->team?->pec))
                            PEC: {{ $quotation->team->pec }}
                        @endif
                    </div>
                @endif
                @if (filled($quotation->team?->phone))
                    <div>Tel. {{ $quotation->team->phone }}</div>
                @endif
                @if (filled($quotation->team?->partita_iva) || filled($quotation->team?->rea))
                    <div>
                        @if (filled($quotation->team?->partita_iva))
                            C.F./P.Iva {{ $quotation->team->partita_iva }}
                        @endif
                        @if (filled($quotation->team?->partita_iva) && filled($quotation->team?->rea))
                            &nbsp;&nbsp;
                        @endif
                        @if (filled($quotation->team?->rea))
                            REA {{ $quotation->team->rea }}
                        @endif
                    </div>
                @endif
            </td>
            <td class="logo-cell" width="15%" valign="middle">
                @if (filled($quotation->team?->logo))
                    @php
                        $logoPath = $quotation->team->logo;
                        $logoData = null;
                        if (\Storage::disk('public')->exists($logoPath)) {
                            $logoData = 'data:'.(\Storage::disk('public')->mimeType($logoPath) ?: 'image/png').';base64,'.base64_encode(\Storage::disk('public')->get($logoPath));
                        }
                    @endphp
                    @if ($logoData)
                        <img src="{{ $logoData }}" class="logo" alt="">
                    @endif
                @endif
            </td>
        </tr>
    </table>

    <table class="header">
        <tr>
            <th>{{ __('filament/resources/quotation.fields.company.label') }}</th>
            <td>{{ $quotation->company?->name }}</td>
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
