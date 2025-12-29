<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestion Dépôts | Pressing Manager</title>
    {{-- Assurez-vous que ces ressources sont correctement chargées par Laravel/Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Styles pour le Loader Modal */
        #loader {
            background-color: rgba(0,0,0,0.5); 
            z-index: 2500;
        }
        /* Style pour les cartes responsives du tableau */
        .order-card {
            border: 1px solid var(--bs-border-color);
            margin-bottom: 1rem;
            background-color: var(--bs-body); /* Assure un fond contrasté */
            border-radius: .5rem;
        }
        @media (min-width: 992px) {
            .order-card {
                display: none; /* Cache les cartes sur les grands écrans */
            }
        }
        @media (max-width: 991.98px) {
            .table-responsive {
                display: none; /* Cache le tableau sur les petits écrans */
            }
        }
        .status-badge {
            display: inline-block;
            padding: .3em .6em;
            border-radius: .3rem;
            font-size: 85%;
            font-weight: 700;
        }
    </style>
</head>
<body class="d-flex">
    {{-- loader --}}
    @include('partials.loader')
    
    {{-- ================================================= --}}
    {{-- 🧩 Sidebar (Navigation) 🧩 --}}
    {{-- ================================================= --}}
    @include('partials.side-bar')

    <div class="flex-grow-1 d-flex flex-column min-vh-100">
        {{-- ================================================= --}}
        {{-- 🧩 Header (Titre et Dark Mode) 🧩 --}}
        {{-- ================================================= --}}
        <header class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-body shadow-sm">
            <button class="btn btn-outline-secondary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar">☰</button>
            <h2 class="h5 fw-bold text-primary mb-0">Gestion des Dépôts</h2>
            <div class="d-flex align-items-center gap-3">
                <span class="text-secondary">Bonjour, <strong>{{ Auth::User()->name }}</strong></span>
                <button id="toggleDarkMode" class="btn btn-outline-secondary">🌙</button>
            </div>
        </header>

        {{-- ================================================= --}}
        {{-- 🧩 Contenu Principal 🧩 --}}
        {{-- ================================================= --}}
        <main class="flex-grow-1 p-4 bg-body-tertiary">
            
            {{-- BLOC 1 : Actions Modals --}}
            <div class="d-flex flex-column flex-md-row gap-3 mb-4 border-bottom pb-3">
                <h3 class="h6 fw-bold text-secondary mb-0 align-self-center d-none d-lg-block">Actions Rapides :</h3>
                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#manageStatusModal">🛠️ Gérer le statut</button>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#cashInModal">💰 Encaissement</button>
            </div>
            
            {{-- BLOC 2 : Filtres de la liste (Intégré à la page) --}}
            <div class="card p-3 mb-4 shadow-sm">
                <h4 class="h6 card-title mb-3">📋 Filtre des Dépôts</h4>
                <form id="filterForm" class="row g-3 align-items-end">
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="startDateFilter" class="form-label">Date de début de dépôt</label>
                        <input type="date" class="form-control" id="startDateFilter" name="start_date">
                    </div>
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="endDateFilter" class="form-label">Date de fin de dépôt</label>
                        <input type="date" class="form-control" id="endDateFilter" name="end_date">
                    </div>
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="statusFilter" class="form-label">Statut du dépôt</label>
                        <select class="form-select" id="statusFilter" name="status">
                            <option value="">Tous les statuts</option>
                            <option value="pending">En attente (Pending)</option>
                            <option value="ready">Prêt (Ready)</option>
                            <option value="delivered">Livré (Delivered)</option>
                            <option value="cancelled">Annulé (Cancelled)</option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-3 d-flex gap-2">
                        <button type="submit" class="btn btn-info w-100">Filtrer</button>
                        <button type="button" class="btn btn-outline-secondary" id="resetFilterBtn">Réinitialiser</button>
                    </div>
                </form>
            </div>

            {{-- BLOC 3 : Zone de résultats (Tableau) --}}
            <h4 class="h6 fw-bold mt-4 mb-3">Résultats de la recherche :</h4>
            
            <div id="ordersTableContainer">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Réf. / Token</th>
                                <th>Date Dépôt</th>
                                <th>Client</th>
                                <th>Statut Livraison</th>
                                <th>Statut Paiement</th>
                                <th>Saisie par</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            {{-- Message initial au lieu du chargement automatique --}}
                            <tr><td colspan="7" class="text-center text-muted">Utilisez le filtre ci-dessus pour charger les dépôts.</td></tr>
                        </tbody>
                    </table>
                </div>

                {{-- Conteneur pour les cartes responsives (petits écrans) --}}
                <div id="ordersCardsContainer">
                    {{-- Les résultats seront insérés ici par JavaScript (format carte) --}}
                </div>

                {{-- Pagination --}}
                <nav id="paginationLinks" class="mt-3 d-flex justify-content-center" aria-label="Pagination des dépôts">
                    {{-- La pagination sera insérée ici par JavaScript --}}
                </nav>
            </div>

        </main>
    </div>

    {{-- ================================================= --}}
    {{-- 🧩 MODALS (Gérer Statut, Encaissement, Détails, Notification, Loader) 🧩 --}}
    {{-- ================================================= --}}

    {{-- Modal 1: Gérer le statut --}}
    <div class="modal fade" id="manageStatusModal" tabindex="-1" aria-labelledby="manageStatusModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="manageStatusModalLabel">🛠️ Gestion du Statut de Dépôt</h5>
                    <button type="button" class="btn-close reset-on-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="manageStatusForm">
                    <div class="modal-body">
                        @csrf
                        <div class="mb-3">
                            <label for="orderTokenSelectStatus" class="form-label">Référence du dépôt (Status Pending)</label>
                            <select class="form-select" id="orderTokenSelectStatus" name="token" required>
                                <option value="">Sélectionner une référence...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="clientNameStatus" class="form-label">Client</label>
                            <input type="text" class="form-control" id="clientNameStatus" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="newStatusSelect" class="form-label">Nouveau Statut</label>
                            <select class="form-select" id="newStatusSelect" name="status" required>
                                <option value="">Choisir un statut</option>
                                <option value="ready">Prêt (Ready)</option>
                                <option value="delivered">Livré (Delivered)</option>
                                <option value="cancelled">Annulé (Cancelled)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary reset-on-close" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning">Mettre à jour le statut</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal 2: Encaissement --}}
    <div class="modal fade" id="cashInModal" tabindex="-1" aria-labelledby="cashInModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cashInModalLabel">💰 Encaissement du Dépôt</h5>
                    <button type="button" class="btn-close reset-on-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="cashInForm">
                    <div class="modal-body">
                        @csrf
                        <div class="mb-3">
                            <label for="orderTokenSelectCash" class="form-label">Référence du dépôt</label>
                             <select class="form-select" id="orderTokenSelectCash" name="token" required>
                                <option value="">Sélectionner une référence...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="clientNameCash" class="form-label">Client</label>
                            <input type="text" class="form-control" id="clientNameCash" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="remainingAmountCash" class="form-label">Montant Restant à Payer (XAF)</label>
                            <input type="number" step="0.01" class="form-control" id="remainingAmountCash" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="amountPaidCash" class="form-label">Montant Encaissé (XAF)</label>
                            <input type="number" step="0.01" class="form-control" id="amountPaidCash" name="amount_paid" required min="0">
                        </div>
                        <div class="mb-3">
                            <label for="paymentMethodCash" class="form-label">Mode de Règlement</label>
                            <select class="form-select" id="paymentMethodCash" name="payment_method" required>
                                <option value="cash">Espèces</option>
                                <option value="card">Carte</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary reset-on-close" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">Valider l'encaissement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- Modal 3: Détails du Dépôt (MODIFIÉ) --}}
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">Détails du Dépôt: <span id="detailsReference" class="text-primary"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- ... Votre contenu de détails (client, dates, statuts) ... --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Client:</strong> <span id="detailsClient"></span></p>
                            <p><strong>Date Dépôt:</strong> <span id="detailsDepositDate"></span></p>
                            <p><strong>Statut Livraison:</strong> <span id="detailsDeliveryStatus"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Saisie par:</strong> <span id="detailsUser"></span></p>
                            <p><strong>Statut Paiement:</strong> <span id="detailsPaymentStatus"></span></p>
                            <p><strong>Montant Total:</strong> <span id="detailsTotalAmount" class="fw-bold"></span> XAF</p>
                        </div>
                    </div>

                    <h6 class="mt-3 mb-2 border-bottom pb-1">Articles Détaillés</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Service</th>
                                    <th>Quantité</th>
                                    <th>Prix Unitaire</th>
                                    <th>Total Article</th>
                                </tr>
                            </thead>
                            <tbody id="detailsItemsBody">
                                {{-- Les articles seront injectés ici --}}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    {{-- NOUVEAU BOUTON D'IMPRESSION --}}
                    <a id="printDepositCouponBtn" href="#" class="btn btn-success" target="_blank" title="Générer et imprimer le coupon de dépôt">
                        <i class="bi bi-printer-fill"></i> Imprimer Coupon
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>


    {{-- Modal Utilitaire : Notification --}}
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

    {{-- Loader Modal Backdrop --}}
    <div id="loader" class="modal-backdrop d-none position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" style="background-color: rgba(0,0,0,0.5); z-index: 2500;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Chargement...</span>
        </div>
    </div>


    {{-- ================================================= --}}
    {{-- 🧩 Scripts JavaScript  🧩 --}}
    {{-- ================================================= --}}
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // --- Variables Globales ---
        let pendingOrders = []; 
        let nonFullyPaidOrders = []; 
        let currentPage = 1;

        // --- 1. Fonctions Utilitaires et d'Aide ---

        // Toggle Dark Mode
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

        // Notifications
        function showNotification(message, isSuccess = true) {
            const modal = $('#notificationModal');
            const modalTitle = $('#notificationModalLabel');
            const modalMessage = $('#notificationMessage');
            modalTitle.text(isSuccess ? 'Succès' : 'Erreur');
            modalMessage.html(message);
            modal.modal('show');
            setTimeout(() => { modal.modal('hide'); }, 5000);
        }

        // Loader
        function showLoader() { $('#loader').removeClass('d-none'); }
        function hideLoader() { $('#loader').addClass('d-none'); }

        // Badges (Statut Livraison)
        function getStatusBadge(status) {
            let color;
            switch(status) {
                case 'pending': color = 'bg-primary'; status = 'En attente'; break;
                case 'ready': color = 'bg-warning text-dark'; status = 'Prêt'; break;
                case 'delivered': color = 'bg-success'; status = 'Livré'; break;
                case 'cancelled': color = 'bg-danger'; status = 'Annulé'; break;
                default: color = 'bg-secondary'; status = 'Inconnu';
            }
            return `<span class="status-badge ${color}">${status}</span>`;
        }

        // Badges (Statut Paiement)
        function getPaymentBadge(status) {
            let color;
            switch(status) {
                case 'paid': color = 'bg-success'; status = 'Payé (Intégral)'; break;
                case 'partially_paid': color = 'bg-warning text-dark'; status = 'Payé (Partiel)'; break;
                case 'pending': color = 'bg-danger'; status = 'Impayé'; break;
                default: color = 'bg-secondary'; status = 'Inconnu';
            }
            return `<span class="status-badge ${color}">${status}</span>`;
        }
        
        // --- 2. Fonction de Rendu de la Pagination ---
        function renderPagination(data) {
            const nav = $('#paginationLinks');
            nav.empty();

            if (data.last_page > 1) {
                let html = '<ul class="pagination">';
                
                // Bouton Précédent
                html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${data.current_page - 1}">Précédent</a>
                          </li>`;

                // Liens numériques (simplifiés)
                if (data.links) {
                    data.links.forEach(link => {
                        // N'affiche que les liens avec des URL (pas ceux de séparation '...') ou des labels numériques
                        if (link.url !== null && (link.label.match(/^\d+$/) || link.active)) {
                            html += `<li class="page-item ${link.active ? 'active' : ''}">
                                        <a class="page-link" href="#" data-page="${link.label}">${link.label}</a>
                                      </li>`;
                        }
                    });
                } else {
                    // Fallback simple si 'links' n'est pas fourni par l'API de Laravel
                    for (let i = 1; i <= data.last_page; i++) {
                        html += `<li class="page-item ${data.current_page === i ? 'active' : ''}">
                                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                                  </li>`;
                    }
                }


                // Bouton Suivant
                html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${data.current_page + 1}">Suivant</a>
                          </li>`;

                html += '</ul>';
                nav.html(html);

                // Écouteur de clic pour la pagination
                nav.find('.page-link').on('click', function(e) {
                    e.preventDefault();
                    const page = parseInt($(this).data('page'));
                    if (!isNaN(page) && page >= 1 && page <= data.last_page) {
                        currentPage = page;
                        filterOrders(page);
                    }
                });
            }
        }

        // --- 3. Fonction de Rendu Principale (Tableau et Cartes) ---
        function populateOrderTable(ordersPaginationObject) {
            const tableBody = $('#ordersTableBody');
            const cardsContainer = $('#ordersCardsContainer').empty();
            tableBody.empty();
            $('#paginationLinks').empty(); 

            // Gère le cas où la réponse est vide
            if (!ordersPaginationObject || !ordersPaginationObject.data || ordersPaginationObject.data.length === 0) {
                tableBody.append('<tr><td colspan="7" class="text-center text-muted">Aucun dépôt trouvé pour ce filtre.</td></tr>');
                return;
            }

            ordersPaginationObject.data.forEach(order => {
                // 🚀 CORRECTION DE L'ERREUR ici : Vérification des relations
                const clientName = order.client?.name ?? 'Client Inconnu';
                const userName = order.user?.name ?? 'Utilisateur Inconnu';
                
                const date = new Date(order.deposit_date).toLocaleDateString('fr-FR');
                // MODIFICATION : Ajout de la classe 'view-details-btn' et de l'attribut 'data-token'
                const actionsHtml = `<button class="btn btn-sm btn-info me-1 view-details-btn" title="Voir Détails" data-token="${order.token}">👁️</button>`;

                // 1. Ligne du Tableau (Grands Écrans)
                const tableRow = `
                    <tr>
                        <td><strong>${order.reference}</strong></td>
                        <td>${date}</td>
                        <td>${clientName}</td>
                        <td>${getStatusBadge(order.delivery_status)}</td>
                        <td>${getPaymentBadge(order.payment_status)}</td>
                        <td>${userName}</td>
                        <td>${actionsHtml}</td>
                    </tr>
                `;
                tableBody.append(tableRow);

                // 2. Carte (Petits Écrans)
                const card = `
                    <div class="card order-card shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="fw-bold mb-0 text-primary">${order.reference}</h6>
                            ${actionsHtml}
                        </div>
                        <ul class="list-unstyled mt-2 small">
                            <li><i class="bi bi-person-fill"></i> Client: <strong>${clientName}</strong></li>
                            <li><i class="bi bi-calendar-day-fill"></i> Dépôt: <strong>${date}</strong></li>
                            <li><i class="bi bi-truck"></i> Statut Livraison: ${getStatusBadge(order.delivery_status)}</li>
                            <li><i class="bi bi-currency-euro"></i> Statut Paiement: ${getPaymentBadge(order.payment_status)}</li>
                            <li><i class="bi bi-person-circle"></i> Saisi par: ${userName}</li>
                        </ul>
                    </div>
                `;
                cardsContainer.append(card);
            });

            // Afficher la pagination
            renderPagination(ordersPaginationObject);
        }
        
        // --- 4. Fonctions de Logique Métier ---

        // Fonction de filtre principale (pour le tableau)
        function filterOrders(page = 1) {
            const formData = $('#filterForm').serialize();
            showLoader();
            
            // L'URL est /orders/filter
            const url = `/orders/filter?${formData}&page=${page}`; 

            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    currentPage = page;
                    // Assurez-vous que l'objet passé ici contient la structure de pagination Laravel complète
                    populateOrderTable(response.orders); 
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "Erreur lors du chargement des dépôts.";
                    showNotification(msg, false);
                    $('#ordersTableBody').empty().append('<tr><td colspan="7" class="text-center text-danger">Échec du chargement des données. Veuillez réessayer.</td></tr>');
                    $('#ordersCardsContainer').empty().append('<div class="alert alert-danger">Échec du chargement des données.</div>');
                    $('#paginationLinks').empty();
                },
                complete: function() { hideLoader(); }
            });
        }

        // Fonction pour charger les références des modals (nécessaire pour les actions rapides)
        function loadModalReferences() {
            showLoader();
            $.get('/orders/references') 
                .done(function(data) {
                    pendingOrders = data.pending_orders || [];
                    nonFullyPaidOrders = data.non_fully_paid_orders || [];
                    
                    // Remplissage Gérer Statut (pending)
                    const selectStatus = $('#orderTokenSelectStatus').empty().append('<option value="">Sélectionner une référence...</option>');
                    pendingOrders.forEach(order => {
                        // Supposons que l'API de références retourne 'client_name' directement.
                        // Sinon, ajustez la requête d'API pour inclure le nom du client.
                        selectStatus.append(`<option value="${order.token}" data-client="${order.client_name}">${order.reference}</option>`);
                    });

                    // Remplissage Encaissement (non intégralement payé)
                    const selectCash = $('#orderTokenSelectCash').empty().append('<option value="">Sélectionner une référence...</option>');
                    nonFullyPaidOrders.forEach(order => {
                        const remaining = (order.total_amount - order.paid_amount).toFixed(2);
                        selectCash.append(`<option value="${order.token}" data-client="${order.client_name}" data-remaining="${remaining}">${order.reference} - (${remaining} XAF restants)</option>`);
                    });

                })
                .fail(function() {
                    // Ne pas crasher si les références ne peuvent être chargées, juste log l'erreur.
                    console.error('Erreur de chargement des listes de références pour les actions.');
                })
                .always(function() { hideLoader(); });
        }
        
        // NOUVEAU : Fonction pour afficher les détails du dépôt
        function showOrderDetails(token) {
            showLoader();
            
            // L'URL de l'API doit être définie dans vos routes Laravel (ex: /api/orders/{token}/details)
            $.get(`/orders/${token}/details`) 
                .done(function(data) {
                    const order = data.order;
                    const items = data.items || []; // S'assurer que vous recevez les articles

                    // Remplir les informations principales
                    $('#detailsReference').text(order.reference);
                    $('#detailsClient').text(order.client_name); 
                    $('#detailsDepositDate').text(new Date(order.deposit_date).toLocaleDateString('fr-FR'));
                    $('#detailsDeliveryStatus').html(getStatusBadge(order.delivery_status));
                    $('#detailsPaymentStatus').html(getPaymentBadge(order.payment_status));
                    $('#detailsUser').text(order.user_name); 
                    $('#detailsTotalAmount').text(parseFloat(order.total_amount).toFixed(2));

                    // Remplir les articles détaillés
                    const itemsBody = $('#detailsItemsBody').empty();
                    if (items.length > 0) {
                        items.forEach(item => {
                            const row = `
                                <tr>
                                    <td>${item.service_name}</td>
                                    <td>${item.quantity}</td>
                                    <td>${parseFloat(item.unit_price).toFixed(2)} XAF</td>
                                    <td>${parseFloat(item.quantity * item.unit_price).toFixed(2)} XAF</td>
                                </tr>
                            `;
                            itemsBody.append(row);
                        });
                    } else {
                        itemsBody.append('<tr><td colspan="4" class="text-center text-muted">Aucun article détaillé.</td></tr>');
                    }

                    // Générer l'URL d'impression et l'injecter dans l'attribut href
                    const printUrl = `/orders/${token}/print`; 
                    $('#printDepositCouponBtn').attr('href', printUrl);
            

                    $('#detailsModal').modal('show');
                })
                .fail(function(xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "Erreur lors du chargement des détails du dépôt.";
                    showNotification(msg, false);
                })
                .always(function() { hideLoader(); });
        }


        // --- 5. Initialisation et Écouteurs d'Événements ---

        $(document).ready(function() {
            loadModalReferences();
        });

        // Soumission Filtre
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            filterOrders(1); // Retourne à la première page lors d'un nouveau filtre
        });

        // Bouton Réinitialiser du filtre
        $('#resetFilterBtn').on('click', function() {
            $('#filterForm')[0].reset();
            filterOrders(1); 
            showNotification("Le filtre a été réinitialisé et les résultats rechargés.", true);
        });
        
        // NOUVEAU : Écouteur pour le bouton "Voir Détails"
        $('#ordersTableContainer').on('click', '.view-details-btn', function() {
            const token = $(this).data('token');
            if (token) {
                showOrderDetails(token);
            }
        });


        // Écouteur pour Gérer Statut
        $('#orderTokenSelectStatus').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const clientName = selectedOption.data('client') || '';
            $('#clientNameStatus').val(clientName);
        });

        // Écouteur pour Encaissement
        $('#orderTokenSelectCash').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const clientName = selectedOption.data('client') || '';
            const remaining = selectedOption.data('remaining') || '';

            $('#clientNameCash').val(clientName);
            $('#remainingAmountCash').val(remaining);
            $('#amountPaidCash').attr('max', parseFloat(remaining));
            $('#amountPaidCash').val(remaining); 
        });

        // Soumission Gérer Statut
        $('#manageStatusForm').on('submit', function(e) {
            e.preventDefault();
            const token = $('#orderTokenSelectStatus').val(); 
            showLoader();
            $.ajax({
                url: `/orders/${token}/status`, 
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    showNotification(response.message, true);
                    $('#manageStatusModal').modal('hide');
                    loadModalReferences(); 
                    filterOrders(currentPage); // Recharge la page courante du tableau
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "Erreur lors de la mise à jour du statut.";
                    showNotification(msg, false);
                },
                complete: function() { hideLoader(); }
            });
        });

        // Soumission Encaissement
        $('#cashInForm').on('submit', function(e) {
            e.preventDefault();
            const token = $('#orderTokenSelectCash').val(); 
            const amountPaid = parseFloat($('#amountPaidCash').val());
            const remaining = parseFloat($('#remainingAmountCash').val());

            if (amountPaid > remaining || amountPaid <= 0 || isNaN(amountPaid)) {
                showNotification(`Le montant encaissé doit être entre 0 et ${remaining} €.`, false);
                return;
            }

            showLoader();
            $.ajax({
                url: `/orders/${token}/cash-in`, 
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    showNotification(response.message, true);
                    $('#cashInModal').modal('hide');
                    loadModalReferences(); 
                    filterOrders(currentPage); // Recharge la page courante du tableau
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "Erreur lors de l'encaissement.";
                    showNotification(msg, false);
                },
                complete: function() { hideLoader(); }
            });
        });
        
        // Réinitialisation des champs lors de la fermeture des modals (Gérer Statut et Encaissement)
        $('.reset-on-close').on('click', function() {
            $('#manageStatusForm')[0].reset();
            $('#cashInForm')[0].reset();
        });

    </script>
</body>
</html>