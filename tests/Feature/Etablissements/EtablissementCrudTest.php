<?php

namespace Tests\Feature\Etablissements;

use App\Models\Etablissement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EtablissementCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_etablissement(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post('/etablissements', [
            'nom' => 'École Test',
            'type' => 'scolaire',
            'ville' => 'Andé',
        ]);

        $response->assertRedirect('/etablissements');
        $this->assertDatabaseHas('etablissements', ['nom' => 'École Test']);
    }

    public function test_etablissement_name_must_be_unique(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Etablissement::factory()->create(['nom' => 'École Existante']);

        $response = $this->actingAs($admin)->post('/etablissements', [
            'nom' => 'École Existante',
            'type' => 'scolaire',
        ]);

        $response->assertSessionHasErrors('nom');
    }

    public function test_super_admin_can_delete_etablissement(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $etablissement = Etablissement::factory()->create();

        $response = $this->actingAs($admin)->delete("/etablissements/{$etablissement->id}");

        $response->assertRedirect('/etablissements');
        $this->assertDatabaseMissing('etablissements', ['id' => $etablissement->id]);
    }
}
