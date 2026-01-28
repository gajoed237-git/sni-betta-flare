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

        /* 2. Pengaturan Margin Kertas F4 */
        @page {
            size: 215mm 330mm;
            margin: 1.5cm;
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
            padding-top: 15px;
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
            margin-bottom: 5px;
            border: none;
            table-layout: fixed;
            /* Kunci lebar kolom agar presisi */
        }

        .info-table td {
            font-size: 9px;
            padding: 1px 0;
            vertical-align: top;
        }

        /* 5. Tabel Utama (Format Sama dengan Registrasi) */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .main-table thead th {
            background-color: #a7a4a4ff;
            color: #000000;
            border: 1px solid #000;
            padding: 8px 4px;
            font-size: 9px;
            text-transform: uppercase;
        }

        .main-table tbody td {
            border: 1px solid #000;
            padding: 2px 4px;
            height: 24px;
            /* Padat sesuai request sebelumnya */
            vertical-align: middle;
            font-size: 9px;
            word-wrap: break-word;
        }

        /* Lebar Kolom yang Presisi */
        .col-no {
            width: 30px;
        }

        .col-reg {
            width: 65px;
        }

        .col-code {
            width: 60px;
        }

        .col-class {
            width: auto;
        }

        .col-check {
            width: 45px;
        }

        .col-ket {
            width: 95px;
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

        /* Summary Box */
        .summary-box {
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #000;
            background-color: #eeeeee;
        }

        .summary-row {
            font-size: 12px;
            margin-bottom: 5px;
        }

        /* 6. Footer (Fixed di bawah kertas) */
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
        }

        .signature-table td {
            font-size: 10px;
            height: 60px;
            vertical-align: bottom;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="page-wrapper">
        <div class="header">
            <h1>{{ $event->name }}</h1>
            <div class="subtitle">FORMULIR REGISTRASI PESERTA</div>
            <div class="header-meta">
                {{ $event->location }} | {{ \Carbon\Carbon::parse($event->start_date)->format('d F Y') }}
            </div>
        </div>

        <table class="info-table">
            <tr>
                <td width="30mm" class="font-bold">NAMA PESERTA :</td>
            </tr>
            <tr> </tr>
            <td width="30mm" class="font-bold">NAMA TEAM:</td>
            </tr>
            <tr>
                <td class="font-bold">ALAMAT :</td>
            </tr>
        </table>

        <table class="main-table">
            <thead>
                <tr>
                    <th class="col-no">NO</th>
                    <th class="col-reg">NO REG</th>
                    <th class="col-code">KODE</th>
                    <th class="col-class">NAMA KELAS</th>
                    <th class="col-check">IN</th>
                    <th class="col-check">OUT</th>
                    <th class="col-ket">KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                {{-- Generate 25 empty rows with the exact format --}}
                @for ($i = 0; $i < 25; $i++)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
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

        <div class="summary-box">
            <div class="summary-row">
                <span class="font-bold">JUMLAH IKAN:</span>
            </div>
            <div class="summary-row">
                <span class="font-bold" style="font-size: 14px;">TOTAL BAYAR:</span>
            </div>
        </div>

        <div class="signature-box">
            <table class="signature-table">
                <tr>
                    <td width="50%">
                        Peserta,<br><br><br><br>
                        ( __________________________ )
                    </td>
                    <td width="50%">
                        Petugas Registrasi,<br><br><br><br>
                        ( {{ $printedBy }} )
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