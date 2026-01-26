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
            height: 50mm;
            padding: 2mm;
            box-sizing: border-box;
            position: relative;
        }

        .label-page+.label-page {
            page-break-before: always;
        }

        /* Wrap everything in a table for stable layout in DomPDF */
        .layout-table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Header Section */
        .event-header {
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            /* Slightly smaller to prevent overflow */
            text-transform: uppercase;
            padding-bottom: 2mm;
            margin-bottom: 2mm;
            border-bottom: 1.5px solid #000;
            height: 8mm;
            overflow: hidden;
            line-height: 1.1;
            word-wrap: break-word;
        }

        .body-table {
            width: 100%;
        }

        .qr-col {
            width: 25mm;
            vertical-align: top;
            padding-top: 1mm;
        }

        .info-col {
            width: 31mm;
            vertical-align: middle;
            text-align: right;
            padding-left: 2mm;
        }

        /* QR Code Styling */
        .qr-box {
            position: relative;
            width: 24mm;
            height: 24mm;
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
            font-size: 7pt;
            font-weight: bold;
            border: 1px solid #000;
        }

        .system-identity {
            font-size: 6pt;
            text-align: center;
            margin-top: 1mm;
            color: #333;
        }

        /* Fish ID Styling */
        .fish-id {
            font-size: 16pt;
            font-weight: bold;
            line-height: 1.2;
            word-wrap: break-word;
            margin-bottom: 2mm;
        }

        .class-name {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            margin-top: 2mm;
            line-height: 1.1;
        }
    </style>
</head>

<body>
    @foreach($fishes as $fish)
    <div class="label-page">
        <div class="event-header">
            {{ $fish['event_name'] }}
        </div>

        <table class="body-table">
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
                        {{ $fish['class_code'] ?: '??' }}. <br>
                        {{ $fish['registration_no'] }}
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