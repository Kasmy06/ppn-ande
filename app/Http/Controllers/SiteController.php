<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\Media;
use App\Models\MessageContact;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function accueil(): View
    {
        return view('site.accueil', [
            'prochaines' => Activite::publie()->aVenir()->orderBy('date_debut')->limit(3)->get(),
            'recentes' => Activite::publie()->orderByDesc('date_debut')->limit(3)->get(),
            'medias' => Media::publie()->latest()->limit(6)->get(),
        ]);
    }

    public function activites(Request $request): View
    {
        $categorie = $request->query('categorie');
        $periode = $request->query('periode') === 'passees' ? 'passees' : 'a_venir';
        $q = trim((string) $request->query('q'));

        $activites = Activite::publie()
            ->when(array_key_exists((string) $categorie, Activite::CATEGORIES), fn ($query) => $query->where('categorie', $categorie))
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
                $w->where('titre', 'like', $like)->orWhere('description', 'like', $like)->orWhere('lieu', 'like', $like);
            }))
            ->when(
                $periode === 'a_venir',
                fn ($query) => $query->aVenir()->orderBy('date_debut'),
                fn ($query) => $query->whereRaw('COALESCE(date_fin, date_debut) < ?', [today()->toDateString()])->orderByDesc('date_debut'),
            )
            ->paginate(9)
            ->withQueryString();

        return view('site.activites', compact('activites', 'categorie', 'periode', 'q'));
    }

    public function activite(Activite $activite): View
    {
        abort_unless($activite->publie, 404);

        return view('site.activite', [
            'activite' => $activite,
            'medias' => $activite->medias()->publie()->latest()->get(),
            'similaires' => Activite::publie()->where('categorie', $activite->categorie)
                ->whereKeyNot($activite->id)->orderByDesc('date_debut')->limit(3)->get(),
        ]);
    }

    public function agenda(Request $request): View
    {
        try {
            $mois = Carbon::createFromFormat('!Y-m', (string) $request->query('mois'))->startOfMonth();
        } catch (\Throwable) {
            $mois = now()->startOfMonth();
        }
        $debut = $mois->copy()->startOfMonth();
        $fin = $mois->copy()->endOfMonth();

        $activites = Activite::publie()
            ->where('date_debut', '<=', $fin->toDateString())
            ->whereRaw('COALESCE(date_fin, date_debut) >= ?', [$debut->toDateString()])
            ->orderBy('date_debut')
            ->get();

        $jours = [];
        $curseur = $debut->copy()->startOfWeek(Carbon::MONDAY);
        $dernier = $fin->copy()->endOfWeek(Carbon::SUNDAY);
        while ($curseur->lte($dernier)) {
            $jour = $curseur->copy();
            $jours[] = [
                'date' => $jour,
                'hors_mois' => $jour->month !== $mois->month,
                'activites' => $activites->filter(
                    fn ($a) => $a->date_debut->lte($jour) && ($a->date_fin ?? $a->date_debut)->gte($jour)
                )->values(),
            ];
            $curseur->addDay();
        }

        return view('site.agenda', [
            'mois' => $mois,
            'precedent' => $mois->copy()->subMonth()->format('Y-m'),
            'suivant' => $mois->copy()->addMonth()->format('Y-m'),
            'jours' => $jours,
            'activites' => $activites,
        ]);
    }

    public function galerie(Request $request): View
    {
        $type = in_array($request->query('type'), ['photo', 'video'], true) ? $request->query('type') : null;

        $medias = Media::publie()
            ->when($type, fn ($q) => $q->where('type', $type))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('site.galerie', compact('medias', 'type'));
    }

    public function aPropos(): View
    {
        return view('site.a-propos');
    }

    public function contact(): View
    {
        return view('site.contact');
    }

    public function envoyerContact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'sujet' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
            'website' => ['nullable', 'max:0'], // champ piège : rempli uniquement par les robots
        ]);
        unset($data['website']);

        MessageContact::create($data);

        return redirect()->route('site.contact')->with('envoye', true);
    }
}
