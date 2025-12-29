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
        /* Styles pour le Loader Global (Centré) */
        #loaderOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 2000;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0; 
            pointer-events: none;
            transition: opacity 0.1s ease-in-out;
        }
        .loader-visible {
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        /* Assure que l'alerte de validation est au-dessus de la modale du formulaire */
        #alertContainer {
            z-index: 1060; 
            position: relative; 
        }

        #sidebar { min-height: 100vh; }
        #userTableWrapper { max-height: 500px; overflow-y: auto; }
        .active-status { color: green; font-weight: bold; }
        .inactive-status { color: red; font-weight: bold; }
    </style>
</head>
<body class="d-flex">

    {{-- LOADER GLOBAL (Utilisé UNIQUEMENT pour le chargement initial) --}}
    <div id="loaderOverlay">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Chargement...</span>
        </div>
    </div>

    @include('partials.side-bar')

    <div class="flex-grow-1 d-flex flex-column">

        <header class="d-flex justify-content-between align-items-center border-bottom p-3 bg-body shadow-sm">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar">☰</button>
                <h2 class="h5 fw-bold text-primary mb-0">Gestion des Utilisateurs</h2>
            </div>
            <button id="toggleDarkMode" class="btn btn-outline-secondary">🌙</button>
        </header>

        <main class="flex-grow-1 container py-4">
            
            <div id="alertContainer" class="mb-3" style="display: none;">
                </div>

            <div class="d-flex justify-content-end mb-3">
                <button id="createUserBtn" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalUser">➕ Ajouter un utilisateur</button>
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

    {{-- MODALE CRÉATION/MODIFICATION --}}
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
                        {{-- BOUTON DE SOUMISSION AVEC SPINNER INTÉGRÉ --}}
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span id="buttonSpinner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                            <span id="buttonText">Enregistrer</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- TOAST --}}
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
        const perPage = 5; 

        // --- UTILS ---
        
        // Loader Global (pour le chargement des données)
        function showLoader() { $('#loaderOverlay').addClass('loader-visible'); }
        function hideLoader() { $('#loaderOverlay').removeClass('loader-visible'); }

        // Loader Bouton (pour la soumission du formulaire)
        function showButtonLoader(text) {
            $('#buttonText').text(text);
            $('#buttonSpinner').show();
            $('#submitBtn').prop('disabled', true);
        }

        function hideButtonLoader(defaultText) {
            $('#buttonText').text(defaultText);
            $('#buttonSpinner').hide();
            $('#submitBtn').prop('disabled', false);
        }

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
                nav.find('.page-link').off('click').on('click', function(e) {
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
                            <button class="btn btn-sm btn-primary btnEdit" data-id="${user.token}">✏️ Modifier</button>
                        </td>
                    </tr>`;
            });
            attachUserListeners();
            renderPagination(paginationObject);
        }

        // --- LOGIQUE AJAX ---

        function loadUsers(page = 1) {
            // Utilisation du loader global
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

        function editUser(token) { 
            const user = users.find(u => u.token == token); 
            if (!user) {
                showToast("Utilisateur non trouvé.", 'danger');
                return;
            }

            $('#alertContainer').slideUp();
            
            $('#userId').val(user.token); 
            $('#userName').val(user.name);
            $('#userEmail').val(user.email);
            $('#userPhone').val(user.phone);
            $('#userRole').val(user.role);
            $('#userStatus').val(user.status);
            
            $('#userPassword').val(''); 
            $('#userPassword').attr('placeholder', 'Laisser vide pour ne pas changer');
            
            $('#editFields').show();
            $('#modalUserLabel').text("Modifier l'utilisateur: " + user.name);
            $('#formUser').attr('action', '{{ route("manager.user.update", ["user" => ":token"]) }}'.replace(':token', user.token)); 
            
            hideButtonLoader('Sauvegarder les modifications'); 

            new bootstrap.Modal(document.getElementById('modalUser')).show();
        }

        // --- ÉCOUTEURS D'ÉVÉNEMENTS ---

        function attachUserListeners() {
            $('.btnEdit').off('click').on('click', function() {
                editUser($(this).data('id')); 
            });
        }

        // Fonction de nettoyage pour retirer le backdrop qui bloque la page
        function cleanModalBackdrop() {
            // Retirer explicitement le backdrop s'il est resté
            $('.modal-backdrop').remove();
            // Retirer la classe de blocage de défilement du body
            $('body').removeClass('modal-open').css('overflow', '');
        }

        $(document).ready(function() {
            showLoader(); 
            loadUsers(currentPage);

            $('#createUserBtn').on('click', function() {
                $('#formUser')[0].reset();
                $('#userId').val('');
                $('#userPassword').attr('placeholder', 'Mot de passe (requis)');
                
                $('#editFields').hide();
                
                $('#modalUserLabel').text("Ajouter un nouvel utilisateur");
                $('#formUser').attr('action', '{{ route("manager.user.store") }}');
                hideButtonLoader('Créer l\'utilisateur'); 
                $('#alertContainer').slideUp();
            });

            // Soumission du formulaire (Création ou Modification)
            $('#formUser').on('submit', function(e) {
                e.preventDefault();
                $('#alertContainer').slideUp(); 

                const form = $(this);
                const isUpdating = $('#userId').val() !== '';
                const url = form.attr('action');
                
                const loadingText = isUpdating ? 'Sauvegarde...' : 'Création...';
                const defaultText = isUpdating ? 'Sauvegarder les modifications' : 'Créer l\'utilisateur';
                showButtonLoader(loadingText); 
                
                form.find('input[name="_method"]').remove();
                
                if (isUpdating) {
                    form.append('<input type="hidden" name="_method" value="PUT">');
                }
                
                let data = form.serialize();

                $.ajax({
                    url: url,
                    method: 'POST', 
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
                        } else if (xhr.status === 405) {
                            showAlert('danger', "❌ Erreur 405: Méthode non autorisée. Vérifiez la route Laravel et le champ _method.");
                        } else {
                            showAlert('danger', `❌ Une erreur est survenue (Status: ${xhr.status}).`);
                        }
                        
                        // En cas d'erreur AJAX, le backdrop pourrait être laissé si la modale n'est pas fermée
                        cleanModalBackdrop();
                    },
                    complete: function() { 
                        // Fermeture du loader du bouton et nettoyage
                        hideButtonLoader(defaultText);
                        $('#formUser').find('input[name="_method"]').remove();
                    }
                });
            });

            // Gérer la fermeture de la modale par l'utilisateur ou par le succès AJAX
            document.getElementById('modalUser').addEventListener('hidden.bs.modal', function () {
                $('#alertContainer').slideUp();
                // CORRECTION CRITIQUE: Exécuter la fonction de nettoyage après la fermeture de la modale
                // C'est l'événement 'hidden' qui se déclenche lorsque Bootstrap a fini de cacher la modale,
                // ce qui est le moment idéal pour garantir le nettoyage du DOM.
                cleanModalBackdrop();
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