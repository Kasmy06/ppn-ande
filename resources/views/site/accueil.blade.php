@extends('layouts.site')

@section('title', "Accueil")

@section('content')
<section class="hero">
  <div class="wrap">
    <h1>Le numérique à Andé, accessible à tous.</h1>
    <p>Formations, ateliers, événements et accompagnement personnalisé : retrouvez toutes les activités du Point de Présence Numérique.</p>
    <div class="hero-actions">
      <a class="btn btn-green" href="{{ route('site.activites') }}"><i class="fas fa-calendar-days"></i> Voir les activités</a>
      <a class="btn btn-ghost" href="{{ route('site.contact') }}"><i class="fas fa-envelope"></i> Nous contacter</a>
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
