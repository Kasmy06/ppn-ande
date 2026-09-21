<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>@yield('title', "PPN d'Andé") – Point de Présence Numérique</title>
  <meta name="description" content="@yield('description', "Découvrez les formations, ateliers et événements du Point de Présence Numérique d'Andé.")"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="{{ asset('css/site.css') }}"/>
</head>
<body>
@php
  $logoPath = \App\Models\ParametreApplication::get('logo_path');
  $nomStructure = \App\Models\ParametreApplication::get('nom_structure', "PPN d'Andé");
@endphp

<header class="site-header">
  <div class="wrap header-in">
    <a href="{{ route('site.accueil') }}" class="brand">
      <span class="brand-logo">
        @if ($logoPath)
          <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath) }}" alt="Logo {{ $nomStructure }}"/>
        @else
          <i class="fas fa-network-wired"></i>
        @endif
      </span>
      <span class="brand-text">{{ $nomStructure }}<small>Point de Présence Numérique</small></span>
    </a>
    <button class="menu-toggle" type="button" aria-label="Menu" onclick="document.querySelector('.site-nav').classList.toggle('open')"><i class="fas fa-bars"></i></button>
    <nav class="site-nav">
      <a href="{{ route('site.accueil') }}" class="{{ request()->routeIs('site.accueil') ? 'active' : '' }}">Accueil</a>
      <a href="{{ route('site.activites') }}" class="{{ request()->routeIs('site.activite*') ? 'active' : '' }}">Activités</a>
      <a href="{{ route('site.galerie') }}" class="{{ request()->routeIs('site.galerie') ? 'active' : '' }}">Galerie</a>
      <a href="{{ route('site.accueil') }}#contact">Contact</a>
      <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="nav-cta"><i class="fas fa-lock"></i> Espace équipe</a>
    </nav>
  </div>
</header>

<main>@yield('content')</main>

<footer class="site-footer" id="contact">
  <div class="wrap footer-in">
    <div>
      <div class="footer-title">{{ $nomStructure }}</div>
      <p>Un lieu d'accès libre au numérique : formations, ateliers et accompagnement pour tous.</p>
    </div>
    <div>
      <div class="footer-title">Contact</div>
      <p>
        @if ($adresse = \App\Models\ParametreApplication::get('adresse'))<i class="fas fa-location-dot"></i> {{ $adresse }}<br>@endif
        @if ($tel = \App\Models\ParametreApplication::get('telephone'))<i class="fas fa-phone"></i> {{ $tel }}<br>@endif
        @if ($mail = \App\Models\ParametreApplication::get('email'))<i class="fas fa-envelope"></i> <a href="mailto:{{ $mail }}">{{ $mail }}</a>@endif
        @if (! ($adresse ?? null) && ! ($tel ?? null) && ! ($mail ?? null))Andé — renseignez-vous auprès de l'équipe.@endif
      </p>
    </div>
    <div>
      <div class="footer-title">Navigation</div>
      <p><a href="{{ route('site.activites') }}">Toutes les activités</a><br><a href="{{ route('login') }}">Espace équipe</a></p>
    </div>
  </div>
  <div class="copy">© {{ date('Y') }} {{ $nomStructure }}</div>
</footer>
<div class="lightbox" id="lightbox" hidden>
  <button type="button" class="lb-close" aria-label="Fermer">&times;</button>
  <div class="lb-body" id="lbBody"></div>
  <div class="lb-title" id="lbTitle"></div>
</div>
<script>
(function () {
  var lb = document.getElementById('lightbox'), body = document.getElementById('lbBody'), title = document.getElementById('lbTitle');
  if (!lb) return;
  function close() { lb.hidden = true; body.innerHTML = ''; document.body.style.overflow = ''; }
  document.addEventListener('click', function (e) {
    var m = e.target.closest('.media');
    if (m) {
      var k = m.dataset.kind, s = m.dataset.src, el;
      if (k === 'photo') { el = document.createElement('img'); el.src = s; el.alt = m.dataset.title; }
      else if (k === 'embed') { el = document.createElement('iframe'); el.src = s + '?autoplay=1'; el.allow = 'autoplay; fullscreen; picture-in-picture'; el.allowFullscreen = true; }
      else { el = document.createElement('video'); el.src = s; el.controls = true; el.autoplay = true; }
      body.innerHTML = ''; body.appendChild(el);
      title.textContent = m.dataset.title; lb.hidden = false; document.body.style.overflow = 'hidden';
    } else if (e.target === lb || e.target.closest('.lb-close')) { close(); }
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();
</script>
</body>
</html>
