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
        /* Styles pour le Loader Modal (omis dans l'original, mais nécessaire) */
        #loader {
            background-color: rgba(0,0,0,0.5); 
            z-index: 2500;
        }
    </style>
</head>
<body class="d-flex">
    
    {{-- ================================================= --}}
    {{-- 🧩 Intégration de la Sidebar (Navigation) 🧩 --}}
    {{-- ================================================= --}}
    <aside class="offcanvas-lg offcanvas-start bg-body-tertiary border-end" tabindex="-1" id="sidebar">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title text-primary fw-bold">🧺 Pressing Manager</h5>
            <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column p-0">
            <nav class="nav flex-column p-3">
                <a href="{{ route('dashboard') }}" class="nav-link text-secondary">🏠 Tableau de bord</a>
                <a href="{{ route('order') }}" class="nav-link text-secondary">➕ Enregistrer un dépôt</a>
                <a href="#" class="nav-link text-primary active">📦 Gérer les Dépôts</a>
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
        {{-- ================================================= --}}
        {{-- 🧩 Header (Titre et Dark Mode) 🧩 --}}
        {{-- ================================================= --}}
        <header class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-body shadow-sm">
            <button class="btn btn-outline-secondary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar">☰</button>
            <h2 class="h5 fw-bold text-primary mb-0">Gestion des Dépôts (Actions)</h2>
            <div class="d-flex align-items-center gap-3">
                <span class="text-secondary">Bonjour, <strong>{{ Auth::User()->name }}</strong></span>
                <button id="toggleDarkMode" class="btn btn-outline-secondary">🌙</button>
            </div>
        </header>

        {{-- ================================================= --}}
        {{-- 🧩 Contenu Principal 🧩 --}}
        {{-- ================================================= --}}
        <main class="flex-grow-1 p-4 bg-body-tertiary">
            <div class="d-flex flex-column flex-md-row gap-3 mb-4">
                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#manageStatusModal">🛠️ Gérer le statut</button>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#cashInModal">💰 Encaissement</button>
                <button class="btn btn-info ms-md-auto" data-bs-toggle="modal" data-bs-target="#filterStateModal">📋 Filtre de Dépôts</button>
            </div>

            <div class="alert alert-info">
                Les actions ci-dessus utilisent le **token** du dépôt pour les opérations.
            </div>

        </main>
    </div>

    {{-- ================================================= --}}
    {{-- 🧩 MODALS (Gérer Statut, Encaissement, Filtre) 🧩 --}}
    {{-- (Inclus tel quel du code fourni) --}}
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
                            <label for="remainingAmountCash" class="form-label">Montant Restant à Payer (€)</label>
                            <input type="number" step="0.01" class="form-control" id="remainingAmountCash" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="amountPaidCash" class="form-label">Montant Encaissé (€)</label>
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

    {{-- Modal 3: Filtre --}}
    <div class="modal fade" id="filterStateModal" tabindex="-1" aria-labelledby="filterStateModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterStateModalLabel">📋 Filtrer les Dépôts</h5>
                    <button type="button" class="btn-close reset-on-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="filterForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="startDateFilter" class="form-label">Date de début de dépôt</label>
                            <input type="date" class="form-control" id="startDateFilter" name="start_date">
                        </div>
                        <div class="mb-3">
                            <label for="endDateFilter" class="form-label">Date de fin de dépôt</label>
                            <input type="date" class="form-control" id="endDateFilter" name="end_date">
                        </div>
                        <div class="mb-3">
                            <label for="statusFilter" class="form-label">Statut du dépôt</label>
                            <select class="form-select" id="statusFilter" name="status">
                                <option value="">Tous les statuts</option>
                                <option value="pending">En attente (Pending)</option>
                                <option value="ready">Prêt (Ready)</option>
                                <option value="delivered">Livré (Delivered)</option>
                                <option value="cancelled">Annulé (Cancelled)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary reset-on-close" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-outline-secondary" id="resetFilterBtn">Réinitialiser le filtre</button>
                        <button type="submit" class="btn btn-info">Appliquer le filtre</button>
                    </div>
                </form>
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
    {{-- 🧩 Scripts JavaScript (Inclus tel quel) 🧩 --}}
    {{-- ================================================= --}}
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // --- Fonctions utilitaires (toggleDarkMode, showNotification, showLoader) ---
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
            modalMessage.html(message);
            modal.modal('show');
            setTimeout(() => { modal.modal('hide'); }, 5000);
        }

        function showLoader() { $('#loader').removeClass('d-none'); }
        function hideLoader() { $('#loader').addClass('d-none'); }

        // --- Logique spécifique des Modals ---
        
        let pendingOrders = []; 
        let nonFullyPaidOrders = []; 


        // Fonction pour charger et remplir les select de références (utilisant le token)
        function loadModalReferences() {
            showLoader();
            // L'URL API est définie SANS le préfixe '/api/' car elle est probablement gérée dans un groupe de routes Web ou API spécifique.
            // Si vos routes sont dans routes/api.php, vous devriez ajouter /api/ devant: $.get('/api/orders/references')
            $.get('/orders/references') 
                .done(function(data) {
                    pendingOrders = data.pending_orders || [];
                    nonFullyPaidOrders = data.non_fully_paid_orders || [];
                    
                    // Remplissage Gérer Statut (pending)
                    const selectStatus = $('#orderTokenSelectStatus').empty().append('<option value="">Sélectionner une référence...</option>');
                    pendingOrders.forEach(order => {
                        selectStatus.append(`<option value="${order.token}" data-client="${order.client_name}">${order.reference}</option>`);
                    });

                    // Remplissage Encaissement (non intégralement payé)
                    const selectCash = $('#orderTokenSelectCash').empty().append('<option value="">Sélectionner une référence...</option>');
                    nonFullyPaidOrders.forEach(order => {
                        const remaining = (order.total_amount - order.paid_amount).toFixed(2);
                        selectCash.append(`<option value="${order.token}" data-client="${order.client_name}" data-remaining="${remaining}">${order.reference} - (${remaining} € restants)</option>`);
                    });

                })
                .fail(function() {
                    showNotification('Erreur de chargement des listes de références.', false);
                })
                .always(function() { hideLoader(); });
        }
        
        $(document).ready(function() {
            loadModalReferences();
        });


        // --- Recherche automatique de client dans les modals (Basée sur le token) ---

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

        // --- SOUMISSION DES FORMULAIRES (AJAX) ---

        // Soumission Gérer Statut (Utilise le token dans l'URL)
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
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "Erreur lors de la mise à jour du statut.";
                    showNotification(msg, false);
                },
                complete: function() { hideLoader(); }
            });
        });

        // Soumission Encaissement (Utilise le token dans l'URL)
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
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "Erreur lors de l'encaissement.";
                    showNotification(msg, false);
                },
                complete: function() { hideLoader(); }
            });
        });

        // Soumission Filtre
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            // Ici, vous appelerez l'API de filtre si vous réactivez le tableau.
            showNotification("Le filtre a été appliqué. Cette action ne fait que simuler l'application car le tableau est désactivé.", true);
            $('#filterStateModal').modal('hide');
        });

        // Bouton Réinitialiser du filtre
        $('#resetFilterBtn').on('click', function() {
            $('#filterForm')[0].reset();
            showNotification("Le filtre a été réinitialisé.", true);
            $('#filterStateModal').modal('hide');
        });
        
        // --- RÉINITIALISATION DES CHAMPS LORS DE LA FERMETURE DES MODALS ---
        
        $('.reset-on-close').on('click', function() {
            $('#manageStatusForm')[0].reset();
            $('#cashInForm')[0].reset();
            $('#filterForm')[0].reset();
        });

    </script>
