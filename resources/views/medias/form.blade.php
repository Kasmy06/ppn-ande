@extends('layouts.app')

@php
  $edition = $media->exists;
  $typeActuel = old('type', $media->type);
@endphp

@section('title', $edition ? 'Modifier un média' : 'Nouveau média')
@section('page-title', $edition ? 'Modifier le média' : 'Nouveau média')
@section('breadcrumb', 'Accueil / Photos & vidéos / '.($edition ? 'Modifier' : 'Ajouter'))

@section('content')
  <div class="table-card" style="padding:1.5rem;max-width:820px;">
    <form method="POST" enctype="multipart/form-data"
          action="{{ $edition ? route('medias.update', $media) : route('medias.store') }}">
      @csrf
      @if ($edition) @method('PUT') @endif

      <div class="form-row">
        <div class="form-group">
          <label>Titre *</label>
          <input class="form-control" type="text" name="titre" value="{{ old('titre', $media->titre) }}" required/>
          @error('titre') <div class="field-error">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
          <label>Type *</label>
          <select class="form-control" name="type" id="typeSel" required>
            <option value="photo" {{ $typeActuel === 'photo' ? 'selected' : '' }}>Photo</option>
            <option value="video" {{ $typeActuel === 'video' ? 'selected' : '' }}>Vidéo</option>
          </select>
        </div>
      </div>

      <div class="form-group" id="blocPhoto">
        <label>{{ $edition ? 'Photo (JPG/PNG, 10 Mo max)' : 'Photos (JPG/PNG, 10 Mo max chacune, 20 maximum)' }} {{ $edition && $media->type === 'photo' ? '— laisser vide pour conserver' : '*' }}</label>
        @if ($media->type === 'photo' && $media->fichier_url)
          <img src="{{ $media->fichier_url }}" alt="" style="height:80px;border-radius:8px;margin-bottom:8px;display:block"/>
        @endif
        <input class="form-control" type="file" name="photos[]" accept="image/*" {{ $edition ? '' : 'multiple' }}/>
        @if (! $edition)<div style="color:var(--muted);font-size:.8rem;margin-top:4px;">Plusieurs photos : le titre sera numéroté (« Titre 1 », « Titre 2 »…).</div>@endif
        @error('photos') <div class="field-error">{{ $message }}</div> @enderror
        @error('photos.*') <div class="field-error">{{ $message }}</div> @enderror
      </div>

      <div id="blocVideo">
        <div class="form-group">
          <label>Lien YouTube ou Vimeo</label>
          <input class="form-control" type="url" name="video_url" value="{{ old('video_url', $media->video_url) }}" placeholder="https://www.youtube.com/watch?v=..."/>
          @error('video_url') <div class="field-error">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
          <label>…ou fichier vidéo (MP4/WebM, 50 Mo max)</label>
          <input class="form-control" type="file" name="video" accept="video/mp4,video/webm,video/quicktime"/>
          @error('video') <div class="field-error">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="form-group">
        <label>Légende</label>
        <textarea class="form-control" name="legende" rows="3">{{ old('legende', $media->legende) }}</textarea>
      </div>
      <div class="form-group">
        <label>Activité liée (optionnel)</label>
        <select class="form-control" name="activite_id">
          <option value="">— Aucune —</option>
          @foreach ($activites as $a)
            <option value="{{ $a->id }}" {{ (string) old('activite_id', $media->activite_id) === (string) $a->id ? 'selected' : '' }}>{{ $a->titre }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        @if (auth()->user()->isSuperAdmin())
          <label style="display:flex;gap:8px;align-items:center;">
            <input type="checkbox" name="publie" value="1" {{ old('publie', $media->publie) ? 'checked' : '' }}/> Publier dans la galerie publique <span style="color:var(--muted);font-weight:400;font-size:.8rem;">(décoché = brouillon)</span>
          </label>
        @else
          <p style="font-size:.85rem;color:var(--muted);"><i class="fas fa-circle-info"></i> Ce média sera enregistré en brouillon : le Super Admin décidera de le publier sur le site public.</p>
        @endif
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;">
        <a class="btn btn-outline" href="{{ route('medias.index') }}">Annuler</a>
        <button class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
<script>
  (function () {
    var sel = document.getElementById('typeSel');
    function sync() {
      document.getElementById('blocPhoto').style.display = sel.value === 'photo' ? '' : 'none';
      document.getElementById('blocVideo').style.display = sel.value === 'video' ? '' : 'none';
    }
    sel.addEventListener('change', sync); sync();
  })();
</script>
@endpush
