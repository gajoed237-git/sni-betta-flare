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

        body {
            font-family: 'Helvetica', sans-serif;
            background-color: #fff;
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
            overflow: hidden;
        }

        .container {
            width: 297mm;
            height: 210mm;
            position: relative;
            background: #fff;
            box-sizing: border-box;
            border: 15px solid #1e293b;
            overflow: hidden;
        }

        /* Watermark Layer */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50%;
            opacity: 0.1;
            /* Logo transparency */
            z-index: 0;
        }

        .inner-border {
            position: absolute;
            top: 20px;
            bottom: 20px;
            left: 20px;
            right: 20px;
            border: 2px solid #e2e8f0;
            box-sizing: border-box;
            z-index: 10;
        }

        .content {
            padding: 40px 60px;
            text-align: center;
            position: relative;
            z-index: 20;
        }

        .header {
            margin-bottom: 20px;
        }

        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #3b82f6;
            letter-spacing: 5px;
            margin-bottom: 5px;
        }

        .certificate-title {
            font-size: 48px;
            color: #1e293b;
            margin: 10px 0;
            font-weight: 800;
            text-transform: uppercase;
        }

        .award-text {
            font-size: 20px;
            color: #64748b;
            margin-bottom: 20px;
        }

        .winner-name {
            font-size: 42px;
            color: #3b82f6;
            font-weight: bold;
            margin-bottom: 5px;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .team-name {
            font-size: 24px;
            color: #475569;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .rank-box {
            display: inline-block;
            background: #1e293b;
            color: #fff;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .event-info {
            font-size: 18px;
            color: #64748b;
            margin-top: 20px;
        }

        .class-info {
            font-size: 22px;
            color: #0f172a;
            font-weight: bold;
            margin-bottom: 40px;
            text-transform: uppercase;
        }

        .footer {
            position: absolute;
            bottom: 80px;
            width: 100%;
            padding: 0 100px;
            box-sizing: border-box;
        }

        .signature-box {
            float: left;
            width: 30%;
            text-align: center;
            border-top: 1px solid #cbd5e1;
            padding-top: 10px;
        }

        .qr-code {
            float: right;
            width: 100px;
            height: 100px;
        }

        .sign-title {
            font-size: 14px;
            color: #94a3b8;
            margin-top: 5px;
        }

        .seal {
            clear: both;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Watermark -->
        <img src="{{ public_path('assets/watermark.png') }}" class="watermark" alt="Watermark">

        <div class="inner-border">
            <div class="content">
                <div class="header">
                    <div class="logo">SIKNUSA</div>
                    <div class="certificate-title">SERTIFIKAT JUARA</div>
                    <div class="award-text">Sertifikat ini diberikan kepada:</div>
                </div>

                <div class="winner-name">{{ $fish->participant_name ?? 'Peserta' }}</div>
                <div class="team-name">{{ $fish->team_name ? '('.$fish->team_name.')' : '' }}</div>

                <div class="award-text">Atas keberhasilannya sebagai:</div>
                <div class="rank-box">
                    @if($type === 'rank')
                    JUARA {{ $fish->final_rank }}
                    @else
                    {{ strtoupper($label) }}
                    @endif
                </div>

                <div class="class-info">
                    KELAS: {{ $fish->bettaClass->name ?? '' }} ({{ $fish->bettaClass->code ?? '' }})
                </div>

                <div class="event-info">
                    Diberikan pada Event <strong>{{ $event->name }}</strong><br>
                    {{ $event->location }}, {{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d F Y') }}
                </div>

                <div class="footer">
                    <div class="signature-box">
                        <strong>{{ $event->committee_name ?: 'Panitia SIKNUSA' }}</strong>
                        <div class="sign-title">Penyelenggara Event</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>