@php
  $kind = $m->type === 'photo' ? 'photo' : ($m->embed_url ? 'embed' : 'file');
  $src = $kind === 'embed' ? $m->embed_url : $m->fichier_url;
@endphp
<button type="button" class="media" data-kind="{{ $kind }}" data-src="{{ $src }}" data-title="{{ $m->titre }}">
  @if ($m->apercu_url)
    <img src="{{ $m->apercu_url }}" alt="{{ $m->titre }}" loading="lazy"/>
  @elseif ($kind === 'file')
    <video src="{{ $m->fichier_url }}#t=0.5" preload="metadata" muted></video>
  @endif
  @if ($m->type === 'video')<span class="play"><i class="fas fa-play"></i></span>@endif
  <span class="media-cap">{{ $m->titre }}</span>
</button>
