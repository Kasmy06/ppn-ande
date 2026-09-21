<?php

namespace Tests\Feature\Roles;

use App\Models\Etablissement;
use App\Models\User;
use App\Models\Visiteur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleRestrictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_cannot_delete_a_visiteur(): void
    {
        $agent = User::factory()->agent()->create();
        $visiteur = Visiteur::factory()->create();

        $response = $this->actingAs($agent)->delete("/visiteurs/{$visiteur->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('visiteurs', ['id' => $visiteur->id]);
    }

    public function test_super_admin_can_delete_a_visiteur(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $visiteur = Visiteur::factory()->create();

        $response = $this->actingAs($admin)->delete("/visiteurs/{$visiteur->id}");

        $response->assertRedirect('/visiteurs');
        $this->assertDatabaseMissing('visiteurs', ['id' => $visiteur->id]);
    }

    public function test_agent_can_create_a_visiteur(): void
    {
        $agent = User::factory()->agent()->create();

        $response = $this->actingAs($agent)->post('/visiteurs', [
            'prenom' => 'Jean',
            'nom' => 'Test',
            'sexe' => 'M',
            'type' => 'Externe',
            'date_visite' => now()->format('Y-m-d'),
            'heure_arrivee' => '09:30',
        ]);

        $response->assertRedirect('/visiteurs');
        $this->assertDatabaseHas('visiteurs', ['prenom' => 'Jean', 'nom' => 'Test']);
    }

    public function test_agent_cannot_create_an_etablissement(): void
    {
        $agent = User::factory()->agent()->create();

        $response = $this->actingAs($agent)->post('/etablissements', [
            'nom' => 'Nouvelle École',
            'type' => 'scolaire',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('etablissements', ['nom' => 'Nouvelle École']);
    }

    public function test_agent_cannot_access_parametres_utilisateurs(): void
    {
        $agent = User::factory()->agent()->create();

        $response = $this->actingAs($agent)->get('/parametres/utilisateurs');

        $response->assertForbidden();
    }

    public function test_super_admin_can_access_parametres_utilisateurs(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->get('/parametres/utilisateurs');

        $response->assertOk();
    }

    public function test_agent_can_view_but_not_edit_etablissement(): void
    {
        $agent = User::factory()->agent()->create();
        $etablissement = Etablissement::factory()->create();

        $this->actingAs($agent)->get('/etablissements')->assertOk();

        $response = $this->actingAs($agent)->put("/etablissements/{$etablissement->id}", [
            'nom' => 'Modifié',
            'type' => 'scolaire',
        ]);

        $response->assertForbidden();
    }
}
