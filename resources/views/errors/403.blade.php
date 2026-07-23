@extends('layouts.app')

@section('title', 'Accès refusé')
@section('page-title', 'Accès refusé')
@section('breadcrumb', 'Erreur 403')

@section('content')
  <div class="table-card" style="padding: 3rem; text-align: center;">
    <div class="confirm-icon" style="margin: 0 auto 1.5rem;">
      <i class="fas fa-lock"></i>
    </div>
    <div class="confirm-title" style="font-size: 1.3rem;">Accès refusé</div>
    <p class="confirm-text" style="max-width: 420px; margin-left: auto; margin-right: auto;">
      {{ $exception->getMessage() ?: "Vous n'avez pas les droits nécessaires pour accéder à cette page ou effectuer cette action." }}
    </p>
    <a href="{{ route('dashboard') }}" class="btn btn-primary" style="display:inline-flex;">
      <i class="fas fa-arrow-left"></i> Retour au tableau de bord
    </a>
  </div>
@endsection
