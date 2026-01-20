<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Membuka SIKNUSA...</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background-color: #f8fafc;
            color: #1e293b;
            text-align: center;
            padding: 20px;
        }

        .card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }

        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        h1 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        p {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.2s;
        }

        .btn:active {
            background-color: #2563eb;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="loader"></div>
        <h1>Sedang Membuka Aplikasi</h1>
        <p>Anda akan diarahkan ke aplikasi SIKNUSA FLARE ID untuk memverifikasi akun.</p>
        <a href="siknusa-flare-id://verify?token={{ $token }}&email={{ urlencode($email) }}" class="btn" id="openLink">Buka Aplikasi Manual</a>
    </div>

    <script>
        window.onload = function() {
            var deepLink = "siknusa-flare-id://verify?token={{ $token }}&email={{ urlencode($email) }}";

            // Try to open deep link automatically
            window.location.href = deepLink;

            // Fallback: if app doesn't open in 2 seconds, show the button clearly
            setTimeout(function() {
                console.log("Deep link attempt finished.");
            }, 2000);
        };
    </script>
</body>

</html>