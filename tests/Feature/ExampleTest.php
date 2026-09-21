<?php

namespace Tests\Feature;

use App\Models\Activite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_shows_public_homepage(): void
    {
        $this->get('/')->assertOk()->assertSee('Prochaines activités');
    }

    public function test_only_published_activities_are_public(): void
    {
        $publiee = Activite::create(['titre' => 'Atelier visible', 'categorie' => 'atelier', 'description' => 'x', 'date_debut' => today()->addDay(), 'publie' => true]);
        $brouillon = Activite::create(['titre' => 'Brouillon caché', 'categorie' => 'atelier', 'description' => 'x', 'date_debut' => today()->addDay(), 'publie' => false]);

        $this->get('/activites')->assertOk()->assertSee('Atelier visible')->assertDontSee('Brouillon caché');
        $this->get("/activites/{$publiee->id}")->assertOk();
        $this->get("/activites/{$brouillon->id}")->assertNotFound();
    }
}
