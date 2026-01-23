<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Registrasi Peserta</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .page {
                page-break-after: always;
                margin: 0;
                padding: 0;
            }
        }

        .page {
            width: 100%;
            min-height: 100vh;
            padding: 15mm 15mm 15mm 15mm;
            background: white;
            margin: 0 auto;
            max-width: 1000px;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 8mm;
            border-bottom: 2px solid #000;
            padding-bottom: 4mm;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            color: #1a4d2e;
            letter-spacing: 1px;
        }

        .header .subtitle {
            font-size: 11px;
            color: #333;
            margin-top: 1mm;
        }

        .header .event-details {
            font-size: 10px;
            color: #333;
            margin-top: 2mm;
            line-height: 1.6;
        }

        .header-info {
            display: flex;
            justify-content: center;
            gap: 20mm;
            margin-top: 3mm;
            font-size: 10px;
        }

        .header-info div {
            flex: 0 1 auto;
        }

        .header-info .label {
            font-weight: bold;
        }

        /* Main Content */
        .content {
            margin-bottom: 4mm;
        }

        .title-section {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 2mm;
            padding: 2mm 0;
            background: #f0f0f0;
            padding-left: 3mm;
        }

        /* Table Styles */
        .table-wrapper {
            width: 100%;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            padding: 0;
        }

        table thead {
            background-color: #2d6a4f;
            color: white;
        }

        table thead th {
            border: 1px solid #000;
            padding: 3mm;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            height: 8mm;
        }

        table tbody td {
            border: 1px solid #000;
            padding: 2.5mm 3mm;
            height: 9.5mm;
            vertical-align: top;
            font-size: 10px;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Column Width */
        .col-no {
            width: 6%;
            text-align: center;
            font-weight: bold;
        }

        .col-reg {
            width: 12%;
            text-align: center;
        }

        .col-class-code {
            width: 10%;
            text-align: center;
        }

        .col-class-name {
            width: 18%;
        }

        .col-fish-in {
            width: 10%;
            text-align: center;
        }

        .col-nominasi {
            width: 12%;
            text-align: center;
        }

        .col-fish-out {
            width: 10%;
            text-align: center;
        }

        .col-keterangan {
            width: 22%;
        }

        /* Footer */
        .footer {
            margin-top: 8mm;
            font-size: 9px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-top: 1px solid #ccc;
            padding-top: 3mm;
        }

        .footer-left {
            text-align: left;
        }

        .footer-center {
            text-align: center;
            flex: 1;
        }

        .footer-right {
            text-align: right;
        }

        .footer-branding {
            font-size: 8px;
            font-weight: bold;
            color: #1a4d2e;
        }

        .signature-box {
            margin-top: 2mm;
            height: 12mm;
            border: 1px solid #999;
            width: 25mm;
            text-align: center;
            font-size: 8px;
            padding-top: 1mm;
        }

        /* Print specific */
        @media print {
            .page {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <h1>{{ $event->name }}</h1>
            <div class="subtitle">FORMULIR REGISTRASI PESERTA</div>
            <div class="event-details">
                @if ($event->location)
                    <div>Lokasi: <strong>{{ $event->location }}</strong></div>
                @endif
                @if ($event->start_date)
                    <div>Tanggal: <strong>{{ \Carbon\Carbon::parse($event->start_date)->locale('id')->format('d F Y') }}
                        @if ($event->end_date && $event->end_date !== $event->start_date)
                            s/d {{ \Carbon\Carbon::parse($event->end_date)->locale('id')->format('d F Y') }}
                        @endif
                    </strong></div>
                @endif
            </div>
        </div>

        <!-- Header Info -->
        <div class="header-info">
            <div>
                <span class="label">Nama Peserta:</span> {{ $participantName }}
            </div>
            <div>
                <span class="label">Tanggal Cetak:</span> {{ $printDate }}
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th class="col-reg">No Reg</th>
                            <th class="col-class-code">Kode Kelas</th>
                            <th class="col-class-name">Nama Kelas</th>
                            <th class="col-fish-in">Fish IN</th>
                            <th class="col-nominasi">Nominasi</th>
                            <th class="col-fish-out">Fish OUT</th>
                            <th class="col-keterangan">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fishes as $index => $fish)
                            <tr>
                                <td class="col-no">{{ $index + 1 }}</td>
                                <td class="col-reg">{{ $fish['registration_no'] }}</td>
                                <td class="col-class-code">{{ $fish['class_code'] }}</td>
                                <td class="col-class-name">{{ $fish['class_name'] }}</td>
                                <td class="col-fish-in"></td>
                                <td class="col-nominasi"></td>
                                <td class="col-fish-out"></td>
                                <td class="col-keterangan"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-left">
                <div>Dicetak oleh: {{ $printedBy }}</div>
            </div>
            <div class="footer-center">
                <div>{{ now()->format('d/m/Y H:i') }}</div>
            </div>
            <div class="footer-right">
                <div class="footer-branding">© SIKNUSA FLARE ID</div>
            </div>
        </div>
    </div>
</body>
</html>
