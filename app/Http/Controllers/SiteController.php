<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\Annonce;
use App\Models\Media;
use App\Models\MessageContact;
use App\Support\Notifier;
use App\Support\SiteInfo;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function accueil(): View
    {
        return view('site.accueil', [
            'annonces' => SiteInfo::page('annonces')
                ? Annonce::publie()->enCours()->orderByDesc('date_publication')->limit(8)->get()
                : collect(),
            // Une seule liste, par date, comme sur la page Activités : plus de séparation à venir / passées.
            'activites' => Activite::publie()->orderByDesc('date_debut')->limit(6)->get(),
            'medias' => SiteInfo::page('galerie') ? Media::publie()->latest()->limit(6)->get() : collect(),
        ]);
    }

    public function annonces(Request $request): View
    {
        abort_unless(SiteInfo::page('annonces'), 404);

        $q = trim((string) $request->query('q'));

        $annonces = Annonce::publie()->enCours()
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
                $w->where('titre', 'like', $like)->orWhere('contenu', 'like', $like);
            }))
            ->orderByDesc('date_publication')
            ->paginate(10)->withQueryString();

        return view('site.annonces', compact('annonces', 'q'));
    }

    public function activites(Request $request): View
    {
        $categorie = $request->query('categorie');
        $q = trim((string) $request->query('q'));

        // Une seule liste, pour toutes les activités publiées, de la plus récente à la plus ancienne.
        $activites = Activite::publie()
            ->when(array_key_exists((string) $categorie, Activite::CATEGORIES), fn ($query) => $query->where('categorie', $categorie))
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
                $w->where('titre', 'like', $like)->orWhere('description', 'like', $like)->orWhere('lieu', 'like', $like);
            }))
            ->orderByDesc('date_debut')
            ->paginate(9)
            ->withQueryString();

        return view('site.activites', compact('activites', 'categorie', 'q'));
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
        abort_unless(SiteInfo::page('agenda'), 404);

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
        abort_unless(SiteInfo::page('galerie'), 404);

        $type = in_array($request->query('type'), ['photo', 'video'], true) ? $request->query('type') : null;
        $q = trim((string) $request->query('q'));

        $medias = Media::publie()
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
                $w->where('titre', 'like', $like)->orWhere('legende', 'like', $like);
            }))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('site.galerie', compact('medias', 'type', 'q'));
    }

    public function aPropos(): View
    {
        abort_unless(SiteInfo::page('a_propos'), 404);

        return view('site.a-propos');
    }

    public function mentions(): View
    {
        abort_unless(SiteInfo::page('mentions'), 404);

        return view('site.mentions');
    }

    public function confidentialite(): View
    {
        abort_unless(SiteInfo::page('confidentialite'), 404);

        return view('site.confidentialite');
    }

    public function sitemap(): Response
    {
        $urls = [['loc' => route('site.accueil')], ['loc' => route('site.activites')]];
        foreach (['annonces' => 'site.annonces', 'a_propos' => 'site.a-propos', 'agenda' => 'site.agenda', 'galerie' => 'site.galerie', 'contact' => 'site.contact',
                  'mentions' => 'site.mentions', 'confidentialite' => 'site.confidentialite'] as $page => $route) {
            if (SiteInfo::page($page)) {
                $urls[] = ['loc' => route($route)];
            }
        }
        foreach (Activite::publie()->orderByDesc('date_debut')->get(['id', 'updated_at']) as $a) {
            $urls[] = ['loc' => route('site.activite', $a), 'lastmod' => $a->updated_at->toDateString()];
        }

        return response()->view('site.sitemap', compact('urls'))->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $texte = "User-agent: *\nDisallow: /dashboard\nDisallow: /gestion-\nDisallow: /parametres\nDisallow: /messages\nDisallow: /login\n\nSitemap: ".route('site.sitemap')."\n";

        return response($texte, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function contact(): View
    {
        abort_unless(SiteInfo::page('contact'), 404);

        return view('site.contact');
    }

    public function envoyerContact(Request $request): RedirectResponse
    {
        abort_unless(SiteInfo::page('contact'), 404);

        $data = $request->validate([
            'nom' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'sujet' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
            'consentement' => ['accepted'],
            'website' => ['nullable', 'max:0'], // champ piège : rempli uniquement par les robots
        ]);
        unset($data['website'], $data['consentement']);

        $message = MessageContact::create($data);
        Notifier::superAdmins('Nouveau message de contact', "De : {$message->nom} <{$message->email}>".($message->telephone ? " — {$message->telephone}" : '')."\nSujet : {$message->sujet}\n\n{$message->message}");

        return redirect()->route('site.contact')->with('envoye', true);
    }
}
