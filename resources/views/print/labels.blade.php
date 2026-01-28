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

        .label-page:last-child {
            page-break-after: auto;
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
            margin-left: -2mm;
            /* Shift left */
            /* Move Barcode down */
            display: inline-block;
        }

        .qr-box img {
            width: 125%;
            height: 125%;
        }

        .qr-badge {
            position: absolute;
            top: 35%;
            left: 42%;
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
            margin-top: 1mm;
            /* Raised higher */
            color: #000000ff;
            margin-left: -2mm;
        }

        /* Fish ID Styling */
        .fish-id {
            font-size: 16pt;
            font-weight: bold;
            line-height: 1.0;
            letter-spacing: -0.5px;
            margin-top: 1mm;
            /* Raised higher */
            margin-bottom: 2mm;
            white-space: nowrap;
        }

        .minus-wrapper {
            margin-top: 1mm;
            margin-left: -3mm;
            /* Shifted right */
        }

        .minus-title {
            font-size: 6pt;
            font-weight: bold;
            margin-bottom: 0.5mm;
            text-transform: uppercase;
        }

        .minus-table {
            width: 100%;
            border-collapse: collapse;
        }

        .minus-table td {
            font-size: 6pt;
            line-height: 1.1;
            padding-bottom: 1px;
            white-space: nowrap;
            font-weight: bold;
            /* Bold all text */
        }

        .box {
            display: inline-block;
            width: 3mm;
            height: 3mm;
            border: 1px solid #000;
            margin-right: 1mm;
            vertical-align: middle;
        }

        .label-footer {
            position: absolute;
            bottom: 1.5mm;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            border-top: 0.5px solid #eee;
            padding-top: 0mm;
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
                        SIKNUSA FLARE ID
                    </div>
                </td>
                <td class="info-col">
                    <div class="fish-id">
                        {{ $fish['class_code'] ?: '??' }}.{{ $fish['registration_no'] }}
                    </div>
                    <div class="minus-wrapper">
                        <div class="minus-title">Detail Minus:</div>
                        <table class="minus-table">
                            <tr>
                                <td><span class="box"></span>Kepala</td>
                                <td><span class="box"></span>Ekor</td>
                            </tr>
                            <tr>
                                <td><span class="box"></span>Badan</td>
                                <td><span class="box"></span>Dasi</td>
                            </tr>
                            <tr>
                                <td><span class="box"></span>Dorsal</td>
                                <td><span class="box"></span>Warna</td>
                            </tr>
                            <tr>
                                <td><span class="box"></span>Anal</td>
                                <td><span class="box"></span>Kerapihan</td>
                            </tr>
                            <tr>
                                <td><span class="box"></span>Mental</td>
                                <td></td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <div class="label-footer">
            {{ $fish['class_name'] }}
        </div>
    </div>
    @endforeach
</body>

</html>