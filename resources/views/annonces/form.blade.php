@extends('layouts.app')

@php $edition = $annonce->exists; @endphp

@section('title', $edition ? 'Modifier une annonce' : 'Nouvelle annonce')
@section('page-title', $edition ? 'Modifier l\'annonce' : 'Nouvelle annonce')
@section('breadcrumb', 'Accueil / Annonces / '.($edition ? 'Modifier' : 'Ajouter'))

@section('content')
  <div class="table-card" style="padding:1.5rem;max-width:820px;">
    <form method="POST" enctype="multipart/form-data"
          action="{{ $edition ? route('annonces.update', $annonce) : route('annonces.store') }}">
      @csrf
      @if ($edition) @method('PUT') @endif

      <div class="form-group">
        <label>Titre *</label>
        <input class="form-control" type="text" name="titre" value="{{ old('titre', $annonce->titre) }}" placeholder="Ex : Fermeture exceptionnelle le 12 octobre" required/>
        @error('titre') <div class="field-error">{{ $message }}</div> @enderror
      </div>
      <div class="form-group">
        <label>Contenu *</label>
        <textarea class="form-control" name="contenu" rows="6" required>{{ old('contenu', $annonce->contenu) }}</textarea>
        @error('contenu') <div class="field-error">{{ $message }}</div> @enderror
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Date de publication *</label>
          <input class="form-control" type="date" name="date_publication" value="{{ old('date_publication', $annonce->date_publication?->format('Y-m-d')) }}" required/>
          @error('date_publication') <div class="field-error">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
          <label>Date d'expiration (optionnelle)</label>
          <input class="form-control" type="date" name="date_expiration" value="{{ old('date_expiration', $annonce->date_expiration?->format('Y-m-d')) }}"/>
          <p style="font-size:0.75rem;color:var(--muted);margin-top:0.4rem;">Passé cette date, l'annonce disparaît du site sans être supprimée.</p>
          @error('date_expiration') <div class="field-error">{{ $message }}</div> @enderror
        </div>
      </div>
      <div class="form-group">
        <label>Image (optionnelle, JPG/PNG, 10 Mo max)</label>
        @if ($annonce->image_path)
          <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($annonce->image_path) }}" alt="" style="height:80px;border-radius:8px;margin-bottom:8px;"/>
        @endif
        <input class="form-control" type="file" name="image" accept="image/*"/>
        @error('image') <div class="field-error">{{ $message }}</div> @enderror
      </div>
      <div class="form-group">
        <label style="display:flex;gap:8px;align-items:center;">
          <input type="checkbox" name="urgente" value="1" {{ old('urgente', $annonce->urgente) ? 'checked' : '' }}/> Annonce urgente <span style="color:var(--muted);font-weight:400;font-size:.8rem;">(mise en avant en haut de la page et de l'accueil)</span>
        </label>
      </div>
      <div class="form-group">
        @if (auth()->user()->isSuperAdmin())
          <label style="display:flex;gap:8px;align-items:center;">
            <input type="checkbox" name="publie" value="1" {{ old('publie', $annonce->publie) ? 'checked' : '' }}/> Publier sur le site public <span style="color:var(--muted);font-weight:400;font-size:.8rem;">(décoché = brouillon, visible seulement par l'équipe)</span>
          </label>
        @else
          <p style="font-size:.85rem;color:var(--muted);"><i class="fas fa-circle-info"></i> Cette annonce sera enregistrée en brouillon : le Super Admin décidera de la publier sur le site public.</p>
        @endif
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;">
        <a class="btn btn-outline" href="{{ route('annonces.index') }}">Annuler</a>
        <button class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
      </div>
    </form>
  </div>
@endsection
