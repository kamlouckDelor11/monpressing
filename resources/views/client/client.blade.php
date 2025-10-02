<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestion Clients | Pressing Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /*
        Le tableau complet doit être une grille pour un affichage plus cohérent.
        L'en-tête et le corps du tableau seront des éléments de grille.
        */
        .clients-table-grid {
            display: grid;
            grid-template-rows: auto 1fr;
            height: 100%;
        }

        /* Le corps du tableau a une hauteur fixe et un défilement */
        .clients-table-body-scrollable {
            max-height: 400px; /* Ajustez cette valeur pour afficher plus ou moins de clients */
            overflow-y: auto;
        }

        /* Assure que les colonnes du thead et du tbody sont de la même largeur */
        .clients-table-body-scrollable table {
            width: 100%;
        }

        /* Le thead est toujours visible en haut du conteneur */
        .clients-table-grid thead {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: var(--bs-body-bg); /* S'adapte au mode sombre/clair */
        }

        /* Espacement des boutons d'action sur les petits écrans */
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
                <a href="#" class="nav-link text-secondary">👔 Gestion des dépôts</a>
                <a href="{{ route('clients.index') }}" class="nav-link text-secondary">👤 Gestion des clients</a>
                <a href="{{ route('articles.index') }}" class="nav-link text-secondary">👔 Gestion des articles</a>
                <a href="{{ route('services.index') }}" class="nav-link text-secondary">👔 Gestion des services</a>
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
            <h2 class="h5 fw-bold text-primary mb-0">Gestion des clients</h2>
            <div class="d-flex align-items-center gap-3">
                <span class="text-secondary">Bonjour, <strong>{{ Auth::User()->name}}</strong></span>
                <button id="toggleDarkMode" class="btn btn-outline-secondary">🌙</button>
            </div>
        </header>

        <main class="flex-grow-1 p-4 bg-body-tertiary">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">Liste des clients</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clientModal">
                    <i class="bi bi-person-plus-fill"></i> Ajouter un client
                </button>
            </div>

            <div class="mb-4 d-flex flex-column flex-md-row gap-3">
                <input type="text" id="nameSearch" class="form-control" placeholder="Rechercher par nom...">
                <input type="text" id="phoneSearch" class="form-control" placeholder="Rechercher par numéro de téléphone...">
                <button id="resetFiltersBtn" class="btn btn-outline-secondary">Réinitialiser</button>
            </div>

            <div class="card shadow-sm">
                <div class="card-body clients-table-grid">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nom</th>
                                <th>Téléphone</th>
                                <th>Adresse</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                    <div class="clients-table-body-scrollable">
                        <table class="table table-hover align-middle mb-0">
                            <tbody id="clients-table-body">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="clientModal" tabindex="-1" aria-labelledby="clientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clientModalLabel">Ajouter un client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="clientForm" method="POST" action="{{ route('clients.store') }}">
                        @csrf
                        <input type="hidden" name="_method" value="POST">
                        <input type="hidden" name="id" id="clientId">
                        <div class="mb-3">
                            <label for="clientName" class="form-label">Nom complet</label>
                            <input type="text" class="form-control" name="name" id="clientName" required>
                        </div>
                        <div class="mb-3">
                            <label for="clientPhone" class="form-label">Numéro de téléphone</label>
                            <input type="tel" class="form-control" name="phone" id="clientPhone" required>
                        </div>
                        <div class="mb-3">
                            <label for="clientAddress" class="form-label">Adresse</label>
                            <input type="text" class="form-control" name="address" id="clientAddress">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" form="clientForm" class="btn btn-primary">Enregistrer le client</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historyModalLabel">Historique des commandes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID Commande</th>
                                    <th>Date de dépôt</th>
                                    <th>Statut commande</th>
                                    <th>Statut paiement</th>
                                    <th>Montant Total</th>
                                </tr>
                            </thead>
                            <tbody id="history-table-body">
                                </tbody>
                        </table>
                    </div>
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
        // Token CSRF pour les requêtes AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Fonction pour afficher le loader
        function showLoader() {
            $('#loader').removeClass('d-none');
        }

        // Fonction pour cacher le loader
        function hideLoader() {
            $('#loader').addClass('d-none');
        }

        // Logique du Dark/Light Mode
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

        // Fonction pour afficher le modal de notification
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

        // Fonction pour charger et afficher la liste des clients
        function loadClients() {
            const nameSearch = $('#nameSearch').val();
            const phoneSearch = $('#phoneSearch').val();
            const url = `{{ route('clients.index') }}?name=${nameSearch}&phone=${phoneSearch}`;
            showLoader()

            $.get(url, function(data) {
                const tbody = $('#clients-table-body');
                tbody.empty();
                if (data.length > 0) {
                    data.forEach(client => {
                        const row = `
                            <tr>
                                <td>${client.name}</td>
                                <td>${client.phone}</td>
                                <td>${client.address}</td>
                                <td class="actions-buttons">
                                    <button class="btn btn-sm btn-warning edit-btn" data-bs-toggle="modal" data-bs-target="#clientModal" data-client-id="${client.token}" data-client-name="${client.name}" data-client-phone="${client.phone}" data-client-address="${client.address}">Modifier</button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-client-id="${client.token}">Supprimer</button>
                                    <button class="btn btn-sm btn-info history-btn" data-bs-toggle="modal" data-bs-target="#historyModal" data-client-id="${client.token}" data-client-name="${client.name}">Historique</button>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    tbody.append('<tr><td colspan="4" class="text-center">Aucun client trouvé.</td></tr>');
                }
                
            }).always(function() {
                hideLoader();
            });
        }

        // Événements pour la recherche et la réinitialisation des filtres
        $('#nameSearch, #phoneSearch').on('input', function() {
            loadClients();
        });
        $('#resetFiltersBtn').on('click', function() {
            $('#nameSearch').val('');
            $('#phoneSearch').val('');
            loadClients();
        });

        // Gestion du formulaire d'ajout/modification via AJAX
        $('#clientForm').on('submit', function(e) {
            e.preventDefault();
             
           const form = $(this);
            const clientId = $('#clientId').val();
            const method = form.find('input[name="_method"]').val();
            const url = clientId ? `/clients/${clientId}` : "{{ route('clients.store') }}";

            showLoader()
            $.ajax({
                url: url,
                method: method,
                data: form.serialize(),
                success: function(response) {
                    $('#clientModal').modal('hide');
                    showNotification(response.message || 'Opération réussie !', true);
                    loadClients(); // Mise à jour de la liste
                    hideLoader()
                },
                error: function(response) {
                    const errorMessage = response.responseJSON.message || 'Une erreur est survenue.';
                    showNotification(errorMessage, false);
                    hideLoader()

                }
            });
        });

        // Logique pour le bouton Modifier
        $(document).on('click', '.edit-btn', function() {
            const clientId = $(this).data('client-id');
            const clientName = $(this).data('client-name');
            const clientPhone = $(this).data('client-phone');
            const clientAddress = $(this).data('client-address');
            
            $('#clientModalLabel').text("Modifier le client");
            $('#clientId').val(clientId);
            $('#clientName').val(clientName);
            $('#clientPhone').val(clientPhone);
            $('#clientAddress').val(clientAddress);
            $('input[name="_method"]').val('PUT');

        });

        // Logique pour le bouton Supprimer
        $(document).on('click', '.delete-btn', function() {
            const clientId = $(this).data('client-id');
            if (confirm('Êtes-vous sûr de vouloir supprimer ce client ?')) {
                $.ajax({
                    url: `/clients/${clientId}`,
                    method: 'DELETE',
                    success: function(response) {
                        showNotification(response.message || 'Client supprimé.', true);
                        loadClients();
                    },
                    error: function(response) {
                        showNotification(response.responseJSON.message || 'Erreur de suppression.', false);
                    }
                });
            }
        });

        // Logique pour l'historique des commandes
        $(document).on('click', '.history-btn', function() {
            const clientId = $(this).data('client-id');
            const clientName = $(this).data('client-name');
            const modalTitle = $('#historyModalLabel');
            const historyBody = $('#history-table-body');
            
            modalTitle.text(`Historique des commandes de ${clientName}`);
            historyBody.html('<tr><td colspan="4" class="text-center">Chargement...</td></tr>');

            $.get(`/clients/${clientId}/orders`, function(data) {
                historyBody.empty();
                if (data.length > 0) {
                    data.forEach(order => {
                        const row = `
                            <tr>
                                <td>${order.reference}</td>
                                <td>${new Date(order.deposit_date).toLocaleDateString()}</td>
                                <td>${order.delivery_status}</td>
                                <td>${order.payment_status}</td>
                                <td>${order.total_amount} FCFA</td>
                            </tr>
                        `;
                        historyBody.append(row);
                    });
                } else {
                    historyBody.append('<tr><td colspan="4" class="text-center">Aucune commande trouvée.</td></tr>');
                }
            }).fail(() => {
                historyBody.html('<tr><td colspan="4" class="text-center text-danger">Erreur de chargement.</td></tr>');
            });
        });

        // Initialisation de la page
        $(document).ready(function() {
            loadClients();
        });
    </script>
</body>
</html>