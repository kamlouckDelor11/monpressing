<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestion Dépôts | Pressing Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .form-section {
            display: none;
        }
        .form-section.active {
            display: block;
        }
        .order-table-container {
            max-height: 250px;
            overflow-y: auto;
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
            <h2 class="h5 fw-bold text-primary mb-0">Gestion des dépôts</h2>
            <div class="d-flex align-items-center gap-3">
                <span class="text-secondary">Bonjour, <strong>{{ Auth::User()->name }}</strong></span>
                <button id="toggleDarkMode" class="btn btn-outline-secondary">🌙</button>
            </div>
        </header>

        <main class="flex-grow-1 p-4 bg-body-tertiary">
            <div class="d-flex flex-column flex-md-row gap-3">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#orderModal" data-order-type="LAVOMATIC">Enregistrer un dépôt Lavomatic</button>
                <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#orderModal" data-order-type="PRESSING">Enregistrer un dépôt Pressing</button>
            </div>
        </main>
    </div>

    <div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderModalLabel">Enregistrer un dépôt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="orderForm" action=" {{route('orders.store') }}" method="POST">
                        @csrf
                        <input type="hidden" id="orderType" name="type">
                        <input type="hidden" name="client_token" id="clientToken">
                        
                        <div class="card mb-4">
                            <div class="card-body">
                                <h6 class="card-title">Informations Client</h6>
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-5">
                                        <label for="clientSearchInput" class="form-label">Rechercher un client (par téléphone)</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="clientSearchInput" placeholder="Ex: 0612345678">
                                            <button type="button" class="btn btn-outline-primary" id="openCreateClientModalBtn">+</button>
                                        </div>
                                        <small id="clientSearchStatus" class="form-text text-muted"></small>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="clientNameInput" class="form-label">Client</label>
                                        <input type="text" class="form-control" id="clientNameInput" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <h6 class="card-title">Détails de la commande</h6>
                                <div id="lavomaticSection" class="form-section">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="kiloInput" class="form-label">Kilos</label>
                                            <input type="number" step="0.01" class="form-control" id="kiloInput">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="pricePerKiloInput" class="form-label">Prix par kilo (XAF)</label>
                                            <input type="number" step="0.01" class="form-control" id="pricePerKiloInput">
                                        </div>
                                        <div class="col-md-4 align-self-end">
                                            <button type="button" class="btn btn-success w-100" id="addToLavomaticCartBtn">Ajouter au panier</button>
                                        </div>
                                    </div>
                                </div>
                                <div id="pressingSection" class="form-section">
                                    <div class="row g-3">
                                        <div class="col-md-3 position-relative">
                                            <label for="articleSearchInput" class="form-label">Article</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="articleSearchInput" placeholder="Rechercher un article...">
                                                <button type="button" class="btn btn-outline-primary" id="openCreateArticleModalBtn">+</button>
                                            </div>
                                            <div id="articleSearchResults" class="list-group position-absolute w-100 mt-1" style="z-index: 1050;"></div>
                                            <input type="hidden" id="selectedArticleToken">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="serviceSelect" class="form-label">Service</label>
                                            <select class="form-select" id="serviceSelect"></select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="pressingUnitPrice" class="form-label">Prix unitaire (XAF)</label>
                                            <input type="number" step="0.01" class="form-control" id="pressingUnitPrice" placeholder="Prix">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="pressingQuantity" class="form-label">Qté</label>
                                            <input type="number" class="form-control" id="pressingQuantity" value="1" min="1">
                                        </div>
                                        <div class="col-md-2 align-self-end">
                                            <button type="button" class="btn btn-success w-100" id="addToPressingCartBtn">Ajouter</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="order-table-container mt-4">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Article/Service</th>
                                                <th>Quantité</th>
                                                <th>Prix unitaire</th>
                                                <th>Total</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="cartItems">
                                            </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <h6 class="card-title">Récapitulatif & Paiement</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="totalAmountInput" class="form-label">Total (XAF)</label>
                                        <input type="text" class="form-control" id="totalAmountInput" name="total_amount" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="discountInput" class="form-label">Remise (XAF)</label>
                                        <input type="number" step="0.01" class="form-control" id="discountInput" name="discount_amount" value="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="finalAmountInput" class="form-label">Montant final (XAF)</label>
                                        <input type="text" class="form-control" id="finalAmountInput" readonly>
                                    </div>
                                    {{-- <div class="col-md-4">
                                        <label for="paymentMethodSelect" class="form-label">Mode de règlement</label>
                                        <select class="form-select" id="paymentMethodSelect" name="payment_method">
                                            <option value="cash">Espèces</option>
                                            <option value="card">Carte</option>
                                            <option value="credit">À crédit</option>
                                        </select>
                                    </div> --}}
                                    {{-- <div class="col-md-4">
                                        <label for="paidAmountInput" class="form-label">Montant perçu (€)</label>
                                        <input type="number" step="0.01" class="form-control" id="paidAmountInput" name="paid_amount">
                                    </div> --}}
                                    <div class="col-md-4">
                                        <label for="depositDateInput" class="form-label">Date de dépôt</label>
                                        <input type="date" class="form-control" id="depositDateInput" name="deposit_date" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="deliveryDateInput" class="form-label">Date de livraison</label>
                                        <input type="date" class="form-control" id="deliveryDateInput" name="delivery_date" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" form="orderForm" class="btn btn-primary">Enregistrer le dépôt</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createClientModal" tabindex="-1" aria-labelledby="createClientModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createClientModalLabel">Ajouter un nouveau client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createClientForm" method="POST" action="{{ route('clients.store') }}">
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
                    <button type="submit" form="createClientForm" class="btn btn-primary">Enregistrer le client</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="createArticleModal" tabindex="-1" aria-labelledby="createArticleModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createArticleModalLabel">Ajouter un nouvel article</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createArticleForm">
                        @csrf
                        <div class="mb-3">
                            <label for="articleName" class="form-label">Nom de l'article</label>
                            <input type="text" class="form-control" id="articleName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="articleDescription" class="form-label">Description (facultatif)</label>
                            <textarea class="form-control" id="articleDescription" name="description"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" form="createArticleForm" class="btn btn-primary">Enregistrer</button>
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

    <div id="loader" class="modal-backdrop d-none position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" style="background-color: rgba(0,0,0,0.5); z-index: 2500;">
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
            modalMessage.html(message);
            
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

        let cart = [];
        let articles = [];
        let services = [];
        let selectedClient = null;

        // Écouteur d'événement pour le champ de remise
        $('#discountInput').on('input', function() {
            let discount = parseFloat($(this).val());
            
            // Récupérer le montant total actuel du panier
            const total = parseFloat($('#totalAmountInput').val());

            // Validation 1 : La remise ne peut pas être négative.
            if (discount < 0) {
                $(this).val(0);
                discount = 0;
            }
            
            // Validation 2 : La remise ne peut pas être supérieure au montant total.
            if (discount > total) {
                $(this).val(total.toFixed(2));
                discount = total;
            }

            // Mettre à jour les totaux après les validations
            updateTotals();
        });

         function updateTotals() {
            let total = 0;
            cart.forEach(item => {
                total += item.total_price;
            });

            const discount = parseFloat($('#discountInput').val()) || 0;
            const finalAmount = total - discount;

            $('#totalAmountInput').val(total.toFixed(2));
            $('#finalAmountInput').val(finalAmount.toFixed(2));
            
            // Mettre à jour l'attribut max du champ de paiement
            $('#paidAmountInput').attr('max', finalAmount);
        }

        function renderCart() {
            const tbody = $('#cartItems');
            tbody.empty();
            
            if (cart.length > 0) {
                cart.forEach((item, index) => {
                    const row = `
                        <tr data-index="${index}" data-price="${item.unit_price}">
                            <td>${item.item_name}</td>
                            <td><input type="number" class="form-control form-control-sm item-quantity" value="${item.quantity}" min="1"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm item-unit-price" value="${item.unit_price}"></td>
                            <td class="item-total">${item.total_price.toFixed(2)}</td>
                            <td><button type="button" class="btn btn-sm btn-danger remove-item-btn">X</button></td>
                            <input type="hidden" name="items[${index}][item_name]" value="${item.item_name}">
                            <input type="hidden" name="items[${index}][item_type]" value="${item.item_type}">
                            <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
                            <input type="hidden" name="items[${index}][unit_price]" value="${item.unit_price}">
                            <input type="hidden" name="items[${index}][total_price]" value="${item.total_price}">
                            ${item.article_token ? `<input type="hidden" name="items[${index}][article_token]" value="${item.article_token}">` : ''}
                            ${item.service_token ? `<input type="hidden" name="items[${index}][service_token]" value="${item.service_token}">` : ''}
                        </tr>
                    `;
                    tbody.append(row);
                });
            } else {
                tbody.append('<tr><td colspan="5" class="text-center">Le panier est vide.</td></tr>');
            }
            
            updateTotals();
        }

        // Recherche en direct du client
        let clientSearchTimeout;
        $('#clientSearchInput').on('input', function() {
            clearTimeout(clientSearchTimeout);
            const phone = $(this).val();
            const statusMessage = $('#clientSearchStatus');
            statusMessage.html('');
            
            if (phone.length >= 8) {
                clientSearchTimeout = setTimeout(() => {
                    showLoader();
                    $.get(`/api/clients/search?phone=${phone}`)
                    .done(function(data) {
                        if (data.found) {
                            statusMessage.html(`Client trouvé : <span class="text-success">${data.client.name}</span>`);
                            $('#clientNameInput').val(data.client.name);
                            $('#clientToken').val(data.client.token);
                            selectedClient = data.client;
                        } else {
                            statusMessage.html(`Aucun client trouvé. <a href="#" data-bs-toggle="modal" data-bs-target="#createClientModal">Ajouter un nouveau client</a>`);
                            $('#clientNameInput').val('');
                            $('#clientToken').val('');
                            selectedClient = null;
                        }
                    })
                    .always(function() {
                        hideLoader();
                    });
                }, 500);
            }
        });
        
        // Recherche en direct de l'article (Pressing)
        let articleSearchTimeout;
        $('#articleSearchInput').on('input', function() {
            clearTimeout(articleSearchTimeout);
            const name = $(this).val();
            const resultsContainer = $('#articleSearchResults');
            resultsContainer.empty();
            
            if (name.length > 1) {
                articleSearchTimeout = setTimeout(() => {
                    showLoader();
                    $.get(`/api/articles/search?name=${name}`)
                    .done(function(data) {
                        articles = data; 
                        if (data.length > 0) {
                            data.forEach(article => {
                                resultsContainer.append(`<a href="#" class="list-group-item list-group-item-action article-result" data-token="${article.token}" data-name="${article.name}">${article.name}</a>`);
                            });
                            resultsContainer.show();
                        } else {
                            resultsContainer.html('<div class="list-group-item">Aucun article trouvé. <a href="#" data-bs-toggle="modal" data-bs-target="#createArticleModal">Ajouter un article</a></div>');
                            resultsContainer.show();
                        }
                    })
                    .always(function() {
                        hideLoader();
                    });
                }, 300);
            } else {
                resultsContainer.hide();
            }
        });

        $(document).on('click', '.article-result', function(e) {
            e.preventDefault();
            const token = $(this).data('token');
            const name = $(this).data('name');
            $('#articleSearchInput').val(name);
            $('#selectedArticleToken').val(token);
            $('#articleSearchResults').hide();
        });
        
        // Logique des modales
        $('#orderModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const orderType = button.data('order-type');
            
            $('#orderModalLabel').text(`Enregistrer un dépôt ${orderType}`);
            $('#orderType').val(orderType);
            
            $('.form-section').removeClass('active');
            if (orderType === 'LAVOMATIC') {
                $('#lavomaticSection').addClass('active');
            } else {
                $('#pressingSection').addClass('active');
            }
            
            $('#orderForm')[0].reset();
            cart = [];
            renderCart();
            $('#clientSearchStatus').empty();
            selectedClient = null;
            $('#depositDateInput').val(new Date().toISOString().slice(0, 10));
        });


        // Gestion des services et du prix unitaire
        $(document).ready(function() {
            $.get("{{ url('/api/services') }}", function(data) {
                services = data;
                const select = $('#serviceSelect');
                select.empty();
                select.append('<option value="">Sélectionner un service...</option>');
                services.forEach(s => {
                    select.append(`<option value="${s.token}" data-price="${s.price}">${s.name}</option>`);
                });
            });
        });

        // Écouteur d'événement pour mettre à jour le prix lorsque le service change
        $('#serviceSelect').on('change', function() {
            const selectedServiceToken = $(this).val();
            const selectedService = services.find(s => s.token === selectedServiceToken);
            
            if (selectedService) {
                $('#pressingUnitPrice').val(selectedService.price);
            } else {
                $('#pressingUnitPrice').val('');
            }
        });

        // Ajout au panier Lavomatic
        $('#addToLavomaticCartBtn').on('click', function() {
            const kilos = parseFloat($('#kiloInput').val());
            const pricePerKilo = parseFloat($('#pricePerKiloInput').val());
            
            if (kilos > 0 && pricePerKilo >= 0) {
                const totalItemPrice = kilos * pricePerKilo;
                
                cart.push({
                    item_name: 'Lavomatic',
                    item_type: 'lavomatic',
                    quantity: kilos,
                    unit_price: pricePerKilo,
                    total_price: totalItemPrice
                });
                
                // Réinitialisation des champs
                $('#kiloInput').val('');
                $('#pricePerKiloInput').val('');
                
                renderCart();
            } else {
                showNotification('Veuillez renseigner les kilos et le prix par kilo.', false);
            }
        });
        
        // Ajout au panier Pressing
        $('#addToPressingCartBtn').on('click', function() {
            const articleToken = $('#selectedArticleToken').val();
            const serviceToken = $('#serviceSelect').val();
            const quantity = parseInt($('#pressingQuantity').val());
            const unitPrice = parseFloat($('#pressingUnitPrice').val());
            
            const selectedArticle = articles.find(a => a.token === articleToken);
            const selectedService = services.find(s => s.token === serviceToken);
            
            if (selectedArticle && selectedService && quantity > 0 && unitPrice >= 0) {
                const totalItemPrice = quantity * unitPrice;
                cart.push({
                    item_name: `${selectedArticle.name} - ${selectedService.name}`,
                    item_type: 'pressing_service',
                    article_token: articleToken,
                    service_token: serviceToken,
                    quantity: quantity,
                    unit_price: unitPrice,
                    total_price: totalItemPrice
                });
                $('#articleSearchInput').val('');
                $('#selectedArticleToken').val('');
                $('#serviceSelect').val('');
                $('#pressingUnitPrice').val('');
                $('#pressingQuantity').val(1);
                renderCart();
            } else {
                showNotification('Veuillez sélectionner un article, un service, le prix unitaire et la quantité.', false);
            }
        });

        // Modification dynamique du panier
        $(document).on('input', '.item-quantity, .item-unit-price', function() {
            const row = $(this).closest('tr');
            const index = row.data('index');
            let quantity = parseFloat(row.find('.item-quantity').val()) || 0;
            let unitPrice = parseFloat(row.find('.item-unit-price').val()) || 0;
            
            if (quantity < 1) quantity = 1;
            
            cart[index].quantity = quantity;
            cart[index].unit_price = unitPrice;
            cart[index].total_price = quantity * unitPrice;

            row.find('.item-total').text(cart[index].total_price.toFixed(2));
            renderCart();
        });
        
        // Suppression d'un article du panier
        $(document).on('click', '.remove-item-btn', function() {
            const index = $(this).closest('tr').data('index');
            cart.splice(index, 1);
            renderCart();
        });

        // Envoi du formulaire
        $('#orderForm').on('submit', function(e) {
            e.preventDefault(); // Empêche la soumission de formulaire par défaut
            
            // Vérifications avant l'envoi
            if (!selectedClient || !$('#clientToken').val()) {
                showNotification('Veuillez sélectionner un client pour la commande.', false);
                return;
            }
            
            if (cart.length === 0) {
                showNotification('Le panier est vide. Veuillez ajouter des articles.', false);
                return;
            }
            
            showLoader();
            
            // Récupération de toutes les données du formulaire
            const formData = new FormData(this);
            
            // Convertir les données du panier en JSON et les ajouter au FormData
            // Cela permet au backend de les traiter facilement
            formData.append('cart_items', JSON.stringify(cart));
    
            $.ajax({
                url: $(this).attr('action'),
                method: $(this).attr('method'),
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        showNotification(response.message || 'Le dépôt a été enregistré avec succès !', true);
                        $('#orderModal').modal('hide'); // Ferme la modal après succès
                    } else {
                        showNotification(response.message || 'Erreur lors de l\'enregistrement du dépôt.', false);
                    }

                //Réinitialiser le formulaire et le panier
                $('#orderForm')[0].reset(); // Réinitialise tous les champs du formulaire
                cart = []; // Vide le tableau du panier
                renderCart(); // Met à jour l'affichage du panier pour qu'il soit vide
                selectedClient = null; // Réinitialise le client sélectionné
                $('#clientSearchStatus').empty(); // Vide le message de statut du client
                },
                error: function(xhr) {
                    hideLoader();
                    let errorMessage = 'Erreur serveur. Veuillez réessayer.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showNotification(errorMessage, false);
                }
            });
        });

        // --- GESTION DES MODALES IMBRIQUÉES (CLIENT & ARTICLE) ---

        // Ouvrir le modal Client sans fermer le modal Order
        $('#openCreateClientModalBtn').on('click', function() {
            $('#createClientModal').modal('show');
        });

        // Ouvrir le modal Article sans fermer le modal Order
        $('#openCreateArticleModalBtn').on('click', function() {
            $('#createArticleModal').modal('show');
        });

        // Soumission AJAX du formulaire Client
        $('#createClientForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action');

            showLoader();
            $.ajax({
                url: url,
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    hideLoader();
                    $('#createClientModal').modal('hide');
                    showNotification(response.message || 'Client créé avec succès !', true);
                    
                    // Mise à jour automatique du formulaire principal
                    if (response.client) {
                        $('#clientSearchInput').val(response.client.phone);
                        $('#clientNameInput').val(response.client.name);
                        $('#clientToken').val(response.client.token);
                        selectedClient = response.client;
                        $('#clientSearchStatus').html(`Client sélectionné : <span class="text-success">${response.client.name}</span>`);
                    }
                    form[0].reset();
                },
                error: function(response) {
                    hideLoader();
                    const errorMessage = response.responseJSON?.message || 'Erreur lors de la création du client.';
                    showNotification(errorMessage, false);
                }
            });
        });

        // Soumission AJAX du formulaire Article
        $('#createArticleForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            // On suppose que la route est /articles (store)
            const url = "{{ route('articles.store') }}"; 

            showLoader();
            $.ajax({
                url: url,
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    hideLoader();
                    $('#createArticleModal').modal('hide');
                    showNotification(response.message || 'Article créé avec succès !', true);

                    // Mise à jour automatique du formulaire principal
                    if (response.article) {
                        $('#articleSearchInput').val(response.article.name);
                        $('#selectedArticleToken').val(response.article.token);
                        // On cache les résultats de recherche car on a sélectionné l'article
                        $('#articleSearchResults').hide();
                    }
                    form[0].reset();
                },
                error: function(response) {
                    hideLoader();
                    const errorMessage = response.responseJSON?.message || 'Erreur lors de la création de l\'article.';
                    showNotification(errorMessage, false);
                }
            });
        });
    </script>
</body>
</html>