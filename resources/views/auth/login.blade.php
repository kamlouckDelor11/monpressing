<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Pressing Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0d6efd10 0%, #fff 100%);
        }

        .card {
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            border: 1px solid #6c757d30;
            position: relative; /* Important pour l'overlay du loader */
            overflow: hidden;
        }

        /* --- Style du Loader --- */
        #loaderOverlay {
            display: none; /* Masqué par défaut */
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Fond semi-transparent */
            z-index: 10;
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
<body>
  
{{-- loader --}}
@include('partials.loader')
    
<div class="card p-5 shadow-lg" style="max-width: 400px; width: 100%;">
    
    <div id="loaderOverlay">
        <div id="loader">Chargement<span>.</span><span>.</span><span>.</span></div>
    </div>

    <a href="{{ route('welcome') }}" 
       class="position-absolute top-0 start-0 m-3 text-decoration-none text-primary fs-4">
        ←
    </a>

    <div class="d-flex justify-content-end mb-3">
        <button id="toggleDarkMode" class="btn btn-outline-secondary btn-sm">☀️</button>
    </div>

    <h1 class="text-center mb-2 fw-bold text-primary fs-4">Connexion</h1>

    @if ($errors->any())  
        <div id="loginError" class="alert alert-danger text-center p-2 mb-4" style="font-size: 0.9rem;">
            {{ $errors->first() }}
        </div>
    @endif
  
    <form id="loginForm" method="POST" action="{{route('login.store')}}">
        @csrf
        <div class="mb-3">
            <input type="text" name="phone" placeholder="Numéro de téléphone" required value="{{old('phone')}}"
                   class="form-control form-control-lg">
        </div>
        <div class="mb-4">
            <input type="password" name="password" placeholder="Code de connexion" required
                   class="form-control form-control-lg">
        </div>
        <button type="submit" id="submitBtn" class="btn btn-primary w-100 btn-lg">🔑 Se connecter</button>
    </form>
</div>

<script>
// --- 1. Gestion du Mode Sombre ---
const toggleBtn = document.getElementById("toggleDarkMode");
const html = document.documentElement;

function updateIcon() {
    toggleBtn.textContent = html.getAttribute("data-bs-theme") === "dark" ? "☀️" : "🌙";
}

toggleBtn.addEventListener("click", () => {
    const newTheme = html.getAttribute("data-bs-theme") === "dark" ? "light" : "dark";
    html.setAttribute("data-bs-theme", newTheme);
    localStorage.setItem("theme", newTheme);
    updateIcon();
});

if(localStorage.getItem("theme")) {
    html.setAttribute("data-bs-theme", localStorage.getItem("theme"));
} else {
    html.setAttribute("data-bs-theme", "dark");
}
updateIcon();

// --- 2. Gestion du Loader et de la soumission ---
const loginForm = document.getElementById('loginForm');
const loaderOverlay = document.getElementById('loaderOverlay');
const submitBtn = document.getElementById('submitBtn');

loginForm.addEventListener('submit', function() {
    // Afficher le loader
    loaderOverlay.style.display = 'flex';
    // Désactiver le bouton pour éviter les doubles clics
    submitBtn.disabled = true;
    submitBtn.innerHTML = "Traitement...";
});

// --- 3. Suppression automatique du message d'erreur ---
document.addEventListener("DOMContentLoaded", function () {
    let alertBox = document.getElementById("loginError");
    if (alertBox) {
        // Si une erreur est présente, on s'assure que le loader est bien caché
        loaderOverlay.style.display = 'none';
        
        setTimeout(() => {
            alertBox.style.transition = "opacity 0.5s ease";
            alertBox.style.opacity = "0";
            setTimeout(() => alertBox.remove(), 500);
        }, 5000);
    }
});
</script>

</body>
</html>