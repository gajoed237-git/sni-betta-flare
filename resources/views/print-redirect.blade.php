<!DOCTYPE html>
<html>
<head>
    <title>Membuka PDF...</title>
</head>
<body>
    <p>Membuka formulir cetak...</p>
    <script type="text/javascript">
        (function() {
            var url = '{{ $url }}';
            window.open(url, '_blank');
            
            // Kembali ke halaman sebelumnya setelah membuka URL
            setTimeout(function() {
                window.history.back();
            }, 500);
        })();
    </script>
</body>
</html>
