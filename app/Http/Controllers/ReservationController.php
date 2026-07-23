<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\JournalActivite;
use App\Models\ParametreApplication;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(Request $request): View
    {
        $mois = Carbon::createFromDate(
            (int) $request->query('annee', now()->year),
            (int) $request->query('mois', now()->month),
            1
        );

        $reservations = Reservation::with('etablissement')
            ->whereYear('date', $mois->year)
            ->whereMonth('date', $mois->month)
            ->orderBy('date')
            ->get()
            ->groupBy(fn ($r) => $r->date->format('Y-m-d'));

        $debutGrille = $mois->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $finGrille = $mois->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $jours = collect();
        $cursor = $debutGrille->copy();
        while ($cursor->lte($finGrille)) {
            $jours->push($cursor->copy());
            $cursor->addDay();
        }

        return view('calendrier.index', [
            'mois' => $mois,
            'jours' => $jours,
            'reservations' => $reservations,
            'etablissements' => Etablissement::orderBy('nom')->get(),
            'capaciteJournaliere' => ParametreApplication::get('capacite_journaliere', 60),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $this->verifierCapacite($data['date'], $data['nb_participants_prevu']);

        $data['cree_par'] = Auth::id();
        $reservation = Reservation::create($data);
        JournalActivite::log('Création réservation', $reservation, $reservation->date->format('d/m/Y'));

        return back()->with('success', 'Réservation ajoutée avec succès.');
    }

    public function update(Request $request, Reservation $reservation): RedirectResponse
    {
        $data = $this->validated($request);

        $this->verifierCapacite($data['date'], $data['nb_participants_prevu'], $reservation->id);

        $reservation->update($data);
        JournalActivite::log('Modification réservation', $reservation, $reservation->date->format('d/m/Y'));

        return back()->with('success', 'Réservation modifiée avec succès.');
    }

    public function destroy(Reservation $reservation): RedirectResponse
    {
        $date = $reservation->date->format('d/m/Y');
        $reservation->delete();
        JournalActivite::log('Suppression réservation', null, $date);

        return back()->with('success', 'Réservation supprimée.');
    }

    private function verifierCapacite(string $date, int $participants, ?int $ignoreId = null): void
    {
        $capacite = (int) ParametreApplication::get('capacite_journaliere', 60);

        $dejaReserves = Reservation::whereDate('date', $date)
            ->where('statut', '!=', 'annulee')
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->sum('nb_participants_prevu');

        if ($dejaReserves + $participants > $capacite) {
            $restant = max($capacite - $dejaReserves, 0);

            throw ValidationException::withMessages([
                'nb_participants_prevu' => "Capacité journalière dépassée : il ne reste que {$restant} place(s) disponible(s) sur cette date (capacité : {$capacite}).",
            ]);
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'etablissement_id' => ['required', 'exists:etablissements,id'],
            'date' => ['required', 'date'],
            'creneau' => ['required', 'in:matin,apres_midi'],
            'nb_participants_prevu' => ['required', 'integer', 'min:1', 'max:500'],
            'nb_encadrants_prevu' => ['required', 'integer', 'min:0', 'max:100'],
            'statut' => ['required', 'in:confirmee,en_attente,annulee'],
        ]);
    }
}
