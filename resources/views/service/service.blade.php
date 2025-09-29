<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Services | Pressing Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .services-table-grid {
            display: grid;
            grid-template-rows: auto 1fr;
            height: 100%;
        }
        .services-table-body-scrollable {
            max-height: 400px;
            overflow-y: auto;
        }
        .services-table-body-scrollable table {
            width: 100%;
        }
        .services-table-grid thead {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: var(--bs-body-bg);
        }
        .actions-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        @media (min-width: 576px) {
            .actions-buttons {
                flex-direction: row;
            }
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
                <a href="{{ route('dashboard') }}" class="nav-link text-secondary">🏠 Tableau de bord</a>
                <a href="{{ route('order') }}" class="nav-link text-secondary">➕ Enregistrer un dépôt</a>
                <a href="{{ route('clients.index') }}" class="nav-link text-secondary">👤 Gérer les clients</a>
                <a href="{{ route('articles.index') }}" class="nav-link text-secondary">👔 Gérer les articles</a>
                <a href="{{ route('services.index') }}" class="nav-link text-secondary">🪣 Gérer les services</a>
                @if (Auth::User()->role === 'admin')
                    <a href="{{ route('manager.gestionnaire') }}" class="nav-link text-secondary">🧑‍💼 Ajouter un gestionnaire</a>
                @endif
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-secondary" data-bs-toggle="dropdown" href="#">💰 Charges</a>
                    <ul class="dropdown-menu">
                        @if (Auth::User()->role === 'admin')
                            <li><a class="dropdown-item" href="#">👥 Salaire</a></li>
                        @endif
                        <li><a class="dropdown-item" href="#">📦 Autres Dépenses</a></li>
                    </ul>
                </div>
                @if (Auth::User()->role === 'admin')
                    <a href="#" class="nav-link text-secondary">📊 Statistiques</a>
                @endif
                <a href="#" class="nav-link text-secondary">⚙️ Paramètres</a>
            </nav>
            <div class="mt-auto p-3 border-top">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100">🚪 Déconnexion</button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex-grow-1 d-flex flex-column min-vh-100">
        <header class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-body shadow-sm">
            <button class="btn btn-outline-secondary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar">☰</button>
            <h2 class="h5 fw-bold text-primary mb-0">Gestion des services</h2>
            <div class="d-flex align-items-center gap-3">
                <span class="text-secondary">Bonjour, <strong>{{ Auth::User()->name }}</strong></span>
                <button id="toggleDarkMode" class="btn btn-outline-secondary">🌙</button>
            </div>
        </header>

        <main class="flex-grow-1 p-4 bg-body-tertiary">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">Liste des services</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#serviceModal">
                    <i class="bi bi-gear-fill"></i> Ajouter un service
                </button>
            </div>

            <div class="mb-4 d-flex flex-column flex-md-row gap-3">
                <input type="text" id="nameSearch" class="form-control" placeholder="Rechercher par nom...">
                <button id="resetFiltersBtn" class="btn btn-outline-secondary">Réinitialiser</button>
            </div>

            <div class="card shadow-sm">
                <div class="card-body services-table-grid">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Désignation</th>
                                <th>Prix</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                    <div class="services-table-body-scrollable">
                        <table class="table table-hover align-middle mb-0">
                            <tbody id="services-table-body">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="serviceModalLabel">Ajouter un service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="serviceForm">
                        @csrf
                        <input type="hidden" name="_method" value="POST">
                        <input type="hidden" name="token" id="serviceToken">
                        <div class="mb-3">
                            <label for="serviceName" class="form-label">Désignation</label>
                            <input type="text" class="form-control" name="name" id="serviceName" required>
                        </div>
                        <div class="mb-3">
                            <label for="servicePrice" class="form-label">Prix (facultatif)</label>
                            <input type="number" step="0.01" class="form-control" name="price" id="servicePrice">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" form="serviceForm" class="btn btn-primary">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">Détails du service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Désignation :</strong> <span id="viewName"></span></p>
                    <p><strong>Prix :</strong> <span id="viewPrice"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="notificationMessage"></p>
                </div>
            </div>
        </div>
    </div>

    <div id="loader" class="modal-backdrop d-none position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" style="background-color: rgba(0,0,0,0.5); z-index: 2000;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Chargement...</span>
        </div>
    </div>
    
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

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

        function showNotification(message, isSuccess = true) {
            const modal = $('#notificationModal');
            const modalTitle = $('#notificationModalLabel');
            const modalMessage = $('#notificationMessage');
            
            modalTitle.text(isSuccess ? 'Succès' : 'Erreur');
            modalMessage.text(message);
            
            modal.modal('show');
            setTimeout(() => {
                modal.modal('hide');
            }, 5000);
        }

        function showLoader() {
            $('#loader').removeClass('d-none');
        }

        function hideLoader() {
            $('#loader').addClass('d-none');
        }

        function loadServices() {
            const nameSearch = $('#nameSearch').val();
            const url = `{{ route('services.index') }}?name=${nameSearch}`;

            showLoader();
            $.get(url, function(data) {
                const tbody = $('#services-table-body');
                tbody.empty();
                if (data.length > 0) {
                    data.forEach(service => {
                        const row = `
                            <tr>
                                <td>${service.name}</td>
                                <td>${service.price ? service.price + ' FCFA' : 'Non défini'}</td>
                                <td class="actions-buttons">
                                    <button class="btn btn-sm btn-info view-btn" data-bs-toggle="modal" data-bs-target="#viewModal" data-service-name="${service.name}" data-service-price="${service.price}">Afficher</button>
                                    <button class="btn btn-sm btn-warning edit-btn" data-bs-toggle="modal" data-bs-target="#serviceModal" data-service-token="${service.token}" data-service-name="${service.name}" data-service-price="${service.price}">Modifier</button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-service-token="${service.token}">Supprimer</button>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    tbody.append('<tr><td colspan="3" class="text-center">Aucun service trouvé.</td></tr>');
                }
            }).always(function() {
                hideLoader();
            });
        }

        $('#nameSearch').on('input', function() {
            loadServices();
        });

        $('#resetFiltersBtn').on('click', function() {
            $('#nameSearch').val('');
            loadServices();
        });

        $('#serviceForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const serviceToken = $('#serviceToken').val();
            const url = serviceToken ? `/services/${serviceToken}` : "{{ route('services.store') }}";
            const method = form.find('input[name="_method"]').val();

            showLoader();
            $.ajax({
                url: url,
                method: method,
                data: form.serialize(),
                success: function(response) {
                    $('#serviceModal').modal('hide');
                    showNotification(response.message || 'Opération réussie !', true);
                    loadServices();
                },
                error: function(response) {
                    const errorMessage = response.responseJSON.message || 'Une erreur est survenue.';
                    showNotification(errorMessage, false);
                }
            }).always(function() {
                hideLoader();
            });
        });

        $(document).on('click', '.edit-btn', function() {
            const serviceToken = $(this).data('service-token');
            const serviceName = $(this).data('service-name');
            const servicePrice = $(this).data('service-price');
            
            $('#serviceModalLabel').text("Modifier le service");
            $('#serviceToken').val(serviceToken);
            $('#serviceName').val(serviceName);
            $('#servicePrice').val(servicePrice);
            $('input[name="_method"]').val('PUT');
        });

        $(document).on('click', '.delete-btn', function() {
            const serviceToken = $(this).data('service-token');
            if (confirm('Êtes-vous sûr de vouloir supprimer ce service ?')) {
                showLoader();
                $.ajax({
                    url: `/services/${serviceToken}`,
                    method: 'DELETE',
                    success: function(response) {
                        showNotification(response.message || 'Service supprimé.', true);
                        loadServices();
                    },
                    error: function(response) {
                        showNotification(response.responseJSON.message || 'Erreur de suppression.', false);
                    }
                }).always(function() {
                    hideLoader();
                });
            }
        });

        $(document).on('click', '.view-btn', function() {
            const serviceName = $(this).data('service-name');
            const servicePrice = $(this).data('service-price');
            
            $('#viewName').text(serviceName);
            $('#viewPrice').text(servicePrice ? servicePrice + ' FCFA' : 'Non défini');
        });
        
        $(document).ready(function() {
            loadServices();
        });
    </script>
</body>
</html>