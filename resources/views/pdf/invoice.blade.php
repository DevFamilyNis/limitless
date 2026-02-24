<!doctype html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <title>Faktura {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 14mm 14mm 14mm 14mm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10.5px;
            color: #111;
            margin: 0;
            line-height: 1.35;
        }

        .top {
            width: 100%;
            margin-bottom: 14px;
        }

        .brand {
            float: left;
            width: 58%;
            font-size: 20px;
            font-weight: 700;
            margin-top: 2px;
        }

        .meta {
            float: right;
            width: 42%;
        }

        .meta table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td {
            padding: 2px 0;
            vertical-align: top;
        }

        .meta td:first-child {
            color: #444;
            width: 52%;
        }

        .meta td:last-child {
            text-align: right;
            font-weight: 700;
        }

        .clear {
            clear: both;
        }

        .parties {
            width: 100%;
            margin-top: 6px;
            margin-bottom: 12px;
        }

        .party {
            width: 48.5%;
            float: left;
            min-height: 132px;
        }

        .party.right {
            float: right;
        }

        .party-title {
            font-size: 10px;
            text-transform: uppercase;
            color: #555;
            margin-bottom: 5px;
        }

        .party-name {
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .row {
            margin: 2px 0;
        }

        .dates {
            margin-bottom: 10px;
        }

        .dates .row {
            margin: 3px 0;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .items th,
        .items td {
            border: 1px solid #333;
            padding: 6px 7px;
            vertical-align: top;
        }

        .items th {
            font-weight: 700;
            background: #f5f5f5;
            text-align: left;
        }

        .num {
            text-align: right;
            white-space: nowrap;
        }

        .summary {
            margin-top: 8px;
            width: 46%;
            margin-left: auto;
            border-collapse: collapse;
        }

        .summary td {
            border: 1px solid #333;
            padding: 6px 7px;
        }

        .summary td:first-child {
            background: #fafafa;
            font-weight: 700;
        }

        .summary .grand td {
            font-weight: 700;
            font-size: 11px;
        }

        .section-title {
            margin-top: 16px;
            margin-bottom: 4px;
            font-weight: 700;
        }

        .footer {
            margin-top: 20px;
            font-size: 10px;
            color: #444;
            border-top: 1px solid #bbb;
            padding-top: 7px;
        }
    </style>
</head>
<body>
@php
    $clientName = $invoice->client?->display_name ?? '-';
    if ($invoice->client?->type?->key === 'person' && $invoice->client?->person) {
        $clientName = trim($invoice->client->person->first_name.' '.$invoice->client->person->last_name);
    }

    $currency = $userSetting?->default_currency ?? 'RSD';
    $issuerName = $userSetting?->display_name ?? config('app.name');
    $issuerAddress = $userSetting?->address;
    $issuerPib = $userSetting?->pib;
    $issuerMb = $userSetting?->mb;
    $issuerBank = $userSetting?->bank_account;
@endphp

<div class="top">
    <div class="brand">{{ $issuerName }}</div>

    <div class="meta">
        <table>
            <tr>
                <td>Datum izdavanja</td>
                <td>{{ $invoice->created_at?->format('d.m.Y') ?? '-' }}</td>
            </tr>
            <tr>
                <td>Broj fakture #</td>
                <td>{{ $invoice->invoice_number }}</td>
            </tr>
        </table>
    </div>

    <div class="clear"></div>
</div>

<div class="parties">
    <div class="party">
        <div class="party-title">Izdavalac</div>
        <div class="party-name">{{ $issuerName }}</div>
        @if ($issuerMb)
            <div class="row">Matični broj {{ $issuerMb }}</div>
        @endif
        @if ($issuerPib)
            <div class="row">PIB {{ $issuerPib }}</div>
        @endif
        @if ($issuerAddress)
            <div class="row">{{ $issuerAddress }}</div>
        @endif
    </div>

    <div class="party right">
        <div class="party-title">Kupac</div>
        <div class="party-name">{{ $clientName }}</div>
        @if ($invoice->client?->company?->mb)
            <div class="row">Matični broj {{ $invoice->client->company->mb }}</div>
        @endif
        @if ($invoice->client?->company?->pib)
            <div class="row">PIB {{ $invoice->client->company->pib }}</div>
        @endif
        @if ($invoice->client?->address)
            <div class="row">{{ $invoice->client->address }}</div>
        @endif
    </div>

    <div class="clear"></div>
</div>

<div class="dates">
    <div class="row">Datum prometa {{ $invoice->issue_date?->format('d.m.Y') ?? '-' }}</div>
    <div class="row">Datum dospeća {{ $invoice->due_date?->format('d.m.Y') ?? '-' }}</div>
    @if ($issuerBank)
        <div class="row">Tekući račun: {{ $issuerBank }}</div>
    @endif
</div>

<table class="items">
    <thead>
    <tr>
        <th style="width: 38%;">Usluge</th>
        <th style="width: 12%;">Jedinica</th>
        <th class="num" style="width: 10%;">Količina</th>
        <th class="num" style="width: 16%;">Cena</th>
        <th class="num" style="width: 10%;">PDV</th>
        <th class="num" style="width: 14%;">Ukupno</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($invoice->items as $item)
        <tr>
            <td>{{ $item->description }}</td>
            <td>Komad</td>
            <td class="num">{{ number_format((float) $item->quantity, 2, ',', '.') }}</td>
            <td class="num">{{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
            <td class="num">0,00</td>
            <td class="num">{{ number_format((float) $item->amount, 2, ',', '.') }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="6">Nema stavki.</td>
        </tr>
    @endforelse
    </tbody>
</table>

<table class="summary">
    <tr>
        <td>Ukupno za naplatu</td>
        <td class="num">{{ number_format((float) $invoice->total, 2, ',', '.') }}</td>
    </tr>
    <tr>
        <td>PDV</td>
        <td class="num">0,00</td>
    </tr>
    <tr class="grand">
        <td>Ukupno za uplatu ({{ $currency }})</td>
        <td class="num">{{ number_format((float) $invoice->total, 2, ',', '.') }}</td>
    </tr>
</table>

<div class="section-title">Komentar</div>
<div>
    Iznos od {{ number_format((float) $invoice->total, 2, ',', '.') }} {{ $currency }}
    @if ($issuerBank)
        uplatiti na tekući račun broj {{ $issuerBank }}.
    @else
        uplatiti na račun izdavaoca.
    @endif
    Prilikom plaćanja navedite poziv na broj: {{ $invoice->invoice_number }}.
    Ovaj račun je punovažan bez potpisa i pečata.
</div>

<div class="section-title">Napomena</div>
<div>Poreski obveznik nije u sistemu PDV-a. PDV nije obračunat na fakturi u skladu sa članom 33. Zakona o porezu na dodatnu vrednost.</div>

<div class="footer">
    {{ $issuerName }}
    @if (! empty($issuerEmail))
        | {{ $issuerEmail }}
    @endif
</div>
</body>
</html>
