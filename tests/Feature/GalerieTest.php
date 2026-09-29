<?php

namespace Tests\Feature;

use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GalerieTest extends TestCase
{
    use RefreshDatabase;

    public function test_gallery_lists_only_published_media_and_filters_by_type(): void
    {
        Media::create(['titre' => 'Photo visible', 'type' => 'photo', 'fichier_path' => 'medias/a.jpg', 'publie' => true]);
        Media::create(['titre' => 'Photo cachée', 'type' => 'photo', 'fichier_path' => 'medias/b.jpg', 'publie' => false]);
        Media::create(['titre' => 'Vidéo YouTube', 'type' => 'video', 'video_url' => 'https://youtu.be/dQw4w9WgXcQ', 'publie' => true]);

        $this->get('/galerie')->assertOk()->assertSee('Photo visible')->assertSee('Vidéo YouTube')->assertDontSee('Photo cachée');
        $this->get('/galerie?type=video')->assertOk()->assertSee('Vidéo YouTube')->assertDontSee('Photo visible');
    }

    public function test_search_filters_media_by_title_or_caption(): void
    {
        Media::create(['titre' => 'Atelier cybersécurité', 'type' => 'photo', 'fichier_path' => 'medias/a.jpg', 'publie' => true]);
        Media::create(['titre' => 'Portes ouvertes', 'legende' => 'Journée découverte du PPN', 'type' => 'photo', 'fichier_path' => 'medias/b.jpg', 'publie' => true]);

        $this->get('/galerie?q=cyber')->assertSee('Atelier cybersécurité')->assertDontSee('Portes ouvertes');
        $this->get('/galerie?q=découverte')->assertSee('Portes ouvertes')->assertDontSee('Atelier cybersécurité');
        $this->get('/galerie?q=introuvable')->assertSee('Aucun contenu ne correspond');
    }

    public function test_youtube_links_become_embed_urls(): void
    {
        $m = new Media(['type' => 'video', 'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);

        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $m->embed_url);
        $this->assertSame('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg', $m->apercu_url);
    }
}
