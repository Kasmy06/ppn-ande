<!DOCTYPE html>
<html lang="fr">
@php
  use App\Support\SiteInfo;
  $logoPath = \App\Models\ParametreApplication::get('logo_path');
  $nomStructure = \App\Models\ParametreApplication::get('nom_structure', "PPN d'Andé");
  $logoUrl = $logoPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath) : null;
  $titrePage = trim($__env->yieldContent('title', "Accueil")).' – '.$nomStructure;
  $descriptionPage = trim($__env->yieldContent('description')) ?: "Découvrez les formations, ateliers et événements du Point de Présence Numérique d'Andé.";
  $imagePartage = trim($__env->yieldContent('og_image')) ?: $logoUrl;
@endphp
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>{{ $titrePage }}</title>
  <meta name="description" content="{{ $descriptionPage }}"/>
  <meta property="og:type" content="website"/>
  <meta property="og:site_name" content="{{ $nomStructure }}"/>
  <meta property="og:title" content="{{ $titrePage }}"/>
  <meta property="og:description" content="{{ $descriptionPage }}"/>
  <meta property="og:url" content="{{ url()->current() }}"/>
  @if ($imagePartage)<meta property="og:image" content="{{ $imagePartage }}"/>@endif
  <meta name="twitter:card" content="summary_large_image"/>
  <link rel="icon" href="{{ $logoUrl ?? asset('favicon.ico') }}"/>
  @if (SiteInfo::page('annonces'))
    <link rel="alternate" type="application/rss+xml" title="Annonces — {{ $nomStructure }}" href="{{ route('site.annonces.rss') }}"/>
  @endif
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="{{ asset('css/site.css') }}"/>
</head>
<body>

<header class="site-header">
  <div class="wrap header-in">
    <a href="{{ route('site.accueil') }}" class="brand">
      <span class="brand-logo">
        @if ($logoUrl)
          <img src="{{ $logoUrl }}" alt="Logo {{ $nomStructure }}"/>
        @else
          <i class="fas fa-network-wired"></i>
        @endif
      </span>
      <span class="brand-text">{{ $nomStructure }}<small>Point de Présence Numérique</small></span>
    </a>
    <button class="menu-toggle" type="button" aria-label="Menu" onclick="document.querySelector('.site-nav').classList.toggle('open')"><i class="fas fa-bars"></i></button>
    <nav class="site-nav">
      <a href="{{ route('site.accueil') }}" class="{{ request()->routeIs('site.accueil') ? 'active' : '' }}">Accueil</a>
      @if (SiteInfo::page('annonces'))<a href="{{ route('site.annonces') }}" class="{{ request()->routeIs('site.annonces') ? 'active' : '' }}">Annonces</a>@endif
      @if (SiteInfo::page('a_propos'))<a href="{{ route('site.a-propos') }}" class="{{ request()->routeIs('site.a-propos') ? 'active' : '' }}">À propos</a>@endif
      <a href="{{ route('site.activites') }}" class="{{ request()->routeIs('site.activite*') ? 'active' : '' }}">Activités</a>
      @if (SiteInfo::page('agenda'))<a href="{{ route('site.agenda') }}" class="{{ request()->routeIs('site.agenda') ? 'active' : '' }}">Agenda</a>@endif
      @if (SiteInfo::page('galerie'))<a href="{{ route('site.galerie') }}" class="{{ request()->routeIs('site.galerie') ? 'active' : '' }}">Galerie</a>@endif
      @if (SiteInfo::page('contact'))<a href="{{ route('site.contact') }}" class="{{ request()->routeIs('site.contact') ? 'active' : '' }}">Contact</a>@endif
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
    @if (SiteInfo::adresse() || SiteInfo::email() || SiteInfo::telephones() || SiteInfo::page('contact'))
    <div>
      <div class="footer-title">Contact</div>
      <p>
        @if ($adresse = SiteInfo::adresse())<i class="fas fa-location-dot"></i> {{ $adresse }}<br>@endif
        @foreach (SiteInfo::telephones() as $tel)<i class="fas fa-phone"></i> <a href="{{ SiteInfo::lienTel($tel) }}">{{ $tel }}</a><br>@endforeach
        @if ($mail = SiteInfo::email())<i class="fas fa-envelope"></i> <a href="mailto:{{ $mail }}">{{ $mail }}</a><br>@endif
        @if (SiteInfo::page('contact'))<a href="{{ route('site.contact') }}">Nous écrire <i class="fas fa-arrow-right"></i></a>@endif
      </p>
    </div>
    @endif
    <div>
      <div class="footer-title">Navigation</div>
      <p>@if (SiteInfo::page('annonces'))<a href="{{ route('site.annonces') }}">Annonces</a> · @endif<a href="{{ route('site.activites') }}">Activités</a>@if (SiteInfo::page('agenda')) · <a href="{{ route('site.agenda') }}">Agenda</a>@endif<br>@if (SiteInfo::page('galerie'))<a href="{{ route('site.galerie') }}">Galerie</a>@endif @if (SiteInfo::page('a_propos')) · <a href="{{ route('site.a-propos') }}">À propos</a>@endif<br><a href="{{ route('login') }}">Espace équipe</a></p>
    </div>
  </div>
  <div class="copy">© {{ date('Y') }} {{ $nomStructure }}@if (SiteInfo::page('mentions')) · <a href="{{ route('site.mentions') }}">Mentions légales</a>@endif @if (SiteInfo::page('confidentialite')) · <a href="{{ route('site.confidentialite') }}">Confidentialité</a>@endif</div>
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

