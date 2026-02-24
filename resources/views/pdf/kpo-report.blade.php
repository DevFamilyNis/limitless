<!doctype html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <title>KPO {{ sprintf('%02d/%d', $report->month, $report->year) }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 12mm 14mm 12mm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #111;
            margin: 0;
        }

        .header {
            margin-bottom: 14px;
        }

        .title {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .meta {
            font-size: 10px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background: #f2f2f2;
            text-align: left;
            font-weight: 700;
        }

        .num {
            text-align: right;
            white-space: nowrap;
        }

        tfoot td {
            font-weight: 700;
            background: #f8f8f8;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">KPO - Knjiga o ostvarenom prometu paušalca</div>
        <div class="meta">Period: {{ $report->period_from?->format('d.m.Y') }} - {{ $report->period_to?->format('d.m.Y') }}</div>
        <div class="meta">Korisnik: {{ $report->user?->name ?? '-' }}</div>
        <div class="meta">Valuta: {{ $report->currency }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 48%;">Datum i opis knjiženja</th>
                <th class="num" style="width: 17%;">Prihodi od prodaje proizvoda</th>
                <th class="num" style="width: 17%;">Prihodi od izvršenih usluga</th>
                <th class="num" style="width: 18%;">Svega prihodi od delatnosti (3+4)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>
                        {{ $row->entry_date?->format('d.m.Y') ?? '-' }}
                        <br>
                        {{ $row->entry_description }}
                    </td>
                    <td class="num">{{ number_format((float) $row->products_amount, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $row->services_amount, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $row->activity_amount, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Nema faktura za izabrani period.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td>UKUPNO</td>
                <td class="num">{{ number_format((float) $report->products_total, 2, ',', '.') }}</td>
                <td class="num">{{ number_format((float) $report->services_total, 2, ',', '.') }}</td>
                <td class="num">{{ number_format((float) $report->activity_total, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
