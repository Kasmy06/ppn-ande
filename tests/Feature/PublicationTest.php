<?php

namespace Tests\Feature;

use App\Models\Activite;
use App\Models\Media;
use App\Models\ParametreApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicationTest extends TestCase
{
    use RefreshDatabase;

    private array $base = ['titre' => 'Atelier secret', 'categorie' => 'atelier', 'description' => 'Contenu', 'date_debut' => '2030-01-10'];

    public function test_new_activity_is_a_draft_until_published(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->superAdmin()->create());

        $this->get('/gestion-activites/create')->assertOk();
        $this->post('/gestion-activites', $this->base + ['image' => UploadedFile::fake()->image('a.jpg')]);

        $activite = Activite::first();
        $this->assertFalse($activite->publie);
        $this->get('/activites')->assertDontSee('Atelier secret');
        $this->get("/activites/{$activite->id}")->assertNotFound();
        $this->assertFalse(Media::first()->publie);
        $this->get('/galerie')->assertDontSee('Atelier secret');

        $this->patch("/gestion-activites/{$activite->id}/publication")->assertRedirect();
        $this->assertTrue($activite->fresh()->publie);
        $this->assertTrue(Media::first()->publie, 'la copie de couverture suit la publication');
        $this->get('/activites')->assertSee('Atelier secret');

        $this->patch("/gestion-activites/{$activite->id}/publication");
        $this->get('/activites')->assertDontSee('Atelier secret');
        $this->assertFalse(Media::first()->publie);
    }

    public function test_agent_media_stays_draft_and_only_admin_can_publish(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->agent()->create());

        $this->post('/gestion-medias', ['titre' => 'Photo agent', 'type' => 'photo', 'publie' => 1, 'photos' => [UploadedFile::fake()->image('a.jpg')]]);
        $media = Media::first();
        $this->assertFalse($media->publie, 'un agent ne peut pas publier');
        $this->get('/galerie')->assertDontSee('Photo agent');

        $this->patch("/gestion-medias/{$media->id}/publication")->assertForbidden();
        $activite = Activite::create($this->base + ['publie' => false]);
        $this->patch("/gestion-activites/{$activite->id}/publication")->assertForbidden();
        $this->assertFalse($activite->fresh()->publie);

        $this->actingAs(User::factory()->superAdmin()->create())->patch("/gestion-medias/{$media->id}/publication");
        $this->get('/galerie')->assertSee('Photo agent');
    }

    public function test_contact_infos_can_be_hidden_without_losing_them(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $params = ['nom_structure' => 'PPN', 'capacite_journaliere' => 60, 'adresse' => 'Rue Cachée', 'telephones' => '+225 01 02 03 04 05', 'email' => 'prive@ppn.ci'];

        $this->put('/parametres/application', $params);
        $this->get('/contact')->assertSee('Rue Cachée')->assertSee('+225 01 02 03 04 05')->assertSee('prive@ppn.ci');

        $this->put('/parametres/application', $params + ['visible_adresse' => 0, 'visible_telephones' => 0, 'visible_email' => 1]);
        $this->get('/contact')->assertDontSee('Rue Cachée')->assertDontSee('+225 01 02 03 04 05')->assertSee('prive@ppn.ci');

        // Les valeurs masquées restent dans le formulaire des paramètres : elles ne sont pas perdues.
        $this->get('/parametres/application')->assertSee('Rue Cachée')->assertSee('+225 01 02 03 04 05');
        $this->put('/parametres/application', $params + ['visible_adresse' => 1, 'visible_telephones' => 1]);
        $this->get('/contact')->assertSee('Rue Cachée')->assertSee('+225 01 02 03 04 05');
    }

    public function test_pages_can_be_switched_off(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $params = ['nom_structure' => 'PPN', 'capacite_journaliere' => 60];

        $this->get('/galerie')->assertOk();
        $this->get('/')->assertSee(route('site.galerie'), false);

        $this->put('/parametres/application', $params + ['visible_page_galerie' => 0, 'visible_page_agenda' => 0, 'visible_page_contact' => 0]);

        $this->get('/galerie')->assertNotFound();
        $this->get('/agenda')->assertNotFound();
        $this->get('/contact')->assertNotFound();
        $this->post('/contact', ['nom' => 'A', 'email' => 'a@b.ci', 'sujet' => 'S', 'message' => 'Un message assez long.'])->assertNotFound();
        $this->get('/')->assertOk()->assertDontSee(route('site.galerie'), false)->assertDontSee(route('site.contact'), false);
        $this->get('/a-propos')->assertOk();
    }

    public function test_logo_is_used_as_favicon_in_the_team_area(): void
    {
        ParametreApplication::set('logo_path', 'logos/mon-logo.png');
        $this->actingAs(User::factory()->superAdmin()->create());

        $this->get('/dashboard')->assertSee('rel="icon"', false)->assertSee('logos/mon-logo.png', false);
        auth()->logout();
        $this->get('/login')->assertSee('rel="icon"', false)->assertSee('logos/mon-logo.png', false);
    }

    public function test_agent_can_add_activity_but_only_as_draft_and_cannot_edit_or_delete(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->agent()->create());

        $this->get('/gestion-activites')->assertOk();
        $this->get('/gestion-activites/create')->assertOk()->assertSee('enregistrée en brouillon');
        $this->post('/gestion-activites', $this->base + ['publie' => 1, 'image' => UploadedFile::fake()->image('a.jpg')])
            ->assertRedirect('/gestion-activites');

        $activite = Activite::firstOrFail();
        $this->assertFalse($activite->publie, 'un agent ne peut pas publier');
        $this->get('/activites')->assertDontSee('Atelier secret');
        $this->assertFalse(Media::first()->publie);

        $this->get('/gestion-activites')->assertSee('Atelier secret')->assertSee('En attente de validation');
        $this->get("/gestion-activites/{$activite->id}/edit")->assertForbidden();
        $this->put("/gestion-activites/{$activite->id}", $this->base + ['titre' => 'Piraté'])->assertForbidden();
        $this->delete("/gestion-activites/{$activite->id}")->assertForbidden();
        $this->assertSame('Atelier secret', $activite->fresh()->titre);

        $this->actingAs(User::factory()->superAdmin()->create())->patch("/gestion-activites/{$activite->id}/publication");
        $this->get('/activites')->assertSee('Atelier secret');
    }
}
