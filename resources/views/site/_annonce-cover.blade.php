@php $imageUrl = $a->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($a->image_path) : null; @endphp
<div class="cf-card" data-image="{{ $imageUrl }}" data-titre="{{ $a->titre }}" data-date="{{ $a->date_publication->translatedFormat('j F Y') }}" data-contenu="{{ $a->contenu }}" data-urgente="{{ $a->urgente ? '1' : '0' }}">
  @if ($imageUrl)
    <img src="{{ $imageUrl }}" alt="{{ $a->titre }}" loading="lazy"/>
  @else
    <div class="cf-noimg"><i class="fas fa-bullhorn"></i></div>
  @endif
  @if ($a->urgente)<span class="cf-flag"><i class="fas fa-triangle-exclamation"></i> Urgent</span>@endif
</div>
