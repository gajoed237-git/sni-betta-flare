<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Champion Standings</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 16px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #333;
            color: #fff;
            font-weight: bold;
        }

        .top-row {
            background-color: #fff9c4;
            font-weight: bold;
        }

        .points-cell {
            text-align: right;
            font-weight: bold;
            color: #d84315;
        }

        .footer {
            margin-top: 50px;
            text-align: right;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">LAPORAN CHAMPION STANDINGS</div>
        <div class="subtitle">{{ $event->name }}</div>
    </div>

    <h3 style="margin-top: 20px;">KATEGORI: JUARA UMUM (TEAM)</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 50px;">Rank</th>
                <th>Team Name</th>
                <th style="width: 50px;">GC</th>
                <th style="width: 50px;">G</th>
                <th style="width: 50px;">S</th>
                <th style="width: 50px;">B</th>
                <th style="width: 80px; text-align: right;">Total Poin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($teams as $index => $team)
            <tr class="{{ $index < 3 ? 'top-row' : '' }}">
                <td>#{{ $index + 1 }}</td>
                <td>{{ $team['name'] }}</td>
                <td>{{ $team['gc'] }}</td>
                <td>{{ $team['gold'] }}</td>
                <td>{{ $team['silver'] }}</td>
                <td>{{ $team['bronze'] }}</td>
                <td class="points-cell">{{ $team['points'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center;">Belum ada data.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <h3 style="margin-top: 40px;">KATEGORI: SINGLE FIGHTER (SF)</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 50px;">Rank</th>
                <th>Participant Name</th>
                <th style="width: 50px;">GC</th>
                <th style="width: 50px;">G</th>
                <th style="width: 50px;">S</th>
                <th style="width: 50px;">B</th>
                <th style="width: 80px; text-align: right;">Total Poin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sfs as $index => $sf)
            <tr class="{{ $index < 3 ? 'top-row' : '' }}">
                <td>#{{ $index + 1 }}</td>
                <td>{{ $sf['name'] }}</td>
                <td>{{ $sf['gc'] }}</td>
                <td>{{ $sf['gold'] }}</td>
                <td>{{ $sf['silver'] }}</td>
                <td>{{ $sf['bronze'] }}</td>
                <td class="points-cell">{{ $sf['points'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center;">Belum ada data.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 30px; font-size: 11px; color: #666;">
        Standard: {{ strtoupper($event->judging_standard) }}.
        Mode Perhitungan: {{ $event->point_accumulation_mode === 'accumulation' ? 'AKUMULASI JUARA' : 'AMBIL POIN TERTINGGI' }}.
        <br>
        @if($event->judging_standard === 'ibc')
        Poin IBC: BOS ({{ $event->point_bos }}), BOO ({{ $event->point_boo }}), BOV ({{ $event->point_bov }}), BOD ({{ $event->point_bod }}), J1 ({{ $event->point_rank1 }}), J2 ({{ $event->point_rank2 }}), J3 ({{ $event->point_rank3 }}).
        @else
        Poin SNI: GC ({{ $event->point_gc }}), BOB ({{ $event->point_bob }}), J1 ({{ $event->point_rank1 }}), J2 ({{ $event->point_rank2 }}), J3 ({{ $event->point_rank3 }}).
        @endif
    </div>

    <div class="footer">
        Dicetak pada: {{ $date }}
    </div>
</body>

</html>