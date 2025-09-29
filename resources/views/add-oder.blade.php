<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nouvelle Commande | Pressing Manager</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
  {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> --}}
</head>
<body class="d-flex">

  <!-- Sidebar -->
  <aside class="offcanvas-lg offcanvas-start bg-body-tertiary border-end" tabindex="-1" id="sidebar">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title text-primary fw-bold">🧺 Manager</h5>
      <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
      <nav class="nav flex-column">
        <a href="{{route('dashboard')}}" class="nav-link text-secondary">← Retour au Dashboard</a>
      </nav>
    </div>
  </aside>

  <!-- Contenu -->
  <div class="flex-grow-1 d-flex flex-column">
    <!-- Header -->
    <header class="d-flex justify-content-between align-items-center border-bottom p-3 bg-body shadow-sm">
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-outline-secondary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar">☰</button>
        <h2 class="h5 fw-bold text-primary">Nouvelle commande</h2>
      </div>
      <button id="toggleDarkMode" class="btn btn-outline-secondary">🌙</button>
    </header>

    <!-- Main -->
    <main class="flex-grow-1 container py-4">
      
      <!-- Section Client -->
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <h5 class="card-title">Client</h5>
          <div class="d-flex flex-column flex-sm-row gap-2">
            <input type="tel" id="clientPhone" placeholder="Téléphone client" class="form-control">
            <button id="btnSearchClient" class="btn btn-primary">🔍 Rechercher</button>
            <button data-bs-toggle="modal" data-bs-target="#modalClient" class="btn btn-success">➕ Nouveau client</button>
          </div>
          <input type="hidden" id="clientId">
          <p id="clientInfo" class="mt-2 text-muted small"></p>
        </div>
      </div>

      <!-- Section Article + Service -->
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <h5 class="card-title">Article & Service</h5>

          <!-- Article -->
          <div class="mb-3">
            <input type="text" id="articleSearch" placeholder="Rechercher un article..." class="form-control">
            <input type="hidden" id="articleId">
            <div id="articleResults" class="list-group mt-1 d-none"></div>
            <button data-bs-toggle="modal" data-bs-target="#modalArticle" class="btn btn-success mt-2">➕ Nouvel article</button>
          </div>

          <!-- Service -->
          <div class="mb-3">
            <select id="serviceSelect" class="form-select"></select>
            <button data-bs-toggle="modal" data-bs-target="#modalService" class="btn btn-success mt-2">➕ Nouveau service</button>
          </div>

          <!-- Quantité + Montant horizontal -->
          <div class="mb-3 d-flex gap-2">
            <input type="number" id="lineQuantity" placeholder="Quantité"  class="form-control" style="width:120px;">
            <input type="number" id="linePrice" placeholder="Montant" class="form-control flex-grow-1">
          </div>

          <button id="btnAddLine" class="btn btn-primary w-100">➕ Ajouter au panier</button>
        </div>
      </div>

      <!-- Section Panier -->
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <h5 class="card-title">🛒 Panier</h5>
          <div class="table-responsive" style="max-height:150px; overflow-y:auto;">
            <table class="table table-sm table-striped">
              <thead>
                <tr>
                  <th>Article</th>
                  <th>Service</th>
                  <th>Quantité</th>
                  <th>Montant</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="orderTableBody"></tbody>
            </table>
          </div>
          <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-3 gap-2">
            <input type="text" id="totalAmount" readonly class="form-control fw-bold text-primary w-100 w-sm-auto" placeholder="Total : 0 FCFA">
            <button id="btnSaveOrder" class="btn btn-success">💾 Sauvegarder le dépôt</button>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Modals -->
  <div class="modal fade" id="modalClient" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="formCreateClient">
          <div class="modal-header"><h5 class="modal-title">Créer un client</h5></div>
          <div class="modal-body">
            <input type="text" name="name" placeholder="Nom complet" class="form-control mb-2">
            <input type="tel" name="phone" placeholder="Téléphone" class="form-control mb-2">
            <input type="email" name="email" placeholder="Email" class="form-control mb-2">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary">Créer</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalArticle" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="formCreateArticle">
          <div class="modal-header"><h5 class="modal-title">Créer un article</h5></div>
          <div class="modal-body">
            <input type="text" name="name" placeholder="Nom de l'article" class="form-control">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary">Créer</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalService" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="formCreateService">
          <div class="modal-header"><h5 class="modal-title">Créer un service</h5></div>
          <div class="modal-body">
            <input type="text" name="name" placeholder="Nom du service" class="form-control">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary">Créer</button>
          </div>
        </form>
      </div>
    </div>
  </div>

<script>
// Dark Mode avec icône dynamique
const toggleBtn = document.getElementById("toggleDarkMode");
const html = document.documentElement;

