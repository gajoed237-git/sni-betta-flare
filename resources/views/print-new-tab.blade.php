<!DOCTYPE html>
<html>
<head>
    <title>Cetak...</title>
    <meta charset="UTF-8">
</head>
<body>
    <script>
        // Ambil URL dari session, buka di tab baru, dan kembali ke halaman sebelumnya
        const url = "{{ $url ?? '' }}";
        if (url) {
            window.open(url, '_blank');
        }
        // Kembali ke halaman sebelumnya
        window.history.back();
    </script>
</body>
</html>
