<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | Pressing Manager</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="d-flex">

  <!-- Sidebar -->
  <aside class="offcanvas-lg offcanvas-start bg-body-tertiary border-end" tabindex="-1" id="sidebar">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title text-primary fw-bold">🧺 Pressing Manager</h5>
      <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-0">
      <nav class="nav flex-column p-3">
        <a href="{{ route('order') }}" class="nav-link text-secondary">➕ Enregistrer un dépôt</a>
        <a href="{{ route('clients.index') }}" class="nav-link text-secondary">✅ Gestion des clients</a>
        <a href="{{ route('manager.order') }}" class="nav-link text-secondary">✅ Gestion des dépôts</a>
        <a href="{{ route('articles.index') }}" class="nav-link text-secondary">✅ Gestion des articles</a>
        <a href="{{ route('services.index') }}" class="nav-link text-secondary">✅ Gestion des services</a>
        @if (Auth::User()->role === 'admin')
            <a href="{{ route('manager.gestionnaire') }}" class="nav-link text-secondary">🧑 Gestionnaire</a>
        @endif
        <div class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-secondary" data-bs-toggle="dropdown" href="#">💰 Charges</a>
          <ul class="dropdown-menu">
            @if (Auth::User()->role === 'admin')
            <li><a class="dropdown-item" href="{{ route('manager.payroll.index') }}">👥 Salaire</a></li>
            @endif  
            <li><a class="dropdown-item" href="{{ route('spenses.index') }}">📦 Autres Dépenses</a></li>
          </ul>
        </div>
        @if (Auth::User()->role === 'admin')
            <a href="#" class="nav-link text-secondary">📊 Statistiques</a>
        @endif
        <a href="#" class="nav-link text-secondary">⚙️ Paramètres</a>
      </nav>

      <!-- Logout -->
      <div class="mt-auto p-3 border-top">
        <form method="POST" action="{{route('logout')}}">
          @csrf
          <button type="submit" class="btn btn-outline-danger w-100">🚪 Déconnexion</button>
        </form>
      </div>
    </div>
  </aside>

  <!-- Main Content -->
  <div class="flex-grow-1 d-flex flex-column min-vh-100">
    <!-- Header -->
    <header class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-body shadow-sm">
      <button class="btn btn-outline-secondary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar">☰</button>
      <h2 class="h5 fw-bold text-primary mb-0">Tableau de bord</h2>
      <div class="d-flex align-items-center gap-3">
        <span class="text-secondary">Bonjour, <strong>{{ Auth::User()->name}}</strong></span>
        <button id="toggleDarkMode" class="btn btn-outline-secondary">🌙</button>
      </div>
    </header>

    <!-- Dashboard Content -->
    <main class="flex-grow-1 p-4 bg-body-tertiary">
      <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h6 class="text-secondary">Commandes en cours</h6>
              <p class="h3 text-primary mb-0">42</p>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h6 class="text-secondary">Clients inscrits</h6>
              <p class="h3 text-primary mb-0">128</p>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h6 class="text-secondary">Employés</h6>
              <p class="h3 text-primary mb-0">7</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Latest Orders Table -->
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title mb-3">Dernières commandes</h5>

          <!-- Filtres -->
          <form id="filterForm" class="row g-3 mb-3">
            <div class="col-md-3">
              <input type="text" id="filterClient" class="form-control" placeholder="Filtrer par client">
            </div>
            <div class="col-md-3">
              <select id="filterStatus" class="form-select">
                <option value="">-- Statut --</option>
                <option value="En cours">En cours</option>
                <option value="Prêt">Prêt</option>
                <option value="Livré">Livré</option>
              </select>
            </div>
            <div class="col-md-3">
              <input type="date" id="filterDate" class="form-control">
            </div>
            <div class="col-md-3 d-flex">
              <button type="button" id="resetFilters" class="btn btn-outline-secondary w-100">Réinitialiser</button>
            </div>
          </form>

          <!-- Tableau -->
          <div class="table-responsive">
            <table class="table table-hover align-middle" id="ordersTable">
              <thead class="table-light">
                <tr>
                  <th>Client</th>
                  <th>Article</th>
                  <th>Statut</th>
                  <th>Date dépôt</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Jean Dupont</td>
                  <td>Chemise</td>
                  <td class="text-primary fw-medium">En cours</td>
                  <td>2025-09-10</td>
                </tr>
                <tr>
                  <td>Marie Claire</td>
                  <td>Robe</td>
                  <td class="text-success fw-medium">Prêt</td>
                  <td>2025-09-09</td>
                </tr>
                <tr>
                  <td>Paul Martin</td>
                  <td>Costume</td>
                  <td class="text-secondary fw-medium">Livré</td>
                  <td>2025-09-08</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>

<script>
// Dark Mode
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

// Filtrage commandes
$(document).ready(function(){
  function filterOrders() {
    let client = $("#filterClient").val().toLowerCase();
    let status = $("#filterStatus").val();
    let date = $("#filterDate").val();

    $("#ordersTable tbody tr").each(function(){
      let rowClient = $(this).find("td:nth-child(1)").text().toLowerCase();
      let rowStatus = $(this).find("td:nth-child(3)").text().trim();
      let rowDate = $(this).find("td:nth-child(4)").text().trim();

      let matchClient = client === "" || rowClient.includes(client);
      let matchStatus = status === "" || rowStatus === status;
      let matchDate = date === "" || rowDate === date;

      if(matchClient && matchStatus && matchDate) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
  }

  $("#filterClient, #filterStatus, #filterDate").on("input change", filterOrders);

  $("#resetFilters").on("click", function(){
    $("#filterClient").val("");
    $("#filterStatus").val("");
    $("#filterDate").val("");
    filterOrders();
  });
});
</script>

</body>
</html>
