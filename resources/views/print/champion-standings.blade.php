<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Champion Standings</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 10mm;
        }

        body {
            font-family: sans-serif;
            margin: 0;
            font-size: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 12px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
            color: #000;
            font-weight: bold;
            font-size: 9px;
        }

        .left-align {
            text-align: left;
        }

        .top-row {
            background-color: #fff9c4;
            font-weight: bold;
        }

        .points-cell {
            text-align: right;
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .rules-section {
            margin-top: 15px;
            font-size: 8px;
            padding: 8px;
            border: 1px solid #ddd;
            background-color: #fafafa;
        }

        .footer {
            margin-top: 10px;
            text-align: right;
            font-size: 8px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">LAPORAN CHAMPION STANDINGS</div>
        <div class="subtitle">{{ $event->name }} ({{ strtoupper($event->judging_standard) }})</div>
    </div>

    @php
        $isIbc = $event->judging_standard === 'ibc';
        $customAwardCount = $event->custom_awards ? count($event->custom_awards) : 0;
        $colSpan = 6 + ($isIbc ? 4 : 4) + $customAwardCount;
    @endphp

    <h4 style="margin: 5px 0 3px 0;">KATEGORI: TEAM (JUARA UMUM)</h4>
    <table>
        <thead>
            <tr>
                <th style="width: 25px;">Rank</th>
                <th class="left-align">Team Name</th>
                @if($isIbc)
                    <th style="width: 40px;">{{ $event->label_bos ?: 'BOS' }}</th>
                    <th style="width: 40px;">{{ $event->label_boo ?: 'BOO' }}</th>
                    <th style="width: 40px;">{{ $event->label_bov ?: 'BOV' }}</th>
                    <th style="width: 40px;">{{ $event->label_bod ?: 'BOD' }}</th>
                @else
                    <th style="width: 40px;">{{ $event->label_gc ?: 'GC' }}</th>
                    <th style="width: 40px;">{{ $event->label_bob ?: 'BOB' }}</th>
                    <th style="width: 40px;">{{ $event->label_bof ?: 'BOF' }}</th>
                    <th style="width: 40px;">{{ $event->label_bos ?: 'BOS' }}</th>
                @endif

                @if($event->custom_awards)
                    @foreach($event->custom_awards as $award)
                        <th style="width: 45px;">{{ strtoupper($award['label']) }}</th>
                    @endforeach
                @endif

                <th style="width: 35px;">J1</th>
                <th style="width: 35px;">J2</th>
                <th style="width: 35px;">J3</th>
                <th style="width: 55px; text-align: right;">Total Poin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($teams as $index => $team)
                <tr class="{{ $index < 3 ? 'top-row' : '' }}">
                    <td>{{ $index + 1 }}</td>
                    <td class="left-align">{{ $team['name'] }}</td>
                    @if($isIbc)
                        <td>{{ $team['bos'] }}</td>
                        <td>{{ $team['boo'] }}</td>
                        <td>{{ $team['bov'] }}</td>
                        <td>{{ $team['bod'] }}</td>
                    @else
                        <td>{{ $team['gc'] }}</td>
                        <td>{{ $team['bob'] }}</td>
                        <td>{{ $team['bof'] }}</td>
                        <td>{{ $team['bos'] }}</td>
                    @endif

                    @if($event->custom_awards)
                        @foreach($event->custom_awards as $award)
                            @php $key = strtolower($award['key']); @endphp
                            <td>{{ $team['custom_titles'][$key] ?? 0 }}</td>
                        @endforeach
                    @endif

                    <td>{{ $team['gold'] }}</td>
                    <td>{{ $team['silver'] }}</td>
                    <td>{{ $team['bronze'] }}</td>
                    <td class="points-cell">{{ number_format($team['points']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $colSpan }}" style="text-align: center;">Belum ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h4 style="margin: 15px 0 3px 0;">KATEGORI: SINGLE FIGHTER (SF)</h4>
    <table>
        <thead>
            <tr>
                <th style="width: 25px;">Rank</th>
                <th class="left-align">Participant Name</th>
                @if($isIbc)
                    <th style="width: 40px;">{{ $event->label_bos ?: 'BOS' }}</th>
                    <th style="width: 40px;">{{ $event->label_boo ?: 'BOO' }}</th>
                    <th style="width: 40px;">{{ $event->label_bov ?: 'BOV' }}</th>
                    <th style="width: 40px;">{{ $event->label_bod ?: 'BOD' }}</th>
                @else
                    <th style="width: 40px;">{{ $event->label_gc ?: 'GC' }}</th>
                    <th style="width: 40px;">{{ $event->label_bob ?: 'BOB' }}</th>
                    <th style="width: 40px;">{{ $event->label_bof ?: 'BOF' }}</th>
                    <th style="width: 40px;">{{ $event->label_bos ?: 'BOS' }}</th>
                @endif

                @if($event->custom_awards)
                    @foreach($event->custom_awards as $award)
                        <th style="width: 45px;">{{ strtoupper($award['label']) }}</th>
                    @endforeach
                @endif

                <th style="width: 35px;">J1</th>
                <th style="width: 35px;">J2</th>
                <th style="width: 35px;">J3</th>
                <th style="width: 55px; text-align: right;">Total Poin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sfs as $index => $sf)
                <tr class="{{ $index < 3 ? 'top-row' : '' }}">
                    <td>{{ $index + 1 }}</td>
                    <td class="left-align">{{ $sf['name'] }}</td>
                    @if($isIbc)
                        <td>{{ $sf['bos'] }}</td>
                        <td>{{ $sf['boo'] }}</td>
                        <td>{{ $sf['bov'] }}</td>
                        <td>{{ $sf['bod'] }}</td>
                    @else
                        <td>{{ $sf['gc'] }}</td>
                        <td>{{ $sf['bob'] }}</td>
                        <td>{{ $sf['bof'] }}</td>
                        <td>{{ $sf['bos'] }}</td>
                    @endif

                    @if($event->custom_awards)
                        @foreach($event->custom_awards as $award)
                            @php $key = strtolower($award['key']); @endphp
                            <td>{{ $sf['custom_titles'][$key] ?? 0 }}</td>
                        @endforeach
                    @endif

                    <td>{{ $sf['gold'] }}</td>
                    <td>{{ $sf['silver'] }}</td>
                    <td>{{ $sf['bronze'] }}</td>
                    <td class="points-cell">{{ number_format($sf['points']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $colSpan }}" style="text-align: center;">Belum ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="rules-section">
        <strong>Point Rules ({{ strtoupper($event->judging_standard) }})</strong><br>
        <div style="margin-top: 5px; line-height: 1.3;">
            - Juara 1: {{ $event->point_rank1 }}pt |
            - Juara 2: {{ $event->point_rank2 }}pt |
            - Juara 3: {{ $event->point_rank3 }}pt<br>

            @if($isIbc)
                @if($event->point_bos > 0) - {{ $event->label_bos ?: 'BOS' }}: {{ $event->point_bos }}pt | @endif
                @if($event->point_boo > 0) - {{ $event->label_boo ?: 'BOO' }}: {{ $event->point_boo }}pt | @endif
                @if($event->point_bov > 0) - {{ $event->label_bov ?: 'BOV' }}: {{ $event->point_bov }}pt | @endif
                @if($event->point_bod > 0) - {{ $event->label_bod ?: 'BOD' }}: {{ $event->point_bod }}pt @endif
            @else
                @if($event->point_gc > 0) - {{ $event->label_gc ?: 'GC' }}: {{ $event->point_gc }}pt | @endif
                @if($event->point_bob > 0) - {{ $event->label_bob ?: 'BOB' }}: {{ $event->point_bob }}pt | @endif
                @if($event->point_bof > 0) - {{ $event->label_bof ?: 'BOF' }}: {{ $event->point_bof }}pt | @endif
                @if($event->point_bos > 0) - {{ $event->label_bos ?: 'BOS' }}: {{ $event->point_bos }}pt @endif
            @endif

            @if($event->custom_awards && count($event->custom_awards) > 0)
                <br>
                @foreach($event->custom_awards as $award)
                    - {{ $award['label'] }}: {{ $award['points'] }}pt
                    @if(!$loop->last) | @endif
                @endforeach
            @endif
        </div>
        <div style="margin-top: 5px; font-size: 7px; color: #777;">
            * Mode Perhitungan:
            {{ $event->point_accumulation_mode === 'accumulation' ? 'Akumulasi Semua Juara' : 'Ambil Poin Tertinggi' }}
        </div>
    </div>

    <div class="footer">
        Dicetak pada: {{ $date }} | SIKNUSA FLARE ID
    </div>
</body>

</html>