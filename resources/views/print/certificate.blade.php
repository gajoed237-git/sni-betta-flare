<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>E-Certificate</title>
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            width: 297mm;
            height: 210mm;
            overflow: hidden;
            /* Mengunci agar tidak ada halaman 2 */
        }

        .container {
            /* Menggunakan tinggi sedikit kurang dari 210mm untuk keamanan */
            width: 297mm;
            height: 209mm;
            position: relative;
            border: 8px solid #1e293b;
            background: #fff;
        }

        .inner-border {
            position: absolute;
            top: 10px;
            bottom: 10px;
            left: 10px;
            right: 10px;
            border: 1.5px solid #e2e8f0;
        }

        .content {
            padding-top: 25px;
            text-align: center;
            height: 100%;
            position: relative;
        }

        .watermark-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 380px;
            opacity: 0.05;
            z-index: 1;
        }

        .logo-img {
            width: 90px;
            margin: 0 auto 10px;
            display: block;
        }

        .certificate-title {
            font-size: 38px;
            color: #1e293b;
            margin-bottom: 5px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .winner-name {
            font-size: 36px;
            color: #3b82f6;
            font-weight: bold;
            margin: 10px 0 5px;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .rank-box {
            display: inline-block;
            background: #1e293b;
            color: #fff;
            padding: 10px 35px;
            border-radius: 50px;
            font-size: 24px;
            font-weight: bold;
            margin: 15px 0;
        }

        /* AREA FOOTER & ID */
        .footer-wrapper {
            position: absolute;
            bottom: 40px;
            /* Jarak aman dari bawah */
            width: 100%;
            text-align: center;
        }

        .signature-box {
            width: 250px;
            margin: 0 auto;
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
        }

        .certificate-id {
            position: absolute;
            bottom: 15px;
            /* Naikkan agar tidak terpotong */
            right: 25px;
            font-size: 10px;
            color: #94a3b8;
            font-family: monospace;
            z-index: 100;
        }
    </style>
</head>

<body>
    <div class="container">
        <img src="{{ public_path('assets/watermark.png') }}" class="watermark-bg">

        <div class="inner-border">
            <div class="content">
                <img src="{{ public_path('assets/bg_siknusa_flare.png') }}" class="logo-img">

                <div class="certificate-title">SERTIFIKAT JUARA</div>
                <div style="font-size: 16px; color: #64748b;">Diberikan kepada:</div>

                <div class="winner-name">{{ $fish->participant_name ?? 'Peserta' }}</div>
                <div style="font-size: 20px; color: #475569; font-weight: bold; margin-bottom: 10px;">
                    {{ $fish->team_name ? '('.$fish->team_name.')' : '' }}
                </div>

                <div style="font-size: 16px; color: #64748b;">Atas keberhasilannya sebagai:</div>
                <div class="rank-box">
                    @if($type === 'rank')
                    JUARA {{ $fish->final_rank }}
                    @else
                    {{ strtoupper($label) }}
                    @endif
                </div>

                <div style="font-size: 18px; color: #0f172a; font-weight: bold; margin-bottom: 15px;">
                    KELAS: {{ $fish->bettaClass->name ?? '' }} ({{ $fish->bettaClass->code ?? '' }})
                </div>

                <div style="font-size: 15px; color: #64748b;">
                    Event <strong>{{ $event->name }}</strong><br>
                    {{ $event->location }}, {{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d F Y') }}
                </div>

                <div class="footer-wrapper">
                    <div class="signature-box">
                        <strong style="font-size: 16px;">{{ $event->committee_name ?: 'Panitia SIKNUSA' }}</strong>
                        <div style="font-size: 12px; color: #94a3b8;">Penyelenggara Event</div>
                    </div>
                </div>

                <div class="certificate-id">
                    SIKNUSA FLARE ID: {{ \Carbon\Carbon::parse($event->event_date)->format('Ymd') }}-{{ str_pad($fish->id, 4, '0', STR_PAD_LEFT) }}
                </div>
            </div>
        </div>
    </div>
</body>

</html>