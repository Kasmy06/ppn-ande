<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Visiteur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompteurVisiteursTest extends TestCase
{
    use RefreshDatabase;

    private function visiteur(string $prenom, string $nom, ?string $date = null): void
    {
        Visiteur::create([
            'prenom' => $prenom, 'nom' => $nom, 'sexe' => 'M', 'type' => 'Externe',
            'date_visite' => $date ?? today()->toDateString(), 'heure_arrivee' => '09:00',
        ]);
    }

    public function test_footer_shows_visits_distinct_people_and_today(): void
    {
        $this->visiteur('Awa', 'Koné');
        $this->visiteur('Awa', 'Koné', '2026-01-10');   // même personne, deuxième visite
        $this->visiteur('Yao', 'Traoré', '2026-02-01');

        $this->get('/')->assertOk()
            ->assertSee('3 visites au PPN', false)
            ->assertSee('2 personnes différentes', false)
            ->assertSee('1 aujourd', false);
    }

    public function test_counter_can_be_hidden_by_the_team(): void
    {
        $this->visiteur('Awa', 'Koné');
        $this->get('/')->assertSee('visites au PPN', false);

        $this->actingAs(User::factory()->superAdmin()->create());
        $this->put('/parametres/application', ['nom_structure' => 'PPN', 'capacite_journaliere' => 60, 'visible_compteur' => 0]);

        $this->get('/')->assertDontSee('visites au PPN', false);
    }
}
