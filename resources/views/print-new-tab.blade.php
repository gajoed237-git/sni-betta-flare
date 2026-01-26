<!DOCTYPE html>
<html>

<head>
    <title>Cetak...</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: #f5f5f5;
        }

        .container {
            text-align: center;
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="container" id="app-data" data-url="{{ session('url') ?? $url ?? '' }}">
        <p id="message">Membuka formulir cetak...</p>
    </div>

    <script>
        window.addEventListener('load', function() {
            const url = document.getElementById('app-data').dataset.url;

            if (url && typeof url === 'string' && url.trim() !== '') {
                // Buka di tab baru
                const newTab = window.open(url, '_blank');

                // Jika popup blocked
                if (!newTab) {
                    document.getElementById('message').textContent = 'Popup blocked. Silakan allow popup.';
                } else {
                    // Kembali ke halaman sebelumnya setelah membuka
                    setTimeout(function() {
                        window.history.back();
                    }, 1000);
                }
            } else {
                document.getElementById('message').textContent = 'URL tidak valid. Kembali...';
                console.error('URL tidak ditemukan:', url);
                setTimeout(function() {
                    window.history.back();
                }, 2000);
            }
        });
    </script>
</body>

</html>