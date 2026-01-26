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
            height: 48mm;
            /* Reduced more to ensure no overflow pages */
            padding: 1mm;
            box-sizing: border-box;
            position: relative;
            overflow: hidden;
            /* Lock content inside */
        }

        .label-page+.label-page {
            page-break-before: always;
        }

        /* Header Section */
        .event-header {
            width: 100%;
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
            /* Slightly smaller for more body room */
            text-transform: uppercase;
            padding-bottom: 0.5mm;
            margin-bottom: 1mm;
            border-bottom: 1px solid #000;
            height: 6mm;
            /* Shrunk header to make room for body margins */
            overflow: hidden;
            display: block;
        }

        /* Use simple tables with fixed layout */
        .layout-table {
            width: 100%;
            border: 0;
            table-layout: fixed;
            margin-top: 0mm;
        }

        .qr-col {
            width: 22mm;
            /* Narrower to shift info-col LEFT */
            vertical-align: top;
            text-align: center;
        }

        .info-col {
            width: 35mm;
            vertical-align: top;
            /* Changed to TOP for better control */
            text-align: left;
            /* Shift text to LEFT as requested */
            padding-left: 2mm;
        }

        /* QR Code Styling */
        .qr-box {
            position: relative;
            width: 22mm;
            height: 22mm;
            margin-top: 6mm;
            /* Move Barcode down */
            display: inline-block;
        }

        .qr-box img {
            width: 120%;
            height: 120%;
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
            margin-left: -2mm;
        }

        /* Fish ID Styling */
        .fish-id {
            font-size: 14pt;
            font-weight: bold;
            line-height: 1.0;
            margin-bottom: 1mm;
            letter-spacing: -0.5px;
            margin-top: 1.5mm;
            /* Align with QR move */
        }

        .class-name {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            line-height: 1.0;
            word-wrap: break-word;
            display: block;
            width: 100%;
            margin-top: 1mm;
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
                        <span style="font-size: 12pt;">{{ $fish['registration_no'] }}</span>
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