// Initialisation de l'icône selon le thème
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
</script>

<script>
// JS existant adapté
let clients=[{id:1,name:"Jean Dupont",phone:"0600000001"},{id:2,name:"Marie Claire",phone:"0600000002"}];
let articles=[{id:1,name:"Chemise"},{id:2,name:"Robe"}];
let services=[{id:1,name:"Nettoyage"},{id:2,name:"Repassage"}];

function loadServices(){
  $('#serviceSelect').empty();
  services.forEach(s=>$('#serviceSelect').append(`<option value="${s.id}">${s.name}</option>`));
}

function updateTotal(){
  let total=0;
  $('#orderTableBody tr').each(function(){
    let montant=parseFloat($(this).find('input[name^="montant"]').val())||0;
    let quantity=parseFloat($(this).find('input[name^="quantity"]').val())||1;
    total+=montant*quantity;
  });
  $('#totalAmount').val("Total : "+total+" FCFA");
}

let lineIndex=0;

$(document).ready(function(){
  loadServices();

  $('#btnSearchClient').click(function(){
    let phone=$('#clientPhone').val();
    let client=clients.find(c=>c.phone===phone);
    if(client){$('#clientId').val(client.id);$('#clientInfo').text("✔ Client : "+client.name);}
    else{$('#clientInfo').text("❌ Client introuvable");}
  });

  $('#articleSearch').on('input',function(){
    let query=$(this).val().toLowerCase();
    let results=articles.filter(a=>a.name.toLowerCase().includes(query));
    $('#articleResults').empty();
    if(results.length>0 && query.length>0){results.forEach(a=>$('#articleResults').append(
      `<div class="list-group-item list-group-item-action cursor-pointer" data-id="${a.id}" data-name="${a.name}">${a.name}</div>`
    )); $('#articleResults').removeClass('d-none');}
    else{$('#articleResults').addClass('d-none');}
  });

  $(document).on('click','#articleResults div',function(){
    $('#articleId').val($(this).data('id'));
    $('#articleSearch').val($(this).data('name'));
    $('#articleResults').addClass('d-none');
  });

  $('#btnAddLine').click(function(){
    let clientId=$('#clientId').val();
    let articleId=$('#articleId').val();
    let articleName=$('#articleSearch').val();
    let serviceId=$('#serviceSelect').val();
    let serviceName=$('#serviceSelect option:selected').text();
    let quantity=$('#lineQuantity').val();
    let montant=$('#linePrice').val();

    if(!clientId||!articleId||!montant||!quantity){alert("⚠ Veuillez sélectionner un client, un article, une quantité et un montant");return;}

    $('#orderTableBody').append(`
      <tr>
        <td>
          ${articleName}<input type="hidden" name="article_id[${lineIndex}]" value="${articleId}"><input type="hidden" name="client_id[${lineIndex}]" value="${clientId}">
        </td>
        <td>
          ${serviceName}<input type="hidden" name="service_id[${lineIndex}]" value="${serviceId}">
        </td>
        <td>
          <input type="number" name="quantity[${lineIndex}]" value="${quantity}" class="form-control form-control-sm" style="width:80px;">
        </td> 
        <td> 
          <input type="number" name="montant[${lineIndex}]" value="${montant}" class="form-control form-control-sm flex-grow-1">
        </td>
        <td>
          <button type="button" class="btn btn-sm btn-danger btnRemove">🗑</button>
        </td>
      </tr>
    `);

    lineIndex++; updateTotal();
    $('#articleId').val(''); $('#articleSearch').val(''); $('#lineQuantity').val(1); $('#linePrice').val('');
  });

  $(document).on('click','.btnRemove',function(){ $(this).closest('tr').remove(); updateTotal(); });
  $(document).on('input','input[name^="montant"], input[name^="quantity"]',updateTotal);

  $('#formCreateClient').submit(function(e){e.preventDefault(); let name=$(this).find('[name="name"]').val(); let phone=$(this).find('[name="phone"]').val(); clients.push({id:clients.length+1,name,phone}); alert("✅ Client ajouté"); $('#modalClient').modal('hide');});
  $('#formCreateArticle').submit(function(e){e.preventDefault(); let name=$(this).find('[name="name"]').val(); articles.push({id:articles.length+1,name}); alert("✅ Article ajouté"); $('#modalArticle').modal('hide');});
  $('#formCreateService').submit(function(e){e.preventDefault(); let name=$(this).find('[name="name"]').val(); services.push({id:services.length+1,name}); loadServices(); alert("✅ Service ajouté"); $('#modalService').modal('hide');});

  $('#btnSaveOrder').click(function(){ if($('#orderTableBody tr').length===0){alert("⚠ Le panier est vide !"); return;} alert("✅ Commande sauvegardée avec succès !");});
});
</script>

</body>
</html>
