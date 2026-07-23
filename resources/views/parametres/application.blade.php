@extends('layouts.app')

@section('title', 'Paramètres')
@section('page-title', 'Paramètres')
@section('breadcrumb', 'Accueil / Paramètres / Application')

@section('content')

  @include('parametres._tabs', ['active' => 'application'])

  <div class="table-card" style="max-width: 560px; padding: 2rem;">
    <form method="POST" action="{{ route('parametres.application.update') }}">
      @csrf
      @method('PUT')

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

      <button type="submit" class="btn btn-primary" style="margin-top:0.5rem;"><i class="fas fa-save"></i> Enregistrer</button>
    </form>
  </div>

@endsection
