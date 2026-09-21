<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>PPN d'Andé – Connexion</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}"/>
  @include('layouts._favicon')
</head>
<body class="login-body">
  @php
    $logoPath = \App\Models\ParametreApplication::get('logo_path');
    $nomStructureLogin = \App\Models\ParametreApplication::get('nom_structure', "PPN d'Andé");
  @endphp
  <div class="left-panel">
    <div class="brand-section">
      <div class="logo-ring" style="{{ $logoPath ? 'background:white;overflow:hidden;' : '' }}">
        @if ($logoPath)
          <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath) }}" alt="Logo" style="width:100%;height:100%;object-fit:contain;border-radius:50%;padding:8px;box-sizing:border-box;"/>
        @else
          <i class="fas fa-network-wired"></i>
        @endif
      </div>
      <div class="brand-name">{{ $nomStructureLogin }}</div>
      <div class="brand-sub">Point de Présence Numérique</div>
      <div class="divider-gold"></div>
      <p class="brand-desc">
        Suivi et analyse en temps réel de la fréquentation du Point de Présence Numérique d'Andé : élèves, fonctionnaires et visiteurs externes.
      </p>
      <div class="stats-preview">
        <div class="stat-badge">
          <div class="num">{{ \App\Models\Visiteur::count() }}</div>
          <div class="lbl">Visiteurs</div>
        </div>
        <div class="stat-badge">
          <div class="num">{{ \App\Models\Visiteur::where('type', 'Élève')->count() }}</div>
          <div class="lbl">Élèves</div>
        </div>
        <div class="stat-badge">
          <div class="num">{{ \App\Models\Etablissement::count() }}</div>
          <div class="lbl">Établissements</div>
        </div>
      </div>
    </div>
  </div>

  <div class="right-panel">
    <div class="login-header">
      <h2>Bienvenue</h2>
      <p>Connectez-vous à votre espace de gestion</p>
    </div>

    @if ($errors->has('auth'))
      <div class="alert-box error">
        <i class="fas fa-circle-exclamation"></i>
        <span>{{ $errors->first('auth') }}</span>
      </div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}" novalidate>
      @csrf
      <div class="form-group">
        <label for="email">Adresse Email</label>
        <div class="input-wrap">
          <input type="email" id="email" name="email" placeholder="admin@ppn-ande.fr" value="{{ old('email') }}" autocomplete="email" required/>
          <i class="fas fa-envelope"></i>
        </div>
        @error('email')
          <div class="field-error">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group">
        <label for="password">Mot de passe</label>
        <div class="input-wrap">
          <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password" required/>
          <i class="fas fa-lock"></i>
          <i class="fas fa-eye toggle-pw" id="togglePw"></i>
        </div>
        @error('password')
          <div class="field-error">{{ $message }}</div>
        @enderror
      </div>

      <button type="submit" class="btn-login">Se connecter</button>
    </form>

    <p class="forgot">
      <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
    </p>

    <div class="login-footer">
      © {{ date('Y') }} PPN d'Andé · Tous droits réservés
    </div>
  </div>

  <script>
    document.getElementById('togglePw').addEventListener('click', function() {
      const pw = document.getElementById('password');
      if (pw.type === 'password') {
        pw.type = 'text';
        this.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        pw.type = 'password';
        this.classList.replace('fa-eye-slash', 'fa-eye');
      }
    });
  </script>
</body>
</html>
