@extends('layouts.app')

@section('title', 'Messages')
@section('page-title', 'Messages du site')
@section('breadcrumb', 'Accueil / Messages')

@section('content')
  <div class="table-card">
    <div class="table-card-header">
      <div class="table-info">
        <div class="title">Formulaire de contact</div>
        <div class="count">{{ $messages->total() }} message(s), dont {{ $nonLus }} non lu(s)</div>
      </div>
    </div>

    @forelse ($messages as $m)
      <div style="padding:1rem 1.25rem;border-top:1px solid var(--border);{{ $m->lu ? 'opacity:.7;' : '' }}">
        <div style="display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;align-items:center;">
          <div>
            @unless ($m->lu)<span class="badge badge-orange">Nouveau</span>@endunless
            <strong>{{ $m->sujet }}</strong>
            <div style="font-size:.8rem;color:var(--muted);">
              {{ $m->nom }} · <a href="mailto:{{ $m->email }}">{{ $m->email }}</a>
              @if ($m->telephone) · <a href="tel:{{ preg_replace('/[^\d+]/', '', $m->telephone) }}">{{ $m->telephone }}</a> @endif
              · {{ $m->created_at->format('d/m/Y H:i') }}
            </div>
          </div>
          <div class="action-btns">
            <form method="POST" action="{{ route('messages.lu', $m) }}" style="display:inline">
              @csrf @method('PATCH')
              <button class="btn btn-sm btn-outline" title="{{ $m->lu ? 'Marquer non lu' : 'Marquer lu' }}"><i class="fas {{ $m->lu ? 'fa-envelope' : 'fa-envelope-open' }}"></i></button>
            </form>
            @if (auth()->user()->isSuperAdmin())
              <form method="POST" action="{{ route('messages.destroy', $m) }}" onsubmit="return confirm('Supprimer ce message ?')" style="display:inline">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
              </form>
            @endif
          </div>
        </div>
        <p style="margin-top:.6rem;white-space:pre-line;">{{ $m->message }}</p>
      </div>
    @empty
      <p style="text-align:center;color:var(--muted);padding:2rem;">Aucun message reçu pour le moment.</p>
    @endforelse

    <div class="pagination-wrap">
      <div class="pagination-info">Page {{ $messages->currentPage() }} sur {{ max($messages->lastPage(), 1) }}</div>
      {{ $messages->links('pagination.custom') }}
    </div>
  </div>
@endsection
