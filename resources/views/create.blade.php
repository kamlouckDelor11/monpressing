<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Création du Pressing</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    .step {
      transition: all 0.5s ease;
      opacity: 0;
      transform: translateX(50px);
      height: 0;
      overflow: hidden;
    }
    .step.active {
      opacity: 1;
      transform: translateX(0);
      height: auto;
    }

    /* Overlay qui bloque les clics */
    #loaderOverlay {
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;cd 
      background: transparent;
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 2000;
      border-radius: 1rem;
    }

    /* Loader */
    #loader {
      padding: 15px 30px;
      border-radius: 8px;
      font-weight: bold;
      text-align: center;
      backdrop-filter: blur(2px);
    }
    #loader span {
      display: inline-block;
      animation: blink 1.5s infinite;
    }
    #loader span:nth-child(2) { animation-delay: 0.2s; }
    #loader span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes blink {
      0%, 100% { opacity: 0.2; }
      50% { opacity: 1; }
    }

    /* Couleur en mode clair/sombre */
    [data-bs-theme="dark"] #loader {
      color: #fff;
      background: rgba(0,0,0,0.4);
    }
    [data-bs-theme="light"] #loader {
      color: #000;
      background: rgba(255,255,255,0.7);
    }

    /* Message de succès centré */
    #successMessage {
      display: none; /* masqué par défaut */
      min-height: 200px;
    }
  </style>
</head>
<body class="bg-body-dark d-flex align-items-center justify-content-center min-vh-100">

  <div class="card shadow-lg rounded-4 p-5 position-relative text-center" style="max-width: 500px; width: 100%;">
    
    <!-- Overlay + Loader -->
    <div id="loaderOverlay">
      <div id="loader">Chargement<span>.</span><span>.</span><span>.</span></div>
    </div>
  <!-- Flèche de retour -->
    <a href="{{ route('welcome') }}" 
      class="position-absolute top-0 start-0 m-3 text-decoration-none text-primary fs-4">
      ←
    </a>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h4 fw-bold text-primary">Création du Pressing</h1>
      <button id="toggleDarkMode" class="btn btn-outline-secondary">☀️</button>
    </div>

    <!-- Zone messages (erreurs uniquement) -->
    <div id="alertContainer"></div>

    <!-- Indicateur d'étape -->
    <div class="d-flex align-items-center mb-4" id="stepIndicators">
      <div class="flex-grow-1 me-2 text-center">
        <div class="rounded-circle border border-primary bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width:32px; height:32px;" id="step0">1</div>
        <div class="mt-1 text-muted small">Bienvenue</div>
      </div>
      <div class="flex-grow-1 me-2 text-center">
        <div class="rounded-circle border border-secondary bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width:32px; height:32px;" id="step1">2</div>
        <div class="mt-1 text-muted small">Pressing</div>
      </div>
      <div class="flex-grow-1 text-center">
        <div class="rounded-circle border border-secondary bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width:32px; height:32px;" id="step2">3</div>
        <div class="mt-1 text-muted small">Admin</div>
      </div>
    </div>

    <!-- Formulaire multi-étapes -->
    <form id="wizardForm" method="POST" action="{{ route('create.store') }}">
      @csrf
      <!-- Étape 0 -->
      <div class="step active" id="stepContent0">
        <p class="text-muted mb-4 text-center">Nous allons configurer votre pressing en quelques étapes simples.</p>
        <div class="text-center">
          <button type="button" class="btn btn-primary px-4" onclick="nextStep()">Commencer</button>
        </div>
      </div>

      <!-- Étape 1 -->
      <div class="step" id="stepContent1">
        <div class="mb-3">
          <input type="text" name="pressing_name" placeholder="Nom du pressing" class="form-control" required>
        </div>
        <div class="d-flex justify-content-between">
          <button type="button" class="btn btn-secondary px-4" onclick="prevStep()">⬅ Précédent</button>
          <button type="button" class="btn btn-primary px-4" onclick="nextStep()">Suivant ➡</button>
        </div>
      </div>

      <!-- Étape 2 -->
      <div class="step" id="stepContent2">
        <div class="mb-3">
          <input type="text" name="admin_name" placeholder="Votre nom complet" class="form-control" required>
        </div>
        <div class="mb-3">
          <input type="email" name="admin_email" placeholder="Email" class="form-control" required>
        </div>
        <div class="mb-3">
          <input type="tel" name="admin_phone" placeholder="Téléphone" class="form-control">
        </div>
        <div class="mb-3">
          <input type="password" name="admin_password" placeholder="Code de connexion" class="form-control" required>
        </div>
        <div class="d-flex justify-content-between">
          <button type="button" class="btn btn-secondary px-4" onclick="prevStep()">⬅ Précédent</button>
          <button type="submit" class="btn btn-success px-4">✅ Enregistrer</button>
        </div>
      </div>
    </form>

    <!-- Message succès centré (masqué par défaut) -->
    <div id="successMessage" class="d-none flex-column justify-content-center align-items-center">
      <div class="alert alert-success w-100 text-center fw-bold"></div>
    </div>
  </div>

