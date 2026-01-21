<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>SIKNUSA FLARE Apps</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            margin: 0;
            font-family: system-ui, sans-serif;
            background: #020617;
        }

        /* BACKGROUND LOGO BESAR */
        .logo-bg {
            position: fixed;
            inset: 0;
            background-image: url('/assets/bg_siknusa_flare.png');
            background-repeat: no-repeat;
            background-position: center;
            background-size: 85%;
            opacity: 0.08;
            animation: bgFloat 18s ease-in-out infinite alternate;
            z-index: 0;
        }

        @keyframes bgFloat {
            0% {
                transform: translateY(0) scale(1);
            }

            50% {
                transform: translateY(-18px) scale(1.02);
            }

            100% {
                transform: translateY(10px) scale(1.01);
            }
        }

        /* CARD */
        .card {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, .06);
            backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, .1);
        }

        /* LOGO ICON KECIL (TIDAK DIHILANGKAN) */
        .logo-icon {
            animation: floatIcon 6s ease-in-out infinite;
        }

        @keyframes floatIcon {
            50% {
                transform: translateY(-6px);
            }
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center text-white overflow-hidden">

    <!-- BACKGROUND -->
    <div class="logo-bg"></div>

    <!-- CONTENT -->
    <div class="card rounded-3xl p-10 md:p-16 text-center max-w-xl mx-auto shadow-2xl">

        <!-- LOGO ICON (BESAR & RESPONSIVE) -->
        <img
            src="/assets/logo_siknusa.png"
            alt="SIKNUSA FLARE ID"
            class="logo-icon mx-auto mb-8
                   h-16 md:h-35 lg:h-40
                   opacity-95">

        <h1 class="text-4xl font-extrabold mb-3">
            SIKNUSA One Platforms
        </h1>

        <p class="text-indigo-200 mb-8">
            Identification Systems Championship of Nuasantara
        </p>

        <a href="{{ url(env('FILAMENT_PATH', 'system-access')) }}"
            class="inline-flex items-center justify-center px-8 py-4 rounded-full font-bold
                  bg-gradient-to-r from-indigo-600 to-purple-600
                  hover:-translate-y-1 transition">
            Access Master Panel →
        </a>

        <div class="text-center text-indigo-300/40 text-sm mt-8">
            &copy; {{ date('Y') }} SIKNUSA FLARE ID
        </div>
    </div>

</body>

</html>