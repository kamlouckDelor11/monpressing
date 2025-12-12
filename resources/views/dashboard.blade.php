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

    <aside class="offcanvas-lg offcanvas-start bg-body-tertiary border-end" tabindex="-1" id="sidebar">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title text-primary fw-bold">🧺 Pressing Manager</h5>
            <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column p-0">
            <nav class="nav flex-column p-3">              
                <a href="{{ route('order') }}" class="nav-link text-secondary" style="color: var(--bs-primary) !important;">➕ Enregistrer un dépôt</a>   
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

            <div class="mt-auto p-3 border-top">
                <form method="POST" action="{{route('logout')}}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100">🚪 Déconnexion</button>
                </form>
            </div>
        </div>
    </aside>

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
                                                'en cours' => 'text-primary',
                                                'prêt' => 'text-success',
                                                'livré' => 'text-secondary',
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
</script>

</body>
</html>