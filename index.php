<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChromaAi - Moderná AI Platforma</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="logo.png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-purple: #9b30ff;
            --dark-purple: #7a1fd1;
            --light-bg: #ffffff;
            --text-color: #1a1a1a;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
            height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Jazykový prepínač vľavo hore */
        .lang-switcher {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }

        .lang-switcher a {
            text-decoration: none;
            color: #6c757d;
            font-weight: 600;
            font-size: 0.9rem;
            transition: color 0.3s;
            margin-right: 10px;
        }

        .lang-switcher a:hover, .lang-switcher a.active {
            color: var(--primary-purple);
        }

        /* Hlavný obsah v strede */
        .hero-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
        }

        .logo-container img {
            max-width: 250px;
            height: auto;
            margin-bottom: 30px;
            filter: drop-shadow(0 10px 15px rgba(155, 48, 255, 0.2));
        }

        .search-container {
            width: 100%;
            max-width: 700px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 18px 25px;
            font-size: 1.1rem;
            border-radius: 16px;
            border: 2px solid #eee;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            outline: none;
        }

        .search-input:focus {
            border-color: var(--primary-purple);
            box-shadow: 0 10px 40px rgba(155, 48, 255, 0.15);
        }

        .search-input::placeholder {
            color: #adb5bd;
        }

        .hint-text {
            margin-top: 15px;
            color: #6c757d;
            font-size: 0.85rem;
        }

        /* Animované pozadie (jemné) */
        .bg-glow {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(155, 48, 255, 0.08) 0%, rgba(255,255,255,0) 70%);
            z-index: -1;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
        }

        footer {
            padding: 20px;
            font-size: 0.8rem;
            color: #adb5bd;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Prepínač jazykov -->
    <div class="lang-switcher">
        <a href="#" class="active">SK</a>
        <a href="#">EN</a>
    </div>

    <!-- Žiara na pozadí -->
    <div class="bg-glow"></div>

    <main class="hero-section">
        <div class="logo-container">
            <img src="logo.png" alt="ChromaAi Logo">
        </div>

        <div class="search-container">
            <input type="text" class="search-input" placeholder="Opýtajte sa čokoľvek..." autofocus>
            <div class="hint-text">Stlačte <strong>Enter</strong> pre začatie chatu</div>
        </div>
    </main>

    <footer>
        &copy; <?php echo date('Y'); ?> ChromaAi. Všetky práva vyhradené.
    </footer>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
