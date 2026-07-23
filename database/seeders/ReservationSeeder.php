<?php

namespace Database\Seeders;

use App\Models\Etablissement;
use App\Models\ParametreApplication;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ParametreApplication::set('nom_structure', "PPN d'Andé");
        ParametreApplication::set('capacite_journaliere', 60);

        $etablissementIds = Etablissement::pluck('id');
        $userId = User::where('role', 'super_admin')->value('id');
        $statuts = ['confirmee', 'confirmee', 'en_attente', 'annulee'];
        $creneaux = ['matin', 'apres_midi'];

        for ($i = 0; $i < 20; $i++) {
            Reservation::create([
                'etablissement_id' => $etablissementIds->random(),
                'date' => now()->addDays(rand(-10, 25))->format('Y-m-d'),
                'creneau' => $creneaux[array_rand($creneaux)],
                'nb_participants_prevu' => rand(10, 30),
                'nb_encadrants_prevu' => rand(1, 4),
                'statut' => $statuts[array_rand($statuts)],
                'cree_par' => $userId,
            ]);
        }
    }
}
