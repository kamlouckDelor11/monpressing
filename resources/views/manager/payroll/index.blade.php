{{-- resources/views/manager/payroll/index.blade.php --}}

<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestion de la Paie | Pressing Manager</title>
    
    {{-- Assurez-vous d'avoir les liens vers Bootstrap et jQuery/Ajax --}}
    @vite(['resources/css/app.css', 'resources/js/app.js']) 
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        /* Styles de base pour la structure */
        body { display: flex; min-height: 100vh; }
        #sidebar { width: 250px; flex-shrink: 0; transition: margin-left 0.3s; }
        .main-content-wrapper { flex-grow: 1; display: flex; flex-direction: column; }
        
        /* Styles pour le Loader, Alerte, etc. */
        #loaderOverlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 2000; display: none; justify-content: center; align-items: center; }
        .tab-content > .tab-pane { padding: 1rem 0; }
        .payroll-item-card { border: 1px solid #333; margin-bottom: 10px; padding: 10px; border-radius: 5px; }
        .net-paid-display { font-size: 1.1rem; font-weight: bold; }

        /* GESTION RESPONSIVE/MOBILE */
        @media (max-width: 768px) {
            #sidebar { 
                position: fixed; 
                height: 100%; 
                z-index: 1030; 
                margin-left: -250px; /* Masqué par défaut */
                transition: margin-left 0.3s ease-in-out;
            }
            body.sidebar-open #sidebar { 
                margin-left: 0; /* Affiché */
            }
            /* L'overlay semi-transparent pour fermer */
            body.sidebar-open::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1020;
            }
            .main-content-wrapper { margin-left: 0 !important; }
            #sidebarToggle { display: block !important; }
            
            /* Amélioration spécifique pour la table du personnel sur mobile */
            #personnel .table-responsive {
                 overflow-x: auto; 
            }
            #personnel table {
                min-width: 480px; 
            }
            /* Ajustement de la disposition des en-têtes et du bouton d'ajout */
            #personnel .d-flex.flex-wrap {
                justify-content: space-between !important;
            }
            #personnel #createEmployeBtn {
                width: 100%;
                margin-top: 0.5rem;
            }
            #personnel h5 {
                width: 100%;
                text-align: center;
            }
            
            /* Masquer le texte des boutons d'action sur très petit écran */
            .btn-action-text {
                display: none;
            }
            .btn-action-icon {
                display: inline;
                padding: 0; /* Supprime le padding du bouton pour l'icône seule */
            }
            
            /* S'assurer que le titre est centré/géré dans le header */
            .header-content {
                display: flex;
                justify-content: space-between; /* Pour garder le titre visible */
                align-items: center;
                width: 100%;
            }
        }
        @media (min-width: 769px) {
             .btn-action-icon {
                display: none;
            }
        }
    </style>
