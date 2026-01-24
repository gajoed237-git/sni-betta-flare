<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        /* 1. Reset & Dasar */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            line-height: 1.2;
            color: #000;
            background-color: #fff;
        }

        /* 2. Pengaturan Margin Kertas */
        @page {
            margin: 1.5cm 2cm;
            /* Margin 2cm kiri-kanan sesuai permintaan */
        }

        .page-wrapper {
            width: 100%;
            min-height: 100%;
            padding: 0;
            margin: 0;
        }

        /* 3. Header Section */
        .header {
            text-align: center;
            border-bottom: 2px solid #000000ff;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 18px;
            color: #000000ff;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .subtitle {
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .header-meta {
            font-size: 9px;
            color: #333;
        }

        /* 4. Info Box */
        .info-table {
            width: 100%;
            margin-bottom: 10px;
            border: collapse;
        }

        .info-table td {
            font-size: 9px;
            padding: 2px 0;
        }

        /* 5. Tabel Utama */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .main-table thead th {
            background-color: #000000ff;
            color: #ffffff;
            border: 1px solid #000;
            padding: 8px 4px;
            font-size: 8px;
            text-transform: uppercase;
        }

        .main-table tbody td {
            border: 1px solid #000;
            padding: 4px 5px;
            height: 30px;
            vertical-align: middle;
            font-size: 9px;
            word-wrap: break-word;
        }

        /* Column Widths */
        .col-no {
            width: 25px;
        }

        .col-code {
            width: 50px;
        }

        .col-class {
            width: auto;
        }

        .col-check {
            width: 40px;
        }

        .col-nom {
            width: 45px;
        }

        .col-winner {
            width: 45px;
        }

        .col-ket {
            width: 80px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        /* 6. Footer */
        .footer {
            position: fixed;
            bottom: 0.8cm;
            left: 2cm;
            right: 2cm;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }

        .footer-table {
            width: 100%;
            font-size: 8px;
            color: #666;
        }

        .signature-box {
            margin-top: 20px;
            width: 100%;
        }

        .signature-table {
            width: 100%;
            margin-top: 10px;
        }

        .signature-table td {
            font-size: 9px;
            height: 60px;
            vertical-align: bottom;
        }
    </style>

<body>
    <div class="page-wrapper">
        <div class="header">
            <h1>{{ $event->name }}</h1>
            <div class="subtitle">LEMBAR KERJA FISH OUT</div>
            <div class="header-meta">
                {{ $event->location }} | {{ \Carbon\Carbon::parse($event->start_date)->format('d F Y') }}
            </div>
        </div>

        <table class="info-table">
            <tr>
                <td width="15%" class="font-bold">NAMA PESERTA</td>
                <td width="35%">: {{ $participant->name }}</td>
                <td width="20%" class="font-bold">TANGGAL CETAK</td>
                <td width="30%">: {{ $printDate }}</td>
            </tr>
            <tr>
                <td class="font-bold">ALAMAT</td>
                <td>: {{ optional($participant->user)->address ?? '-' }}</td>
                <td class="font-bold">TOTAL ENTRY</td>
                <td>: {{ $fishes->count() }} Ikan</td>
            </tr>
        </table>

        <table class="main-table">
            <thead>
                <tr>
                    <th class="col-no">NO</th>
                    <th class="col-code">KODE</th>
                    <th class="col-class">NAMA KELAS</th>
                    <th class="col-check">FISH IN</th>
                    <th class="col-nom">NOMINASI</th>
                    <th class="col-winner">JUARA</th>
                    <th class="col-check">FISH OUT</th>
                    <th class="col-ket">KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($fishes as $index => $fish)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center font-bold">{{ $fish->bettaClass->code ?? '-' }}</td>
                    <td>{{ $fish->bettaClass->name ?? '-' }}</td>
                    <td class="text-center">V</td>
                    <td class="text-center">{{ $fish->is_nominated ? 'V' : '' }}</td>
                    <td class="text-center font-bold">{{ $fish->final_rank ? $fish->final_rank : '' }}</td>
                    <td></td>
                    <td></td>
                </tr>
                @endforeach

                {{-- Add empty rows up to 15 if needed --}}
                @for ($i = $fishes->count(); $i < 15; $i++)
                    <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    </tr>
                    @endfor
            </tbody>
        </table>

        <div class="signature-box">
            <table class="signature-table">
                <tr>
                    <td width="50%" class="text-center">
                        Peserta / Penanggung Jawab,<br><br><br><br>
                        ( __________________________ )
                    </td>
                    <td width="50%" class="text-center">
                        Panitia Event,<br><br><br><br>
                        ( __________________________ )
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td width="33%">Dicetak oleh: {{ $printedBy }}</td>
                <td width="33%" class="text-center">{{ now()->format('d/m/Y H:i:s') }}</td>
                <td width="33%" class="text-right font-bold" style="color: #000000ff;">© SIKNUSA FLARE ID</td>
            </tr>
        </table>
    </div>
</body>

</html>