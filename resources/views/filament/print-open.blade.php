<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membuka Cetak...</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
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
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #2d6a4f;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        p {
            color: #666;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <p>Membuka PDF di tab baru...</p>
    </div>
    
    <script>
        (function() {
            const url = '{{ $url }}';
            
            // Buka PDF di tab baru
            const newTab = window.open(url, '_blank');
            
            // Jika browser block popup, show error
            if (!newTab) {
                document.querySelector('p').textContent = 'Popup blocked. Silakan allow popup untuk browser ini.';
                return;
            }
            
            // Close modal/dialog setelah membuka
            setTimeout(function() {
                // Untuk Filament Livewire, dispatch event untuk close
                if (window.dispatchEvent) {
                    window.dispatchEvent(new CustomEvent('filament-modal-close'));
                }
                
                // Fallback: coba go back
                window.history.back();
            }, 300);
        })();
    </script>
</body>
</html>
