{{-- resources/views/manager/payroll/select_bulletin.blade.php --}}

<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- Ajout du token CSRF --}}
    <title>Sélection Bulletin Paie | {{ $employe->full_name }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js']) 
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        /* Styles de base pour la structure */
        body { display: flex; min-height: 100vh; }
        #sidebar { width: 250px; flex-shrink: 0; transition: margin-left 0.3s; }
        .main-content-wrapper { flex-grow: 1; display: flex; flex-direction: column; }
        
        /* GESTION RESPONSIVE/MOBILE (Mêmes styles que dans index.blade.php) */
        @media (max-width: 768px) {
            #sidebar { 
                position: fixed; 
                height: 100%; 
                z-index: 1030; 
                margin-left: -250px; 
                transition: margin-left 0.3s ease-in-out;
            }
            body.sidebar-open #sidebar { margin-left: 0; }
            body.sidebar-open::before { content: ''; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 1020; }
            .main-content-wrapper { margin-left: 0 !important; }
            #sidebarToggle { display: block !important; }
        }
        
        /* Style spécifique pour la zone d'aperçu */
        #bulletinPreview {
            border: 1px solid #6c757d;
            padding: 15px;
            margin-top: 20px;
            min-height: 300px;
            overflow: auto; /* Permet le défilement si le bulletin est long */
            background-color: var(--bs-body-bg); /* S'assure que le fond est lisible */
        }
        .preview-message {
            text-align: center;
            color: #6c757d;
            padding: 50px;
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
                <a class="nav-link active" href="{{ url('manager/payroll') }}">← Retour Menu Paie</a>
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
            
            <h2 class="h5 fw-bold text-primary mb-0 mx-auto mx-md-0">Sélection Bulletin</h2>
            
            <div class="d-none d-md-block" style="width: 100px;"></div> 
        </header>

        <main class="flex-grow-1 container-fluid py-4">
            
            <h1 class="h4 mb-4">
                Générer le Bulletin de Paie pour <span class="text-info">{{ $employe->full_name }}</span>
            </h1>
            <hr>
            
            @if(session('error'))
                <div class="alert alert-danger mt-3">{{ session('error') }}</div>
            @endif

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Sélectionner la Période</h5>
                    
                    {{-- FORMULAIRE de sélection --}}
                    {{-- Note: Le formulaire ne sert plus qu'à déclencher le PDF final --}}
                    <form id="bulletinForm" action="{{ route('manager.bulletin.generate', ['employe' => $employe->token]) }}" method="GET" target="_blank">
                        @csrf
                        
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-5">
                                <label for="month" class="form-label">Mois</label>
                                <select name="month" id="month" class="form-select" required>
                                    @php
                                        $months = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];
                                        $currentMonth = now()->month;
                                    @endphp
                                    @foreach($months as $num => $name)
                                        <option value="{{ $num }}" @if($num == $currentMonth) selected @endif>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-12 col-md-5">
                                <label for="year" class="form-label">Année</label>
                                <select name="year" id="year" class="form-select" required>
                                    @php
                                        $currentYear = now()->year;
                                        $startYear = $currentYear - 3;
                                    @endphp
                                    @for ($year = $currentYear; $year >= $startYear; $year--)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                            
                            <div class="col-12 col-md-2 d-flex align-items-end">
                                <button type="submit" id="generatePdfBtn" class="btn btn-success w-100" disabled>
                                    Générer PDF 📄
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <h2 class="h5 mt-4">Aperçu du Bulletin</h2>
            <div id="bulletinPreview" class="shadow-sm">
                <div class="preview-message" id="previewInitialMessage">
                    Sélectionnez un mois et une année pour afficher l'aperçu du bulletin.
                </div>
                {{-- Le contenu HTML de l'aperçu sera inséré ici par AJAX --}}
            </div>

            {{-- Bouton de retour principal --}}
            <a href="{{ url('manager/payroll') }}" class="btn btn-secondary mt-4">
                ← Retour à la Liste des Employés
            </a>
            
        </main>
    </div>

    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        
        const employeToken = '{{ $employe->token }}';
        const baseUrl = '{{ url('manager/payroll') }}';

        // --- APERÇU LOGIC ---
        
        /**
         * Charge l'aperçu du bulletin pour la période sélectionnée.
         */
        function loadBulletinPreview() {
            const month = $('#month').val();
            const year = $('#year').val();
            const previewArea = $('#bulletinPreview');
            const generateBtn = $('#generatePdfBtn');

            if (!month || !year) {
                previewArea.html('<div class="preview-message">Sélectionnez un mois et une année pour afficher l\'aperçu du bulletin.</div>');
                generateBtn.prop('disabled', true);
                return;
            }
            
            // Afficher un loader pendant le chargement
            previewArea.html('<div class="preview-message"><div class="spinner-border text-primary" role="status"></div> Chargement de l\'aperçu...</div>');
            generateBtn.prop('disabled', true);

            $.ajax({
                url: `${baseUrl}/preview-bulletin/${employeToken}`,
                method: 'GET',
                data: { month: month, year: year },
                success: function(response) {
                    // Si le bulletin est trouvé, insérer le HTML de l'aperçu
                    previewArea.html(response.html);
                    generateBtn.prop('disabled', false);
                },
                error: function(xhr) {
                    // Si la paie n'est pas trouvée (Erreur 404 de firstOrFail)
                    const message = xhr.responseJSON && xhr.responseJSON.message 
                                    ? xhr.responseJSON.message 
                                    : "Aucun bulletin trouvé pour cette période. Veuillez vérifier vos données de paie.";
                                    
                    previewArea.html(`<div class="preview-message text-danger">❌ ${message}</div>`);
                    generateBtn.prop('disabled', true);
                }
            });
        }
        
        // --- DOCUMENT READY & EVENT LISTENERS ---

        $(document).ready(function() {
            // Chargement de l'aperçu initial
            loadBulletinPreview(); 

            // Événement de changement pour le mois et l'année
            $('#month, #year').on('change', loadBulletinPreview);
            
            // 🌟 GESTION DU MENU MOBILE (SIDEBAR) 🌟
            const $body = $('body');
            
            $('#sidebarToggle').off('click').on('click', function(e) {
                e.stopPropagation(); 
                $body.addClass('sidebar-open');
            });

            $('#sidebarCloseBtn').off('click').on('click', function() {
                $body.removeClass('sidebar-open');
            });
            
             $body.off('click.sidebar').on('click.sidebar', function(e) { 
                if ($(window).width() <= 768 && $body.hasClass('sidebar-open') && !$(e.target).closest('#sidebar, #sidebarToggle').length) {
                    $body.removeClass('sidebar-open');
                }
            });
            
            // Gestion du Dark Mode
            const toggleBtn = document.getElementById("toggleDarkMode");
            const html = document.documentElement;
            toggleBtn.addEventListener("click", () => {
                const newTheme = html.getAttribute("data-bs-theme") === "light" ? "dark" : "light";
                html.setAttribute("data-bs-theme", newTheme);
            });
        });
    </script>
    
</body>
</html>