<script>
// Gestion étapes
let currentStep = 0;
const totalSteps = 3;
function showStep(step) {
  for (let i = 0; i < totalSteps; i++) {
    document.getElementById(`stepContent${i}`).classList.remove('active');
    const indicator = document.getElementById(`step${i}`);
    indicator.classList.replace('bg-primary','bg-secondary');
    indicator.classList.replace('border-primary','border-secondary');
  }
  document.getElementById(`stepContent${step}`).classList.add('active');
  const activeIndicator = document.getElementById(`step${step}`);
  activeIndicator.classList.replace('bg-secondary','bg-primary');
  activeIndicator.classList.replace('border-secondary','border-primary');
}
function nextStep(){ if(currentStep < totalSteps-1){ currentStep++; showStep(currentStep);} }
function prevStep(){ if(currentStep > 0){ currentStep--; showStep(currentStep);} }
document.addEventListener("DOMContentLoaded",()=>{showStep(currentStep);});

// Dark/Light Mode
const toggleBtn=document.getElementById("toggleDarkMode"),html=document.documentElement;
function updateIcon(){toggleBtn.textContent=html.getAttribute("data-bs-theme")==="light"?"🌙":"☀️";}
if(localStorage.getItem("theme")){html.setAttribute("data-bs-theme",localStorage.getItem("theme"));}
updateIcon();
toggleBtn.addEventListener("click",()=>{const newTheme=html.getAttribute("data-bs-theme")==="light"?"dark":"light";html.setAttribute("data-bs-theme",newTheme);localStorage.setItem("theme",newTheme);updateIcon();});

// AJAX soumission
$(document).ready(function(){
  $('#wizardForm').on('submit',function(e){
    e.preventDefault();
    $('#loaderOverlay').fadeIn();
    $('#alertContainer').empty().hide(); // on cache les erreurs

    let form = $(this);
    $.ajax({
      url: form.attr('action'),
      method: 'POST',
      data: form.serialize(),
      success:function(response){
        $('#loaderOverlay').fadeOut();
        form.hide(); 
        $('#successMessage').removeClass('d-none');
        $('#successMessage').addClass('d-flex');
        $('#stepIndicators').removeClass('d-flex');
        $('#stepIndicators').addClass('d-none');
        $('#successMessage .alert').html(response.message);

      },
      error:function(xhr){
        $('#loaderOverlay').fadeOut();
        $('#alertContainer').show(); 
        if(xhr.status===422){
          let errors = xhr.responseJSON.errors;
          let list = "<ul>";
          $.each(errors,function(k,v){ list+="<li>"+v[0]+"</li>"; });
          list+="</ul>";
          showAlert('danger', list);
        } else {
          showAlert('danger', "❌ Une erreur est survenue.");
        }
      }
    });
  });

  function showAlert(type,message){
    let alert=`
      <div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>`;
    $('#alertContainer').html(alert);
  }
});
</script>

</body>
</html>
