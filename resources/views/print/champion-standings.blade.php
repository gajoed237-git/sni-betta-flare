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
        <div class="title">LAPORAN JUARA UMUM (TEAM)</div>
        <div class="subtitle">SNI BETTA FLARE 2024</div>
    </div>

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
            @foreach($teams as $index => $team)
            <tr class="{{ $index < 3 ? 'top-row' : '' }}">
                <td>#{{ $index + 1 }}</td>
                <td>{{ $team['name'] }}</td>
                <td>{{ $team['gc'] }}</td>
                <td>{{ $team['gold'] }}</td>
                <td>{{ $team['silver'] }}</td>
                <td>{{ $team['bronze'] }}</td>
                <td class="points-cell">{{ $team['points'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px; font-size: 11px; color: #666;">
        * Perhitungan Poin: Grand Champion (30), Juara 1 (15), Juara 2 (7), Juara 3 (3).
    </div>

    <div class="footer">
        Dicetak pada: {{ $date }}
    </div>
</body>

</html>