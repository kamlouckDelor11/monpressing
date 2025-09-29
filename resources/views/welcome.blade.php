<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accueil | Pressing Manager</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> --}}
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 bg-body-tertiary">

  <div class="card shadow-lg rounded-4 border border-secondary p-5 text-center" style="max-width: 500px; backdrop-filter: blur(10px);">
    <!-- Dark/Light Toggle -->
   <div class="d-flex justify-content-end mb-3">
    <button id="toggleDarkMode" class="btn btn-outline-secondary btn-sm">☀️</button>
  </div>
    <!-- Logo -->
    <div class="mb-4">
      <svg class="bi bi-list" xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="text-primary" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M2.5 12.5a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-11zm0-4a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-11zm0-4a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-11z"/>
      </svg>
    </div>

    <!-- Titre -->
    <h1 class="display-6 fw-bold text-primary mb-3">
      Bienvenue sur <span class="text-info">Pressing Manager</span>
    </h1>

    <!-- Description -->
    <p class="text-secondary mb-4">
      Gérez facilement vos clients, commandes et vêtements grâce à une plateforme simple, moderne et rapide.
    </p>

    <!-- Boutons -->
    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mb-3">
      <a href="{{ route('create') }}" class="btn btn-outline-primary px-4 py-2 fw-medium">
        Créer mon Pressing
      </a>
      <a href="{{ route('login') }}" class="btn btn-outline-primary px-4 py-2 fw-medium">
        Se Connecter
      </a>
    </div>
  </div>

<script>
// Dark/Light Mode avec icône dynamique
const toggleBtn = document.getElementById("toggleDarkMode");
const html = document.documentElement;

function updateIcon() {
  toggleBtn.textContent = html.getAttribute("data-bs-theme") === "light" ? "🌙" : "☀️";
}

if (localStorage.getItem("theme")) {
  html.setAttribute("data-bs-theme", localStorage.getItem("theme"));
}
updateIcon();

toggleBtn.addEventListener("click", () => {
  const newTheme = html.getAttribute("data-bs-theme") === "light" ? "dark" : "light";
  html.setAttribute("data-bs-theme", newTheme);
  localStorage.setItem("theme", newTheme);
  updateIcon();
});
</script>

</body>
</html>
