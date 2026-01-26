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
            background-color: #fff;
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
            overflow: hidden;
            line-height: 1.2;
        }

        .container {
            width: 297mm;
            height: 210mm;
            position: relative;
            background: #fff;
            border: 8px solid #1e293b;
            overflow: hidden;
        }

        .inner-border {
            position: absolute;
            top: 10px;
            bottom: 10px;
            left: 10px;
            right: 10px;
            border: 1.5px solid #e2e8f0;
            z-index: 10;
        }

        .watermark-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            opacity: 0.05;
            z-index: 1;
        }

        .content {
            padding: 30px 50px;
            text-align: center;
            position: relative;
            z-index: 20;
            height: 100%;
        }

        .logo-img {
            width: 100px;
            height: auto;
            margin: 0 auto 10px;
            display: block;
        }

        .certificate-title {
            font-size: 42px;
            color: #1e293b;
            margin: 0 0 10px 0;
            font-weight: 800;
            text-transform: uppercase;
        }

        .winner-name {
            font-size: 38px;
            color: #3b82f6;
            font-weight: bold;
            margin: 10px 0 5px 0;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .rank-box {
            display: inline-block;
            background: #1e293b;
            color: #fff;
            padding: 12px 40px;
            border-radius: 50px;
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .footer-area {
            position: absolute;
            bottom: 60px;
            left: 0;
            right: 0;
        }

        .signature-box {
            width: 280px;
            margin: 0 auto;
            text-align: center;
            border-top: 1px solid #cbd5e1;
            padding-top: 10px;
        }

        .certificate-id {
            position: absolute;
            bottom: 20px;
            right: 30px;
            font-size: 11px;
            color: #94a3b8;
            font-family: monospace; /* Font kode agar lebih formal */
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
                <div style="font-size: 18px; color: #64748b; margin-bottom: 10px;">Sertifikat ini diberikan kepada:</div>

                <div class="winner-name">{{ $fish->participant_name ?? 'Peserta' }}</div>
                <div style="font-size: 22px; color: #475569; margin-bottom: 15px; font-weight: bold;">
                    {{ $fish->team_name ? '('.$fish->team_name.')' : '' }}
                </div>

                <div style="font-size: 18px; color: #64748b; margin-bottom: 10px;">Atas keberhasilannya sebagai:</div>
                <div class="rank-box">
                    @if($type === 'rank')
                        JUARA {{ $fish->final_rank }}
                    @else
                        {{ strtoupper($label) }}
                    @endif
                </div>

                <div style="font-size: 20px; color: #0f172a; font-weight: bold; margin-bottom: 25px;">
                    KELAS: {{ $fish->bettaClass->name ?? '' }} ({{ $fish->bettaClass->code ?? '' }})
                </div>

                <div style="font-size: 16px; color: #64748b;">
                    Diberikan pada Event <strong>{{ $event->name }}</strong><br>
                    {{ $event->location }}, {{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d F Y') }}
                </div>

                <div class="footer-area">
                    <div class="signature-box">
                        <strong>{{ $event->committee_name ?: 'Panitia SIKNUSA' }}</strong>
                        <div style="font-size: 14px; color: #94a3b8;">Penyelenggara Event</div>
                    </div>
                </div>

                <div class="certificate-id">
                    SIKNUSA FLARE ID - {{ \Carbon\Carbon::parse($event->event_date)->format('Ymd') }}-{{ str_pad($fish->id, 4, '0', STR_PAD_LEFT) }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
