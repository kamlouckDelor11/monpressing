<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques | Pressing Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script> 
    <style>
        /* Styles CSS inchangés pour la mise en page */
        @media (min-width: 992px) { 
            #sidebar { width: 250px; visibility: visible !important; transform: none !important; }
        }
        .nav-link { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .action-btns { display: flex; gap: 5px; }
        .accordion-header-btn {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            text-align: left;
            padding: 1rem 1.25rem;
            background-color: transparent;
            border: none;
            color: inherit;
        }
        .accordion-header-btn .bi {
            transition: transform 0.3s;
        }
        .accordion-header-btn.collapsed .bi-dash-circle {
            display: none; 
        }
        .accordion-header-btn:not(.collapsed) .bi-plus-circle {
            display: none; 
        }
        /* Styles de pagination personnalisés (laissés pour référence) */
        .custom-pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem 0;
        }
        .pagination-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 5px;
        }
        .pagination-link {
            display: block;
            min-width: 38px;
            height: 38px;
            line-height: 38px;
            text-align: center;
            text-decoration: none;
            border: 1px solid var(--bs-border-color);
            border-radius: 0.25rem;
            color: var(--bs-primary);
            transition: all 0.2s;
            font-weight: 500;
            background-color: var(--bs-body-bg);
        }
        .pagination-link:hover {
            background-color: var(--bs-primary);
            color: #fff;
            border-color: var(--bs-primary);
        }
        .pagination-item.active .pagination-link {
            background-color: var(--bs-primary);
            color: #fff;
            border-color: var(--bs-primary);
        }
        .pagination-item.disabled .pagination-link {
            color: var(--bs-secondary);
            pointer-events: none;
            opacity: 0.6;
        }
        #alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1060; 
            width: 350px;
            max-width: 90%;
        }
        .chart-container {
            position: relative;
            height: 350px; 
            width: 100%;
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
            <h2 class="h5 fw-bold text-primary mb-0">Statistiques & Objectifs</h2>
            <div class="d-flex align-items-center gap-3">
                <span class="text-secondary">Bonjour, <strong>{{ Auth::User()->name}}</strong></span>
                <button id="toggleDarkMode" class="btn btn-outline-secondary">🌙</button>
            </div>
        </header>

        <main class="flex-grow-1 p-4 bg-body-tertiary">
            
            <div id="alert-container">
                {{-- Les alertes AJAX s'injecteront ici --}}
            </div>
            
            @php
                $formOpen = $errors->any() ? 'show' : '';
            @endphp
            
            {{-- Formulaire de Création d'Objectif --}}
            <div class="card shadow-sm mb-5">
                <div class="card-header bg-primary text-white p-0" id="headingGoal">
                    <button class="accordion-header-btn {{ $formOpen ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGoalForm" aria-expanded="{{ $formOpen ? 'true' : 'false' }}" aria-controls="collapseGoalForm">
                        <h5 class="mb-0"><i class="bi bi-bullseye me-2"></i> Fixer un Nouvel Objectif</h5>
                        <i class="bi bi-plus-circle"></i><i class="bi bi-dash-circle"></i>
                    </button>
                </div>
                
                <div id="collapseGoalForm" class="collapse {{ $formOpen }}" aria-labelledby="headingGoal">
                    <div class="card-body">
                        <form id="createGoalForm" action="{{ route('manager.goals.store') }}" method="POST" class="row g-3">
                            @csrf
                            
                            {{-- Type d'objectif --}}
                            <div class="col-md-4">
                                <label for="type" class="form-label">Type d'Objectif</label>
                                <select name="type" id="type" class="form-select" required>
                                    <option value="">Choisir...</option>
                                    <option value="deposits">Nombre de Dépôts</option>
                                    <option value="revenue">Chiffre d'Affaires</option>
                                    <option value="deliveries">Nombre de Livraisons</option>
                                    <option value="new_clients">Nouveaux Clients</option>
                                    <option value="charges">Charges Totales (à minimiser)</option>
                                </select>
                                <div class="invalid-feedback" data-field="type"></div>
                            </div>

                            {{-- Périodicité --}}
                            <div class="col-md-2">
                                <label for="periodicity" class="form-label">Période</label>
                                <select name="periodicity" id="periodicity" class="form-select" required>
                                    <option value="monthly">Mensuel</option>
                                    <option value="quarterly">Trimestriel</option>
                                    <option value="annual">Annuel</option>
                                </select>
                                <div class="invalid-feedback" data-field="periodicity"></div>
                            </div>

                            {{-- Valeur Cible --}}
                            <div class="col-md-3">
                                <label for="target_value" class="form-label">Valeur Cible</label>
                                <input type="number" step="0.01" name="target_value" id="target_value" class="form-control" required>
                                <div class="invalid-feedback" data-field="target_value"></div>
                            </div>

                            {{-- Utilisateur Cible --}}
                            <div class="col-md-3">
                                <label for="user_token" class="form-label">Attribué à (Optionnel)</label>
                                <select name="user_token" id="user_token" class="form-select">
                                    <option value="">-- Tout le Pressing --</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->token }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" data-field="user_token"></div>
                            </div>

                            {{-- Date de Début --}}
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Date de Début</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ \Carbon\Carbon::now()->toDateString() }}" required>
                                <div class="invalid-feedback" data-field="start_date"></div>
                            </div>

                            {{-- Date de Fin --}}
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">Date de Fin</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" required>
                                <div class="invalid-feedback" data-field="end_date"></div>
                            </div>
                            
                            {{-- Bouton --}}
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i> Enregistrer l'Objectif</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Tableau des Objectifs Actuels --}}
            <div class="card shadow-sm mb-5">
                <div class="card-header p-0" id="headingGoalsTable">
                    <button class="accordion-header-btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGoalsTable" aria-expanded="true" aria-controls="collapseGoalsTable">
                        <h5 class="mb-0"><i class="bi bi-list-task me-2"></i> Objectifs Actuels</h5>
                        <i class="bi bi-plus-circle"></i><i class="bi bi-dash-circle"></i>
                    </button>
                </div>
                
                <div id="collapseGoalsTable" class="collapse show" aria-labelledby="headingGoalsTable">
                    {{-- ZONE D'INJECTION AJAX : Conteneur pour la vue partielle --}}
                    <div id="goals-table-wrapper">
                        @include('statistics.partials.goals_table_content', $goalsData)
                    </div>
                </div>
            </div>

            {{-- Section des Graphiques et Analyse --}}
            <div class="card shadow-sm mb-5">
                <div class="card-header p-0" id="headingAnalytics">
                    <button class="accordion-header-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAnalytics" aria-expanded="false" aria-controls="collapseAnalytics">
                        <h5 class="mb-0"><i class="bi bi-graph-up-arrow me-2"></i> Analyse des Tendances et Graphiques</h6>
                        <i class="bi bi-plus-circle"></i><i class="bi bi-dash-circle"></i>
                    </button>
                </div>

                <div id="collapseAnalytics" class="collapse" aria-labelledby="headingAnalytics">
                    <div class="card-body">

                        {{-- CHAMP DE FILTRAGE PAR ANNÉE --}}
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label for="filterYear" class="form-label">Année des Données</label>
                                <select id="filterYear" class="form-select" data-current-year="{{ $selectedYear }}">
                                    @php
                                        $currentYear = date('Y');
                                        $startYear = $currentYear - 5; 
                                    @endphp
                                    @for ($y = $currentYear; $y >= $startYear; $y--)
                                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="row g-4">
                            {{-- Graphique 1: Tendances du CA --}}
                            <div class="col-lg-6">
                                <div class="card shadow-sm border-0">
                                    <div class="card-body">
                                        <h6 class="card-title">Chiffre d'Affaires Mensuel (<span id="chartYearDisplay">{{ $selectedYear }}</span>)</h6>
                                        <div class="chart-container">
                                            <canvas id="revenueChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Graphique 2: Volume de Dépôts --}}
                            <div class="col-lg-6">
                                <div class="card shadow-sm border-0">
                                    <div class="card-body">
                                        <h6 class="card-title">Volume de Dépôts par Mois (<span id="chartYearDisplay2">{{ $selectedYear }}</span>)</h6>
                                        <div class="chart-container">
                                            <canvas id="depositChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

{{-- Modale Statique pour l'édition des objectifs --}}
<div class="modal fade" id="staticEditGoalModal" tabindex="-1" aria-labelledby="staticEditGoalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div id="editModalContent"  style="min-height: 200px;">
                <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div>
            </div>
        </div>
    </div>
</div>

{{-- ------------------------------------------------------------------------------------------------ --}}
{{-- SCRIPT JAVASCRIPT/JQUERY (Logique Modifiée pour Modale Statique & Ajout des Graphiques) --}}
{{-- ------------------------------------------------------------------------------------------------ --}}
<script>
    const GOALS_TABLE_URL = "{{ route('manager.goals.table') }}";
    const CHART_DATA_URL = "{{ route('manager.chart.data') }}";
    const GOAL_EDIT_CONTENT_URL = "{{ url('manager/goals/get-edit-form') }}"; 
    
    let revenueChartInstance = null;
    let depositChartInstance = null;

    /**
     * Affiche une alerte temporaire.
     */
    function showAlert(type, message) {
        const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
        $('#alert-container').html(alertHtml); 
        setTimeout(() => { $('#alert-container').empty(); }, 5000);
    }
    
    /**
     * Nettoie les erreurs de validation des formulaires.
     */
    function clearFormErrors(form) {
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').empty();
    }
    
    /**
     * Recharge la table des objectifs.
     */
    function loadGoalsTable(page = 1) {
        $.ajax({
            url: GOALS_TABLE_URL,
            method: 'GET',
            data: { page: page },
            beforeSend: function() {
                $('#goals-table-wrapper').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div></div>');
            },
            success: function(response) {
                $('#goals-table-wrapper').html(response.html);
            },
            error: function() {
                showAlert('danger', 'Erreur lors du chargement des objectifs.');
            }
        });
    }

    /**
     * Charge le contenu du formulaire d'édition dans la modale statique.
     */
    function loadEditFormContent(goalToken) {
        const modalContent = $('#editModalContent');
        
        // Afficher le loader pendant le chargement
        modalContent.html('<div class="d-flex justify-content-center align-items-center" style="min-height: 200px;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div></div>');
        
        $.ajax({
            url: GOAL_EDIT_CONTENT_URL + '/' + goalToken,
            method: 'GET',
            success: function(response) {
                modalContent.html(response.html);
                initializeEditFormListeners(); // Réinitialiser l'écouteur après l'injection
            },
            error: function() {
                modalContent.html('<div class="alert alert-danger m-3">Erreur lors du chargement du formulaire d\'édition. Veuillez recharger la page.</div>');
            }
        });
    }

    /**
     * Initialise l'écouteur de soumission du formulaire de modification.
     */
    function initializeEditFormListeners() {
        const form = $('#staticEditGoalModal').find('.editGoalForm');
        if (form.length) {
            form.off('submit').on('submit', handleEditGoalSubmission);
        }
    }
    
    /**
     * Logique de soumission pour la modification (dans la modale statique).
     */
    function handleEditGoalSubmission(e) {
        e.preventDefault(); 
        
        const form = $(this);
        const modal = $('#staticEditGoalModal');
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        clearFormErrors(form);

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Mise à jour...'); 

        $.ajax({
            url: form.attr('action'),
            method: 'POST', 
            data: form.serialize(),
            success: function(response) {
                showAlert('success', response.success || 'Objectif mis à jour.');
                
                modal.modal('hide'); 
                
                submitBtn.prop('disabled', false).html(originalText); 
                
                const currentPage = $('#goals-table-wrapper').find('.pagination-item.active .pagination-link').text() || 1;
                loadGoalsTable(currentPage); 
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText); 

                if (xhr.status === 422) { 
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        form.find(`[name="${field}"]`).addClass('is-invalid');
                        form.find(`[data-field="${field}"]`).text(messages[0]);
                    });
                    showAlert('warning', 'Veuillez corriger les erreurs de validation.');
                } else {
                    showAlert('danger', xhr.responseJSON.error || "Une erreur est survenue lors de la modification.");
                }
            }
        });
    }
    
    /**
     * Logique de suppression d'objectif.
     */
    function handleGoalDeletion(e) {
        e.preventDefault(); 
        
        const deleteBtn = $(this);
        const goalToken = deleteBtn.data('goal-token');
        const deleteUrl = `{{ url('manager/goals') }}/${goalToken}`; 
        const modalId = `#deleteGoalModal-${goalToken}`;

        
        const originalText = deleteBtn.html(); 
        deleteBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>'); 

        $.ajax({
            url: deleteUrl,
            type: 'POST', 
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE' 
            },
            success: function(response) {
                
                $(modalId).modal('hide'); 

                showAlert('success', response.success || 'Objectif supprimé.');
                
                deleteBtn.prop('disabled', false).html(originalText); 
                const currentPage = $('#goals-table-wrapper').find('.pagination-item.active .pagination-link').text() || 1;
                loadGoalsTable(currentPage);
            },
            error: function(xhr) {
                deleteBtn.prop('disabled', false).html(originalText); 
                showAlert('danger', xhr.responseJSON.error || "Erreur lors de la suppression.");
            }
        });
    }

    /**
     * Logique de création d'objectif.
     */
    function handleCreateGoalSubmission(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Enregistrement...'); 
        clearFormErrors(form);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                showAlert('success', response.success || 'Objectif créé.');
                form[0].reset(); 
                loadGoalsTable(1); 
                submitBtn.prop('disabled', false).html(originalText); 
                
                // Fermer le formulaire de création après succès
                $('#collapseGoalForm').collapse('hide');

            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText); 
                if (xhr.status === 422) { 
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        form.find(`[name="${field}"]`).addClass('is-invalid');
                        form.find(`[data-field="${field}"]`).text(messages[0]);
                    });
                    showAlert('warning', 'Veuillez corriger les erreurs de validation.');
                } else {
                    showAlert('danger', xhr.responseJSON.error || "Une erreur est survenue lors de la création.");
                }
            }
        });
    }

    // ----------------------------------------------------
    // LOGIQUE GRAPHIQUES (CHART.JS)
    // ----------------------------------------------------

    /**
     * Dessine les deux graphiques Chart.js
     * @param {Object} revenueData - Données du chiffre d'affaires
     * @param {Object} depositData - Données des dépôts
     */
    function drawCharts(revenueData, depositData) {
        // Détruire les instances précédentes si elles existent
        if (revenueChartInstance) {
            revenueChartInstance.destroy();
        }
        if (depositChartInstance) {
            depositChartInstance.destroy();
        }
        
        const labels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];

        // --- Graphique 1: Revenu ---
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        revenueChartInstance = new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Chiffre d\'Affaires (DA)',
                    data: revenueData,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)', // Bleu
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Montant (DA)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });
        
        // --- Graphique 2: Dépôts ---
        const depositCtx = document.getElementById('depositChart').getContext('2d');
        depositChartInstance = new Chart(depositCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Nombre de Dépôts',
                    data: depositData,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)', // Vert/Cyan
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    tension: 0.3, // Courbe
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Quantité'
                        },
                        // S'assurer que les valeurs sont des entiers pour le comptage
                        ticks: {
                            precision: 0 
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });
    }

    /**
     * Récupère les données de graphique pour l'année sélectionnée.
     */
    function fetchChartData(year) {
        $.ajax({
            url: CHART_DATA_URL,
            method: 'GET',
            data: { year: year },
            beforeSend: function() {
                // Optionnel : Afficher des loaders sur les zones de graphiques
            },
            success: function(response) {
                // Mettre à jour les années affichées
                $('#chartYearDisplay').text(year);
                $('#chartYearDisplay2').text(year);
                
                // Dessiner les graphiques avec les nouvelles données
                drawCharts(response.revenue, response.deposits); 
            },
            error: function() {
                showAlert('danger', 'Erreur lors du chargement des données graphiques.');
            }
        });
    }

    // Initialisation
    $(document).ready(function() {
        
        // ----------------------------------------------------
        // INITIALISATION DES ÉCOUTEURS
        // ----------------------------------------------------
        
        // 1. Logique de Création (Statique)
        $('#createGoalForm').on('submit', handleCreateGoalSubmission);

        // 2. Logique de Modification (DÉLÉGATION vers la modale statique)
        $(document).on('click', '.btn-edit-goal', function(e) {
            e.preventDefault();
            const goalToken = $(this).data('goal-token');
            loadEditFormContent(goalToken); // Charge le contenu et ouvre la modale
            $('#staticEditGoalModal').modal('show');
        });
        
        // 3. Logique de Suppression (Délégation pour les boutons dynamiques)
        $(document).on('click', '.btn-confirm-delete', handleGoalDeletion);
        
        // 4. Gestion de la Pagination (Délégation)
        $('#goals-table-wrapper').on('click', '.pagination-link', function(e) {
            e.preventDefault();
            if ($(this).parent().hasClass('active') || $(this).parent().hasClass('disabled')) {
                return;
            }
            const url = $(this).attr('href');
            if (url) {
                const page = new URL(url).searchParams.get('page');
                loadGoalsTable(page);
            }
        });

        // 5. Gestion du Filtre d'Année pour les Graphiques
        $('#filterYear').on('change', function() {
            const selectedYear = $(this).val();
            fetchChartData(selectedYear);
        });

        // ----------------------------------------------------
        // GESTION DU CYCLE DE VIE DES MODALES 
        // ----------------------------------------------------
        $(document).on('hidden.bs.modal', function () {
            const modal = $(this);
            // Si l'ID commence par 'deleteGoalModal-' c'est une modale dynamique : on la détruit.
            if (modal.attr('id') && modal.attr('id').startsWith('deleteGoalModal-')) {
                modal.remove(); 
            }
            // Nettoyage général du body et backdrop (important)
            $('body').removeClass('modal-open'); 
            $('.modal-backdrop').remove(); 
        });

        // ----------------------------------------------------
        // CHARGEMENT INITIAL DES DONNÉES
        // ----------------------------------------------------

        // Chargement initial des données des graphiques
        const initialYear = $('#filterYear').val();
        
        // NOTE: $chartData['revenue'] et $chartData['deposits'] DOIVENT être passées 
        // par le contrôleur lors du chargement initial de la page.
        // Exemple: return view('statistics.index', ['chartData' => $data, ...]);
        const initialRevenueData = @json($chartData['revenue'] ?? []);
        const initialDepositData = @json($chartData['deposits'] ?? []);
        
        if (initialRevenueData.length > 0 || initialDepositData.length > 0) {
            drawCharts(initialRevenueData, initialDepositData);
        } else {
             // Si les données initiales ne sont pas passées (ou sont vides), on fait un appel AJAX initial
             // Ceci est une bonne pratique si la page se charge sans les données complètes.
             // fetchChartData(initialYear); 
        }

        // Logic Dark Mode
        // (Assurez-vous que cette logique est définie dans app.js ou ici)
        const currentTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        if (currentTheme === 'light') {
            document.documentElement.setAttribute('data-bs-theme', 'light');
            $('#toggleDarkMode').html('☀️');
        } else {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
            $('#toggleDarkMode').html('🌙');
        }

        $('#toggleDarkMode').on('click', function() {
            const theme = document.documentElement.getAttribute('data-bs-theme');
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-bs-theme', 'light');
                localStorage.setItem('theme', 'light');
                $(this).html('☀️');
            } else {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                $(this).html('🌙');
            }
            // Re-dessiner les graphiques pour s'adapter au nouveau thème (couleurs des labels)
            fetchChartData($('#filterYear').val());
        });
        
    });
</script>
</body>
</html>