@extends('layouts.site')

@section('title', $activite->titre)
@section('description', \Illuminate\Support\Str::limit($activite->description, 150))

@section('content')
<div class="wrap detail">
  <article>
    <div class="detail-cover">
      @if ($activite->image_path)
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($activite->image_path) }}" alt="{{ $activite->titre }}"/>
      @else
        <i class="fas fa-laptop-code"></i>
      @endif
    </div>
    <span class="tag {{ $activite->categorie }}">{{ $activite->categorie_label }}</span>
    @if ($activite->est_passee) <span class="badge-passee">Terminée</span> @endif
    <h1>{{ $activite->titre }}</h1>
    <div class="prose">{{ $activite->description }}</div>
    @if ($medias->isNotEmpty())
      <h2 style="margin:32px 0 14px;font-size:1.3rem;color:var(--violet)">Photos &amp; vidéos</h2>
      <div class="media-grid">@foreach ($medias as $m) @include('site._media', ['m' => $m]) @endforeach</div>
    @endif
    <p style="margin-top:28px"><a href="{{ route('site.activites') }}"><i class="fas fa-arrow-left"></i> Retour aux activités</a></p>
  </article>

  <aside class="aside-box">
    <h3>Informations pratiques</h3>
    <ul>
      <li><i class="fas fa-calendar-days"></i><div><span>Date</span>
        @if ($activite->date_fin && ! $activite->date_fin->isSameDay($activite->date_debut))
          Du {{ $activite->date_debut->translatedFormat('j F Y') }}<br>au {{ $activite->date_fin->translatedFormat('j F Y') }}
        @else
          {{ $activite->date_debut->translatedFormat('l j F Y') }}
        @endif
      </div></li>
      @if ($activite->horaires)<li><i class="fas fa-clock"></i><div><span>Horaires</span>{{ $activite->horaires }}</div></li>@endif
      @if ($activite->lieu)<li><i class="fas fa-location-dot"></i><div><span>Lieu</span>{{ $activite->lieu }}</div></li>@endif
    </ul>
    <a class="btn btn-green" href="#contact" style="width:100%;justify-content:center"><i class="fas fa-envelope"></i> Nous contacter</a>
  </aside>
</div>

@if ($similaires->isNotEmpty())
<section class="section alt">
  <div class="wrap">
    <div class="section-head"><h2>Dans la même catégorie</h2></div>
    <div class="grid">@foreach ($similaires as $a) @include('site._carte', ['a' => $a]) @endforeach</div>
  </div>
</section>
@endif
@endsection
