<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion | Pressing Manager</title>
   @vite(['resources/css/app.css', 'resources/js/app.js'])
  {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> --}}
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
    }
  </style>
</head>
<body>

<div class="card p-5 shadow-lg" style="max-width: 400px; width: 100%;">
  <!-- Flèche de retour -->
  <a href="{{ route('welcome') }}" 
     class="position-absolute top-0 start-0 m-3 text-decoration-none text-primary fs-4">
     ←
  </a>
  <!-- Toggle Dark/Light Mode -->
  <div class="d-flex justify-content-end mb-3">
    <button id="toggleDarkMode" class="btn btn-outline-secondary btn-sm">☀️</button>
  </div>
{{-- 
  <!-- Logo -->
  <div class="text-center mb-4">
    <svg class="mx-auto w-14 h-14 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M3 6h18M3 12h18M3 18h18"></path>
    </svg>
  </div> --}}

  <!-- Titre -->
  <h1 class="text-center mb-2 fw-bold text-primary fs-4">Connexion</h1>
  @if ($errors->any())  
        <p id= "loginError" class="text-center text-muted mb-4 alert alert-danger show">{{ $errors->first() }}</p>
  @endif
  
  <!-- Formulaire -->
  <form method="POST" action="{{route('login.store')}}">
    @csrf
    <div class="mb-3">
      <input type="text" name="phone" placeholder="Numéro de téléphone" required value="{{old('phone')}}"
             class="form-control form-control-lg">
    </div>
    <div class="mb-4">
      <input type="password" name="password" placeholder="Code de connexion" required
             class="form-control form-control-lg">
    </div>
    <button type="submit" class="btn btn-primary w-100 btn-lg">🔑 Se connecter</button>
  </form>

  <!-- Lien inscription (optionnel) -->
  {{-- <p class="mt-3 text-center text-muted">
    Pas encore de compte ? <a href="{{ route('create') }}" class="text-primary fw-medium">Créer un compte</a>
  </p> --}}
</div>

<script>
// Dark/Light Mode
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

// Appliquer le thème enregistré ou dark par défaut
if(localStorage.getItem("theme")) {
  html.setAttribute("data-bs-theme", localStorage.getItem("theme"));
} else {
  html.setAttribute("data-bs-theme", "dark");
}
updateIcon();

//supprimer le message d'erreur de connexion
document.addEventListener("DOMContentLoaded", function () {
    let alertBox = document.getElementById("loginError");
    if (alertBox) {
        setTimeout(() => {
            alertBox.classList.add("fade");  // animation Bootstrap
            alertBox.classList.remove("show");
            setTimeout(() => alertBox.remove(), 500); // supprime du DOM après animation
        }, 5000); // 5 secondes
    }
});

</script>

</body>
</html>
