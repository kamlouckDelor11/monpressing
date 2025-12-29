<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sécurisation du compte</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    #loaderOverlay { display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 100; flex-direction: column; justify-content: center; align-items: center; backdrop-filter: blur(2px); border-radius: 1rem; }
    #loader span { display: inline-block; animation: blink 1.5s infinite; }
    @keyframes blink { 0%, 100% { opacity: 0.2; } 50% { opacity: 1; } }
  </style>
</head>
<body class="bg-body-dark d-flex align-items-center justify-content-center min-vh-100">

  <div class="card shadow-lg rounded-4 p-5 position-relative text-center" style="max-width: 450px; width: 100%;">
    
    <div id="loaderOverlay">
      <div id="loader" class="text-white">Mise à jour<span>.</span><span>.</span><span>.</span></div>
    </div>

    <h1 class="h4 fw-bold text-primary mb-3">Sécurité de votre compte</h1>
    
    <div class="alert alert-warning small mb-4">
        Par mesure de sécurité, vous devez modifier le code de connexion temporaire suite à la réinitialisation de votre compte.
    </div>

    <div id="alertContainer"></div>

    <form id="passwordUpdateForm" method="POST" action="{{ route('password.update.store') }}">
      @csrf
      <div class="mb-3">
        <input type="password" name="old_password" placeholder="Ancien code" class="form-control" required>
      </div>
      <div class="mb-3">
        <input type="password" name="password" placeholder="Nouveau code" class="form-control" required>
      </div>
      <div class="mb-3">
        <input type="password" name="password_confirmation" placeholder="Confirmer le nouveau code" class="form-control" required>
      </div>
      
      <button type="submit" id="submitBtn" class="btn btn-primary w-100 fw-bold">🚀 Mettre à jour mon code</button>
    </form>
  </div>

<script>
$(document).ready(function(){
  $('#passwordUpdateForm').on('submit', function(e){
    e.preventDefault();
    $('#loaderOverlay').css('display', 'flex').hide().fadeIn(200);
    $('#submitBtn').prop('disabled', true);
    $('#alertContainer').empty();

    $.ajax({
      url: $(this).attr('action'),
      method: 'POST',
      data: $(this).serialize(),
      success: function(response){
        $('#loaderOverlay').fadeOut(300);
        let successHtml = `<div class="alert alert-success">${response.message}</div>`;
        $('#alertContainer').html(successHtml);
        setTimeout(() => { window.location.href = "{{ route('dashboard') }}"; }, 2000);
      },
      error: function(xhr){
        $('#loaderOverlay').fadeOut(200);
        $('#submitBtn').prop('disabled', false);
        let errorMsg = "❌ Une erreur est survenue.";
        if(xhr.status === 422){
            errorMsg = "<ul>";
            $.each(xhr.responseJSON.errors, function(k, v){ errorMsg += "<li>"+v[0]+"</li>"; });
            errorMsg += "</ul>";
        }
        $('#alertContainer').html(`<div class="alert alert-danger">${errorMsg}</div>`);
      }
    });
  });
});
</script>
</body>
</html>