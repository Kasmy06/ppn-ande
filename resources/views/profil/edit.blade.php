@extends('layouts.app')

@section('title', 'Mon profil')
@section('page-title', 'Mon Profil')
@section('breadcrumb', 'Accueil / Profil')

@section('content')
  <div class="table-card" style="max-width: 560px; padding: 2rem;">
    <form method="POST" action="{{ route('profil.update') }}">
      @csrf
      @method('PUT')

      <div class="form-group">
        <label>Nom complet</label>
        <input class="form-control" type="text" name="name" value="{{ old('name', $user->name) }}" required/>
        @error('name') <div class="field-error">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <label>Adresse email</label>
        <input class="form-control" type="email" name="email" value="{{ old('email', $user->email) }}" required/>
        @error('email') <div class="field-error">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <label>Rôle</label>
        <input class="form-control" type="text" value="{{ $user->role === 'super_admin' ? 'Super Admin' : 'Agent d\'accueil' }}" disabled/>
      </div>

      <hr style="border:none;border-top:1px solid var(--border);margin:1.5rem 0;">

      <p style="font-size:0.8rem;color:var(--muted);margin-bottom:1rem;">Laissez les champs ci-dessous vides pour conserver votre mot de passe actuel.</p>

      <div class="form-row">
        <div class="form-group">
          <label>Nouveau mot de passe</label>
          <input class="form-control" type="password" name="password"/>
          @error('password') <div class="field-error">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
          <label>Confirmer</label>
          <input class="form-control" type="password" name="password_confirmation"/>
        </div>
      </div>

      <div class="form-group">
        <label>Mot de passe actuel (requis pour changer de mot de passe)</label>
        <input class="form-control" type="password" name="current_password"/>
        @error('current_password') <div class="field-error">{{ $message }}</div> @enderror
      </div>

      <button type="submit" class="btn btn-primary" style="margin-top:0.5rem;"><i class="fas fa-save"></i> Enregistrer</button>
    </form>
  </div>
@endsection
