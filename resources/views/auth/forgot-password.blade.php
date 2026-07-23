<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>PPN d'Andé – Mot de passe oublié</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}"/>
</head>
<body class="login-body">
  <div class="left-panel">
    <div class="brand-section">
      <div class="logo-ring"><i class="fas fa-key"></i></div>
      <div class="brand-name">PPN d'Andé</div>
      <div class="brand-sub">Point de Présence Numérique</div>
      <div class="divider-gold"></div>
      <p class="brand-desc">Réinitialisez votre mot de passe pour retrouver l'accès à votre espace de gestion.</p>
    </div>
  </div>

  <div class="right-panel">
    <div class="login-header">
      <h2>Mot de passe oublié</h2>
      <p>Indiquez votre email, un lien de réinitialisation vous sera envoyé</p>
    </div>

    @if (session('success'))
      <div class="alert-box" style="background:#d1fae5;color:#065f46;border:1px solid #a7f3d0;">
        <i class="fas fa-circle-check"></i>
        <span>{{ session('success') }}</span>
      </div>
    @endif
    @if ($errors->any())
      <div class="alert-box error">
        <i class="fas fa-circle-exclamation"></i>
        <span>{{ $errors->first() }}</span>
      </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
      @csrf
      <div class="form-group">
        <label for="email">Adresse Email</label>
        <div class="input-wrap">
          <input type="email" id="email" name="email" placeholder="admin@ppn-ande.fr" value="{{ old('email') }}" required/>
          <i class="fas fa-envelope"></i>
        </div>
      </div>
      <button type="submit" class="btn-login">Envoyer le lien de réinitialisation</button>
    </form>

    <p class="forgot"><a href="{{ route('login') }}">Retour à la connexion</a></p>

    <div class="login-footer">© {{ date('Y') }} PPN d'Andé · Tous droits réservés</div>
  </div>
</body>
</html>
