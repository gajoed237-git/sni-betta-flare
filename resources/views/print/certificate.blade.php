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

            border: 8px solid #1e293b;

            overflow: hidden;

        }



        .watermark-bg {

            position: absolute;

            top: 50%;

            left: 50%;

            transform: translate(-50%, -50%);

            width: 35%;

            opacity: 0.06;

            z-index: 1;

        }



        .inner-border {

            position: absolute;

            top: 8px;

            bottom: 8px;

            left: 8px;

            right: 8px;

            border: 1.5px solid #e2e8f0;

            box-sizing: border-box;

            z-index: 10;

        }



        .content {

            padding: 25px 50px;

            text-align: center;

            position: relative;

            z-index: 20;

        }



        .header {

            margin-bottom: 10px;

        }



        .logo-img {

            width: 120px;

            height: auto;

            margin: 0 auto 5px;

            display: block;

        }



        .certificate-title {

            font-size: 44px;

            color: #1e293b;

            margin: 8px 0;

            font-weight: 800;

            text-transform: uppercase;

        }



        .award-text {

            font-size: 18px;

            color: #64748b;

            margin-bottom: 15px;

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



        .class-info {

            font-size: 22px;

            color: #0f172a;

            font-weight: bold;

            margin-bottom: 40px;

            text-transform: uppercase;

        }



        .event-info {

            font-size: 18px;

            color: #64748b;

            margin-top: 20px;

        }



        .footer {

            position: absolute;

            bottom: 60px;

            width: 100%;

            padding: 0 80px;

            box-sizing: border-box;

        }



        .signature-box {

            float: left;

            width: 30%;

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

            font-weight: normal;

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

        <img src="{{ public_path('assets/watermark.png') }}" class="watermark-bg" alt="">



        <div class="inner-border">

            <div class="content">

                <div class="header">

                    <img src="{{ public_path('assets/bg_siknusa_flare.png') }}" alt="SIKNUSA Logo" class="logo-img">

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



                <div class="certificate-id">

                    SIKNUSA FLARE ID - {{ str_pad($fish->id, 4, '0', STR_PAD_LEFT) }}

                </div>

            </div>

        </div>

    </div>

</body>



</html>