</head>
<body class="d-flex">
    {{-- loader --}}
    @include('partials.loader')
    
    <div id="sidebar" class="bg-dark text-white p-3 shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="h6 text-uppercase fw-bold mb-0">Menu Paie</h3>
            <button id="sidebarCloseBtn" class="btn btn-close btn-close-white d-md-none" aria-label="Close" style="cursor: pointer;"></button>
        </div>

        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#personnel">👤 Personnel</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#creation">💰 Création Paie</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#paiement">✅ Règlement Paie</a>
            </li>
        </ul>
        <hr class="my-3">
        <p class="small text-muted">Retour au Dashboard</p>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary w-100">🏠 Dashboard</a>
    </div>

    <div class="main-content-wrapper">
        
        <header class="d-flex justify-content-between align-items-center border-bottom p-3 bg-body shadow-sm">
            <div class="d-flex align-items-center gap-2">
                <button id="sidebarToggle" class="btn btn-outline-secondary d-md-none" style="display: none;">☰</button>
                <button id="toggleDarkMode" class="btn btn-outline-secondary">🌙</button>
            </div>
            
            <h2 class="h5 fw-bold text-primary mb-0 mx-auto mx-md-0">Gestion de la Paie</h2>
            
            <div class="d-none d-md-block" style="width: 100px;"></div> 
        </header>

        <main class="flex-grow-1 container-fluid py-4">
            
            <div id="alertContainer" class="mb-3" style="display: none;"></div>
            
            <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1070;">
                <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body"></div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>

            <div class="tab-content" id="payrollTabsContent">
                
                {{-- TAB 1: État du Personnel --}}
                <div class="tab-pane fade show active" id="personnel" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <h5 class="card-title mb-2 mb-md-0">Liste des Employés</h5>
                        <button id="createEmployeBtn" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalEmploye">➕ Ajouter un employé</button>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nom Complet</th>
                                            <th>Fonction</th>
                                            <th class="d-none d-md-table-cell">Salaire de Base</th>
                                            <th class="d-none d-md-table-cell">Date d'Embauche</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="employeTableBody">
                                        {{-- Contenu chargé par AJAX --}}
                                    </tbody>
                                </table>
                            </div>
                            <nav id="employePaginationLinks" class="mt-3 d-flex justify-content-center"></nav>
                        </div>
                    </div>
                </div>

                {{-- TAB 2: Création de la Paie --}}
                <div class="tab-pane fade" id="creation" role="tabpanel">
                    <div class="card shadow-sm position-relative">
                        {{-- Loader spécifique Création --}}
                        <div id="creationLoader" class="position-absolute top-0 start-0 w-100 h-100 d-none justify-content-center align-items-center" style="background: rgba(255,255,255,0.8); z-index: 10; border-radius: inherit;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Comptabilisation de la Paie</h5>
                            
                            <div class="row g-3 mb-3 align-items-end border-bottom pb-3">
                                <div class="col-6 col-md-3">
                                    <label for="payrollMonth" class="form-label">Mois</label>
                                    <select id="payrollMonth" class="form-select"></select>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label for="payrollYear" class="form-label">Année</label>
                                    <input type="number" id="payrollYear" class="form-control" readonly>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="selectEmploye" class="form-label">Sélectionner l'Employé</label>
                                    <select id="selectEmploye" class="form-select"></select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <label for="baseSalaryInput" class="form-label">Salaire de Base</label>
                                    <input type="number" id="baseSalaryInput" class="form-control" step="0.01" readonly>
                                </div>
                                <div class="col-6 col-md-2 offset-md-8">
                                    <button id="addToPayrollBtn" class="btn btn-primary w-100 mt-md-4" disabled>Ajouter</button>
                                </div>
                            </div>

                            <div id="payrollItemsContainer">
                                {{-- Les formulaires de paie individuels s'affichent ici --}}
                            </div>
                            
                            <div class="mt-4">
                                <button id="savePayrollBtn" class="btn btn-success" style="display: none;">💾 Sauvegarder la Paie</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB 3: Règlement des Paies --}}
                <div class="tab-pane fade" id="paiement" role="tabpanel">
                    <div class="card shadow-sm position-relative">
                        {{-- Loader spécifique Paiement --}}
                        <div id="paymentLoader" class="position-absolute top-0 start-0 w-100 h-100 d-none justify-content-center align-items-center" style="background: rgba(255,255,255,0.8); z-index: 10; border-radius: inherit;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Interface de Paiement</h5>
                            <form id="payPaieForm">
                                @csrf
                                <div class="row g-3 mb-4">
                                    <div class="col-12 col-md-6">
                                        <label for="selectPaie" class="form-label">Sélectionner la Paie à Régler</label>
                                        <select id="selectPaie" name="paie_token" class="form-select" required>
                                            <option value="">-- Choisir une paie non réglée --</option>
                                            {{-- Options chargées par le contrôleur --}}
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="transactionDate" class="form-label">Date de Règlement</label>
                                        <input type="date" id="transactionDate" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="descriptionInput" class="form-label">Description Dépense</label>
                                        <input type="text" id="descriptionInput" name="description" class="form-control" value="" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="paymentMode" class="form-label">Mode de Paiement</label>
                                        <select id="paymentMode" name="payment_mode" class="form-select" required>
                                            <option value="cash">Espèces</option>
                                            <option value="transfer">Virement Bancaire</option>
                                            <option value="mobile">Paiement Mobile</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div id="paieDetails" class="row mb-4 border p-3 rounded g-2" style="display:none;">
                                    <h6>Synthèse de la Paie</h6>
                                    <div class="col-6 col-md-4">Base Salaires: <span id="totalBaseSalaryDisplay" class="fw-bold">0</span></div>
                                    <div class="col-6 col-md-4">Avantages: <span id="totalAdvantagesDisplay" class="fw-bold">0</span></div>
                                    <div class="col-6 col-md-4">Primes: <span id="totalPrimesDisplay" class="fw-bold">0</span></div>
                                    <div class="col-6 col-md-4">Ret. Fiscales: <span id="totalFiscalRetentionsDisplay" class="fw-bold text-danger">0</span></div>
                                    <div class="col-6 col-md-4">Ret. Sociales: <span id="totalSocialRetentionsDisplay" class="fw-bold text-danger">0</span></div>
                                    <div class="col-6 col-md-4">Ret. Exc.: <span id="totalExceptionalRetentionDisplay" class="fw-bold text-danger">0</span></div>
                                    <div class="col-6 col-md-4">Cotis. Pat.: <span id="totalPatronalContributionsDisplay" class="fw-bold">0</span></div>
                                    <div class="col-6 col-md-4">Charges Fiscales: <span id="totalFiscalChargesDisplay" class="fw-bold">0</span></div>
                                    <div class="col-12 col-md-4 text-center mt-3">
                                        <label class="form-label">Montant Net à Payer</label>
                                        <input type="text" id="netToPayInput" name="amount" class="form-control text-center text-success" readonly required>
                                    </div>
                                </div>
                                
                                <button type="submit" id="submitPaymentBtn" class="btn btn-success" disabled>Régler la Paie</button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <div id="loaderOverlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Chargement...</span>
        </div>
    </div>
    
    <div class="modal fade" id="modalEmploye" tabindex="-1" aria-labelledby="modalEmployeLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-md-down">
            <div class="modal-content position-relative">
                {{-- Loader spécifique au modal (Centré) --}}
                <div id="modalEmployeLoader" class="position-absolute top-0 start-0 w-100 h-100 d-none justify-content-center align-items-center" style="background: rgba(255,255,255,0.8); z-index: 1056; border-radius: inherit;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>
                <form id="formEmploye">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEmployeLabel">Ajouter/Modifier un Employé</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="employeToken" name="token">
                        
                        <label for="fullName" class="form-label">Nom Complet</label>
                        <input type="text" id="fullName" name="full_name" class="form-control mb-2" required>
                        
                        <label for="functionInput" class="form-label">Fonction</label>
                        <input type="text" id="functionInput" name="function" class="form-control mb-2" required>
                        
                        <label for="baseSalaryInputModal" class="form-label">Salaire de Base</label>
                        <input type="number" id="baseSalaryInputModal" name="base_salary" class="form-control mb-2" step="0.01" required>
                        
                        <label for="hiringDate" class="form-label">Date d'Embauche</label>
                        <input type="date" id="hiringDate" name="hiring_date" class="form-control mb-2" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" id="saveEmployeBtn">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        let allEmployes = []; 
        let payrollCart = {}; 
        const currentYear = new Date().getFullYear();
        const baseUrl = '{{ url('manager/payroll') }}'; 

        // --- UTILS : Loader, Toast, Alert ---
        function showLoader() { $('#loaderOverlay').fadeIn(100); }
        function hideLoader() { $('#loaderOverlay').fadeOut(100); }
        
        /**
         * Affiche un message de succès (Toast)
         * @param {string} message 
         */
        function showToast(message) {
            const toastElement = document.getElementById('successToast');
            const toastBody = $('#successToast').find('.toast-body');
            
            toastBody.text(message);
            
            const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
            toast.show();
        }
        
        /**
         * Affiche un message d'erreur (Alert)
         * @param {string} type 
         * @param {string} message 
         */
        function showAlert(type, message) {
            $('#alertContainer').html(`<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`).slideDown();
        }

        // Fonction de nettoyage pour retirer le backdrop qui bloque la page (Fix navigation bloquée)
        function cleanModalBackdrop() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('overflow', '');
        }

        // --- EMPLOYÉ LOGIC (TAB 1) ---
        
        function renderEmployes(data) {
            const tbody = $('#employeTableBody');
            tbody.empty();
            
            if (data.data.length === 0) {
                tbody.html('<tr><td colspan="5" class="text-center text-muted">Aucun employé enregistré.</td></tr>');
                $('#employePaginationLinks').empty();
                return;
            }

            allEmployes = data.data; 
            
            data.data.forEach(employe => {
                const hiringDate = new Date(employe.hiring_date).toLocaleDateString('fr-FR');
                // Notez l'application des classes d-none d-md-table-cell aux <td> des colonnes masquées
                tbody.append(`
                    <tr>
                        <td>${employe.full_name}</td>
                        <td>${employe.function}</td>
                        <td class="d-none d-md-table-cell">${parseFloat(employe.base_salary).toLocaleString('fr-FR', { minimumFractionDigits: 2 })} XOF</td>
                        <td class="d-none d-md-table-cell">${hiringDate}</td>
                        <td class="d-flex gap-1">
                            <button class="btn btn-sm btn-info btnEditEmploye" data-token="${employe.token}">
                                <span class="btn-action-text">Modifier</span>
                                <span class="btn-action-icon" aria-hidden="true">📝</span>
                            </button>
                            <a href="${baseUrl}/select-bulletin/${employe.token}" class="btn btn-sm btn-primary">
                                <span class="btn-action-text">Bulletin</span>
                                <span class="btn-action-icon" aria-hidden="true">📄</span>
                            </a>
                        </td>
                    </tr>
                `);
            });
            renderEmployePagination(data);
            attachEmployeListeners();
        }

        function renderEmployePagination(data) {
            const nav = $('#employePaginationLinks');
            nav.empty();

            if (data.last_page > 1) {
                let html = '<ul class="pagination">';
                
                data.links.forEach(link => {
                    const urlParams = new URLSearchParams(link.url);
                    const page = urlParams.get('employePage') || (link.label.includes('Previous') ? data.current_page - 1 : data.current_page + 1);
                    
                    if (link.url) {
                         html += `<li class="page-item ${link.active ? 'active' : ''}">
                                    <a class="page-link" href="#" data-page="${page}">${link.label.replace('&laquo; Previous', 'Précédent').replace('Next &raquo;', 'Suivant')}</a>
                                </li>`;
                    }
                });
                html += '</ul>';
                nav.html(html);

                nav.find('.page-link').on('click', function(e) {
                    e.preventDefault();
                    const page = $(this).data('page');
                    if (page) {
                        loadEmployes(page);
                    }
                });
            }
        }

        function loadEmployes(page = 1) {
            showLoader();
            $.ajax({
                url: `${baseUrl}` + `?employePage=${page}`,
                method: 'GET',
                success: function(response) {
                    renderEmployes(response.employes);
                    renderPaiesSelect(response.unpaidPaies);
                    if ($('#creation').hasClass('active')) {
                        populatePayrollCreationData();
                    }
                },
                error: function(xhr) {
                    showAlert('danger', "❌ Erreur lors du chargement de l'état du personnel.");
                },
                complete: function() { hideLoader(); }
            });
        }
        
        function attachEmployeListeners() {
            $('.btnEditEmploye').off('click').on('click', function() {
                const token = $(this).data('token');
                editEmploye(token);
            });
        }

        function editEmploye(token) {
            showLoader();
            $('#formEmploye')[0].reset();
            $('#modalEmployeLabel').text("Modifier l'Employé");
            $('#employeToken').val(token);

            $.ajax({
                url: `${baseUrl}/employe/${token}`,
                method: 'GET',
                success: function(employe) {
                    $('#fullName').val(employe.full_name);
                    $('#functionInput').val(employe.function);
                    $('#baseSalaryInputModal').val(employe.base_salary);
                    $('#hiringDate').val(employe.hiring_date);
                    
                    // Ouvrir le modal
                    new bootstrap.Modal(document.getElementById('modalEmploye')).show();
                },
                error: function() {
                    showAlert("danger", "Erreur lors de la récupération des données de l'employé.");
                },
                complete: function() { hideLoader(); }
            });
        }
        
        // --- PAYROLL CREATION LOGIC (TAB 2) ---
        
        function populatePayrollCreationData() {
            const select = $('#selectEmploye');
            select.empty().append('<option value="">-- Sélectionner l\'employé --</option>');
            
            allEmployes.forEach(employe => {
                if (!payrollCart[employe.token]) {
                     select.append(`<option value="${employe.token}" data-salary="${parseFloat(employe.base_salary).toFixed(2)}">${employe.full_name}</option>`);
                }
            });

            const monthSelect = $('#payrollMonth');
            if (monthSelect.children().length === 0) {
                 for (let i = 1; i <= 12; i++) {
                    const monthName = new Date(currentYear, i - 1, 1).toLocaleDateString('fr-FR', { month: 'long' });
                    monthSelect.append(`<option value="${i}">${monthName}</option>`);
                }
                const defaultMonth = (new Date().getMonth() === 0) ? 12 : new Date().getMonth();
                monthSelect.val(defaultMonth); 
            }

            $('#payrollYear').val(currentYear);
             
            $('#savePayrollBtn').toggle(Object.keys(payrollCart).length > 0);
        }

        function buildPayrollItemForm(employeToken, fullName, baseSalary) {
            return `
                <div class="payroll-item-card" data-token="${employeToken}">
                    <h6 class="text-primary">${fullName}</h6>
                    <input type="hidden" name="employe_token" value="${employeToken}">
                    <input type="hidden" name="base_salary" value="${baseSalary}">
                    
                    <div class="row g-2">
                        <div class="col-6 col-md-3">
                            <label class="form-label">Salaire de Base</label>
                            <input type="number" class="form-control" value="${baseSalary}" step="0.01" readonly>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Avantages</label>
                            <input type="number" name="advantages" class="form-control payroll-amount" value="0.00" step="0.01">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Prime</label>
                            <input type="number" name="prime" class="form-control payroll-amount" value="0.00" step="0.01">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label text-danger">Ret. Exc.</label>
                            <input type="number" name="exceptional_retention" class="form-control payroll-amount" value="0.00" step="0.01">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label text-danger">Ret. Fiscales</label>
                            <input type="number" name="fiscal_retention" class="form-control payroll-amount" value="0.00" step="0.01">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label text-danger">Ret. Sociales</label>
                            <input type="number" name="social_retention" class="form-control payroll-amount" value="0.00" step="0.01">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Cotis. Pat.</label>
                            <input type="number" name="patronal_contribution" class="form-control payroll-amount" value="0.00" step="0.01">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Charge Fiscale</label>
                            <input type="number" name="fiscal_charge" class="form-control payroll-amount" value="0.00" step="0.01">
                        </div>
                        <div class="col-12 mt-2 d-flex justify-content-between align-items-center">
                            <p class="mb-0">Net à Payer: <span class="fw-bold text-success net-paid-display">0.00</span></p>
                            <button type="button" class="btn btn-sm btn-danger btnRemovePayrollItem">Supprimer</button>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function calculateNetPaid(card) {
            const base = parseFloat(card.find('[name="base_salary"]').val()) || 0;
            const advantages = parseFloat(card.find('[name="advantages"]').val()) || 0;
            const prime = parseFloat(card.find('[name="prime"]').val()) || 0;
            const fiscalRet = parseFloat(card.find('[name="fiscal_retention"]').val()) || 0;
            const socialRet = parseFloat(card.find('[name="social_retention"]').val()) || 0;
            const exceptionalRet = parseFloat(card.find('[name="exceptional_retention"]').val()) || 0;

            let netPaid = base + advantages + prime - fiscalRet - socialRet - exceptionalRet;
            
            card.find('.net-paid-display').text(netPaid.toLocaleString('fr-FR', { minimumFractionDigits: 2 }));
            
            const token = card.data('token');
            if (payrollCart[token]) {
                payrollCart[token] = {
                    employe_token: token,
                    base_salary: base,
                    advantages: advantages,
                    prime: prime,
                    fiscal_retention: fiscalRet,
                    social_retention: socialRet,
                    patronal_contribution: parseFloat(card.find('[name="patronal_contribution"]').val()) || 0,
                    fiscal_charge: parseFloat(card.find('[name="fiscal_charge"]').val()) || 0,
                    exceptional_retention: exceptionalRet,
                    net_paid: netPaid
                };
            }
            $('#savePayrollBtn').toggle(Object.keys(payrollCart).length > 0);
        }

        // --- PAYROLL PAYMENT LOGIC (TAB 3) ---

        function renderPaiesSelect(unpaidPaies) {
            const select = $('#selectPaie');
            select.empty().append('<option value="">-- Choisir une paie non réglée --</option>');
            
            unpaidPaies.forEach(paie => {
                const monthName = new Date(paie.year, paie.month - 1, 1).toLocaleDateString('fr-FR', { month: 'long' });
                select.append(`<option value="${paie.token}">Paie de ${monthName} ${paie.year}</option>`);
            });
            
            if (unpaidPaies.length === 0) {
                 $('#paieDetails').slideUp();
                 $('#submitPaymentBtn').prop('disabled', true);
            }
        }
        
        // --- DOCUMENT READY & EVENT LISTENERS ---

        $(document).ready(function() {
            loadEmployes(1); 

            // 🌟 GESTION DU MENU MOBILE (SIDEBAR) 🌟
            const $body = $('body');
            const $modalEmploye = $('#modalEmploye');
            
            // Ouvrir la sidebar
            $('#sidebarToggle').off('click').on('click', function(e) {
                e.stopPropagation(); 
                $body.addClass('sidebar-open');
            });

            // Fermer la sidebar (bouton interne)
            $('#sidebarCloseBtn').off('click').on('click', function() {
                $body.removeClass('sidebar-open');
            });
            
            // Fermer la sidebar après la navigation (clic sur un onglet)
            $('a[data-bs-toggle="tab"]').on('click', function() {
                if ($(window).width() <= 768) {
                    setTimeout(() => {
                        $body.removeClass('sidebar-open');
                    }, 100);
                }
            });
            
            // Fermer la sidebar en cliquant en dehors (sur l'overlay semi-transparent)
             $body.off('click.sidebar').on('click.sidebar', function(e) { 
                if ($(window).width() <= 768 && $body.hasClass('sidebar-open') && !$(e.target).closest('#sidebar, #sidebarToggle').length) {
                    $body.removeClass('sidebar-open');
                }
            });

            // GESTION DU CONFLIT MODAL/MENU (Pour les écrans < 768px)
            // Empêche l'ouverture du menu quand le modal est ouvert et réactive le bouton après fermeture.
            $modalEmploye.on('show.bs.modal', function () {
                 if ($(window).width() <= 768) {
                     $('#sidebarToggle').prop('disabled', true);
                 }
            });

            $modalEmploye.on('hidden.bs.modal', function () {
                $('#sidebarToggle').prop('disabled', false);
            });


            // 1. Soumission du formulaire Employé
            $('#formEmploye').on('submit', function(e) {
                e.preventDefault();
                // Utilisation du loader interne au modal
                $('#modalEmployeLoader').removeClass('d-none').addClass('d-flex');
                
                const token = $('#employeToken').val();
                const isUpdating = token !== '';
                
                let url = isUpdating ? `${baseUrl}/employe/${token}` : '{{ route('manager.employe.store') }}';
                let method = 'POST'; 
                let data = $(this).serialize();
                
                if (isUpdating) {
                    data += '&_method=PUT'; 
                }

                $.ajax({
                    url: url,
                    method: method,
                    data: data,
                    success: function(response) {
                        // Rechargement de la page pour résoudre les problèmes de backdrop
                        window.location.reload();
                    },
                    error: function(xhr) {
                        const errorMessage = xhr.responseJSON.message || "Erreur lors de l'enregistrement de l'employé.";
                        showAlert('danger', errorMessage);
                        // Cacher le loader interne en cas d'erreur
                        $('#modalEmployeLoader').removeClass('d-flex').addClass('d-none');
                    },
                    // Pas de complete() car on recharge la page en succès
                });
            });

            // 2. Événements Tab 2 : Création de la Paie
            
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                if ($(e.target).attr('href') === '#creation') {
                    populatePayrollCreationData();
                }
            });
            
            // Chargement du salaire de base quand l'employé est sélectionné
            $('#selectEmploye').on('change', function() {
                const selected = $(this).find('option:selected');
                const salary = selected.data('salary');
                
                $('#baseSalaryInput').val(salary ? parseFloat(salary).toFixed(2) : ''); 
                $('#addToPayrollBtn').prop('disabled', !salary);
            });

            // Ajout au panier de paie
            $('#addToPayrollBtn').on('click', function() {
                const token = $('#selectEmploye').val();
                const fullName = $('#selectEmploye option:selected').text();
                const baseSalary = $('#baseSalaryInput').val();

                if (!token || payrollCart[token] || !baseSalary) return; 

                $('#payrollItemsContainer').append(buildPayrollItemForm(token, fullName, baseSalary));

                payrollCart[token] = {
                    employe_token: token,
                    base_salary: parseFloat(baseSalary),
                    advantages: 0, prime: 0, fiscal_retention: 0, social_retention: 0,
                    patronal_contribution: 0, fiscal_charge: 0, exceptional_retention: 0,
                    net_paid: parseFloat(baseSalary)
                };
                
                const newCard = $(`[data-token="${token}"]`);
                calculateNetPaid(newCard);

                $('#selectEmploye').val('').trigger('change');
                populatePayrollCreationData(); 
            });

            // Suppression d'un item de paie
            $('#payrollItemsContainer').on('click', '.btnRemovePayrollItem', function() {
                const card = $(this).closest('.payroll-item-card');
                const token = card.data('token');
                
                delete payrollCart[token];
                card.remove();
                
                populatePayrollCreationData(); 
            });

            // Recalcul du net à payer en cas de changement de montant
            $('#payrollItemsContainer').on('input', '.payroll-amount', function() {
                const card = $(this).closest('.payroll-item-card');
                calculateNetPaid(card);
            });

            // Sauvegarde de la Paie
            $('#savePayrollBtn').on('click', function() {
                if (Object.keys(payrollCart).length === 0) return;
                
                $('#creationLoader').removeClass('d-none').addClass('d-flex');
                
                const paieData = {
                    month: $('#payrollMonth').val(),
                    year: $('#payrollYear').val(),
                    items: Object.values(payrollCart) 
                };

                $.ajax({
                    url: '{{ route('manager.paie.store') }}',
                    method: 'POST',
                    data: paieData,
                    success: function(response) {
                        showToast(response.message);
                        payrollCart = {}; 
                        $('#payrollItemsContainer').empty();
                        loadEmployes(1); 
                    },
                    error: function(xhr) {
                         const errorMessage = xhr.responseJSON.message || "Erreur lors de la sauvegarde de la paie.";
                         showAlert('danger', errorMessage);
                    },
                    complete: function() { $('#creationLoader').removeClass('d-flex').addClass('d-none'); }
                });
            });

            // 3. Événements Tab 3 : Paiement
            
            // Sélection d'une paie impayée
            $('#selectPaie').on('change', function() {
                const token = $(this).val();
                
                if (!token) {
                    $('#paieDetails').slideUp();
                    $('#submitPaymentBtn').prop('disabled', true);
                    return;
                }

                $('#paymentLoader').removeClass('d-none').addClass('d-flex');
                $.ajax({
                    url: `${baseUrl}/unpaid-paie/${token}`,
                    method: 'GET',
                    success: function(paie) {
                        $('#totalBaseSalaryDisplay').text(parseFloat(paie.total_base_salary).toLocaleString('fr-FR', { minimumFractionDigits: 2 }));
                        $('#totalAdvantagesDisplay').text(parseFloat(paie.total_advantages).toLocaleString('fr-FR', { minimumFractionDigits: 2 }));
                        $('#totalPrimesDisplay').text(parseFloat(paie.total_primes).toLocaleString('fr-FR', { minimumFractionDigits: 2 }));
                        $('#totalFiscalRetentionsDisplay').text(parseFloat(paie.total_fiscal_retentions).toLocaleString('fr-FR', { minimumFractionDigits: 2 }));
                        $('#totalSocialRetentionsDisplay').text(parseFloat(paie.total_social_retentions).toLocaleString('fr-FR', { minimumFractionDigits: 2 }));
                        $('#totalExceptionalRetentionDisplay').text(parseFloat(paie.total_exceptional_retention).toLocaleString('fr-FR', { minimumFractionDigits: 2 }));
                        $('#totalPatronalContributionsDisplay').text(parseFloat(paie.total_patronal_contributions).toLocaleString('fr-FR', { minimumFractionDigits: 2 }));
                        $('#totalFiscalChargesDisplay').text(parseFloat(paie.total_fiscal_charges).toLocaleString('fr-FR', { minimumFractionDigits: 2 }));
                        
                        $('#netToPayInput').val(parseFloat(paie.net_to_pay).toFixed(2));
                        
                        const monthName = new Date(paie.year, paie.month - 1, 1).toLocaleDateString('fr-FR', { month: 'long' });
                        $('#descriptionInput').val(`Règlement de la paie du ${monthName} ${paie.year}`);

                        $('#paieDetails').slideDown();
                        $('#submitPaymentBtn').prop('disabled', false);
                    },
                    error: function(xhr) {
                        showAlert("danger", xhr.responseJSON.message || "Erreur lors de la récupération des détails de la paie.");
                        $('#paieDetails').slideUp();
                        $('#submitPaymentBtn').prop('disabled', true);
                    },
                    complete: function() { $('#paymentLoader').removeClass('d-flex').addClass('d-none'); }
                });
            });

            // Soumission du Paiement
            $('#payPaieForm').on('submit', function(e) {
                e.preventDefault();
                $('#paymentLoader').removeClass('d-none').addClass('d-flex');
                
                $.ajax({
                    url: '{{ route('manager.paie.pay') }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        showToast(response.message);
                        $('#payPaieForm')[0].reset();
                        $('#paieDetails').slideUp();
                        $('#submitPaymentBtn').prop('disabled', true);
                        loadEmployes(1); 
                    },
                    error: function(xhr) {
                        showAlert('danger', xhr.responseJSON.message || "Erreur lors du règlement de la paie.");
                    },
                    complete: function() { $('#paymentLoader').removeClass('d-flex').addClass('d-none'); }
                });
            });

            // Gestion du Dark Mode
            const toggleBtn = document.getElementById("toggleDarkMode");
            const html = document.documentElement;
            toggleBtn.addEventListener("click", () => {
                const newTheme = html.getAttribute("data-bs-theme") === "light" ? "dark" : "light";
                html.setAttribute("data-bs-theme", newTheme);
            });
        });


        // Assurez-vous que ce code est dans la section <script> de votre page
        $(document).ready(function() {            
            // 1. Obtenir l'élément du modal
            const modalEmployeElement = document.getElementById('modalEmploye');

            // 2. Définir une fonction de réinitialisation pour le formulaire et les états du modal
            function resetEmployeForm() {
                const form = document.getElementById('formEmploye');
                if (form) {
                    form.reset(); // Réinitialise tous les champs du formulaire
                }
                // Vider spécifiquement le champ caché utilisé pour les tokens d'édition
                $('#employeToken').val('');
                // Rétablir le titre par défaut
                $('#modalEmployeLabel').text('Ajouter/Modifier un Employé');
            }

            // 3. Attacher l'écouteur d'événement de fermeture de modal
            // L'événement 'hidden.bs.modal' est le moment idéal pour vider le contenu.
            $(modalEmployeElement).on('hidden.bs.modal', function () {
                resetEmployeForm();
            });

            // Optionnel : Réutiliser la fonction pour l'ajout initial si vous ouvrez le modal manuellement
            $('#createEmployeBtn').on('click', function() {
                resetEmployeForm(); // Réinitialiser avant d'ouvrir en mode "Ajouter"
                // ... Logique pour ouvrir le modal ...
            });

        });
    </script>
    
</body>
</html>