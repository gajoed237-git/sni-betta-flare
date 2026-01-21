<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Aquarium Labels</title>
    <style>
        @page {
            margin: 0;
            size: 75mm 50mm;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 75mm;
            height: 50mm;
            background: #fff;
            overflow: hidden;
            font-family: 'Arial', sans-serif;
        }

        .label-page {
            width: 75mm;
            height: 50mm;
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
        }

        .label-page+.label-page {
            page-break-before: always;
        }

        /* Top Bar / Header */
        .event-header {
            position: absolute;
            top: 1mm;
            left: 0;
            width: 75mm;
            height: 10mm;
            text-align: center;
            line-height: 10mm;
            font-weight: bold;
            font-size: 12pt;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
        }

        /* QR Code Area */
        .qr-area {
            position: absolute;
            top: 20mm;
            left: 4mm;
            width: 30mm;
        }

        .qr-box {
            position: relative;
            width: 28mm;
            height: 28mm;
            margin: 0 auto;
        }

        .qr-box img {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* Standard text in middle of QR */
        .qr-badge {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 1px 3px;
            font-size: 8pt;
            font-weight: bold;
            border: 1px solid #000;
            z-index: 100;
        }

        .system-identity {
            width: 100%;
            text-align: center;
            font-size: 7pt;
            font-weight: normal;
            margin-top: -5mm;
        }

        /* Fish Identification Area (Right side) */
        .id-area {
            position: absolute;
            top: 15mm;
            right: 4mm;
            width: 35mm;
            text-align: center;
        }

        .fish-id {
            font-size: 18pt;
            font-weight: bold;
            white-space: nowrap;
        }

        /* Class Name (Bottom right) */
        .class-name {
            position: absolute;
            bottom: 4mm;
            right: 4mm;
            width: 35mm;
            text-align: right;
            font-size: 7pt;
            font-weight: bold;
            overflow: hidden;
            white-space: nowrap;
        }
    </style>
</head>

<body>
    @foreach($fishes as $fish)
    <div class="label-page">
        <div class="event-header">
            {{ $fish['event_name'] }}
        </div>

        <div class="qr-area">
            <div class="qr-box">
                <img src="data:image/svg+xml;base64,{{ $fish['qr_code'] }}">
                <div class="qr-badge">
                    {{ $fish['judging_standard'] }}
                </div>
            </div>
            <div class="system-identity">
                Siknusa Flare ID
            </div>
        </div>

        <div class="id-area">
            <div class="fish-id">
                {{ $fish['class_code'] ?: '??' }}. {{ $fish['registration_no'] }}
            </div>
        </div>

        <div class="class-name">
            {{ $fish['class_name'] }}
        </div>
    </div>
    @endforeach
</body>

</html>