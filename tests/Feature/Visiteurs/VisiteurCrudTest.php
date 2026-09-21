<?php

namespace Tests\Feature\Visiteurs;

use App\Models\Etablissement;
use App\Models\User;
use App\Models\Visiteur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisiteurCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_visiteur_list_requires_authentication(): void
    {
        $this->get('/visiteurs')->assertRedirect('/login');
    }

    public function test_visiteur_creation_requires_valid_data(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post('/visiteurs', []);

        $response->assertSessionHasErrors(['prenom', 'nom', 'sexe', 'type', 'date_visite']);
    }

    public function test_visiteur_can_be_created_with_etablissement(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $etablissement = Etablissement::factory()->create();

        $response = $this->actingAs($admin)->post('/visiteurs', [
            'prenom' => 'Marie',
            'nom' => 'Dupont',
            'sexe' => 'F',
            'type' => 'Élève',
            'etablissement_id' => $etablissement->id,
            'classe_ou_poste' => 'CM2',
            'date_visite' => '2026-05-10',
            'heure_arrivee' => '09:30',
        ]);

        $response->assertRedirect('/visiteurs');
        $this->assertDatabaseHas('visiteurs', [
            'prenom' => 'Marie',
            'nom' => 'Dupont',
            'etablissement_id' => $etablissement->id,
        ]);
    }

    public function test_visiteur_can_be_updated(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $visiteur = Visiteur::factory()->create(['prenom' => 'Ancien']);

        $response = $this->actingAs($admin)->put("/visiteurs/{$visiteur->id}", [
            'prenom' => 'Nouveau',
            'nom' => $visiteur->nom,
            'sexe' => $visiteur->sexe,
            'type' => $visiteur->type,
            'date_visite' => $visiteur->date_visite->format('Y-m-d'),
            'heure_arrivee' => '09:30',
        ]);

        $response->assertRedirect('/visiteurs');
        $this->assertDatabaseHas('visiteurs', ['id' => $visiteur->id, 'prenom' => 'Nouveau']);
    }

    public function test_visiteur_search_filters_results(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Visiteur::factory()->create(['prenom' => 'Unique', 'nom' => 'Findme']);
        Visiteur::factory()->create(['prenom' => 'Autre', 'nom' => 'Personne']);

        $response = $this->actingAs($admin)->get('/visiteurs?q=Findme');

        $response->assertOk();
        $response->assertSee('Findme');
        $response->assertDontSee('Personne');
    }

    public function test_csv_export_downloads_successfully(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Visiteur::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/visiteurs/export?format=csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