</body>
</html>

Erreur transactionnelle lors de l'enregistrement de l'encaissement. PDOException: SQLSTATE[HY000]: General error: 1364 Field 'user_token' doesn't have a default value in C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php:53 Stack trace: #0 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php(53): PDOStatement->execute() #1 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\Connection.php(811): Illuminate\Database\MySqlConnection->Illuminate\Database\{closure}('insert into `tr...', Array) #2 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\Connection.php(778): Illuminate\Database\Connection->runQueryCallback('insert into `tr...', Array, Object(Closure)) #3 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php(42): Illuminate\Database\Connection->run('insert into `tr...', Array, Object(Closure)) #4 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\Query\Builder.php(3804): Illuminate\Database\MySqlConnection->insert('insert into `tr...', Array) #5 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\Eloquent\Builder.php(2220): Illuminate\Database\Query\Builder->insert(Array) #6 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\Eloquent\Model.php(1412): Illuminate\Database\Eloquent\Builder->__call('insert', Array) #7 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\Eloquent\Model.php(1240): Illuminate\Database\Eloquent\Model->performInsert(Object(Illuminate\Database\Eloquent\Builder)) #8 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\Eloquent\Builder.php(1204): Illuminate\Database\Eloquent\Model->save() #9 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Support\helpers.php(390): Illuminate\Database\Eloquent\Builder->Illuminate\Database\Eloquent\{closure}(Object(App\Models\Transaction)) #10 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\Eloquent\Builder.php(1203): tap(Object(App\Models\Transaction), Object(Closure)) #11 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Support\Traits\ForwardsCalls.php(23): Illuminate\Database\Eloquent\Builder->create(Array) #12 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\Eloquent\Model.php(2540): Illuminate\Database\Eloquent\Model->forwardCallTo(Object(Illuminate\Database\Eloquent\Builder), 'create', Array) #13 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\Eloquent\Model.php(2556): Illuminate\Database\Eloquent\Model->__call('create', Array) #14 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\app\Http\Controllers\OrderController.php(266): Illuminate\Database\Eloquent\Model::__callStatic('create', Array) #15 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\OrderController->cashIn(Object(Illuminate\Http\Request), '6b9b2a70-c799-4...') #16 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\OrderController), 'cashIn') #17 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController() #18 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run() #19 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request)) #20 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #21 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure)) #22 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Auth\Middleware\Authenticate.php(63): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #23 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Auth\Middleware\Authenticate->handle(Object(Illuminate\Http\Request), Object(Closure)) #24 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken.php(87): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #25 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\VerifyCsrfToken->handle(Object(Illuminate\Http\Request), Object(Closure)) #26 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\View\Middleware\ShareErrorsFromSession.php(48): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #27 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\View\Middleware\ShareErrorsFromSession->handle(Object(Illuminate\Http\Request), Object(Closure)) #28 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php(120): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #29 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php(63): Illuminate\Session\Middleware\StartSession->handleStatefulRequest(Object(Illuminate\Http\Request), Object(Illuminate\Session\Store), Object(Closure)) #30 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Session\Middleware\StartSession->handle(Object(Illuminate\Http\Request), Object(Closure)) #31 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse.php(36): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #32 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse->handle(Object(Illuminate\Http\Request), Object(Closure)) #33 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Cookie\Middleware\EncryptCookies.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #34 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Cookie\Middleware\EncryptCookies->handle(Object(Illuminate\Http\Request), Object(Closure)) #35 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #36 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure)) #37 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request)) #38 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route)) #39 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request)) #40 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request)) #41 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request)) #42 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #43 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure)) #44 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure)) #45 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #46 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure)) #47 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure)) #48 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #49 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure)) #50 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #51 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure)) #52 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(48): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #53 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure)) #54 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #55 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure)) #56 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #57 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure)) #58 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #59 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure)) #60 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #61 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure)) #62 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request)) #63 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Application.php(1220): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request)) #64 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\public\index.php(20): Illuminate\Foundation\Application->handleRequest(Object(Illuminate\Http\Request)) #65 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php(23): require_once('C:\\Users\\pc pro...') #66 {main} Next Illuminate\Database\QueryException: SQLSTATE[HY000]: General error: 1364 Field 'user_token' doesn't have a default value (Connection: mysql, SQL: insert into `transactions` (`order_token`, `amount`, `payment_method`, `payment_date`, `token`, `updated_at`, `created_at`) values (6b9b2a70-c799-4e0b-816f-4e6eee7339b3, 500.00, cash, 2025-11-24 21:53:51, a355b314-b8e5-44fd-a7d3-e593a334f997, 2025-11-24 21:53:51, 2025-11-24 21:53:51)) in C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\Connection.php:824 Stack trace: #0 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\Connection.php(778): Illuminate\Database\Connection->runQueryCallback('insert into `tr...', Array, Object(Closure)) #1 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php(42): Illuminate\Database\Connection->run('insert into `tr...', Array, Object(Closure)) #2 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\Query\Builder.php(3804): Illuminate\Database\MySqlConnection->insert('insert into `tr...', Array) #3 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\Eloquent\Builder.php(2220): Illuminate\Database\Query\Builder->insert(Array) #4 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\Eloquent\Model.php(1412): Illuminate\Database\Eloquent\Builder->__call('insert', Array) #5 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\Eloquent\Model.php(1240): Illuminate\Database\Eloquent\Model->performInsert(Object(Illuminate\Database\Eloquent\Builder)) #6 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\Eloquent\Builder.php(1204): Illuminate\Database\Eloquent\Model->save() #7 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Support\helpers.php(390): Illuminate\Database\Eloquent\Builder->Illuminate\Database\Eloquent\{closure}(Object(App\Models\Transaction)) #8 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\Eloquent\Builder.php(1203): tap(Object(App\Models\Transaction), Object(Closure)) #9 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Support\Traits\ForwardsCalls.php(23): Illuminate\Database\Eloquent\Builder->create(Array) #10 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\Eloquent\Model.php(2540): Illuminate\Database\Eloquent\Model->forwardCallTo(Object(Illuminate\Database\Eloquent\Builder), 'create', Array) #11 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Database\Eloquent\Model.php(2556): Illuminate\Database\Eloquent\Model->__call('create', Array) #12 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\app\Http\Controllers\OrderController.php(266): Illuminate\Database\Eloquent\Model::__callStatic('create', Array) #13 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\OrderController->cashIn(Object(Illuminate\Http\Request), '6b9b2a70-c799-4...') #14 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\OrderController), 'cashIn') #15 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController() #16 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run() #17 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request)) #18 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #19 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure)) #20 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Auth\Middleware\Authenticate.php(63): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #21 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Auth\Middleware\Authenticate->handle(Object(Illuminate\Http\Request), Object(Closure)) #22 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken.php(87): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #23 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\VerifyCsrfToken->handle(Object(Illuminate\Http\Request), Object(Closure)) #24 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\View\Middleware\ShareErrorsFromSession.php(48): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #25 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\View\Middleware\ShareErrorsFromSession->handle(Object(Illuminate\Http\Request), Object(Closure)) #26 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php(120): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #27 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php(63): Illuminate\Session\Middleware\StartSession->handleStatefulRequest(Object(Illuminate\Http\Request), Object(Illuminate\Session\Store), Object(Closure)) #28 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Session\Middleware\StartSession->handle(Object(Illuminate\Http\Request), Object(Closure)) #29 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse.php(36): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #30 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse->handle(Object(Illuminate\Http\Request), Object(Closure)) #31 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Cookie\Middleware\EncryptCookies.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #32 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Cookie\Middleware\EncryptCookies->handle(Object(Illuminate\Http\Request), Object(Closure)) #33 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #34 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure)) #35 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request)) #36 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route)) #37 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request)) #38 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request)) #39 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request)) #40 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #41 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure)) #42 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure)) #43 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #44 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure)) #45 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure)) #46 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #47 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure)) #48 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #49 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure)) #50 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(48): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #51 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure)) #52 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #53 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure)) #54 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #55 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure)) #56 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #57 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure)) #58 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request)) #59 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure)) #60 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request)) #61 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\Application.php(1220): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request)) #62 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\public\index.php(20): Illuminate\Foundation\Application->handleRequest(Object(Illuminate\Http\Request)) #63 C:\Users\pc pro\Desktop\BUR\PAGEPRO\monpressing\vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php(23): require_once('C:\\Users\\pc pro...') #64 {main}

