<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestion des Utilisateurs | Pressing Manager</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        /* Styles pour le Loader */
        #loaderOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 2000;
            display: none; /* Caché par défaut */
            justify-content: center;
            align-items: center;
        }
        #sidebar { min-height: 100vh; }
        #userTableWrapper { max-height: 500px; overflow-y: auto; }
        .active-status { color: green; font-weight: bold; }
        .inactive-status { color: red; font-weight: bold; }
    </style>
</head>
<body class="d-flex">

    <div id="loaderOverlay">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Chargement...</span>
        </div>
    </div>

    <aside class="offcanvas-lg offcanvas-start bg-body-tertiary border-end" tabindex="-1" id="sidebar">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title text-primary fw-bold">🧺 Pressing Manager</h5>
            <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            <nav class="nav flex-column p-3">
                 <a href="{{ route('dashboard') }}" class="nav-link text-secondary">🏠 Tableau de bord</a>
                <a href="{{ route('order') }}" class="nav-link text-secondary">➕ Enregistrer un dépôt</a>
                <a href="{{ route('clients.index') }}" class="nav-link text-secondary">✅ Gestion des clients</a>
                <a href="{{ route('manager.order') }}" class="nav-link text-secondary">✅ Gestion des dépôts</a>
                <a href="{{ route('articles.index') }}" class="nav-link text-secondary">✅ Gestion des articles</a>
                <a href="{{ route('services.index') }}" class="nav-link text-secondary">✅ Gestion des services</a>
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
        </div>
    </aside>

    <div class="flex-grow-1 d-flex flex-column">

        <header class="d-flex justify-content-between align-items-center border-bottom p-3 bg-body shadow-sm">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar">☰</button>
                <h2 class="h5 fw-bold text-primary mb-0">Gestion des Utilisateurs</h2>
            </div>
            <button id="toggleDarkMode" class="btn btn-outline-secondary">🌙</button>
        </header>

        <main class="flex-grow-1 container py-4">

            <div class="d-flex justify-content-end mb-3">
                <button id="createUserBtn" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalUser">➕ Ajouter un utilisateur</button>
            </div>

            <div id="alertContainer" class="mb-3" style="display: none;">
                </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">🧑‍💼 Utilisateurs du Pressing</h5>
                    <div id="userTableWrapper" class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="userTableBody">
                                <tr><td colspan="6" class="text-center text-muted">Chargement des utilisateurs...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <nav id="paginationLinks" class="mt-3 d-flex justify-content-center" aria-label="Pagination des utilisateurs"></nav>
                </div>
            </div>

        </main>
    </div>

    <div class="modal fade" id="modalUser" tabindex="-1" aria-labelledby="modalUserLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formUser" action="{{ route('manager.user.store') }}" method="POST">
                    @csrf
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalUserLabel">Créer / Modifier un utilisateur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Annuler"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Champ caché pour le TOKEN de l'utilisateur --}}
                        <input type="hidden" id="userId" name="token"> 
                        
                        <label for="userName" class="form-label">Nom Complet</label>
                        <input type="text" id="userName" name="name" class="form-control mb-2" required>
                        
                        <label for="userEmail" class="form-label">Email</label>
                        <input type="email" id="userEmail" name="email" class="form-control mb-2" required>
                        
                        <label for="userPhone" class="form-label">Téléphone</label>
                        <input type="tel" id="userPhone" name="phone" class="form-control mb-2">
                        
                        <label for="userPassword" class="form-label">Mot de passe</label>
                        <input type="password" id="userPassword" name="password" class="form-control mb-2">
                        
                        {{-- Champs de modification (affichés uniquement en mode modification) --}}
                        <div id="editFields" style="display: none;">
                            <label for="userRole" class="form-label">Rôle</label>
                            <select id="userRole" class="form-select mb-2" name="role" required>
                                <option value="employe">Employé</option>
                                <option value="admin">Admin</option>
                            </select>

                            <label for="userStatus" class="form-label">Statut</label>
                            <select id="userStatus" class="form-select mb-2" name="status" required>
                                <option value="active">Actif</option>
                                <option value="inactive">Inactif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="position-fixed top-0 end-0 p-3" style="z-index:1080">
        <div id="liveToast" class="toast align-items-center text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // --- Variables Globales ---
        let users = []; 
        let currentPage = 1;
        const perPage = 5; // Nombre d'utilisateurs par page pour le frontend

        // --- UTILS ---
        
        // Loader
        function showLoader() { $('#loaderOverlay').fadeIn(100); }
        function hideLoader() { $('#loaderOverlay').fadeOut(100); }

        // Toast
        const toastElement = document.getElementById('liveToast');
        const toast = new bootstrap.Toast(toastElement);
        function showToast(message, color = 'primary') {
            document.getElementById('toastMessage').textContent = message;
            toastElement.className = `toast align-items-center text-bg-${color} border-0`;
            toast.show();
        }

        // Alertes (pour les erreurs de validation)
        function showAlert(type, message) {
            $('#alertContainer').html(`
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `).slideDown();
        }

        // --- RENDU ET LOGIQUE D'AFFICHAGE ---

        function getStatusClass(status) {
            return status === 'active' ? 'active-status' : 'inactive-status';
        }

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
                for (let i = 1; i <= data.last_page; i++) {
                    html += `<li class="page-item ${data.current_page === i ? 'active' : ''}">
                                <a class="page-link" href="#" data-page="${i}">${i}</a>
                              </li>`;
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
                        loadUsers(page);
                    }
                });
            }
        }

        function renderUsers(paginationObject) {
            const tbody = document.getElementById("userTableBody");
            tbody.innerHTML = "";
            users = paginationObject.data || [];
            
            if (users.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Aucun utilisateur trouvé.</td></tr>';
                $('#paginationLinks').empty();
                return;
            }

            users.forEach(user => {
                const status = user.status === 'active' ? "Actif" : "Inactif";
                const statusClass = getStatusClass(user.status);

                tbody.innerHTML += `
                    <tr>
                        <td>${user.name}</td>
                        <td>${user.email}</td>
                        <td>${user.phone || '-'}</td>
                        <td>${user.role}</td>
                        <td class="${statusClass}">${status}</td>
                        <td class="d-flex gap-1 flex-wrap">
                            {{-- Utilise user.token comme data-id --}}
                            <button class="btn btn-sm btn-primary btnEdit" data-id="${user.token}">✏️ Modifier</button>
                        </td>
                    </tr>`;
            });
            attachUserListeners();
            renderPagination(paginationObject);
        }

        // --- LOGIQUE AJAX ---

        // 1. Charger la liste des utilisateurs (avec pagination)
        function loadUsers(page = 1) {
            showLoader();
            $.ajax({
                url: '{{ route("manager.users.index") }}' + `?page=${page}`,
                method: 'GET',
                success: function(response) {
                    currentPage = page;
                    renderUsers(response.users); 
                },
                error: function(xhr) {
                    showAlert('danger', "❌ Erreur lors du chargement des utilisateurs.");
                    $('#userTableBody').html('<tr><td colspan="6" class="text-center text-danger">Échec du chargement des données.</td></tr>');
                    $('#paginationLinks').empty();
                },
                complete: function() { hideLoader(); }
            });
        }

        // 2. Préparer la modale pour l'édition
        function editUser(token) { // Change l'argument pour accepter le token
             // Cherche l'utilisateur par token, pas par id
            const user = users.find(u => u.token == token); 
            if (!user) {
                showToast("Utilisateur non trouvé.", 'danger');
                return;
            }

            // Réinitialiser les alertes
            $('#alertContainer').slideUp();
            
            // Mettre à jour les champs
            $('#userId').val(user.token); // Met le token dans le champ caché
            $('#userName').val(user.name);
            $('#userEmail').val(user.email);
            $('#userPhone').val(user.phone);
            $('#userRole').val(user.role);
            $('#userStatus').val(user.status);
            
            $('#userPassword').val(''); 
            $('#userPassword').attr('placeholder', 'Laisser vide pour ne pas changer');
            
            // Afficher les champs de modification et mettre à jour le titre
            $('#editFields').show();
             // Utilise le token dans la route de mise à jour
            $('#modalUserLabel').text("Modifier l'utilisateur: " + user.name);
            $('#formUser').attr('action', '{{ route("manager.user.update", ["user" => ":token"]) }}'.replace(':token', user.token)); 
            $('#submitBtn').text('Sauvegarder les modifications');

            // Ouvrir la modale
            new bootstrap.Modal(document.getElementById('modalUser')).show();
        }

        // --- ÉCOUTEURS D'ÉVÉNEMENTS ---

        function attachUserListeners() {
            // Écouteur pour l'édition
            $('.btnEdit').off('click').on('click', function() {
                // Passe le data-id (qui est le token) à editUser
                editUser($(this).data('id')); 
            });
        }

        $(document).ready(function() {
            loadUsers(currentPage);

            // Clic sur "Ajouter un utilisateur" : réinitialise la modale en mode création
            $('#createUserBtn').on('click', function() {
                $('#formUser')[0].reset();
                $('#userId').val('');
                $('#userPassword').attr('placeholder', 'Mot de passe (requis)');
                
                // Cacher les champs de modification spécifiques
                $('#editFields').hide();
                
                $('#modalUserLabel').text("Ajouter un nouvel utilisateur");
                $('#formUser').attr('action', '{{ route("manager.user.store") }}');
                $('#submitBtn').text('Créer l\'utilisateur');
                $('#alertContainer').slideUp();
            });

            // Soumission du formulaire (Création ou Modification)
            $('#formUser').on('submit', function(e) {
                e.preventDefault();
                showLoader();
                $('#alertContainer').slideUp(); 

                const form = $(this);
                const isUpdating = $('#userId').val() !== '';
                const url = form.attr('action');
                
                // Déterminer la méthode HTTP pour l'appel AJAX
                // Si c'est une mise à jour (token est présent), on utilise PUT. Sinon POST.
                let httpMethod = isUpdating ? 'PUT' : 'POST'; 
                // Serialisation des données du formulaire
                let data = form.serialize();

                $.ajax({
                    url: url,
                    method: 'post', // C'est ici que la méthode PUT est envoyée
                    data: data,
                    success: function(response) {
                        $('#modalUser').modal('hide');
                        showToast(response.message, 'success');
                        loadUsers(currentPage); 
                    },
                    error: function(xhr) {
                        $('#alertContainer').empty(); 
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let list = "<ul>";
                            $.each(errors, function(k, v) { list += "<li>" + v[0] + "</li>"; });
                            list += "</ul>";
                            showAlert('danger', "<strong>Erreur de validation:</strong> " + list);
                        } else {
                            showAlert('danger', "❌ Une erreur est survenue lors de l'opération ou vous n'avez pas la permission (Vérifiez la console).");
                        }
                    },
                    complete: function() { hideLoader(); }
                });
            });

            // Gérer la fermeture de la modale pour cacher les alertes
            document.getElementById('modalUser').addEventListener('hidden.bs.modal', function () {
                $('#alertContainer').slideUp();
            });

            // Dark Mode
            const toggleBtn = document.getElementById("toggleDarkMode");
            const html = document.documentElement;
            html.setAttribute("data-bs-theme", "dark");
            toggleBtn.addEventListener("click", () => {
                const newTheme = html.getAttribute("data-bs-theme") === "light" ? "dark" : "light";
                html.setAttribute("data-bs-theme", newTheme);
            });
        });
    </script>
    
</body>
</html>