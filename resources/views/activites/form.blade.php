@extends('layouts.app')

@php $edition = $activite->exists; @endphp

@section('title', $edition ? 'Modifier une activité' : 'Nouvelle activité')
@section('page-title', $edition ? 'Modifier l\'activité' : 'Nouvelle activité')
@section('breadcrumb', 'Accueil / Activités / '.($edition ? 'Modifier' : 'Ajouter'))

@section('content')
  <div class="table-card" style="padding:1.5rem;max-width:820px;">
    <form method="POST" enctype="multipart/form-data"
          action="{{ $edition ? route('activites.update', $activite) : route('activites.store') }}">
      @csrf
      @if ($edition) @method('PUT') @endif

      <div class="form-group">
        <label>Titre *</label>
        <input class="form-control" type="text" name="titre" value="{{ old('titre', $activite->titre) }}" required/>
        @error('titre') <div class="field-error">{{ $message }}</div> @enderror
      </div>
      <div class="form-group">
        <label>Catégorie *</label>
        <select class="form-control" name="categorie" required>
          @foreach (\App\Models\Activite::CATEGORIES as $cle => $label)
            <option value="{{ $cle }}" {{ old('categorie', $activite->categorie) === $cle ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Description *</label>
        <textarea class="form-control" name="description" rows="6" required>{{ old('description', $activite->description) }}</textarea>
        @error('description') <div class="field-error">{{ $message }}</div> @enderror
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Date de début *</label>
          <input class="form-control" type="date" name="date_debut" value="{{ old('date_debut', $activite->date_debut?->format('Y-m-d')) }}" required/>
          @error('date_debut') <div class="field-error">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
          <label>Date de fin (optionnelle)</label>
          <input class="form-control" type="date" name="date_fin" value="{{ old('date_fin', $activite->date_fin?->format('Y-m-d')) }}"/>
          @error('date_fin') <div class="field-error">{{ $message }}</div> @enderror
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Horaires</label>
          <input class="form-control" type="text" name="horaires" value="{{ old('horaires', $activite->horaires) }}" placeholder="Ex : 14h00 – 16h30"/>
        </div>
        <div class="form-group">
          <label>Lieu</label>
          <input class="form-control" type="text" name="lieu" value="{{ old('lieu', $activite->lieu) }}" placeholder="Ex : Salle informatique du PPN"/>
        </div>
      </div>
      <div class="form-group">
        <label>Image (JPG/PNG, 10 Mo max)</label>
        @if ($activite->image_path)
          <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($activite->image_path) }}" alt="" style="height:80px;border-radius:8px;margin-bottom:8px;"/>
        @endif
        <input class="form-control" type="file" name="image" accept="image/*"/>
        @error('image') <div class="field-error">{{ $message }}</div> @enderror
      </div>
      <div class="form-group">
        <label style="display:flex;gap:8px;align-items:center;">
          <input type="checkbox" name="publie" value="1" {{ old('publie', $activite->publie) ? 'checked' : '' }}/> Publier sur le site
        </label>
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;">
        <a class="btn btn-outline" href="{{ route('activites.index') }}">Annuler</a>
        <button class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
      </div>
    </form>
  </div>
@endsection
