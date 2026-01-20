<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Aquarium Labels</title>
    <style>
        @page {
            margin: 10px;
        }

        body {
            font-family: 'Helvetica', sans-serif;
            margin: 0;
            padding: 0;
        }

        .grid {
            width: 100%;
        }

        .label-container {
            width: 31%;
            height: 220px;
            border: 2px solid #000;
            margin: 6px;
            display: inline-block;
            vertical-align: top;
            position: relative;
            box-sizing: border-box;
            background: #fff;
            overflow: hidden;
        }

        .label-header {
            background: #000;
            color: #fff;
            text-align: center;
            padding: 6px 2px;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1.2;
        }

        .label-content {
            padding: 5px;
            text-align: center;
        }

        .class-name {
            font-size: 11px;
            font-weight: bold;
            color: #000;
            height: 30px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px dashed #ccc;
            margin-bottom: 2px;
            padding: 0 4px;
        }

        .qr-section {
            position: relative;
            display: inline-block;
            margin: 2px 0;
            width: 100px;
            height: 100px;
        }

        .qr-section img {
            width: 100px;
            height: 100px;
        }

        .qr-center-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 1px 3px;
            font-size: 8px;
            font-weight: bold;
            color: #000;
            border: 1px solid #000;
            text-align: center;
            z-index: 10;
        }

        .bottom-info {
            margin-top: 2px;
            border-top: 1px solid #eee;
            padding-top: 4px;
        }

        .info-row {
            line-height: 1;
            margin-bottom: 2px;
        }

        .info-label {
            font-size: 8px;
            color: #666;
            font-weight: bold;
        }

        .info-value {
            font-size: 14px;
            font-weight: bold;
            color: #000;
        }

        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            background: #000;
            text-align: center;
            padding: 2px 0;
            font-size: 7px;
            font-weight: bold;
            color: #fff;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <div class="grid">
        @foreach($fishes as $fish)
        <div class="label-container">
            <div class="label-header">
                {{ strtoupper($fish['event_name']) }}
            </div>
            <div class="label-content">
                <div class="class-name">
                    {{ $fish['class_name'] }}
                </div>
                <div class="qr-section">
                    <img src="data:image/svg+xml;base64,{{ $fish['qr_code'] }}">
                    <div class="qr-center-text">SBF-QR</div>
                </div>
                <div class="bottom-info">
                    <div class="info-row">
                        <span class="info-label">REG:</span>
                        <span class="info-value">{{ $fish['registration_no'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">CODE:</span>
                        <span class="info-value">{{ $fish['class_code'] }}</span>
                    </div>
                </div>
            </div>
            <div class="footer">SCAN TO JUDGE • SNI BETTA FLARE</div>
        </div>
        @endforeach
    </div>
</body>

</html>