<div class="am-overlay" id="annonceModal" hidden>
  <div class="am-box">
    <button type="button" class="am-close" aria-label="Fermer">&times;</button>
    <div class="am-img-wrap" id="amImgWrap"><img id="amImg" src="" alt=""/></div>
    <div class="am-body">
      <div class="am-date" id="amDate"></div>
      <h3 id="amTitre"></h3>
      <p id="amContenu"></p>
      <button type="button" class="btn btn-outline no-print" id="amPrint" style="margin-top:14px"><i class="fas fa-print"></i> Imprimer cette annonce</button>
    </div>
  </div>
</div>
<script>
(function () {
  var modal = document.getElementById('annonceModal');
  if (!modal) return;
  var img = document.getElementById('amImg'), imgWrap = document.getElementById('amImgWrap');
  var dateEl = document.getElementById('amDate'), titreEl = document.getElementById('amTitre'), contenuEl = document.getElementById('amContenu');

  window.ouvrirAnnonceModal = function (data) {
    if (data.image) { img.src = data.image; img.alt = data.titre; imgWrap.hidden = false; }
    else { img.src = ''; imgWrap.hidden = true; }
    dateEl.textContent = data.date;
    titreEl.textContent = '';
    if (data.urgente) {
      var flag = document.createElement('span');
      flag.className = 'am-flag';
      flag.textContent = 'Urgent';
      titreEl.appendChild(flag);
      titreEl.appendChild(document.createTextNode(' '));
    }
    titreEl.appendChild(document.createTextNode(data.titre));
    contenuEl.textContent = data.contenu;
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
  };

  function fermer() { modal.hidden = true; document.body.style.overflow = ''; }

  var boutonImprimer = document.getElementById('amPrint');
  if (boutonImprimer) {
    boutonImprimer.addEventListener('click', function () {
      document.body.classList.add('imprimer-annonce');
      window.print();
    });
  }
  window.addEventListener('afterprint', function () { document.body.classList.remove('imprimer-annonce'); });

  // Ouverture générique pour toute carte marquée .annonce-trigger (grille de la page Annonces).
  document.addEventListener('click', function (e) {
    var el = e.target.closest('.annonce-trigger');
    if (el) {
      window.ouvrirAnnonceModal({
        image: el.dataset.image || '',
        date: el.dataset.date,
        titre: el.dataset.titre,
        contenu: el.dataset.contenu,
        urgente: el.dataset.urgente === '1',
      });
    } else if (e.target === modal || e.target.closest('.am-close')) {
      fermer();
    }
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && ! modal.hidden) fermer(); });
})();
</script>
</body>
</html>
