@extends('layouts.app')

@section('title', 'Paramètres')
@section('page-title', 'Paramètres')
@section('breadcrumb', 'Accueil / Paramètres / Application')

@section('content')

  @include('parametres._tabs', ['active' => 'application'])

  <div class="table-card" style="max-width: 720px; padding: 2rem;">
    <form method="POST" action="{{ route('parametres.application.update') }}" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <div class="form-group">
        <label>Logo de la structure</label>
        <div style="display:flex;align-items:center;gap:1.25rem;margin-bottom:0.5rem;">
          <div style="width:64px;height:64px;border-radius:14px;background:var(--bg);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
            @if ($logoUrl)
              <img src="{{ $logoUrl }}" alt="Logo actuel" style="width:100%;height:100%;object-fit:contain;"/>
            @else
              <i class="fas fa-network-wired" style="font-size:1.5rem;color:var(--muted);"></i>
            @endif
          </div>
          <div style="flex:1;">
            <input class="form-control" type="file" name="logo" accept=".jpg,.jpeg,.png,.svg,.webp"/>
            <p style="font-size:0.75rem;color:var(--muted);margin-top:0.4rem;">JPG, PNG, SVG ou WEBP, 2 Mo maximum. Remplace le logo affiché dans le menu et sur la page de connexion.</p>
            @error('logo') <div class="field-error">{{ $message }}</div> @enderror
          </div>
        </div>
        @if ($logoUrl)
          <button type="submit" form="formSupprimerLogo" class="btn btn-outline btn-sm"><i class="fas fa-trash"></i> Supprimer le logo actuel</button>
        @endif
      </div>

      <div class="form-group">
        <label>Nom de la structure</label>
        <input class="form-control" type="text" name="nom_structure" value="{{ old('nom_structure', $nomStructure) }}" required/>
        @error('nom_structure') <div class="field-error">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <label>Capacité d'accueil journalière</label>
        <input class="form-control" type="number" name="capacite_journaliere" min="1" max="1000" value="{{ old('capacite_journaliere', $capaciteJournaliere) }}" required/>
        <p style="font-size:0.78rem;color:var(--muted);margin-top:0.4rem;">Nombre maximum de participants pouvant être reçus le même jour. Utilisée par le module Calendrier pour détecter les conflits de réservation.</p>
        @error('capacite_journaliere') <div class="field-error">{{ $message }}</div> @enderror
      </div>

      <hr style="border:0;border-top:1px solid var(--border);margin:1.5rem 0;">
      <div style="font-weight:700;margin-bottom:0.25rem;">Site public : contact et présentation</div>
      <p style="font-size:0.78rem;color:var(--muted);margin-bottom:1rem;">Ces informations s'affichent sur le site public (pages Contact, À propos et pied de page). Un champ vide n'est pas affiché.</p>

      <div class="form-group">
        <label>Adresse</label>
        <input class="form-control" type="text" name="adresse" value="{{ old('adresse', $contact['adresse']) }}" placeholder="Ex : Village d'Andé, près de la mairie"/>
        @error('adresse') <div class="field-error">{{ $message }}</div> @enderror
      </div>
      <div class="form-group">
        <label>Téléphones (un numéro par ligne)</label>
        <textarea class="form-control" name="telephones" rows="3">{{ old('telephones', $contact['telephones']) }}</textarea>
        @error('telephones') <div class="field-error">{{ $message }}</div> @enderror
      </div>
      <div class="form-group">
        <label>E-mail de contact</label>
        <input class="form-control" type="email" name="email" value="{{ old('email', $contact['email']) }}" placeholder="contact@exemple.ci"/>
        @error('email') <div class="field-error">{{ $message }}</div> @enderror
      </div>
      <div class="form-group">
        <label>Horaires d'ouverture</label>
        <textarea class="form-control" name="horaires" rows="3" placeholder="Lundi – vendredi : 8h – 17h&#10;Samedi : 9h – 12h">{{ old('horaires', $contact['horaires']) }}</textarea>
        @error('horaires') <div class="field-error">{{ $message }}</div> @enderror
      </div>
      <div class="form-group">
        <label>Présentation du PPN (page « À propos »)</label>
        <textarea class="form-control" name="a_propos" rows="6">{{ old('a_propos', $contact['a_propos']) }}</textarea>
        @error('a_propos') <div class="field-error">{{ $message }}</div> @enderror
      </div>
      <div class="form-group">
        <label>Carte (adresse d'intégration)</label>
        <input class="form-control" type="url" name="carte_url" value="{{ old('carte_url', $contact['carte_url']) }}" placeholder="https://www.google.com/maps/embed?pb=..."/>
        <p style="font-size:0.75rem;color:var(--muted);margin-top:0.4rem;">Google Maps → Partager → Intégrer une carte → copiez uniquement l'adresse qui suit <code>src="</code>.</p>
        @error('carte_url') <div class="field-error">{{ $message }}</div> @enderror
      </div>

      <button type="submit" class="btn btn-primary" style="margin-top:0.5rem;"><i class="fas fa-save"></i> Enregistrer</button>
    </form>
  </div>

  @if ($logoUrl)
    <form id="formSupprimerLogo" method="POST" action="{{ route('parametres.application.logo.destroy') }}" style="display:none;">
      @csrf
      @method('DELETE')
    </form>
  @endif

@endsection
