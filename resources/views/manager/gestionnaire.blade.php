<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des employés | Pressing Manager</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    #sidebar { min-height: 100vh; }
    #employeeTableWrapper { max-height: 300px; overflow-y: auto; }
  </style>
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

  <!-- Contenu principal -->
  <div class="flex-grow-1 d-flex flex-column">

    <!-- Header -->
    <header class="d-flex justify-content-between align-items-center border-bottom p-3 bg-body shadow-sm">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar">☰</button>
        <h2 class="h5 fw-bold text-primary mb-0">Gestion des employés</h2>
      </div>
      <button id="toggleDarkMode" class="btn btn-outline-secondary">🌙</button>
    </header>

    <!-- Main -->
    <main class="flex-grow-1 container py-4">

      <!-- Bouton créer employé -->
      <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalEmployee">➕ Créer un employé</button>
      </div>

      <!-- Liste des employés -->
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">🧑‍💼 Employés</h5>
          <div id="employeeTableWrapper" class="table-responsive">
            <table class="table table-striped table-hover mb-0">
              <thead>
                <tr>
                  <th>Nom</th>
                  <th>Email</th>
                  <th>Téléphone</th>
                  <th>Rôle</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="employeeTableBody"></tbody>
            </table>
          </div>
        </div>
      </div>

    </main>
  </div>

  <!-- Modal Création/Modification -->
  <div class="modal fade" id="modalEmployee" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="formEmployee">
          <div class="modal-header">
            <h5 class="modal-title">Créer / Modifier un employé</h5>
          </div>
          <div class="modal-body">
            <input type="hidden" id="employeeId">
            <input type="text" id="employeeName" placeholder="Nom" class="form-control mb-2" required>
            <input type="email" id="employeeEmail" placeholder="Email" class="form-control mb-2" required>
            <input type="tel" id="employeePhone" placeholder="Téléphone" class="form-control mb-2">
            <select id="employeeRole" class="form-select mb-2">
              <option value="admin">Admin</option>
              <option value="employe">Employé</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Toast -->
  <div class="position-fixed top-0 end-0 p-3" style="z-index:1080">
    <div id="liveToast" class="toast align-items-center text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="toastMessage"></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>

  <script>
    // Dark mode
    const toggleBtn = document.getElementById("toggleDarkMode");
    const html = document.documentElement;
    html.setAttribute("data-bs-theme", "dark");
    toggleBtn.addEventListener("click", ()=>{
      const newTheme = html.getAttribute("data-bs-theme")==="light"?"dark":"light";
      html.setAttribute("data-bs-theme", newTheme);
    });

    // Toast
    const toastElement = document.getElementById('liveToast');
    const toast = new bootstrap.Toast(toastElement);
    function showToast(message,color='primary'){
      document.getElementById('toastMessage').textContent = message;
      toastElement.className = `toast align-items-center text-bg-${color} border-0`;
      toast.show();
    }

    // Données employés (exemple)
    let employees = [
      {id:1,name:"Jean Dupont",email:"jean@example.com",phone:"0600000001",role:"admin",blocked:false},
      {id:2,name:"Marie Claire",email:"marie@example.com",phone:"0600000002",role:"employe",blocked:false}
    ];

    function renderEmployees(){
      const tbody = document.getElementById("employeeTableBody");
      tbody.innerHTML = "";
      employees.forEach(emp=>{
        const status = emp.blocked?"Bloqué":"Actif";
        const btnClass = emp.blocked?"btn-success":"btn-danger";
        const btnText = emp.blocked?"Débloquer":"Bloquer";
        tbody.innerHTML += `
          <tr>
            <td>${emp.name}</td>
            <td>${emp.email}</td>
            <td>${emp.phone}</td>
            <td>${emp.role}</td>
            <td>${status}</td>
            <td class="d-flex gap-1 flex-wrap">
              <button class="btn btn-sm btn-primary btnEdit" data-id="${emp.id}">✏️</button>
              <button class="btn btn-sm ${btnClass} btnToggleBlock" data-id="${emp.id}">${btnText}</button>
            </td>
          </tr>`;
      });
    }

    document.addEventListener("DOMContentLoaded", ()=>{
      renderEmployees();

      // Création / modification
      document.getElementById("formEmployee").addEventListener("submit",function(e){
        e.preventDefault();
        const id = document.getElementById("employeeId").value;
        const emp = {
          id: id || employees.length+1,
          name: document.getElementById("employeeName").value,
          email: document.getElementById("employeeEmail").value,
          phone: document.getElementById("employeePhone").value,
          role: document.getElementById("employeeRole").value,
          blocked: false
        };
        if(id){
          const index = employees.findIndex(emp => emp.id == id);
          emp.blocked = employees[index].blocked;
          employees[index] = emp;
          showToast(`Employé "${emp.name}" modifié`,'success');
        } else {
          employees.push(emp);
          showToast(`Employé "${emp.name}" créé`,'success');
        }
        renderEmployees();
        bootstrap.Modal.getInstance(document.getElementById("modalEmployee")).hide();
        this.reset();
        document.getElementById("employeeId").value = '';
      });

      // Actions dynamiques
      document.addEventListener("click",function(e){
        if(e.target.classList.contains("btnEdit")){
          const id = e.target.dataset.id;
          const emp = employees.find(emp => emp.id == id);
          document.getElementById("employeeId").value = emp.id;
          document.getElementById("employeeName").value = emp.name;
          document.getElementById("employeeEmail").value = emp.email;
          document.getElementById("employeePhone").value = emp.phone;
          document.getElementById("employeeRole").value = emp.role;
          new bootstrap.Modal(document.getElementById("modalEmployee")).show();
        }
        if(e.target.classList.contains("btnToggleBlock")){
          const id = e.target.dataset.id;
          const emp = employees.find(emp => emp.id == id);
          emp.blocked = !emp.blocked;
          renderEmployees();
          showToast(emp.blocked?`Employé "${emp.name}" bloqué`:`Employé "${emp.name}" débloqué`, emp.blocked?'warning':'success');
        }
      });
    });
  </script>

</body>
</html>
