<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestion des Dépenses | Mon Pressing</title>
    
    {{-- Inclusions de base --}}
    @vite(['resources/css/app.css', 'resources/js/app.js']) 
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        /* BASE & LAYOUT (Inchangé) */
        html, body { height: 100%; margin: 0; padding: 0; }
        body { display: flex; min-height: 100vh; }
        #sidebar { width: 250px; flex-shrink: 0; transition: margin-left 0.3s; }
        .main-content-wrapper { flex-grow: 1; display: flex; flex-direction: column; }
        
        /* Assure que la zone de contenu s'étend verticalement et est scrollable (le seul scroll) */
        .main-content-area { 
            height: calc(100vh - 64px); 
            overflow-y: auto; 
            padding: 1rem !important; 
        }
        
        /* Garantit que les conteneurs (interfaces) prennent toute la hauteur */
        .interface-container {
             width: 100%; 
             height: 100%;
             display: flex;
             flex-direction: column;
        }

        /* Garantit que la carte occupe 100% de la hauteur disponible et gère le flex/scroll interne */
        .spense-card { 
            height: 100%; 
            max-height: 100%;
            display: flex; 
            flex-direction: column; 
        }
        
        .spense-card .card-body { 
            flex-grow: 1; 
            overflow-y: hidden; 
            display: flex; 
            flex-direction: column; 
            padding: 0; 
        }
        
        /* Conteneur Listes/Panier : doit scroller */
        #categoriesList {
            flex-grow: 1;
            overflow-y: auto; 
        }
        
        /* --- STRUCTURE DE L'INTERFACE DE COMPTABILISATION --- */
        
        /* Styles pour les deux colonnes internes (formulaire et panier) */
        .entry-col {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            flex: 1 1 50%; 
            border-right: 1px solid var(--bs-border-color);
            position: relative; 
        }
        
        #transaction-entry .card-body > .entry-col:last-child {
            border-right: none;
        }

        /* Le Panier (liste des items) doit scroller et prendre l'espace restant */
        #panierItems {
            flex-grow: 1; 
            overflow-y: auto; /* Défilement local sur desktop */
            padding-bottom: 0; 
        }
        
        /* Conteneur de validation - Reste dans le flux sur mobile/desktop */
        .panier-validation-area {
            flex-shrink: 0;
            margin-top: auto; 
            padding-top: 1rem;
            border-top: 1px solid var(--bs-border-color);
        }
        
        /* Conteneur Date + Bouton Ajouter */
        .form-bottom-actions {
            flex-shrink: 0; 
            padding-top: 1rem;
            border-top: 1px solid var(--bs-border-color);
        }
        
        /* Retrait du conteneur de scroll du formulaire, on laisse le main-content-area gérer */
        #addItemForm {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }


        /* Sur desktop (>= 768px), on passe en mode flex horizontal (deux colonnes) */
        @media (min-width: 768px) { 
            #transaction-entry .card-body {
                flex-direction: row;
                overflow-y: hidden; 
            }
            .form-bottom-actions {
                padding-top: 0;
                border-top: none;
                margin-top: auto; 
                position: relative; 
            }
             .panier-validation-area {
                position: relative !important; 
                padding: 1rem;
            }
        }


        /* Responsive Mobile (< 768px) - CORRECTION V11 */
        @media (max-width: 767px) { 
            /* SIDEBAR */
            #sidebar { position: fixed; height: 100%; z-index: 1030; margin-left: -250px; transition: margin-left 0.3s ease-in-out; }
            body.sidebar-open #sidebar { margin-left: 0; }
            body.sidebar-open::before { content: ''; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 1020; }
            
            #sidebarToggle { display: block !important; }
            
            /* CONTENT AREA: Hauteur automatique et défilement géré par la hauteur du contenu */
            .main-content-area { 
                height: auto; 
                min-height: calc(100vh - 64px); 
                padding-bottom: 1rem !important; 
                overflow-y: visible;
            } 
            
            /* INTERFACE CONTROLE: Désactiver le flex/hauteur fixe pour permettre le défilement */
            .interface-container, .spense-card, .spense-card .card-body {
                height: auto !important;
                flex-basis: auto !important;
                flex-grow: 0 !important;
                flex-shrink: 0 !important;
                max-height: none !important;
            }

            /* ENTRIES: Empilement des colonnes */
            #transaction-entry .card-body {
                flex-direction: column;
                overflow-y: visible;
            }
            
            /* COLONNES */
            .entry-col {
                border-right: none !important;
                border-bottom: 1px solid var(--bs-border-color); 
                height: auto !important; 
                flex-grow: 0 !important;
                flex-shrink: 0 !important;
            }
            #transaction-entry .card-body > .entry-col:last-child {
                border-bottom: none !important;
            }
            
            /* FORMULAIRE INTERNE */
            #addItemForm {
                flex-grow: 0; 
            }
            .form-bottom-actions {
                position: relative;
                bottom: auto; 
                margin: 0;
                padding: 1rem;
                padding-top: 1rem;
                border-top: 1px solid var(--bs-border-color);
                z-index: 1; 
                background-color: transparent;
            }
            
            /* PANIER */
            #panierItems {
                overflow-y: visible; 
                flex-grow: 0;
            }

            .panier-validation-area {
                position: relative !important;
                bottom: auto !important;
                left: auto !important;
                right: auto !important;
                margin-top: auto; 
                padding: 1rem;
                border-top: 1px solid var(--bs-border-color);
                box-shadow: none;
            }
        }
        
        /* Loader Overlays */
        .loader-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1050;
            color: white;
            flex-direction: column;
            text-align: center;
        }

        /* Z-INDEX POUR LE MODAL DE MESSAGE */
        .z-index-alert {
            z-index: 11000 !important; 
        }
        
        /* Style Panier */
        .panier-item {
            border-left: 4px solid var(--bs-primary);
            padding-left: 10px;
        }
        /* Style Actif pour le Sidebar */
        .sidebar-link.active-spense {
            background-color: var(--bs-primary);
            color: var(--bs-white) !important;
        }
        
        /* 💡 V14: Style pour les messages Flash */
        #flash-message-container {
            position: fixed;
            top: 65px; /* Juste sous le header */
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 600px;
            z-index: 1040; /* Au-dessus de tout sauf les modals */
        }
        .flash-success {
            background-color: #d1e7dd; /* Vert clair Bootstrap */
            color: #0f5132;
            border-color: #badbcc;
        }
        .flash-error {
            background-color: #f8d7da; /* Rouge clair Bootstrap */
            color: #842029;
            border-color: #f5c2c7;
        }
    </style>
