@php $imageUrl = $a->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($a->image_path) : null; @endphp
<article class="annonce {{ $a->urgente ? 'urgente' : '' }}">
  @if ($a->urgente)<span class="annonce-flag"><i class="fas fa-triangle-exclamation"></i> Urgent</span>@endif
  @if ($imageUrl)<img class="annonce-img" src="{{ $imageUrl }}" alt="{{ $a->titre }}" loading="lazy"/>@endif
  <div class="annonce-body">
    <div class="annonce-date">{{ $a->date_publication->translatedFormat('j F Y') }}</div>
    <h3>{{ $a->titre }}</h3>
    <p>{{ $a->contenu }}</p>
  </div>
</article>
