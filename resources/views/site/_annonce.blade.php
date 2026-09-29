@php $imageUrl = $a->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($a->image_path) : null; @endphp
<article class="annonce annonce-trigger {{ $a->urgente ? 'urgente' : '' }}"
  data-image="{{ $imageUrl }}" data-date="{{ $a->date_publication->translatedFormat('j F Y') }}"
  data-titre="{{ $a->titre }}" data-contenu="{{ $a->contenu }}" data-urgente="{{ $a->urgente ? '1' : '0' }}">
  @if ($a->urgente)<span class="annonce-flag"><i class="fas fa-triangle-exclamation"></i> Urgent</span>@endif
  @if ($imageUrl)
    <div class="annonce-img-wrap"><img class="annonce-img" src="{{ $imageUrl }}" alt="{{ $a->titre }}" loading="lazy"/></div>
  @endif
  <div class="annonce-body">
    <div class="annonce-date">{{ $a->date_publication->translatedFormat('j F Y') }}</div>
    <h3>{{ $a->titre }}</h3>
    <p>{{ \Illuminate\Support\Str::limit($a->contenu, 160) }}</p>
  </div>
</article>
