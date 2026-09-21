<?php

namespace Tests\Feature;

use App\Mail\NotificationEquipe;
use App\Models\Activite;
use App\Models\Media;
use App\Models\MessageContact;
use App\Models\User;
use App\Support\ImageOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NouveautesTest extends TestCase
{
    use RefreshDatabase;

    private array $base = ['titre' => 'Atelier agent', 'categorie' => 'atelier', 'description' => 'Contenu', 'date_debut' => '2030-01-10'];

    public function test_large_photos_are_resized_and_compressed(): void
    {
        Storage::fake('public');

        // Photo « de téléphone » : 3000 x 2000 avec du bruit pour qu'elle soit lourde.
        $img = imagecreatetruecolor(3000, 2000);
        for ($i = 0; $i < 60000; $i++) {
            imagesetpixel($img, random_int(0, 2999), random_int(0, 1999), random_int(0, 0xFFFFFF));
        }
        $tmp = tempnam(sys_get_temp_dir(), 'ppn').'.jpg';
        imagejpeg($img, $tmp, 100);
        $poidsOriginal = filesize($tmp);

        $chemin = ImageOptimizer::stocker(new UploadedFile($tmp, 'grosse.jpg', 'image/jpeg', null, true), 'medias');

        [$largeur, $hauteur] = getimagesizefromstring(Storage::disk('public')->get($chemin));
        $this->assertLessThanOrEqual(1600, max($largeur, $hauteur));
        $this->assertSame(1600, $largeur);
        $this->assertSame(1067, $hauteur, 'le ratio est conservé');
        $this->assertLessThan($poidsOriginal, Storage::disk('public')->size($chemin));
        @unlink($tmp);
    }

    public function test_small_images_are_stored_untouched(): void
    {
        Storage::fake('public');
        $fichier = UploadedFile::fake()->image('petite.jpg', 400, 300);
        $original = file_get_contents($fichier->getRealPath());

        $chemin = ImageOptimizer::stocker($fichier, 'medias');

        $this->assertSame($original, Storage::disk('public')->get($chemin));
    }

    public function test_agent_submissions_are_flagged_notified_and_validated_together(): void
    {
        Storage::fake('public');
        Mail::fake();
        $admin = User::factory()->superAdmin()->create(['email' => 'admin@ppn.ci']);
        $agent = User::factory()->agent()->create();

        $this->actingAs($agent)->post('/gestion-activites', $this->base + [
            'image' => UploadedFile::fake()->image('a.jpg'),
            'photos' => [UploadedFile::fake()->image('b.jpg')],
        ]);

        $activite = Activite::firstOrFail();
        $this->assertTrue($activite->a_valider);
        $this->assertSame(1, Media::where('a_valider', true)->count(), 'la photo supplémentaire est à valider avec l\'activité');
        Mail::assertSent(NotificationEquipe::class, fn ($m) => $m->hasTo('admin@ppn.ci') && str_contains($m->texte, 'Atelier agent'));

        $this->actingAs($admin)->get('/dashboard')->assertSee('title="À valider"', false);

        $this->patch("/gestion-activites/{$activite->id}/publication");
        $this->assertFalse($activite->fresh()->a_valider);
        $this->assertSame(2, Media::where('publie', true)->count(), 'publier l\'activité publie aussi ses médias soumis');
        $this->assertSame(0, Media::where('a_valider', true)->count());
    }

    public function test_agent_media_notifies_admins(): void
    {
        Storage::fake('public');
        Mail::fake();
        User::factory()->superAdmin()->create(['email' => 'admin@ppn.ci']);

        $this->actingAs(User::factory()->agent()->create())
            ->post('/gestion-medias', ['titre' => 'Photo à valider', 'type' => 'photo', 'photos' => [UploadedFile::fake()->image('a.jpg')]]);

        Mail::assertSent(NotificationEquipe::class, fn ($m) => str_contains($m->texte, 'Photo à valider'));
        $this->assertTrue(Media::first()->a_valider);
    }

    public function test_contact_message_notifies_admins(): void
    {
        Mail::fake();
        User::factory()->superAdmin()->create(['email' => 'admin@ppn.ci']);

        $this->post('/contact', ['nom' => 'Awa', 'email' => 'awa@example.ci', 'sujet' => 'Bonjour', 'message' => 'Un message assez long.', 'consentement' => 1]);

        $this->assertSame(1, MessageContact::count());
        Mail::assertSent(NotificationEquipe::class, fn ($m) => $m->hasTo('admin@ppn.ci') && str_contains($m->texte, 'Awa'));
    }

    public function test_notification_failure_never_blocks_the_action(): void
    {
        User::factory()->superAdmin()->create(['email' => 'admin@ppn.ci']);
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1]);

        $this->post('/contact', ['nom' => 'Awa', 'email' => 'awa@example.ci', 'sujet' => 'Bonjour', 'message' => 'Un message assez long.', 'consentement' => 1])
            ->assertRedirect('/contact');
        $this->assertSame(1, MessageContact::count());
    }

    public function test_legal_pages_use_settings_and_can_be_customised_or_hidden(): void
    {
        $this->get('/mentions-legales')->assertOk()->assertSee('Propriété intellectuelle');
        $this->get('/confidentialite')->assertOk()->assertSee('2013-450')->assertSee('ARTCI')->assertSee('12 mois');
        $this->get('/contact')->assertSee('politique de confidentialité');
        $this->get('/')->assertSee(route('site.mentions'), false)->assertSee(route('site.confidentialite'), false);

        $this->actingAs(User::factory()->superAdmin()->create());
        $base = ['nom_structure' => 'PPN', 'capacite_journaliere' => 60];
        $this->put('/parametres/application', $base + [
            'responsable' => 'Mme Directrice', 'hebergeur' => 'Hébergeur Test SA', 'duree_conservation' => '6 mois',
        ]);
        $this->get('/mentions-legales')->assertSee('Mme Directrice')->assertSee('Hébergeur Test SA');
        $this->get('/confidentialite')->assertSee('Mme Directrice')->assertSee('6 mois');

        $this->put('/parametres/application', $base + ['confidentialite' => 'Notre texte à nous.']);
        $this->get('/confidentialite')->assertSee('Notre texte à nous.')->assertDontSee('ARTCI');

        $this->put('/parametres/application', $base + ['visible_page_mentions' => 0, 'visible_page_confidentialite' => 0]);
        $this->get('/mentions-legales')->assertNotFound();
        $this->get('/confidentialite')->assertNotFound();
        $this->get('/')->assertDontSee(route('site.mentions'), false);
    }

    public function test_sitemap_lists_only_public_pages_and_robots_points_to_it(): void
    {
        $publique = Activite::create($this->base + ['titre' => 'Activité publique', 'publie' => true]);
        $brouillon = Activite::create($this->base + ['titre' => 'Activité brouillon', 'publie' => false]);

        $reponse = $this->get('/sitemap.xml')->assertOk();
        $this->assertStringContainsString('application/xml', $reponse->headers->get('Content-Type'));
        $reponse->assertSee(route('site.activite', $publique), false)->assertDontSee(route('site.activite', $brouillon), false)
            ->assertSee(route('site.galerie'), false);

        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: '.route('site.sitemap'), false)->assertSee('Disallow: /dashboard');
    }
}
