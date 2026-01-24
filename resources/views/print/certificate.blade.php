<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>E-Certificate</title>
    <style>
        @page {
            margin: 0;
            size: landscape;
        }

        body {
            font-family: 'Helvetica', sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            height: 100%;
            position: relative;
            background: #fff;
            border: 20px solid #1e293b;
            box-sizing: border-box;
        }

        .inner-border {
            position: absolute;
            top: 20px;
            bottom: 20px;
            left: 20px;
            right: 20px;
            border: 2px solid #e2e8f0;
            box-sizing: border-box;
        }

        .content {
            padding: 60px;
            text-align: center;
        }

        .header {
            margin-bottom: 40px;
        }

        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #3b82f6;
            letter-spacing: 5px;
            margin-bottom: 10px;
        }

        .certificate-title {
            font-size: 56px;
            color: #1e293b;
            margin: 20px 0;
            font-weight: 800;
            text-transform: uppercase;
        }

        .award-text {
            font-size: 20px;
            color: #64748b;
            margin-bottom: 40px;
        }

        .winner-name {
            font-size: 42px;
            color: #3b82f6;
            font-weight: bold;
            margin-bottom: 10px;
            text-decoration: underline;
        }

        .team-name {
            font-size: 24px;
            color: #475569;
            margin-bottom: 30px;
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
        }

        .event-info {
            font-size: 18px;
            color: #64748b;
            margin-top: 40px;
        }

        .class-info {
            font-size: 20px;
            color: #0f172a;
            font-weight: bold;
            margin-bottom: 50px;
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
        <div class="inner-border">
            <div class="content">
                <div class="header">
                    <div class="logo">SIKNUSA</div>
                    <div class="certificate-title">SERTIFIKAT JUARA</div>
                    <div class="award-text">Sertifikat ini diberikan kepada:</div>
                </div>

                <div class="winner-name">{{ $fish->participant_name }}</div>
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
                    KELAS: {{ $fish->betta_class?->name }} ({{ $fish->betta_class?->code }})
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