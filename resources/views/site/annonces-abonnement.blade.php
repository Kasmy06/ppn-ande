@extends('layouts.site')

@section('title', "S'abonner aux annonces")
@section('description', "Recevez automatiquement les nouvelles annonces du Point de Présence Numérique d'Andé dans votre lecteur de flux.")

@section('content')
<section class="page-hero">
  <div class="wrap"><h1>S'abonner aux annonces</h1><p>Soyez averti dès qu'une nouvelle annonce est publiée.</p></div>
</section>

<section class="section">
  <div class="wrap legal">
    <h2>1. Copiez l'adresse du flux</h2>
    <p>Voici l'adresse à donner à votre lecteur de flux :</p>
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:14px 0 6px">
      <input id="fluxUrl" type="text" readonly value="{{ $fluxUrl }}" style="flex:1;min-width:260px;padding:11px 14px;border:1.5px solid var(--border);border-radius:12px;font:inherit;background:#fff"/>
      <button type="button" class="btn btn-green" id="copierFlux"><i class="fas fa-copy"></i> Copier</button>
    </div>
    <p id="copieOk" style="color:#166534;font-size:.9rem;min-height:1.2em"></p>

    <h2>2. Choisissez un lecteur de flux</h2>
    <p>Un lecteur de flux (ou une extension de navigateur) affiche les annonces et vous prévient des nouveautés. Par exemple :</p>
    <ul style="margin:8px 0 0 20px;line-height:1.8">
      <li><strong>Feedly</strong> ou <strong>Inoreader</strong> : services en ligne, gratuits pour un usage simple.</li>
      <li><strong>Thunderbird</strong> : messagerie gratuite qui gère aussi les flux RSS.</li>
      <li>Une extension RSS pour votre navigateur.</li>
    </ul>
    <p style="margin-top:14px">Dans le lecteur, choisissez « Ajouter un flux » et collez l'adresse ci-dessus.</p>

    <h2>3. Ou consultez les annonces directement</h2>
    <p><a href="{{ route('site.annonces') }}">Voir toutes les annonces sur le site <i class="fas fa-arrow-right"></i></a></p>
  </div>
</section>

<script>
(function () {
  var bouton = document.getElementById('copierFlux'), champ = document.getElementById('fluxUrl'), message = document.getElementById('copieOk');
  if (!bouton) return;
  bouton.addEventListener('click', function () {
    champ.select();
    var ok = function () { message.textContent = 'Adresse copiée.'; };
    if (navigator.clipboard) {
      navigator.clipboard.writeText(champ.value).then(ok, function () { document.execCommand('copy'); ok(); });
    } else {
      document.execCommand('copy'); ok();
    }
  });
})();
</script>
@endsection
