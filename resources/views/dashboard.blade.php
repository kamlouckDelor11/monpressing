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
        .kpi-card { transition: transform 0.2s; }
        .kpi-card:hover { transform: translateY(-3px); }
        .growth-indicator { font-size: 0.9rem; font-weight: 500; }
        .growth-up { color: var(--bs-success); }
        .growth-down { color: var(--bs-danger); }
        .growth-flat { color: var(--bs-secondary); }

        @media (min-width: 992px) { 
            #sidebar { width: 250px; visibility: visible !important; transform: none !important; }
        }
        .nav-link { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .accordion-button::after { 
            background-image: none !important; 
            content: '+'; 
            font-size: 1.5rem; 
            font-weight: bold;
            transform: none !important; 
            transition: none;
        }
        .accordion-button:not(.collapsed)::after { content: '−'; }
        .accordion-button:focus { box-shadow: none; }
        .accordion-item { border: none; margin-bottom: 1rem; border-radius: 8px !important; overflow: hidden; }
        
        .search-box { position: relative; }
        .search-box i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #6c757d; }
        .search-box input { padding-left: 35px; }

        .spinner-btn { display: none; margin-right: 5px; }
    </style>
</head>
<body class="d-flex">
    @include('partials.loader')
    @include('partials.side-bar')

    <div class="flex-grow-1 d-flex flex-column min-vh-100">
        <header class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-body shadow-sm">
            <button class="btn btn-outline-secondary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar">☰</button>
            <h2 class="h5 fw-bold text-primary mb-0">
                @if(Auth::user()->role === 'manager') Interface Manager Système @else Tableau de bord @endif
            </h2>
            <div class="d-flex align-items-center gap-3">
                <span class="text-secondary d-none d-md-inline">Bonjour, <strong>{{ Auth::User()->name }}</strong></span>
                <button id="toggleDarkMode" class="btn btn-outline-secondary">🌙</button>
            </div>
        </header>

        <main class="flex-grow-1 p-4 bg-body-tertiary">
            <div id="globalAlert" class="alert alert-dismissible fade show d-none" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <span id="alertMessage"></span>
                <button type="button" class="btn-close" onclick="$('#globalAlert').addClass('d-none')"></button>
            </div>

            @if(Auth::user()->status === 'inactive')
                <div class="container d-flex justify-content-center align-items-center" style="min-height: 60vh;">
                    <div class="card shadow-lg border-danger text-center p-5" style="max-width: 600px;">
                        <div class="card-body">
                            <i class="bi bi-exclamation-triangle-fill text-danger display-1 mb-4"></i>
                            <h2 class="card-title text-danger mb-3">Abonnement Expiré</h2>
                            <p class="card-text fs-5 text-secondary mb-4">Votre compte est inactif. Contactez l'administrateur.</p>
                        </div>
                    </div>
                </div>

            @elseif(Auth::user()->role === 'manager')
                <h4 class="mb-4">Statistiques du Réseau</h4>
                <div class="row g-4 mb-5">
                    <div class="col-md-4"><div class="card kpi-card shadow-sm border-start border-primary border-4"><div class="card-body"><h6 class="text-secondary small">Total Pressings</h6><p class="h3 mb-0" id="stat-total">--</p></div></div></div>
                    <div class="col-md-4"><div class="card kpi-card shadow-sm border-start border-success border-4"><div class="card-body"><h6 class="text-secondary small">Abonnements Actifs</h6><p class="h3 mb-0 text-success" id="stat-active">--</p></div></div></div>
                    <div class="col-md-4"><div class="card kpi-card shadow-sm border-start border-danger border-4"><div class="card-body"><h6 class="text-secondary small">Pressings Inactifs</h6><p class="h3 mb-0 text-danger" id="stat-inactive">--</p></div></div></div>
                </div>

                <div class="accordion" id="managerAccordion">
                    {{-- SECTION 1 : PRESSINGS --}}
                    <div class="accordion-item shadow-sm border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button bg-body text-primary fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAbonnements">
                                <i class="bi bi-card-checklist me-2"></i> Gestion des Pressings & Abonnements
                            </button>
                        </h2>
                        <div id="collapseAbonnements" class="accordion-collapse collapse show" data-bs-parent="#managerAccordion">
                            <div class="accordion-body p-0">
                                <div class="p-3 border-bottom d-flex justify-content-end">
                                    <select id="filterPlan" class="form-select form-select-sm" style="width: 200px;">
                                        <option value="">Tous les statuts</option>
                                        <option value="basic">Plan Actif (Basic)</option>
                                        <option value="inactive">Inactif</option>
                                    </select>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light"><tr><th>Pressing</th><th>Plan</th><th>Expire le</th><th class="text-end px-4">Action</th></tr></thead>
                                        <tbody id="pressingsTableBody"></tbody>
                                    </table>
                                </div>
                                <div id="paginationNav" class="py-3 d-flex justify-content-center"></div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 2 : UTILISATEURS --}}
                    <div class="accordion-item shadow-sm border-0 mt-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-body text-primary fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePasswords">
                                <i class="bi bi-shield-lock me-2"></i> Sécurité & Comptes Utilisateurs
                            </button>
                        </h2>
                        <div id="collapsePasswords" class="accordion-collapse collapse" data-bs-parent="#managerAccordion">
                            <div class="accordion-body p-0">
                                <div class="p-3 border-bottom">
                                    <div class="search-box" style="max-width: 300px;">
                                        <i class="bi bi-search"></i>
                                        <input type="text" id="filterUserName" class="form-control form-control-sm" placeholder="Rechercher un nom ou email...">
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light"><tr><th>Utilisateur</th><th>Email</th><th>Pressing</th><th class="text-end px-4">Action</th></tr></thead>
                                        <tbody id="usersTableBody"></tbody>
                                    </table>
                                </div>
                                <div id="paginationUsers" class="py-3 d-flex justify-content-center"></div>
                            </div>
                        </div>
                    </div>
                </div>

            @else
                {{-- Contenu Employé --}}
                <h4 class="mb-4">Indicateurs Clés (KPI)</h4>
                <div class="row g-4 mb-5">
                    {{-- KPI 1: Dépôts --}}
                    <div class="col-sm-6 col-lg-3">
                        <div class="card shadow-sm kpi-card border-start border-primary border-4">
                            <div class="card-body">
                                <h6 class="text-secondary">Dépôts (5 derniers jours)</h6>
                                <p class="h3 text-primary mb-1">{{ $deposits['current_count'] }}</p>
                                <p class="growth-indicator {{ ($deposits['growth_rate'] ?? 0) >= 0 ? 'growth-up' : 'growth-down' }} mb-0">
                                    <i class="bi {{ ($deposits['growth_rate'] ?? 0) >= 0 ? 'bi-graph-up' : 'bi-graph-down' }}"></i> {{ abs($deposits['growth_rate'] ?? 0) }}% vs 5j précédents
                                </p>
                            </div>
                        </div>
                    </div>
                    {{-- KPI 2: CA --}}
                    <div class="col-sm-6 col-lg-3">
                        <div class="card shadow-sm kpi-card border-start border-success border-4">
                            <div class="card-body">
                                <h6 class="text-secondary">Chiffre d'Affaires (5j)</h6>
                                <p class="h3 text-success mb-1">{{ number_format($ca['current_amount'], 0, ',', ' ') }} XAF</p>
                                <p class="growth-indicator {{ ($ca['growth_rate'] ?? 0) >= 0 ? 'growth-up' : 'growth-down' }} mb-0">
                                    <i class="bi {{ ($ca['growth_rate'] ?? 0) >= 0 ? 'bi-graph-up' : 'bi-graph-down' }}"></i> {{ abs($ca['growth_rate'] ?? 0) }}% vs 5j précédents
                                </p>
                            </div>
                        </div>
                    </div>
                    {{-- KPI 3: Trésorerie --}}
                    <div class="col-sm-6 col-lg-3">
                        <div class="card shadow-sm kpi-card border-start border-info border-4">
                            <div class="card-body">
                                <h6 class="text-secondary">Solde Trésorerie</h6>
                                <p class="h3 {{ $treasury['solde'] >= 0 ? 'text-info' : 'text-danger' }} mb-1">{{ number_format($treasury['solde'], 0, ',', ' ') }} XAF</p>
                                <p class="growth-indicator text-muted mb-0 small">
                                    E: {{ number_format($treasury['encaissements'], 0, ',', ' ') }} | D: {{ number_format($treasury['decaissements'], 0, ',', ' ') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    {{-- KPI 4: Commandes --}}
                    <div class="col-sm-6 col-lg-3">
                        <div class="card shadow-sm kpi-card border-start border-warning border-4">
                            <div class="card-body">
                                <h6 class="text-secondary">En cours / Prêtes</h6>
                                <p class="h3 text-warning mb-1">{{ $pendingOrdersCount }}</p>
                                <p class="growth-indicator text-muted mb-0">
                                    <i class="bi bi-check2-circle"></i> {{ $readyOrdersCount }} prêtes
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Derniers Dépôts</h5>
                        <div class="row g-3 mb-3">
                            <div class="col-md-3"><input type="text" id="filterClient" class="form-control" placeholder="Nom du client..."></div>
                            <div class="col-md-3">
                                <select id="filterStatus" class="form-select">
                                    <option value="">-- Tous les statuts --</option>
                                    <option value="pending">Pending</option>
                                    <option value="ready">Ready</option>
                                    <option value="delivered">Delivered</option>
                                </select>
                            </div>
                            <div class="col-md-3"><input type="date" id="filterDate" class="form-control"></div>
                            <div class="col-md-3"><button type="button" id="resetFilters" class="btn btn-outline-secondary w-100">Réinitialiser</button></div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="ordersTable">
                                <thead class="table-light">
                                    <tr><th>Réf.</th><th>Client</th><th>Total</th><th>Statut</th><th>Date dépôt</th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($latestOrders as $order)
                                        <tr class="order-row" 
                                            data-client="{{ strtolower($order->client->name ?? '') }}" 
                                            data-status="{{ strtolower($order->delivery_status) }}" 
                                            data-date="{{ $order->deposit_date }}">
                                            <td>{{ $order->reference }}</td>
                                            <td>{{ $order->client->name ?? 'N/A' }}</td>
                                            <td>{{ number_format($order->total_amount, 0, ',', ' ') }} XAF</td>
                                            <td class="fw-medium {{ [
                                                'pending' => 'text-primary',
                                                'ready' => 'text-success',
                                                'delivered' => 'text-secondary'
                                            ][strtolower($order->delivery_status)] ?? 'text-dark' }}">
                                                {{ ucfirst($order->delivery_status) }}
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($order->deposit_date)->format('d/m/Y') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center">Aucun dépôt trouvé.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </main>
    </div>

    @if(Auth::user()->role === 'manager')
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0"><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body text-center p-4">
                    <h4 id="modalPressingName" class="mb-4">--</h4>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="openRenewal()">Renouveler Abonnement</button>
                        <button class="btn btn-outline-danger" onclick="confirmDeactivation()">Désactiver le compte</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="renewalModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <form id="renewalForm">
                    <div class="modal-body p-4">
                        <h6 class="fw-bold mb-3">Renouvellement</h6>
                        <input type="hidden" id="renewalToken">
                        <div class="mb-3">
                            <label class="small mb-1">Date de début</label>
                            <input type="date" id="last_subscription_at" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="small mb-1">Durée (Mois)</label>
                            <input type="number" id="duration" class="form-control" value="1" min="1">
                        </div>
                        <button type="submit" class="btn btn-success w-100" id="btnSubmitRenewal">
                            <span class="spinner-border spinner-border-sm spinner-btn"></span> Valider
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="userPasswordModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header"><h6 class="modal-title">Reset Password</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <form id="userPasswordForm">
                    <div class="modal-body p-4">
                        <input type="hidden" id="resetUserToken">
                        <p class="small text-muted mb-3">Utilisateur : <strong id="resetUserNameDisplay"></strong></p>
                        <div class="mb-3">
                            <label class="small mb-1">Nouveau mot de passe</label>
                            <input type="password" id="newUserPassword" class="form-control" required minlength="6">
                        </div>
                        <button type="submit" class="btn btn-warning w-100" id="btnSubmitReset">
                            <span class="spinner-border spinner-border-sm spinner-btn"></span> Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <script>
    // --- LOGIQUE COMMUNE ---
    const html = document.documentElement;
    const themeBtn = document.getElementById("toggleDarkMode");
    if(themeBtn) {
        themeBtn.addEventListener("click", () => {
            const newTheme = html.getAttribute("data-bs-theme") === "light" ? "dark" : "light";
            html.setAttribute("data-bs-theme", newTheme);
            localStorage.setItem("theme", newTheme);
        });
    }

    function showAlert(msg, type = 'success') {
        const alertDiv = $('#globalAlert');
        alertDiv.removeClass('d-none alert-success alert-danger').addClass('alert-' + type);
        $('#alertMessage').text(msg);
        setTimeout(() => alertDiv.addClass('d-none'), 5000);
    }

    // --- LOGIQUE MANAGER ---
    @if(Auth::user()->role === 'manager')
    let selectedPressingToken = null;

    function toggleLoader(btnId, show) {
        const btn = $(btnId);
        btn.prop('disabled', show);
        show ? btn.find('.spinner-btn').show() : btn.find('.spinner-btn').hide();
    }

    function debounce(func, timeout = 300){
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => { func.apply(this, args); }, timeout);
        };
    }

    function loadPressings(page = 1) {
        $.ajax({
            url: "{{ route('manager.api.pressings') }}",
            data: { page: page, plan: $('#filterPlan').val() },
            success: function(res) {
                $('#stat-total').text(res.total || 0);
                $('#stat-active').text(res.active_count || 0);
                $('#stat-inactive').text(res.inactive_count || 0);
                let rows = '';
                if(res.data && res.data.length > 0) {
                    res.data.forEach(p => {
                        rows += `<tr>
                            <td><strong>${p.name}</strong><br><small class="text-muted">${p.token}</small></td>
                            <td><span class="badge ${p.subscription_plan === 'basic' ? 'bg-success' : 'bg-danger'}">${p.subscription_plan}</span></td>
                            <td>${p.subscription_expires_at || '-'}</td>
                            <td class="text-end px-4"><button class="btn btn-sm btn-light border shadow-sm" onclick="openDetail('${p.token}', '${p.name}')">Gérer</button></td>
                        </tr>`;
                    });
                } else {
                    rows = '<tr><td colspan="4" class="text-center">Aucun pressing trouvé</td></tr>';
                }
                $('#pressingsTableBody').html(rows);
                renderPagination(res, 'loadPressings', '#paginationNav');
            }
        });
    }

    function loadUsers(page = 1) {
        $.ajax({
            url: "{{ route('manager.api.users') }}",
            data: { page: page, name: $('#filterUserName').val() },
            success: function(res) {
                let rows = '';
                if(res.data && res.data.length > 0) {
                    res.data.forEach(u => {
                        rows += `<tr>
                            <td><strong>${u.name}</strong></td>
                            <td>${u.email}</td>
                            <td><small class="badge bg-secondary">${u.pressing ? u.pressing.name : 'SYSTÈME'}</small></td>
                            <td class="text-end px-4">
                                <button class="btn btn-sm btn-outline-warning" onclick="openResetPasswordModal('${u.token}', '${u.name}')">
                                    <i class="bi bi-key"></i> Reset
                                </button>
                            </td>
                        </tr>`;
                    });
                } else {
                    rows = '<tr><td colspan="4" class="text-center">Aucun utilisateur trouvé</td></tr>';
                }
                $('#usersTableBody').html(rows);
                renderPagination(res, 'loadUsers', '#paginationUsers');
            }
        });
    }

    function renderPagination(res, functionName, targetDiv) {
        let html = '';
        if (res.last_page > 1) {
            for (let i = 1; i <= res.last_page; i++) {
                html += `<button class="btn btn-sm mx-1 ${i === res.current_page ? 'btn-primary' : 'btn-outline-secondary'}" onclick="${functionName}(${i})">${i}</button>`;
            }
        }
        $(targetDiv).html(html);
    }

    function openDetail(token, name) {
        selectedPressingToken = token;
        $('#modalPressingName').text(name);
        $('#detailModal').modal('show');
    }

    function openRenewal() {
        $('#renewalToken').val(selectedPressingToken);
        $('#detailModal').modal('hide');
        $('#renewalModal').modal('show');
    }

    function openResetPasswordModal(token, name) {
        $('#resetUserToken').val(token);
        $('#resetUserNameDisplay').text(name);
        $('#userPasswordModal').modal('show');
    }

    function updatePressing(data, modalToClose) {
        $.ajax({
            url: "{{ route('manager.api.pressings.update') }}",
            method: 'POST',
            data: { ...data, _token: "{{ csrf_token() }}" },
            success: function() {
                $(modalToClose).modal('hide');
                showAlert('Mise à jour effectuée !');
                loadPressings(1);
            },
            error: () => showAlert('Erreur lors de la mise à jour', 'danger'),
            complete: () => toggleLoader('#btnSubmitRenewal', false)
        });
    }

    function confirmDeactivation() {
        if (confirm("Voulez-vous vraiment désactiver ce pressing ?")) {
            updatePressing({ token: selectedPressingToken, plan: 'inactive' }, '#detailModal');
        }
    }

    $(document).ready(function() {
        loadPressings();
        loadUsers();

        // Fix des filtres
        $('#filterPlan').on('change', () => loadPressings(1));
        $('#filterUserName').on('keyup', debounce(() => loadUsers(1)));

        $('#renewalForm').on('submit', function(e) {
            e.preventDefault();
            toggleLoader('#btnSubmitRenewal', true);
            updatePressing({
                token: $('#renewalToken').val(),
                plan: 'basic',
                last_subscription_at: $('#last_subscription_at').val(),
                duration: $('#duration').val()
            }, '#renewalModal');
        });

        $('#userPasswordForm').on('submit', function(e) {
            e.preventDefault();
            toggleLoader('#btnSubmitReset', true);
            $.ajax({
                url: "{{ route('manager.api.users.reset-password') }}",
                method: 'POST',
                data: {
                    user_token: $('#resetUserToken').val(),
                    password: $('#newUserPassword').val(),
                    _token: "{{ csrf_token() }}"
                },
                success: function() {
                    $('#userPasswordModal').modal('hide');
                    showAlert('Mot de passe mis à jour !');
                    $('#newUserPassword').val('');
                },
                error: () => showAlert('Erreur serveur', 'danger'),
                complete: () => toggleLoader('#btnSubmitReset', false)
            });
        });
    });
    @else
    // --- LOGIQUE EMPLOYÉ (Filtres locaux) ---
    $(document).ready(function() {
        function filterOrders() {
            const client = $('#filterClient').val().toLowerCase();
            const status = $('#filterStatus').val().toLowerCase();
            const date = $('#filterDate').val();

            $('.order-row').each(function() {
                const rowClient = $(this).data('client');
                const rowStatus = $(this).data('status');
                const rowDate = $(this).data('date');

                const matchClient = rowClient.includes(client);
                const matchStatus = status === "" || rowStatus === status;
                const matchDate = date === "" || rowDate.includes(date);

                $(this).toggle(matchClient && matchStatus && matchDate);
            });
        }

        $('#filterClient, #filterStatus, #filterDate').on('input change', filterOrders);
        $('#resetFilters').click(function() {
            $('#filterClient, #filterDate').val('');
            $('#filterStatus').val('');
            $('.order-row').show();
        });
    });
    @endif
    </script>
</body>
</html>