</head>
<body class="d-flex">
    
    <div id="flash-message-container"></div>
    
    <div id="sidebar" class="bg-dark text-white p-3 shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="h6 text-uppercase fw-bold mb-0">Menu Dépenses</h3>
            <button id="sidebarCloseBtn" class="btn btn-close btn-close-white d-lg-none" aria-label="Close"></button>
        </div>

        <ul class="nav nav-pills flex-column">
            <li class="nav-item mb-2">
                <a class="nav-link sidebar-link active-spense" href="#" data-target="#category-management" id="link-categories">
                    <i class="bi bi-list-task"></i>💸 Nature Dépense
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link sidebar-link" href="#" data-target="#transaction-entry" id="link-entry">
                    <i class="bi bi-cash-stack"></i> ✅ Enregistrer Dépense
                </a>
            </li>
        </ul>
        <hr class="my-3">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary w-100 mb-2">🏠 Dashboard</a>
    </div>

    <div class="main-content-wrapper">
        
        <header class="d-flex justify-content-between align-items-center border-bottom p-3 bg-body shadow-sm flex-shrink-0">
            <div class="d-flex align-items-center gap-2">
                {{-- Bouton Menu visible sur petit écran --}}
                <button id="sidebarToggle" class="btn btn-outline-secondary d-lg-none">☰</button> 
                {{-- 💡 V14: Icône dynamique --}}
                <button id="toggleDarkMode" class="btn btn-outline-secondary">
                    <span id="darkModeIcon">🌙</span>
                </button>
            </div>
            
            <h1 class="h5 fw-bold text-primary mb-0 mx-auto mx-md-0">Gestion des Dépenses</h1>
            
            <div class="d-none d-md-block" style="width: 100px;"></div> 
        </header>

        <main class="flex-grow-1 container-fluid main-content-area">
            
            {{-- 1. Interface Catégories de Dépense (Plein écran) --}}
            <div class="interface-container" id="category-management">
                <div class="card shadow-sm spense-card flex-grow-1 position-relative">
                     <div class="loader-overlay" id="categoryLoader">
                         <div class="spinner-border text-white" role="status"></div>
                         <p class="mt-2 mb-0">Chargement...</p>
                     </div>
                    <div class="card-header d-flex justify-content-between align-items-center flex-shrink-0">
                        <h2 class="h5 mb-0">Catégories Enregistrées</h2>
                        {{-- V8: Gestion manuelle pour éviter le conflit modal --}}
                        <button class="btn btn-primary btn-sm" id="openAddCategoryModalBtn">
                            <i class="bi bi-plus-circle"></i> Ajouter Catégorie
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 flex-shrink-0">
                            <input type="text" id="categorySearch" class="form-control" placeholder="Rechercher par nom de catégorie...">
                        </div>
                        <div id="categoriesList" class="flex-grow-1 overflow-auto">
                            <p class="text-center text-muted">Chargement des catégories...</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Interface Comptabiliser Dépense (Plein écran, caché par défaut) --}}
            <div class="interface-container" id="transaction-entry" style="display: none;">
                <div class="card shadow-sm spense-card flex-grow-1 position-relative">
                    <div class="loader-overlay" id="entryLoader">
                        <div class="spinner-border text-white" role="status"></div>
                        <p class="mt-2 mb-0">Comptabilisation en cours...</p>
                    </div>
                    <div class="card-header flex-shrink-0">
                        <h2 class="h5 mb-0">Comptabiliser une Dépense</h2>
                    </div>
                    
                    <div class="card-body">
                        
                        {{-- Colonne Saisie --}}
                        <div class="entry-col">
                            <form id="addItemForm" class="flex-grow-1 d-flex flex-column">
                                <h6 class="border-bottom pb-2 mb-3 flex-shrink-0">Nouvelle Transaction</h6>
                                
                                {{-- CONTENU DU FORMULAIRE --}}
                                <div class="form-content-area flex-grow-1">
                                    <div class="row g-2">
                                        <div class="col-md-6 mb-3">
                                            <label for="category_token" class="form-label small">Catégorie</label>
                                            <select id="category_token" class="form-select form-select-sm" required>
                                                <option value="">-- Chargement des catégories... --</option>
                                                {{-- Les options seront remplies par JavaScript (toutes) --}}
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="amount_spens" class="form-label small">Montant (F)</label>
                                            <input type="number" id="amount_spens" class="form-control form-control-sm" step="0.01" required>
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-12">
                                            <label for="description_spens" class="form-label small">Description/Note</label>
                                            <input type="text" id="description_spens" class="form-control form-control-sm" placeholder="Détail (facultatif)">
                                        </div>
                                    </div>
                                    
                                    {{-- Contenu pour forcer le scroll sur mobile --}}
                                    <div style="height: 10px;" class="d-md-none"></div> 

                                </div>
                                {{-- Fin CONTENU DU FORMULAIRE --}}

                                {{-- CONTENEUR D'ACTION (Date + Ajouter au Panier) --}}
                                <div class="form-bottom-actions flex-shrink-0">
                                    <div class="row g-2">
                                        <div class="col-6 mb-3">
                                            <label for="date_spens" class="form-label small">Date</label>
                                            <input type="date" id="date_spens" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                        <div class="col-6 mb-3 d-flex align-items-end">
                                            <button type="submit" class="btn btn-success btn-sm w-100">Ajouter au Panier</button>
                                        </div>
                                    </div>
                                </div>
                                {{-- Fin CONTENEUR D'ACTION --}}

                            </form>
                        </div>
                        
                        {{-- Colonne Panier & Validation --}}
                        <div class="entry-col">
                            <h6 class="border-bottom pb-2 mb-3 flex-shrink-0">Panier de Comptabilisation (<span id="panierCount">0</span>)</h6>
                            {{-- Zone de scroll du détail du panier --}}
                            <div id="panierItems" class="mb-4">
                                <p class="text-muted text-center small">Le panier est vide.</p>
                            </div>
                            
                            {{-- Conteneur de validation - DANS LE FLUX --}}
                            <div class="panier-validation-area" id="panierValidationArea"> 
                                <form id="submitPanierForm" class="flex-shrink-0">
                                    <div class="mb-3">
                                        <label for="payment_method" class="form-label small">Mode de Paiement</label>
                                        <select id="payment_method" name="payment_method" class="form-select" required>
                                            <option value="cash">Espèces</option>
                                            <option value="bank">Virement Bancaire</option>
                                            <option value="check">Chèque</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="panier_total_input" class="form-label fw-bold">Montant Total du Panier (F)</label>
                                        <input type="text" id="panier_total_input" name="total_amount" class="form-control form-control-lg text-end" value="0.00" readonly required>
                                    </div>
                                    <button type="submit" id="comptabiliserBtn" class="btn btn-primary w-100" disabled>
                                        Comptabiliser les Dépenses
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    {{-- 3. MODAUX (Inchangé) --}}
    
    {{-- MODAL GLOBAL DE DIALOGUE/ALERTE --}}
    <div class="modal fade z-index-alert" id="globalMessageModal" tabindex="-1" aria-labelledby="globalMessageLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="globalMessageLabel">Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="globalMessageBody">
                    ...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
    
    {{-- MODAL AJOUT/MODIFICATION CATÉGORIE --}}
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="categoryForm">
                    @csrf
                    <input type="hidden" id="categoryToken" name="token">
                    <div class="modal-header">
                        <h5 class="modal-title" id="categoryModalLabel">Ajouter/Modifier Catégorie</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="description" class="form-label">Nom de la Catégorie</label>
                            <input type="text" class="form-control" id="description" name="description" required>
                        </div>
                        <div class="mb-3">
                            <label for="nature" class="form-label">Nature</label>
                            <select class="form-select" id="nature" name="nature" required>
                                <option value="variable">Variable</option>
                                <option value="fixed">Fixe</option>
                            </select>
                        </div>
                        <div class="mb-3" id="defaultAmountContainer" style="display: none;">
                            <label for="default_amount" class="form-label">Montant par défaut (Facultatif, si Fixe)</label>
                            <input type="number" class="form-control" id="default_amount" name="default_amount" step="0.01">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status" id="categorySubmitLoader"></span>
                            Sauvegarder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- MODAL HISTORIQUE DES TRANSACTIONS --}}
    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historyModalLabel">Historique des Transactions de <span id="historyCategoryName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Filtre par date --}}
                    <form id="historyFilterForm" class="row g-3 mb-4">
                        <input type="hidden" id="historyCategoryToken">
                        <div class="col-md-5">
                            <label for="historyStartDate" class="form-label small">Date Début</label>
                            <input type="date" id="historyStartDate" class="form-control">
                        </div>
                        <div class="col-md-5">
                             <label for="historyEndDate" class="form-label small">Date Fin</label>
                            <input type="date" id="historyEndDate" class="form-control">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-info w-100">Filtrer</button>
                        </div>
                    </form>
                    
                    {{-- Affichage de l'historique --}}
                    <div id="historyModalContent">
                        <p class="text-center text-muted">Veuillez charger l'historique...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>


    {{-- 4. JAVASCRIPT (V14: UX & Notifications) --}}
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        let currentPage = 1;
        let panier = []; 
        
        // Initialisation des instances Modals (Non utilisés pour les messages flash)
        const globalMessageModalElement = document.getElementById('globalMessageModal');
        const globalMessageModal = new bootstrap.Modal(globalMessageModalElement);
        
        const addCategoryModalElement = document.getElementById('addCategoryModal');
        const addCategoryModal = new bootstrap.Modal(addCategoryModalElement);
        
        const historyModalElement = document.getElementById('historyModal');
        const historyModal = new bootstrap.Modal(historyModalElement);


        // 💡 V14: Nouveau système de notification Flash
        function showFlashMessage(message, isError = false) {
            const container = $('#flash-message-container');
            const alertClass = isError ? 'alert-danger flash-error' : 'alert-success flash-success';
            const html = `
                <div class="alert ${alertClass} alert-dismissible fade show mb-2" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            container.append(html);
            const newAlert = container.children().last();
            
            setTimeout(() => {
                newAlert.alert('close'); // Utilise la méthode Bootstrap pour fermer/masquer
            }, 5000); // Disparaît après 5 secondes
        }

        // Ancienne fonction showGlobalMessage (utilisée pour les confirmations complexes/blocantes)
        function showGlobalMessage(title, message, isError = false) {
            $('#globalMessageLabel').text(title);
            $('#globalMessageLabel').toggleClass('text-danger', isError); 
            $('#globalMessageBody').html(message);
            globalMessageModal.show();
        }
        
        // Nettoyage forcé après fermeture du modal de message (si on l'utilise)
        $(globalMessageModalElement).on('hidden.bs.modal', function () {
            $('body').removeClass('modal-open'); 
            $('.modal-backdrop').remove(); 
        });


        // --- NAVIGATION & STRUCTURE (Inchangé) ---
        $('.sidebar-link').on('click', function(e) {
            e.preventDefault();
            const target = $(this).data('target');
            
            $('.sidebar-link').removeClass('active-spense');
            $(this).addClass('active-spense');
            
            $('.main-content-area > .interface-container').hide();
            $(target).show();
            
            if ($(window).width() <= 992) { $('body').removeClass('sidebar-open'); }
        });

        $(document).ready(function() {
            const $body = $('body');
            $('#sidebarToggle').on('click', function(e) { e.stopPropagation(); $body.toggleClass('sidebar-open'); });
            $('#sidebarCloseBtn').on('click', function() { $body.removeClass('sidebar-open'); });
            $body.on('click.sidebar', function(e) { if ($(window).width() <= 992 && $body.hasClass('sidebar-open') && !$(e.target).closest('#sidebar, #sidebarToggle').length) { $body.removeClass('sidebar-open'); } });

            // 💡 V14: Gestion du mode clair/sombre et de l'icône
            const toggleBtn = document.getElementById("toggleDarkMode");
            const html = document.documentElement;
            const iconSpan = document.getElementById("darkModeIcon");

            function updateThemeIcon(theme) {
                iconSpan.textContent = theme === "dark" ? '🌙' : '☀️';
            }

            // Initialisation de l'icône au chargement
            updateThemeIcon(html.getAttribute("data-bs-theme"));

            toggleBtn.addEventListener("click", () => {
                const currentTheme = html.getAttribute("data-bs-theme");
                const newTheme = currentTheme === "light" ? "dark" : "light";
                html.setAttribute("data-bs-theme", newTheme);
                updateThemeIcon(newTheme);
            });


            loadCategories(); 
            loadAllCategoriesForSelect(); 
        });
        
        // --- 1. GESTION DES CATÉGORIES (CRUD & FILTRE) ---

        // Fonction pour charger TOUTES les catégories (pour le sélecteur du formulaire)
        function loadAllCategoriesForSelect() {
            $.get("{{ route('spenses.categories.data') }}", { all: true }, function(response) {
                const categories = Array.isArray(response) ? response : response.data || [];
                populateCategoryOptions(categories);
            }).fail(function() {
                 console.error("Impossible de charger toutes les catégories pour le sélecteur.");
                 let options = '<option value="">-- ERREUR de chargement --</option>';
                 $('#category_token').html(options);
            });
        }

        // Fonction pour peupler le SELECT du formulaire de comptabilisation
        function populateCategoryOptions(categories) {
            let options = '<option value="">-- Choisir une catégorie --</option>';
            categories.forEach(cat => {
                const natureLabel = cat.nature === 'fixed' ? 'Fixe' : 'Variable';
                options += `<option value="${cat.token}" data-nature="${cat.nature}" data-amount="${cat.default_amount || ''}">
                                ${cat.description} (${natureLabel})
                            </option>`;
            });
            $('#category_token').html(options);
        }

        // Fonction pour charger les catégories Paginées (pour l'interface de gestion)
        function loadCategories(page = 1, search = '') {
            currentPage = page;
            $('#categoryLoader').show();
            $('#categoriesList').html('<p class="text-center text-primary mt-3">Chargement...</p>');

            $.get("{{ route('spenses.categories.data') }}", { page: page, search: search }, function(response) {
                $('#categoryLoader').hide();
                let html = '<ul class="list-group list-group-flush">';
                
                if (response.data.length === 0) {
                     html = '<p class="text-center text-warning mt-3">Aucune catégorie trouvée.</p>';
                } else {
                    response.data.forEach(cat => {
                        const natureBadge = cat.nature === 'fixed' ? 'success' : 'info';
                        const amountInfo = cat.default_amount ? `<span class="badge bg-secondary">${parseFloat(cat.default_amount).toFixed(0)} F</span>` : '';
                        
                        const actionButtons = `
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-sm btn-info edit-category" data-category='${JSON.stringify(cat)}'>
                                    <i class="bi bi-pencil"></i> Modifier
                                </button>
                                <button class="btn btn-sm btn-warning view-history" data-bs-toggle="modal" data-bs-target="#historyModal" data-token="${cat.token}" data-name="${cat.description}">
                                    <i class="bi bi-clock-history"></i> Historique
                                </button>
                            </div>
                        `;
                        
                        html += `
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                <div>
                                    <strong>${cat.description}</strong> 
                                    <span class="badge bg-${natureBadge}">${cat.nature.toUpperCase()}</span>
                                    ${amountInfo}
                                </div>
                                ${actionButtons}
                            </li>
                        `;
                    });
                    html += '</ul>';
                    
                    html += '<nav class="mt-3 flex-shrink-0"><ul class="pagination pagination-sm justify-content-center">';
                    for (let i = 1; i <= response.last_page; i++) {
                        html += `<li class="page-item ${i === response.current_page ? 'active' : ''}">
                                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                                 </li>`;
                    }
                    html += '</ul></nav>';
                }
                $('#categoriesList').html(html);
                
            }).fail(function() {
                 $('#categoryLoader').hide();
                 // 💡 V14: Utilisation du Flash message
                 showFlashMessage("Impossible de charger les catégories.", true);
            });
        }
        
        // V8: Ouverture manuelle pour l'ajout
        $('#openAddCategoryModalBtn').on('click', function() {
            $('#categoryForm')[0].reset();
            $('#categoryModalLabel').text('Ajouter Catégorie');
            $('#categoryToken').val('');
            $('#defaultAmountContainer').hide();
            addCategoryModal.show();
        });

        $('#categoriesList').on('click', '.page-link', function(e) {
            e.preventDefault();
            loadCategories($(this).data('page'), $('#categorySearch').val());
        });
        $('#categorySearch').on('keyup', function() {
            loadCategories(1, $(this).val());
        });
        $('#nature').on('change', function() {
            if ($(this).val() === 'fixed') { $('#defaultAmountContainer').show(); } else { $('#defaultAmountContainer').hide(); $('#default_amount').val(''); }
        }).trigger('change'); 
        
        // Gestion de l'édition (Prépare les données) 
        $('#categoriesList').on('click', '.edit-category', function() {
            const data = $(this).data('category');
            
            addCategoryModal.hide(); 

            setTimeout(() => {
                $('#categoryModalLabel').text('Modifier Catégorie: ' + data.description);
                $('#categoryToken').val(data.token);
                $('#description').val(data.description);
                $('#nature').val(data.nature).trigger('change');
                $('#default_amount').val(data.default_amount);
                
                addCategoryModal.show();
            }, 50); 
        });
        
        // Réinitialisation du formulaire après fermeture (par la croix/bouton)
        $(addCategoryModalElement).on('hidden.bs.modal', function () {
            $('#categoryForm')[0].reset();
            $('#categoryModalLabel').text('Ajouter/Modifier Catégorie');
            $('#categoryToken').val('');
            $('#defaultAmountContainer').hide();
        });
        
        // Soumission du formulaire (Création/Modification)
        $('#categoryForm').on('submit', function(e) {
            e.preventDefault();
            const token = $('#categoryToken').val();
            
            const url = token 
                ? "{{ route('spenses.categories.update', ['spens' => ':token']) }}".replace(':token', token) 
                : "{{ route('spenses.categories.store') }}";

            let formData = $(this).serializeArray();
            
            if (token) {
                formData.push({name: '_method', value: 'PUT'});
            }
            
            $('#categorySubmitLoader').removeClass('d-none');
            const $submitButton = $('#categoryForm button[type="submit"]');
            $submitButton.prop('disabled', true);
            
            $.ajax({
                url: url,
                method: 'POST', 
                data: $.param(formData), 
                success: function(response) {
                    addCategoryModal.hide(); 
                    // 💡 V14: Utilisation du Flash message
                    showFlashMessage(response.message);
                    loadCategories(currentPage, $('#categorySearch').val()); 
                    loadAllCategoriesForSelect(); 
                },
                error: function(xhr) {
                    const message = xhr.responseJSON ? (xhr.responseJSON.message || JSON.stringify(xhr.responseJSON)) : "Erreur inconnue.";
                    // 💡 V14: Utilisation du Flash message pour erreur
                    showFlashMessage(message, true);
                },
                complete: function() {
                    $('#categorySubmitLoader').addClass('d-none');
                    $submitButton.prop('disabled', false); 
                }
            });
        });

        // --- 2. GESTION DU PANIER & COMPTABILISATION (Inchangé) ---
        function updatePanierDisplay() {
            let total = 0;
            let html = '<ul class="list-group list-group-flush">';
            if (panier.length === 0) {
                 html = '<p class="text-muted text-center small">Le panier est vide.</p>';
                 $('#comptabiliserBtn').prop('disabled', true);
            } else {
                panier.forEach((item, index) => {
                    total += parseFloat(item.amount);
                    html += `
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent panier-item">
                            <div class="flex-grow-1 me-2">
                                <strong class="text-primary">${item.category_desc}</strong> 
                                <span class="d-block small text-muted">${item.description} (${item.date})</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="fw-bold me-2">${item.amount} F</span>
                                <button class="btn btn-danger btn-sm flex-shrink-0 remove-item" data-index="${index}" title="Supprimer la ligne">&times;
                                </button>
                            </div>
                        </li>
                    `;
                });
                html += '</ul>';
                $('#comptabiliserBtn').prop('disabled', false);
            }
            $('#panierItems').html(html);
            $('#panierCount').text(panier.length);
            $('#panier_total_input').val(total.toFixed(2)); 
        }
        $('#addItemForm').on('submit', function(e) {
            e.preventDefault();
            const categorySelect = $('#category_token option:selected');
            if (!categorySelect.val()) { showFlashMessage("Veuillez choisir une catégorie.", true); return; }
            if (!$('#amount_spens').val() || parseFloat($('#amount_spens').val()) <= 0) { showFlashMessage("Veuillez saisir un montant valide.", true); return; }
            const newItem = {
                category_token: categorySelect.val(),
                category_desc: categorySelect.text().trim().split('(')[0].trim(),
                amount: parseFloat($('#amount_spens').val()).toFixed(2),
                description: $('#description_spens').val() || 'Aucune description',
                date: $('#date_spens').val(),
            };
            panier.push(newItem);
            updatePanierDisplay();
            $('#amount_spens').val('');
            $('#description_spens').val('');
            $('#category_token').val(''); 
            showFlashMessage("Article ajouté au panier.");
        });
        $('#panierItems').on('click', '.remove-item', function() {
            const index = $(this).data('index');
            if (index !== undefined && !isNaN(index) && index >= 0 && index < panier.length) {
                panier.splice(index, 1);
                updatePanierDisplay();
                showFlashMessage("Article retiré du panier.");
            } else {
                 showFlashMessage("Impossible de trouver l'élément à supprimer dans le panier.", true);
            }
        });
        $('#submitPanierForm').on('submit', function(e) {
            e.preventDefault();
            if (panier.length === 0) { showFlashMessage("Le panier est vide.", true); return; }
            $('#entryLoader').show();
            $('#comptabiliserBtn').prop('disabled', true);
            $.post("{{ route('spenses.comptabiliser') }}", { 
                items: panier,
                payment_method: $('#payment_method').val(),
            }, function(response) {
                // 💡 V14: Utilisation du Flash message
                showFlashMessage(response.message);
                panier = []; 
                updatePanierDisplay();
                $('#submitPanierForm')[0].reset(); 
            }).fail(function(xhr) {
                const message = xhr.responseJSON ? (xhr.responseJSON.message || JSON.stringify(xhr.responseJSON)) : "Erreur inconnue lors de la comptabilisation.";
                // 💡 V14: Utilisation du Flash message pour erreur
                showFlashMessage(message, true);
            }).always(function() {
                $('#entryLoader').hide();
                $('#comptabiliserBtn').prop('disabled', false);
            });
        });
        $('#category_token').on('change', function() {
            const selected = $('#category_token option:selected');
            const nature = selected.data('nature');
            const defaultAmount = selected.data('amount');
            if (nature === 'fixed' && defaultAmount) {
                $('#amount_spens').val(defaultAmount).prop('readonly', true);
            } else {
                $('#amount_spens').val('').prop('readonly', false);
            }
        });

        // --- 3. GESTION DE L'HISTORIQUE & ANNULATION (Inchangé) ---
        function loadHistory(categoryToken, startDate = null, endDate = null, page = 1) {
            const url = "{{ route('spenses.transactions.history', ['spens' => ':token']) }}".replace(':token', categoryToken);
            $('#historyModalContent').html('<p class="text-center text-primary mt-3"><div class="spinner-border spinner-border-sm"></div> Chargement des transactions...</p>');
            $.get(url, { start_date: startDate, end_date: endDate, page: page }, function(response) {
                let items = Array.isArray(response) ? response : response.data || [];
                let lastPage = response.last_page || 1;
                let currentPage = response.current_page || 1;
                let html = '<ul class="list-group list-group-flush">';
                if (items.length === 0) {
                     html = '<p class="text-center text-muted mt-3">Aucune transaction trouvée.</p>';
                } else {
                    items.forEach(item => {
                        const statusBadge = item.status === 'validated' ? 'success' : 'danger';
                        const actionText = item.status === 'validated' ? 'Annuler' : 'Valider';
                        const actionClass = item.status === 'validated' ? 'btn-danger' : 'btn-success';
                        const actionButton = `
                            <button class="btn ${actionClass} btn-sm cancel-transaction" 
                                    data-item-token="${item.token}" 
                                    data-action="${actionText}">
                                ${actionText}
                            </button>
                        `;
                        html += `
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                <div>
                                    <strong>${item.amount_spens} F</strong> - ${item.description || 'N/A'}
                                    <br><small class="text-muted">Date: ${item.date_spens} | Mode: ${item.payment_mode}</small>
                                </div>
                                <div>
                                    <span class="badge bg-${statusBadge} me-2">${item.status.toUpperCase()}</span>
                                    ${actionButton}
                                </div>
                            </li>
                        `;
                    });
                    html += '</ul>';
                    if (lastPage > 1 || (items.length > 5 && !startDate && !endDate)) {
                        let paginationHtml = '<nav class="mt-3"><ul class="pagination pagination-sm justify-content-center">';
                        for (let i = 1; i <= lastPage; i++) {
                            paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                                                <a class="page-link history-page-link" href="#" data-page="${i}">${i}</a>
                                            </li>`;
                        }
                        paginationHtml += '</ul></nav>';
                        html += paginationHtml;
                    }
                }
                $('#historyModalContent').html(html);
            });
        }
        $('#categoriesList').on('click', '.view-history', function() {
            const token = $(this).data('token');
            const name = $(this).data('name');
            $('#historyCategoryToken').val(token);
            $('#historyCategoryName').text(name);
            $('#historyFilterForm')[0].reset(); 
            loadHistory(token, null, null, 1); 
        });
        $('#historyFilterForm').on('submit', function(e) {
            e.preventDefault();
            const token = $('#historyCategoryToken').val();
            const start = $('#historyStartDate').val();
            const end = $('#historyEndDate').val();
            loadHistory(token, start, end, 1);
        });
        $('#historyModalContent').on('click', '.history-page-link', function(e) {
            e.preventDefault();
            const token = $('#historyCategoryToken').val();
            const start = $('#historyStartDate').val();
            const end = $('#historyEndDate').val();
            loadHistory(token, start, end, $(this).data('page'));
        });
        $('#historyModalContent').on('click', '.cancel-transaction', function() {
            const action = $(this).data('action');
            if (!confirm(`Confirmer l'action : ${action} cette transaction ?`)) { return; }
            const itemToken = $(this).data('item-token');
            const categoryToken = $('#historyCategoryToken').val(); 
            const start = $('#historyStartDate').val();
            const end = $('#historyEndDate').val();
            $.post("{{ route('spenses.cancel.item') }}", { 
                item_token: itemToken,
            }, function(response) {
                // 💡 V14: Utilisation du Flash message
                showFlashMessage(response.message);
                loadHistory(categoryToken, start, end, 1); 
            }).fail(function() {
                // 💡 V14: Utilisation du Flash message pour erreur
                showFlashMessage("Erreur lors de la mise à jour de la transaction.", true);
            });
        });
    </script>
</body>
</html>

