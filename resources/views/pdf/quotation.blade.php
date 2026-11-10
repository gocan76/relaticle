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
        .areas th, .areas td { border-bottom: 1px solid #e5e7eb; }
        .areas .amount { text-align: right; }
        .total td { font-weight: bold; border-top: 2px solid #111827; }
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

    <table class="areas">
        <thead>
            <tr>
                <th>{{ __('filament/resources/quotation.sections.areas.title') }}</th>
                <th class="amount">{{ __('filament/resources/quotation.fields.total.label') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($areas as $area)
                <tr>
                    <td>{{ $area->getLabel() }}</td>
                    <td class="amount">{{ number_format((float) $quotation->{$area->column()}, 2) }} &euro;</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>{{ __('filament/resources/quotation.fields.total.label') }}</td>
                <td class="amount">{{ number_format($quotation->total_amount, 2) }} &euro;</td>
            </tr>
        </tbody>
    </table>

    @if ($quotation->notes)
        <p>{{ $quotation->notes }}</p>
    @endif
</body>
</html>
