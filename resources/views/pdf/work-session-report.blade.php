<!doctype html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <title>Radne sesije {{ $dateFrom->format('d.m.Y') }} – {{ $dateTo->format('d.m.Y') }}</title>
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

        th, td {
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

        .badge-active {
            color: #166534;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Izveštaj radnih sesija</div>
        <div class="meta">Period: {{ $dateFrom->format('d.m.Y') }} – {{ $dateTo->format('d.m.Y') }}</div>
        <div class="meta">Korisnik: {{ $userName ?? 'Svi korisnici' }}</div>
        <div class="meta">Generisano: {{ now()->format('d.m.Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                @if($userName === null)
                    <th style="width: 22%;">Korisnik</th>
                @endif
                <th style="width: {{ $userName === null ? '18%' : '22%' }};">Datum</th>
                <th style="width: {{ $userName === null ? '12%' : '16%' }};">Početak</th>
                <th style="width: {{ $userName === null ? '12%' : '16%' }};">Kraj</th>
                <th style="width: {{ $userName === null ? '18%' : '22%' }};">Trajanje</th>
                <th style="width: {{ $userName === null ? '18%' : '24%' }};">Podsetnik</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sessions as $session)
                <tr>
                    @if($userName === null)
                        <td>{{ $session->user->name }}</td>
                    @endif
                    <td>{{ $session->work_date->format('d.m.Y') }}</td>
                    <td>{{ $session->started_at->format('H:i') }}</td>
                    <td>
                        @if ($session->ended_at)
                            {{ $session->ended_at->format('H:i') }}
                        @else
                            <span class="badge-active">Aktivan</span>
                        @endif
                    </td>
                    <td>
                        @if ($session->duration_minutes !== null)
                            {{ intdiv($session->duration_minutes, 60) }}h {{ $session->duration_minutes % 60 }}m
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if ($session->reminder_acknowledged_at)
                            Potvrđen
                        @elseif ($session->reminder_due_at)
                            {{ $session->reminder_due_at->format('H:i') }}
                            @if ($session->reminder_due_at->isPast())
                                (čeka)
                            @endif
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $userName === null ? 6 : 5 }}">Nema sesija za izabrani period.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($sessions->isNotEmpty())
            <tfoot>
                <tr>
                    @if($userName === null)
                        <td colspan="4">UKUPNO</td>
                    @else
                        <td colspan="3">UKUPNO</td>
                    @endif
                    <td>
                        @php $h = intdiv((int) $totalMinutes, 60); $m = (int) $totalMinutes % 60; @endphp
                        {{ $h }}h {{ $m }}m
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
