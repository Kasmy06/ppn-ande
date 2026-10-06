<a class="card" href="{{ route('site.activite', $a) }}">
  <div class="card-img">
    @if ($a->image_path)
      <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($a->image_path) }}" alt="{{ $a->titre }}" loading="lazy"/>
    @else
      <i class="fas fa-laptop-code"></i>
    @endif
    <div class="card-date"><small>{{ $a->date_debut->translatedFormat('M') }}</small>{{ $a->date_debut->format('d') }}</div>
  </div>
  <div class="card-body">
    <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
      <span class="tag {{ $a->categorie }}">{{ $a->categorie_label }}</span>
      @if ($a->est_passee)<span class="badge-passee">Terminée</span>@endif
    </div>
    <h3>{{ $a->titre }}</h3>
    <p>{{ \Illuminate\Support\Str::limit($a->description, 110) }}</p>
    <div class="meta">
      @if ($a->horaires)<span><i class="fas fa-clock"></i>{{ $a->horaires }}</span>@endif
      @if ($a->lieu)<span><i class="fas fa-location-dot"></i>{{ $a->lieu }}</span>@endif
    </div>
  </div>
</a>
