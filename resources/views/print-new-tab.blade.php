<!DOCTYPE html>
<html>
<head>
    <title>Cetak...</title>
    <meta charset="UTF-8">
</head>
<body>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const url = "{{ $url ?? '' }}";
            if (url && url.trim() !== '') {
                window.open(url, '_blank');
            }
            // Kembali ke halaman sebelumnya
            setTimeout(function() {
                window.history.back();
            }, 500);
        });
    </script>
</body>
</html>
