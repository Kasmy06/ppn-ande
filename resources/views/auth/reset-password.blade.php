<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>PPN d'Andé – Réinitialiser le mot de passe</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}"/>
</head>
<body class="login-body">
  <div class="left-panel">
    <div class="brand-section">
      <div class="logo-ring"><i class="fas fa-lock-open"></i></div>
      <div class="brand-name">PPN d'Andé</div>
      <div class="brand-sub">Point de Présence Numérique</div>
      <div class="divider-gold"></div>
      <p class="brand-desc">Choisissez un nouveau mot de passe pour votre compte.</p>
    </div>
  </div>

  <div class="right-panel">
    <div class="login-header">
      <h2>Nouveau mot de passe</h2>
      <p>Minimum 8 caractères</p>
    </div>

    @if ($errors->any())
      <div class="alert-box error">
        <i class="fas fa-circle-exclamation"></i>
        <span>{{ $errors->first() }}</span>
      </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" novalidate>
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <div class="form-group">
        <label for="email">Adresse Email</label>
        <div class="input-wrap">
          <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required/>
          <i class="fas fa-envelope"></i>
        </div>
      </div>
      <div class="form-group">
        <label for="password">Nouveau mot de passe</label>
        <div class="input-wrap">
          <input type="password" id="password" name="password" required/>
          <i class="fas fa-lock"></i>
        </div>
      </div>
      <div class="form-group">
        <label for="password_confirmation">Confirmer le mot de passe</label>
        <div class="input-wrap">
          <input type="password" id="password_confirmation" name="password_confirmation" required/>
          <i class="fas fa-lock"></i>
        </div>
      </div>
      <button type="submit" class="btn-login">Réinitialiser le mot de passe</button>
    </form>

    <div class="login-footer">© {{ date('Y') }} PPN d'Andé · Tous droits réservés</div>
  </div>
</body>
</html>
