<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil | Pressing Manager</title>

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0d6efd">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        /* Configuration de la carte pour le loader absolu */
        .card {
            position: relative;
            overflow: hidden;
        }

        /* Nouveau Style du Loader (Identique à la page Login) */
        #loaderOverlay {
            display: none; /* Masqué par défaut */
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6); /* Fond semi-transparent */
            z-index: 100;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(2px);
        }

        #loader {
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: bold;
            text-align: center;
            color: white;
            font-size: 1.2rem;
        }

        #loader span {
            display: inline-block;
            animation: blink 1.5s infinite;
        }

        #loader span:nth-child(2) { animation-delay: 0.2s; }
        #loader span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes blink {
            0%, 100% { opacity: 0.2; }
            50% { opacity: 1; }
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 bg-body-tertiary">
    {{-- loader --}}
    @include('partials.loader')

    <div class="card shadow-lg rounded-4 border border-secondary p-5 text-center" style="max-width: 500px; width: 100%; backdrop-filter: blur(10px);">
        
        <div id="loaderOverlay">
            <div id="loader">Chargement<span>.</span><span>.</span><span>.</span></div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <button id="pwaInstallBtn" class="btn btn-info btn-sm fw-bold shadow-sm" style="display: none;">
                📲 Installer Pressing Manager
            </button>
            
            <button id="toggleDarkMode" class="btn btn-outline-secondary btn-sm" type="button">☀️</button>
        </div>

        <div class="mb-4">
            <img src="{{ asset('icons/icon-512x512.png') }}" alt="Logo Pressing Manager" width="96" height="96" class="img-fluid rounded-4 shadow-sm">
        </div>

        <h1 class="display-6 fw-bold text-primary mb-3">
            Bienvenue sur <span class="text-info">Pressing Manager</span>
        </h1>

        <p class="text-secondary mb-4">
            Gérez facilement vos clients, dépôts et vêtements grâce à une plateforme simple, moderne et rapide.
        </p>

        <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mb-3">
            <a href="{{ route('create') }}" class="btn btn-outline-primary px-4 py-2 fw-medium btn-load">
                Créer mon Pressing
            </a>
            <a href="{{ route('login') }}" class="btn btn-outline-primary px-4 py-2 fw-medium btn-load">
                Se Connecter
            </a>
        </div>
    </div>

    <script>
        const loaderOverlay = document.getElementById('loaderOverlay');

        // Fonction pour afficher le loader (modifiée pour display: flex)
        function showLoader() {
            loaderOverlay.style.display = 'flex';
        }

        // --- 1. Gestion du Mode Sombre ---
        const toggleBtn = document.getElementById("toggleDarkMode");
        const html = document.documentElement;

        function updateIcon() {
            const currentTheme = html.getAttribute("data-bs-theme");
            toggleBtn.textContent = currentTheme === "light" ? "🌙" : "☀️";
        }

        const savedTheme = localStorage.getItem("theme") || "dark";
        html.setAttribute("data-bs-theme", savedTheme);
        updateIcon();

        toggleBtn.addEventListener("click", () => {
            const newTheme = html.getAttribute("data-bs-theme") === "light" ? "dark" : "light";
            html.setAttribute("data-bs-theme", newTheme);
            localStorage.setItem("theme", newTheme);
            updateIcon();
        });

        // --- 2. Logique PWA ---
        let deferredPrompt;
        const pwaBtn = document.getElementById('pwaInstallBtn');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            pwaBtn.style.display = 'block';
        });

        pwaBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    showLoader();
                    pwaBtn.style.display = 'none';
                }
                deferredPrompt = null;
            }
        });

        // --- 3. Gestion du Loader sur les liens ---
        document.querySelectorAll('.btn-load').forEach(btn => {
            btn.addEventListener('click', (e) => {
                showLoader();
            });
        });

        // --- 4. Service Worker ---
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register("{{ asset('sw.js') }}")
                    .then(reg => console.log('PWA : Service Worker prêt !'))
                    .catch(err => console.error('PWA : Erreur :', err));
            });
        }
    </script>
</body>
</html>