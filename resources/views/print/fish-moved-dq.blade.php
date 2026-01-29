<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Ikan Pindah Kelas & Diskualifikasi (DQ)</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 16pt;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 12pt;
            color: #444;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            word-wrap: break-word;
            height: 25px;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 50px;
            font-size: 9pt;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Laporan Ikan Pindah Kelas & Diskualifikasi (DQ)</h1>
        <p>{{ $event->name }}</p>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;" class="text-center">No</th>
                <th style="width: 15%;" class="text-center">No Reg</th>
                <th style="width: 15%;" class="text-center">Status</th>
                <th style="width: 20%;">Kelas Awal</th>
                <th style="width: 25%;">Kelas Baru</th>
                <th style="width: 25%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalRows = 50;
                $currentCount = 0;
            @endphp

            @foreach($fishes as $index => $fish)
                @php $currentCount++; @endphp
                <tr>
                    <td class="text-center">{{ $currentCount }}</td>
                    <td class="text-center">{{ $fish->registration_no }}</td>
                    <td class="text-center">
                        @if($fish->status === 'disqualified')
                            <strong>DQ</strong>
                        @else
                            PINDAH
                        @endif
                    </td>
                    <td>
                        @if($fish->originalClass)
                            ({{ $fish->originalClass->code }}) {{ $fish->originalClass->name }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($fish->status === 'moved' && $fish->bettaClass)
                            ({{ $fish->bettaClass->code }}) {{ $fish->bettaClass->name }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $fish->admin_note }}</td>
                </tr>
            @endforeach

            {{-- Render blank rows to reach $totalRows --}}
            @for($i = $currentCount + 1; $i <= $totalRows; $i++)
                <tr>
                    <td class="text-center" style="color: #ccc;">{{ $i }}</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <div class="footer">
        Dicetak oleh: {{ Auth::user()->name ?? 'System' }} pada {{ date('d/m/Y H:i') }}
    </div>
</body>

</html>