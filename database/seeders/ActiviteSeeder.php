<?php

namespace Database\Seeders;

use App\Models\Activite;
use App\Models\Media;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ActiviteSeeder extends Seeder
{
    public function run(): void
    {
        $activites = [
            ['Initiation à l\'informatique', 'formation', 'Découvrez l\'ordinateur pas à pas : allumer, naviguer, créer un dossier, utiliser la souris et le clavier. Aucun prérequis.', '+3 days', '14h00 – 16h00', 'Salle informatique du PPN', 'act-initiation'],
            ['Atelier démarches en ligne', 'atelier', 'Apprenez à effectuer vos démarches administratives en ligne (impôts, CAF, santé) en toute sécurité.', '+7 days', '10h00 – 12h00', 'Salle informatique du PPN', 'act-demarches'],
            ['Journée portes ouvertes', 'evenement', 'Venez découvrir le PPN, ses équipements et son équipe. Démonstrations et goûter offerts.', '+14 days', '9h00 – 17h00', 'PPN d\'Andé', 'act-portes'],
            ['Permanence d\'accompagnement individuel', 'accompagnement', 'Un agent vous aide sur rendez-vous pour vos besoins numériques : mails, documents, smartphone.', '+2 days', 'Sur rendez-vous', 'PPN d\'Andé', 'act-permanence'],
            ['Sensibilisation à la cybersécurité', 'atelier', 'Mots de passe, hameçonnage, protection de vos données : les bons réflexes pour naviguer sereinement.', '-10 days', '15h00 – 17h00', 'Salle informatique du PPN', 'act-cyber'],
            ['Formation bureautique', 'formation', 'Traitement de texte et tableur : créer un CV, une lettre, un budget simple.', '-25 days', '14h00 – 17h00', 'Salle informatique du PPN', 'act-bureautique'],
        ];

        foreach ($activites as [$titre, $categorie, $description, $decalage, $horaires, $lieu, $visuel]) {
            $activite = Activite::firstOrCreate(['titre' => $titre], [
                'categorie' => $categorie,
                'description' => $description,
                'date_debut' => now()->modify($decalage)->toDateString(),
                'horaires' => $horaires,
                'lieu' => $lieu,
                'publie' => true,
            ]);

            if (! $activite->image_path) {
                $activite->update(['image_path' => $this->installer($visuel, 'activites')]);
            }
        }

        // Visuels de démonstration pour la galerie (à remplacer par de vraies photos).
        $galerie = [
            ['La salle informatique', 'gal-salle', 'Initiation à l\'informatique'],
            ['Atelier collectif', 'gal-atelier', 'Atelier démarches en ligne'],
            ['Temps fort du PPN', 'gal-evenement', 'Journée portes ouvertes'],
            ['Le PPN d\'Andé', 'gal-equipe', null],
        ];

        foreach ($galerie as [$titre, $visuel, $titreActivite]) {
            Media::firstOrCreate(['titre' => $titre, 'type' => 'photo'], [
                'fichier_path' => $this->installer($visuel, 'medias'),
                'activite_id' => $titreActivite ? Activite::where('titre', $titreActivite)->value('id') : null,
                'publie' => true,
            ]);
        }
    }

    /** Copie un visuel de démonstration dans le disque public et retourne son chemin. */
    private function installer(string $nom, string $dossier): string
    {
        $chemin = "{$dossier}/{$nom}.svg";
        Storage::disk('public')->put($chemin, file_get_contents(__DIR__."/demo/{$nom}.svg"));

        return $chemin;
    }
}
