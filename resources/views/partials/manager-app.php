
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary">Gestion des Abonnements</h3>
        <div class="col-md-3">
            <select id="planFilter" class="form-select shadow-sm">
                <option value="">Tous les plans</option>
                <option value="basic">Plan Basic</option>
                <option value="inactive">Inactif</option>
            </select>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="pressingsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Nom du Pressing</th>
                            <th>Expiration</th>
                            <th>Plan Actuel</th>
                            <th class="text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="pressingsTableBody">
                        {{-- Chargé par AJAX --}}
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-center border-0" id="paginationLinks">
            {{-- Pagination chargée par AJAX --}}
        </div>
    </div>
</div>

<div class="modal fade" id="pressingDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold" id="detailPressingName">Détail du Pressing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="pressingInfoSummary" class="mb-4">
                    <p class="mb-1 text-muted">Statut de l'abonnement : <span id="detailStatus" class="fw-bold"></span></p>
                    <p class="mb-1 text-muted">Dernière souscription : <span id="detailLastSub" class="fw-bold"></span></p>
                </div>
                
                <div class="d-grid gap-2">
                    <button id="btnDeactivatePlan" class="btn btn-outline-danger btn-lg">
                        <i class="bi bi-slash-circle me-2"></i> Rendre Inactif
                    </button>
                    <button id="btnOpenExtendSub" class="btn btn-primary btn-lg">
                        <i class="bi bi-calendar-check me-2"></i> Renouveler l'abonnement
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="updateSubscriptionModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Renouvellement</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="subscriptionForm">
                <div class="modal-body">
                    <input type="hidden" id="subPressingToken">
                    <div class="mb-3">
                        <label class="form-label small">Date de souscription</label>
                        <input type="date" id="last_subscription_at" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Durée (en mois)</label>
                        <input type="number" id="duration_months" class="form-control" min="1" placeholder="Ex: 1, 6, 12" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="submit" class="btn btn-success w-100">Calculer & Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let currentPage = 1;
    let currentPlan = '';

    function fetchPressings(page = 1, plan = '') {
        $.ajax({
            url: "", // Créez cette route
            data: { page: page, plan: plan },
            success: function(res) {
                let rows = '';
                res.data.forEach(p => {
                    let badge = p.subscription_plan === 'basic' ? 'bg-success' : 'bg-danger';
                    rows += `
                        <tr>
                            <td><span class="fw-bold">${p.name}</span><br><small class="text-muted">${p.token}</small></td>
                            <td>${p.subscription_expires_at ? new Date(p.subscription_expires_at).toLocaleDateString() : 'N/A'}</td>
                            <td><span class="badge ${badge}">${p.subscription_plan.toUpperCase()}</span></td>
                            <td class="text-end px-4">
                                <button class="btn btn-sm btn-outline-primary" onclick="showDetail('${p.token}', '${p.name}', '${p.subscription_plan}', '${p.last_subscription_at}')">Gérer</button>
                            </td>
                        </tr>`;
                });
                $('#pressingsTableBody').html(rows);
                renderPagination(res);
            }
        });
    }

    function renderPagination(res) {
        let links = '';
        if (res.last_page > 1) {
            for (let i = 1; i <= res.last_page; i++) {
                links += `<button class="btn btn-sm mx-1 ${i === res.current_page ? 'btn-primary' : 'btn-outline-primary'}" onclick="changePage(${i})">${i}</button>`;
            }
        }
        $('#paginationLinks').html(links);
    }

    window.changePage = function(page) { fetchPressings(page, currentPlan); };
    $('#planFilter').on('change', function() { currentPlan = $(this).val(); fetchPressings(1, currentPlan); });

    window.showDetail = function(token, name, plan, lastSub) {
        $('#subPressingToken').val(token);
        $('#detailPressingName').text(name);
        $('#detailStatus').text(plan.toUpperCase()).attr('class', plan === 'basic' ? 'text-success' : 'text-danger');
        $('#detailLastSub').text(lastSub || 'Jamais');
        $('#pressingDetailModal').modal('show');
    };

    // Action : Désactiver
    $('#btnDeactivatePlan').click(function() {
        if(confirm("Désactiver le pressing et tous ses utilisateurs ?")) {
            const token = $('#subPressingToken').val();
            updateStatus(token, 'inactive');
        }
    });

    // Action : Ouvrir Modale Renouvellement
    $('#btnOpenExtendSub').click(function() {
        $('#pressingDetailModal').modal('hide');
        $('#updateSubscriptionModal').modal('show');
    });

    // Submit Renouvellement
    $('#subscriptionForm').submit(function(e) {
        e.preventDefault();
        const data = {
            token: $('#subPressingToken').val(),
            last_subscription_at: $('#last_subscription_at').val(),
            duration: $('#duration_months').val(),
            plan: 'basic'
        };
        updateStatus(data.token, 'basic', data.last_subscription_at, data.duration);
    });

    function updateStatus(token, plan, lastSub = null, duration = null) {
        $.ajax({
            url: "",
            method: 'POST',
            data: { 
                _token: "{{ csrf_token() }}",
                token: token,
                plan: plan,
                last_subscription_at: lastSub,
                duration: duration
            },
            success: function() {
                $('#updateSubscriptionModal').modal('hide');
                $('#pressingDetailModal').modal('hide');
                fetchPressings(currentPage, currentPlan);
                alert("Mise à jour réussie");
            }
        });
    }

    fetchPressings();
});
</script>
