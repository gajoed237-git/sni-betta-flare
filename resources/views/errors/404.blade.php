<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan | SIKNUSA</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-bg: #050510;
            --accent-glow: #3a86ff;
            --accent-orange: #ff006e;
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--primary-bg);
            color: #fff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated Background Bubbles */
        .bubbles {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: 1;
            overflow: hidden;
            top: 0;
            left: 0;
        }

        .bubble {
            position: absolute;
            bottom: -100px;
            width: 40px;
            height: 40px;
            background: var(--accent-glow);
            border-radius: 50%;
            opacity: 0.1;
            filter: blur(20px);
            animation: rise 15s infinite ease-in;
        }

        @keyframes rise {
            0% {
                transform: translateY(0) scale(0.5);
                opacity: 0.1;
            }

            50% {
                opacity: 0.3;
            }

            100% {
                transform: translateY(-120vh) scale(1.5);
                opacity: 0;
            }
        }

        .container {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 2rem;
            max-width: 600px;
            width: 90%;
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-code {
            font-size: 15rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #3a86ff, #ff006e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            display: inline-block;
            filter: drop-shadow(0 0 20px rgba(58, 134, 255, 0.3));
        }

        .fish-shadow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 12rem;
            opacity: 0.05;
            z-index: -1;
            animation: swim 6s infinite ease-in-out;
        }

        @keyframes swim {

            0%,
            100% {
                transform: translate(-55%, -50%) rotate(-5deg);
            }

            50% {
                transform: translate(-45%, -50%) rotate(5deg);
            }
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        p {
            font-size: 1.1rem;
            color: #a0a0c0;
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            padding: 1.2rem 3rem;
            background: linear-gradient(135deg, #3a86ff, #00d2ff);
            color: #fff;
            text-decoration: none;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 20px rgba(58, 134, 255, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 30px rgba(58, 134, 255, 0.5);
            filter: brightness(1.1);
        }

        .btn:active {
            transform: translateY(0) scale(0.98);
        }

        /* Floating particles */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            background: #fff;
            border-radius: 50%;
            opacity: 0.2;
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translate(0, 0) rotate(0deg);
            }

            100% {
                transform: translate(100px, -100px) rotate(360deg);
            }
        }

        @media (max-width: 480px) {
            .error-code {
                font-size: 8rem;
            }

            h1 {
                font-size: 1.8rem;
            }

            p {
                font-size: 1rem;
            }

            .container {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>

    <div class="bubbles" id="bubbleContainer"></div>

    <div class="container">
        <div class="fish-shadow">🐠</div>
        <div class="error-code">404</div>
        <h1>Ups! Ikan Lepas dari Soliter
            <p>Halaman yang Anda cari tidak ditemukan atau telah dipindahkan, coba cek di meja Juri atau mari kembali ke
                dashboard utama.
            </p>
            <a href="/system-access" class="btn">Kembali ke Dashboard</a>
    </div>

    <script>
        const container = document.getElementById('bubbleContainer');
        for (let i = 0; i < 15; i++) {
            const bubble = document.createElement('div');
            bubble.className = 'bubble';
            bubble.style.left = Math.random() * 100 + '%';
            bubble.style.width = Math.random() * 60 + 20 + 'px';
            bubble.style.height = bubble.style.width;
            bubble.style.animationDuration = Math.random() * 10 + 10 + 's';
            bubble.style.animationDelay = Math.random() * 5 + 's';
            container.appendChild(bubble);
        }
    </script>
</body>

</html>