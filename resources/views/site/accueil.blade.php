@extends('layouts.site')

@section('title', "Accueil")

@section('content')
@if ($annonces->isNotEmpty())
<section class="section" style="padding-bottom:0">
  <div class="wrap">
    <div class="cf-wrap">
      <button type="button" class="cf-arrow cf-prev" aria-label="Annonce précédente">&lsaquo;</button>
      <div class="cf-stage" id="cfStage">
        @foreach ($annonces as $a)
          <div class="cf-item">@include('site._annonce-cover', ['a' => $a])</div>
        @endforeach
      </div>
      <button type="button" class="cf-arrow cf-next" aria-label="Annonce suivante">&rsaquo;</button>
    </div>
    <div class="cf-info" id="cfInfo">
      <div class="cf-info-date"></div>
      <h3 class="cf-info-title"></h3>
    </div>
    @if (\App\Support\SiteInfo::page('annonces'))
      <p style="margin-top:14px"><a href="{{ route('site.annonces') }}">Toutes les annonces <i class="fas fa-arrow-right"></i></a></p>
    @endif
  </div>
</section>
<script>
(function () {
  var stage = document.getElementById('cfStage');
  if (!stage) return;
  var items = Array.prototype.slice.call(stage.querySelectorAll('.cf-item'));
  var total = items.length;
  var actif = 0;
  var infoDate = document.querySelector('#cfInfo .cf-info-date');
  var infoTitre = document.querySelector('#cfInfo .cf-info-title');

  function placer() {
    items.forEach(function (item, i) {
      var ecart = i - actif;
      if (ecart > total / 2) ecart -= total;
      if (ecart < -total / 2) ecart += total;
      var abs = Math.abs(ecart);
      var visible = abs <= 2;
      var tx = ecart * 105;
      var rot = ecart === 0 ? 0 : (ecart > 0 ? -35 : 35);
      var echelle = abs === 0 ? 1 : (abs === 1 ? 0.8 : 0.62);
      var opacite = abs === 0 ? 1 : (abs === 1 ? 0.75 : (abs === 2 ? 0.35 : 0));
      item.style.transform = 'translateX(' + tx + 'px) rotateY(' + rot + 'deg) scale(' + echelle + ')';
      item.style.opacity = opacite;
      item.style.zIndex = 100 - abs;
      item.style.pointerEvents = visible ? 'auto' : 'none';
      item.classList.toggle('cf-actif', ecart === 0);
    });
    var carte = items[actif].querySelector('.cf-card');
    if (infoDate) infoDate.textContent = carte.dataset.date;
    if (infoTitre) infoTitre.innerHTML = (carte.dataset.urgente === '1' ? '<span class="cf-info-flag">Urgent</span> ' : '') + carte.dataset.titre;
  }

  items.forEach(function (item, i) {
    item.addEventListener('click', function () { actif = i; placer(); });
  });

  var prev = document.querySelector('.cf-prev'), next = document.querySelector('.cf-next');
  if (prev) prev.addEventListener('click', function () { actif = (actif - 1 + total) % total; placer(); });
  if (next) next.addEventListener('click', function () { actif = (actif + 1) % total; placer(); });

  // Glissement au doigt sur mobile.
  var depart = null;
  stage.addEventListener('touchstart', function (e) { depart = e.touches[0].clientX; }, { passive: true });
  stage.addEventListener('touchend', function (e) {
    if (depart === null) return;
    var delta = e.changedTouches[0].clientX - depart;
    if (delta > 40) { actif = (actif - 1 + total) % total; placer(); }
    else if (delta < -40) { actif = (actif + 1) % total; placer(); }
    depart = null;
  });

  placer();
})();
</script>
@endif
<section class="hero">
  <div class="wrap">
    <h1>Le numérique à Andé, accessible à tous.</h1>
    <p>Formations, ateliers, événements et accompagnement personnalisé : retrouvez toutes les activités du Point de Présence Numérique.</p>
    <div class="hero-actions">
      <a class="btn btn-green" href="{{ route('site.activites') }}"><i class="fas fa-calendar-days"></i> Voir les activités</a>
      @if (\App\Support\SiteInfo::page('contact'))<a class="btn btn-ghost" href="{{ route('site.contact') }}"><i class="fas fa-envelope"></i> Nous contacter</a>@endif
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head">
      <div><h2>Prochaines activités</h2><p>Ce qui se passe bientôt au PPN.</p></div>
      <a class="btn btn-outline" href="{{ route('site.activites') }}">Tout voir <i class="fas fa-arrow-right"></i></a>
    </div>
    @if ($prochaines->isEmpty())
      <p class="empty">Aucune activité programmée pour le moment. Revenez bientôt !</p>
    @else
      <div class="grid">@foreach ($prochaines as $a) @include('site._carte', ['a' => $a]) @endforeach</div>
    @endif
  </div>
</section>

<section class="section alt">
  <div class="wrap">
    <div class="section-head"><div><h2>Ce que nous proposons</h2><p>Quatre façons de progresser avec le numérique.</p></div></div>
    <div class="features">
      @foreach ([
        ['formation', 'fa-graduation-cap', 'Formations', 'Des parcours pour acquérir des compétences numériques, du niveau débutant au niveau avancé.'],
        ['atelier', 'fa-screwdriver-wrench', 'Ateliers', 'Des séances pratiques et conviviales pour apprendre en faisant.'],
        ['evenement', 'fa-star', 'Événements', 'Rencontres, portes ouvertes et temps forts autour du numérique.'],
        ['accompagnement', 'fa-hand-holding-heart', 'Accompagnement', 'Une aide individuelle pour vos démarches et vos usages du quotidien.'],
      ] as [$cat, $icone, $titre, $texte])
        <a class="feature" href="{{ route('site.activites', ['categorie' => $cat]) }}">
          <i class="fas {{ $icone }}"></i><h3>{{ $titre }}</h3><p>{{ $texte }}</p>
        </a>
      @endforeach
    </div>
  </div>
</section>

@if ($medias->isNotEmpty())
<section class="section alt">
  <div class="wrap">
    <div class="section-head">
      <div><h2>En images</h2><p>Photos et vidéos du PPN.</p></div>
      <a class="btn btn-outline" href="{{ route('site.galerie') }}">Toute la galerie <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="media-grid">@foreach ($medias as $m) @include('site._media', ['m' => $m]) @endforeach</div>
  </div>
</section>
@endif

@if ($recentes->isNotEmpty())
<section class="section">
  <div class="wrap">
    <div class="section-head"><div><h2>Dernières activités</h2><p>Un aperçu de la vie du PPN.</p></div></div>
    <div class="grid">@foreach ($recentes as $a) @include('site._carte', ['a' => $a]) @endforeach</div>
  </div>
</section>
@endif
@endsection
