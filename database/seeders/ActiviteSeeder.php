<?php

namespace Database\Seeders;

use App\Models\Activite;
use Illuminate\Database\Seeder;

class ActiviteSeeder extends Seeder
{
    public function run(): void
    {
        $activites = [
            ['Initiation à l\'informatique', 'formation', 'Découvrez l\'ordinateur pas à pas : allumer, naviguer, créer un dossier, utiliser la souris et le clavier. Aucun prérequis.', '+3 days', '14h00 – 16h00', 'Salle informatique du PPN'],
            ['Atelier démarches en ligne', 'atelier', 'Apprenez à effectuer vos démarches administratives en ligne (impôts, CAF, santé) en toute sécurité.', '+7 days', '10h00 – 12h00', 'Salle informatique du PPN'],
            ['Journée portes ouvertes', 'evenement', 'Venez découvrir le PPN, ses équipements et son équipe. Démonstrations et goûter offerts.', '+14 days', '9h00 – 17h00', 'PPN d\'Andé'],
            ['Permanence d\'accompagnement individuel', 'accompagnement', 'Un agent vous aide sur rendez-vous pour vos besoins numériques : mails, documents, smartphone.', '+2 days', 'Sur rendez-vous', 'PPN d\'Andé'],
            ['Sensibilisation à la cybersécurité', 'atelier', 'Mots de passe, hameçonnage, protection de vos données : les bons réflexes pour naviguer sereinement.', '-10 days', '15h00 – 17h00', 'Salle informatique du PPN'],
            ['Formation bureautique', 'formation', 'Traitement de texte et tableur : créer un CV, une lettre, un budget simple.', '-25 days', '14h00 – 17h00', 'Salle informatique du PPN'],
        ];

        foreach ($activites as [$titre, $categorie, $description, $decalage, $horaires, $lieu]) {
            Activite::firstOrCreate(['titre' => $titre], [
                'categorie' => $categorie,
                'description' => $description,
                'date_debut' => now()->modify($decalage)->toDateString(),
                'horaires' => $horaires,
                'lieu' => $lieu,
                'publie' => true,
            ]);

        }
    }
}
