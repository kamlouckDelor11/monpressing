<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Pressing Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .kpi-card {
            transition: transform 0.2s;
        }
        .kpi-card:hover {
            transform: translateY(-3px);
        }
        .growth-indicator {
            font-size: 0.9rem;
            font-weight: 500;
        }
        .growth-up { color: var(--bs-success); }
        .growth-down { color: var(--bs-danger); }
        .growth-flat { color: var(--bs-secondary); }

        /* Correction pour la largeur et les liens du sidebar */
        @media (min-width: 992px) { /* Appliquer à lg et plus */
            #sidebar {
                width: 250px; 
                visibility: visible !important;
                transform: none !important;
            }
        }
        .nav-link {
            white-space: nowrap; 
            overflow: hidden;
            text-overflow: ellipsis; 
        }
    </style>
</head>
<body class="d-flex">
    {{-- loader --}}
    @include('partials.loader')
    
    @include('partials.side-bar')

    <div class="flex-grow-1 d-flex flex-column min-vh-100">
        <header class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-body shadow-sm">
            <button class="btn btn-outline-secondary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar">☰</button>
            <h2 class="h5 fw-bold text-primary mb-0">Tableau de bord</h2>
            <div class="d-flex align-items-center gap-3">
                <span class="text-secondary">Bonjour, <strong>{{ Auth::User()->name}}</strong></span>
                <button id="toggleDarkMode" class="btn btn-outline-secondary">🌙</button>
            </div>
        </header>

        <main class="flex-grow-1 p-4 bg-body-tertiary">
            
            {{-- CAS 1 : UTILISATEUR INACTIF --}}
            @if(Auth::user()->status === 'inactive')
                <div class="container d-flex justify-content-center align-items-center" style="min-height: 60vh;">
                    <div class="card shadow-lg border-danger text-center p-5" style="max-width: 600px;">
                        <div class="card-body">
                            <i class="bi bi-exclamation-triangle-fill text-danger display-1 mb-4"></i>
                            <h2 class="card-title text-danger mb-3">Abonnement Expiré</h2>
                            <p class="card-text fs-5 text-secondary mb-4">
                                Votre compte est actuellement <strong>inactif</strong>. Pour continuer à utiliser l'application et accéder à vos données, veuillez renouveler votre abonnement auprès de l'administrateur.
                            </p>
                            <a href="#" class="btn btn-danger btn-lg px-5 disabled">Accès Restreint</a>
                        </div>
                    </div>
                </div>

            {{-- CAS 2 : MANAGER / ADMIN --}}
            @elseif(Auth::user()->role === 'manager')
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Gestionnaire de l'Application</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
                        <i class="bi bi-person-plus"></i> Nouvel Utilisateur
                    </button>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 card-title">Liste des Utilisateurs & Abonnements</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="usersTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                        <th>Statut</th>
                                        <th>Dernière Connexion</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    {{-- Les données seront chargées ici via AJAX --}}
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                            Chargement des utilisateurs...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Note: Le script JS pour charger les utilisateurs doit être ajouté ou adapté ci-dessous --}}
                {{-- Je suppose que vous avez déjà un script qui tape sur la route 'manager.users.index' --}}

            {{-- CAS 3 : EMPLOYÉ / CAISSIER (Dashboard Standard) --}}
            @else

            <h4 class="mb-4">Indicateurs Clés (KPI)</h4>

            <div class="row g-4 mb-5">
                {{-- KPI 1: Dépôts sur 5 jours --}}
                <div class="col-sm-6 col-lg-3">
                    <div class="card shadow-sm kpi-card border-start border-primary border-4">
                        <div class="card-body">
                            <h6 class="text-secondary">Dépôts (5 derniers jours)</h6>
                            <p class="h3 text-primary mb-1">{{ $deposits['current_count'] }}</p>
                            @php
                                $growthRate = $deposits['growth_rate'] ?? 0; // Sécurité
                                $indicatorClass = $growthRate > 0 ? 'growth-up' : ($growthRate < 0 ? 'growth-down' : 'growth-flat');
                                $indicatorIcon = $growthRate > 0 ? 'bi-graph-up' : ($growthRate < 0 ? 'bi-graph-down' : 'bi-arrow-right');
                                $growthText = abs($growthRate) . '%';
                            @endphp
                            <p class="growth-indicator {{ $indicatorClass }} mb-0">
                                <i class="bi {{ $indicatorIcon }}"></i> {{ $growthText }} vs 5j précédents
                            </p>
                        </div>
                    </div>
                </div>

                {{-- KPI 2: Chiffre d'Affaires (CA) sur 5 jours --}}
                <div class="col-sm-6 col-lg-3">
                    <div class="card shadow-sm kpi-card border-start border-success border-4">
                        <div class="card-body">
                            <h6 class="text-secondary">Chiffre d'Affaires (5 derniers jours)</h6>
                            <p class="h3 text-success mb-1">{{ number_format($ca['current_amount'], 0, ',', ' ') }} XAF</p>
                            @php
                                $growthRate = $ca['growth_rate'] ?? 0; // Sécurité
                                $indicatorClass = $growthRate > 0 ? 'growth-up' : ($growthRate < 0 ? 'growth-down' : 'growth-flat');
                                $indicatorIcon = $growthRate > 0 ? 'bi-graph-up' : ($growthRate < 0 ? 'bi-graph-down' : 'bi-arrow-right');
                                $growthText = abs($growthRate) . '%';
                            @endphp
                            <p class="growth-indicator {{ $indicatorClass }} mb-0">
                                <i class="bi {{ $indicatorIcon }}"></i> {{ $growthText }} vs 5j précédents
                            </p>
                        </div>
                    </div>
                </div>

                {{-- KPI 3: Solde de Trésorerie (Encaissements - Décaissements) --}}
                <div class="col-sm-6 col-lg-3">
                    <div class="card shadow-sm kpi-card border-start border-info border-4">
                        <div class="card-body">
                            <h6 class="text-secondary">Solde de Trésorerie (Cumulé)</h6>
                            @php
                                $soldeClass = $treasury['solde'] >= 0 ? 'text-info' : 'text-danger';
                            @endphp
                            <p class="h3 {{ $soldeClass }} mb-1">{{ number_format($treasury['solde'], 0, ',', ' ') }} XAF</p>
                            <p class="growth-indicator text-muted mb-0">
                                <i class="bi bi-wallet2"></i> **{{ number_format($treasury['encaissements'], 0, ',', ' ') }}** E | **{{ number_format($treasury['decaissements'], 0, ',', ' ') }}** D
                            </p>
                        </div>
                    </div>
                </div>

                {{-- KPI 4: Commandes en cours (Non livrées) --}}
                <div class="col-sm-6 col-lg-3">
                    <div class="card shadow-sm kpi-card border-start border-warning border-4">
                        <div class="card-body">
                            <h6 class="text-secondary">Commandes en cours (Non Livrées)</h6>
                            <p class="h3 text-warning mb-1">{{ $pendingOrdersCount }}</p>
                            <p class="growth-indicator text-muted mb-0">
                                <i class="bi bi-box-seam"></i> {{ $readyOrdersCount }} prêtes à être retirées
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Derniers Dépôts</h5>

                    <form id="filterForm" class="row g-3 mb-3">
                        <div class="col-md-3">
                            <input type="text" id="filterClient" class="form-control" placeholder="Filtrer par client...">
                        </div>
                        <div class="col-md-3">
                            <select id="filterStatus" class="form-select">
                                <option value="">-- Statut --</option>
                                <option value="pending">En cours</option>
                                <option value="ready">Prêt</option>
                                <option value="delivrered">Livré</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" id="filterDate" class="form-control">
                        </div>
                        <div class="col-md-3 d-flex">
                            <button type="button" id="resetFilters" class="btn btn-outline-secondary w-100">Réinitialiser</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="ordersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Réf.</th>
                                    <th>Client</th>
                                    <th>Total</th>
                                    <th>Statut</th>
                                    <th>Date dépôt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestOrders as $order)
                                    <tr>
                                        <td>{{ $order->reference }}</td>
                                        <td>{{ $order->client->name ?? 'N/A' }}</td>
                                        <td>{{ number_format($order->total_amount, 0, ',', ' ') }} XAF</td>
                                        @php
                                            $statusClass = [
                                                'pending' => 'text-primary',
                                                'ready' => 'text-success',
                                                'delivered' => 'text-secondary',
                                            ][strtolower($order->delivery_status)] ?? 'text-dark';
                                        @endphp
                                        <td class="{{ $statusClass }} fw-medium">{{ ucfirst($order->delivery_status) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($order->deposit_date)->format('d/m/Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Aucun dépôt récent trouvé.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            @endif {{-- Fin des conditions d'affichage --}}
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

// Filtrage (Front-end pour les lignes déjà chargées)
$(document).ready(function(){
    function filterOrders() {
        let client = $("#filterClient").val().toLowerCase();
        let status = $("#filterStatus").val();
        let date = $("#filterDate").val();

        $("#ordersTable tbody tr").each(function(){
            let rowClient = $(this).find("td:nth-child(2)").text().toLowerCase();
            let rowStatus = $(this).find("td:nth-child(4)").text().trim();
            let rowDate = $(this).find("td:nth-child(5)").text().trim();

            let matchClient = client === "" || rowClient.includes(client);
            let matchStatus = status === "" || rowStatus.toLowerCase() === status.toLowerCase();
            let matchDate = date === "" || rowDate.includes(date.split('-').reverse().join('/'));

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

// Script spécifique pour le Manager (Chargement des utilisateurs)
@if(Auth::user()->role === 'manager')
$(document).ready(function() {
    $.ajax({
        url: "{{ route('manager.users.index') }}",
        type: "GET",
        success: function(response) {
            let rows = '';
            // Adaptez 'response.data' selon la structure exacte de votre retour JSON
            let users = response.data || response; 
            
            if(users.length > 0){
                users.forEach(user => {
                    let statusBadge = user.status === 'active' 
                        ? '<span class="badge bg-success">Actif</span>' 
                        : '<span class="badge bg-danger">Inactif</span>';
                    
                    rows += `<tr>
                        <td>${user.name}</td>
                        <td>${user.email}</td>
                        <td><span class="badge bg-secondary">${user.role}</span></td>
                        <td>${statusBadge}</td>
                        <td>${user.last_login_at || '-'}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1" onclick="editUser('${user.token}')">Éditer</button>
                            <button class="btn btn-sm btn-outline-warning" onclick="resetPassword('${user.token}')">Reset MDP</button>
                        </td>
                    </tr>`;
                });
                $('#usersTableBody').html(rows);
            } else {
                $('#usersTableBody').html('<tr><td colspan="6" class="text-center">Aucun utilisateur trouvé.</td></tr>');
            }
        },
        error: function() {
            $('#usersTableBody').html('<tr><td colspan="6" class="text-center text-danger">Erreur lors du chargement.</td></tr>');
        }
    });
});

// Fonctions placeholders pour les actions (à connecter avec vos modales existantes)
function editUser(token) {
    // Logique pour ouvrir la modale d'édition
    console.log("Edit user", token);
    // Exemple: $('#editUserModal').modal('show');
}
function resetPassword(token) {
    if(confirm("Réinitialiser le mot de passe de cet utilisateur ?")) {
        // Appel AJAX pour reset
        console.log("Reset password", token);
    }
}
@endif
</script>

</body>
</html>