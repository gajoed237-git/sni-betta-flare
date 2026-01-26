<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Aquarium Labels</title>
    <style>
        @page {
            margin: 0;
            size: 60mm 50mm;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 60mm;
            height: 50mm;
            background: #fff;
            overflow: hidden;
            font-family: 'Arial', sans-serif;
        }

        .label-page {
            width: 60mm;
            height: 49.5mm;
            /* Slightly less than 50 to prevent overflow page */
            padding: 1.5mm;
            box-sizing: border-box;
            position: relative;
        }

        .label-page+.label-page {
            page-break-before: always;
        }

        /* Header Section */
        .event-header {
            width: 100%;
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
            text-transform: uppercase;
            padding-bottom: 1.5mm;
            margin-bottom: 1.5mm;
            border-bottom: 1px solid #000;
            height: 8mm;
            /* Fixed height for consistency */
            overflow: hidden;
            display: block;
        }

        .body-container {
            width: 100%;
            margin-top: 1mm;
        }

        /* Use simple tables for PDF reliability */
        .layout-table {
            width: 100%;
            border: 0;
        }

        .qr-col {
            width: 26mm;
            vertical-align: top;
            text-align: center;
        }

        .info-col {
            vertical-align: middle;
            text-align: right;
            padding-left: 1mm;
        }

        /* QR Code Styling */
        .qr-box {
            position: relative;
            width: 24mm;
            height: 24mm;
            display: inline-block;
        }

        .qr-box img {
            width: 100%;
            height: 100%;
        }

        .qr-badge {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 1px 2px;
            font-size: 6pt;
            font-weight: bold;
            border: 1px solid #000;
        }

        .system-identity {
            font-size: 5pt;
            text-align: center;
            margin-top: 0.5mm;
            color: #666;
        }

        /* Fish ID Styling */
        .fish-id {
            font-size: 15pt;
            font-weight: bold;
            line-height: 1.1;
            margin-bottom: 1mm;
            letter-spacing: -0.5px;
        }

        .class-name {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            line-height: 1.1;
            word-wrap: break-word;
            display: block;
            width: 100%;
        }
    </style>
</head>

<body>
    @foreach($fishes as $fish)
    <div class="label-page">
        <div class="event-header">
            {{ $fish['event_name'] }}
        </div>

        <table class="layout-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="qr-col">
                    <div class="qr-box">
                        <img src="data:image/svg+xml;base64,{{ $fish['qr_code'] }}">
                        <div class="qr-badge">
                            {{ $fish['judging_standard'] }}
                        </div>
                    </div>
                    <div class="system-identity">
                        Siknusa Flare ID
                    </div>
                </td>
                <td class="info-col">
                    <div class="fish-id">
                        {{ $fish['class_code'] ?: '??' }} <br>
                        <span style="font-size: 14pt;">{{ $fish['registration_no'] }}</span>
                    </div>
                    <div class="class-name">
                        {{ $fish['class_name'] }}
                    </div>
                </td>
            </tr>
        </table>
    </div>
    @endforeach
</body>

</html>