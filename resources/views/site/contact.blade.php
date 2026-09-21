@extends('layouts.site')

@section('title', 'Contact')
@section('description', "Contactez le Point de Présence Numérique d'Andé : téléphone, adresse, horaires et formulaire de contact.")

@php
  use App\Support\SiteInfo;
  $adresse = SiteInfo::adresse();
  $mail = SiteInfo::email();
  $horaires = SiteInfo::horaires();
  $carte = SiteInfo::carteUrl();
@endphp

@section('content')
<section class="page-hero">
  <div class="wrap"><h1>Nous contacter</h1><p>Une question, une inscription ? Écrivez-nous ou appelez-nous.</p></div>
</section>

<section class="section">
  <div class="wrap contact-grid">
    <div>
      <div class="aside-box" style="position:static">
        <h3>Coordonnées</h3>
        <ul>
          @if ($adresse)<li><i class="fas fa-location-dot"></i><div><span>Adresse</span>{{ $adresse }}</div></li>@endif
          @foreach (SiteInfo::telephones() as $tel)
            <li><i class="fas fa-phone"></i><div><span>Téléphone</span><a href="{{ SiteInfo::lienTel($tel) }}">{{ $tel }}</a></div></li>
          @endforeach
          @if ($mail)<li><i class="fas fa-envelope"></i><div><span>E-mail</span><a href="mailto:{{ $mail }}">{{ $mail }}</a></div></li>@endif
          @if ($horaires)<li><i class="fas fa-clock"></i><div><span>Horaires</span><span style="white-space:pre-line;color:var(--text);font-size:.92rem">{{ $horaires }}</span></div></li>@endif
        </ul>
      </div>
      @if ($carte)
        <iframe class="carte" src="{{ $carte }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Carte du PPN" allowfullscreen></iframe>
      @endif
    </div>

    <div>
      @if (session('envoye'))
        <div class="alert-ok"><i class="fas fa-circle-check"></i> Merci ! Votre message a bien été envoyé, nous vous répondrons rapidement.</div>
      @endif
      <form class="contact-form" method="POST" action="{{ route('site.contact.envoyer') }}">
        @csrf
        <div style="position:absolute;left:-9999px" aria-hidden="true">
          <label>Ne pas remplir <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
        </div>
        <div class="row2">
          <div class="field">
            <label for="nom">Nom *</label>
            <input id="nom" type="text" name="nom" value="{{ old('nom') }}" required maxlength="120"/>
            @error('nom')<div class="err">{{ $message }}</div>@enderror
          </div>
          <div class="field">
            <label for="email">E-mail *</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="150"/>
            @error('email')<div class="err">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="row2">
          <div class="field">
            <label for="telephone">Téléphone</label>
            <input id="telephone" type="tel" name="telephone" value="{{ old('telephone') }}" maxlength="30"/>
            @error('telephone')<div class="err">{{ $message }}</div>@enderror
          </div>
          <div class="field">
            <label for="sujet">Sujet *</label>
            <input id="sujet" type="text" name="sujet" value="{{ old('sujet') }}" required maxlength="150"/>
            @error('sujet')<div class="err">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="field">
          <label for="message">Message *</label>
          <textarea id="message" name="message" rows="6" required minlength="10" maxlength="3000">{{ old('message') }}</textarea>
          @error('message')<div class="err">{{ $message }}</div>@enderror
        </div>
        <button class="btn btn-green" type="submit"><i class="fas fa-paper-plane"></i> Envoyer le message</button>
      </form>
    </div>
  </div>
</section>
@endsection
