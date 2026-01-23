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

    /* 2. Pengaturan Margin Kertas (Manual via Wrapper) */
    @page {
        margin: 0; /* Margin nol agar kita kontrol penuh via CSS */
    }

    .page-wrapper {
        padding: 1.5cm 1.5cm; /* Space Kanan & Kiri 1.5cm agar rapi */
        width: 100%;
        min-height: 100%;
    }

    /* 3. Header Section */
    .header {
        text-align: center;
        border-bottom: 2px solid #1a4d2e;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }

    .header h1 {
        font-size: 18px;
        color: #1a4d2e;
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

    /* 4. Info Box (Atas Tabel) */
    .info-table {
        width: 100%;
        margin-bottom: 10px;
        border: none;
    }

    .info-table td {
        font-size: 9px;
        padding: 2px 0;
    }

    /* 5. Tabel Utama */
    .main-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed; /* Kunci agar tidak melebar ke kanan */
    }

    .main-table thead th {
        background-color: #2d6a4f;
        color: #ffffff;
        border: 1px solid #000;
        padding: 8px 4px;
        font-size: 9px;
        text-transform: uppercase;
    }

    .main-table tbody td {
        border: 1px solid #000;
        padding: 4px 6px;
        height: 32px; /* Tinggi baris ideal agar 25 baris muat di F4 */
        vertical-align: middle;
        font-size: 9px;
        word-wrap: break-word;
    }

    /* Zebra Striping */
    .main-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    /* Lebar Kolom yang Presisi */
    .col-no { width: 30px; }
    .col-reg { width: 65px; }
    .col-code { width: 60px; }
    .col-class { width: auto; } /* Biarkan nama kelas fleksibel */
    .col-check { width: 45px; }
    .col-ket { width: 95px; }

    /* Text Helpers */
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .font-bold { font-weight: bold; }

    /* 6. Footer (Fixed di bawah kertas) */
    .footer {
        position: fixed;
        bottom: 0.8cm;
        left: 1.5cm;
        right: 1.5cm;
        border-top: 1px solid #ccc;
        padding-top: 5px;
    }

    .footer-table {
        width: 100%;
        font-size: 8px;
        color: #666;
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
                <td width="15%" class="font-bold">NAMA PESERTA</td>
                <td width="35%">: {{ $participantName }}</td>
                <td width="20%" class="text-right font-bold">TANGGAL CETAK</td>
                <td width="30%" class="text-right">: {{ $printDate }}</td>
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
                @foreach ($fishes as $index => $fish)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center font-bold">{{ $fish['registration_no'] }}</td>
                    <td class="text-center">{{ $fish['class_code'] }}</td>
                    <td>{{ $fish['class_name'] }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td width="33%">Dicetak oleh: {{ $printedBy }}</td>
                <td width="33%" class="text-center">{{ now()->format('d/m/Y H:i:s') }}</td>
                <td width="33%" class="text-right font-bold" style="color: #1a4d2e;">© SIKNUSA FLARE ID</td>
            </tr>
        </table>
    </div>
</body>
</html>