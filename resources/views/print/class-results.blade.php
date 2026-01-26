<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Results - {{ $class->name }}</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 15mm 20mm 15mm 20mm;
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
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .rank-1 {
            background-color: #fff9c4;
            font-weight: bold;
        }

        .rank-2 {
            background-color: #f5f5f5;
        }

        .rank-3 {
            background-color: #efebe9;
        }

        .footer {
            margin-top: 50px;
            text-align: right;
            font-style: italic;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">LAPORAN HASIL PENILAIAN</div>
        <div class="subtitle">Siknusa Flare ID</div>
        <div style="margin-top: 5px;">Kelas: {{ $class->name }} ({{ $class->code }})</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 50px;">Rank</th>
                <th style="width: 100px;">Reg No</th>
                <th>Owner / Team</th>
                <th style="width: 80px;">Avg Score</th>
                <th style="width: 60px;">Judges</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $res)
            <tr class="rank-{{ $res->rank_in_class }}">
                <td>{{ $res->rank_in_class }}</td>
                <td>{{ $res->fish->registration_no }}</td>
                <td>
                    {{ $res->fish->participant_name }}
                    @if($res->fish->team_name)
                    <br><small style="color: #666;">Team: {{ $res->fish->team_name }}</small>
                    @endif
                </td>
                <td>{{ number_format($res->average_score, 2) }}</td>
                <td>{{ $res->total_judges }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ $date }}
    </div>
</body>

</html>