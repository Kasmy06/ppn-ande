<?php

namespace App\Support;

use App\Models\ParametreApplication;

/**
 * Coordonnées et textes du site public, modifiables dans Paramètres > Application.
 * Chaque information et chaque page peut être masquée au public sans être effacée.
 */
class SiteInfo
{
    /** Informations de contact que l'équipe peut afficher ou masquer. */
    public const INFOS = [
        'adresse' => 'Adresse',
        'telephones' => 'Numéros de téléphone',
        'email' => 'Adresse e-mail',
        'horaires' => 'Horaires d\'ouverture',
        'carte' => 'Carte',
    ];

    /** Pages du site public que l'équipe peut activer ou désactiver. */
    public const PAGES = [
        'a_propos' => 'À propos',
        'agenda' => 'Agenda',
        'galerie' => 'Galerie photos & vidéos',
        'contact' => 'Contact (formulaire et coordonnées)',
    ];

    /** Une information/page est visible tant que l'équipe ne l'a pas explicitement masquée. */
    public static function visible(string $cle): bool
    {
        return ParametreApplication::get('visible_'.$cle, '1') !== '0';
    }

    public static function page(string $nom): bool
    {
        return self::visible('page_'.$nom);
    }

    /** @return string[] */
    public static function telephones(): array
    {
        if (! self::visible('telephones')) {
            return [];
        }
        $brut = (string) ParametreApplication::get('telephones', '');

        return array_values(array_filter(array_map('trim', preg_split('/\R/', $brut))));
    }

    public static function lienTel(string $numero): string
    {
        return 'tel:'.preg_replace('/[^\d+]/', '', $numero);
    }

    public static function adresse(): ?string
    {
        return self::visible('adresse') ? (ParametreApplication::get('adresse') ?: null) : null;
    }

    public static function email(): ?string
    {
        return self::visible('email') ? (ParametreApplication::get('email') ?: null) : null;
    }

    public static function horaires(): ?string
    {
        return self::visible('horaires') ? (ParametreApplication::get('horaires') ?: null) : null;
    }

    public static function aPropos(): ?string
    {
        return ParametreApplication::get('a_propos') ?: null;
    }

    /** Adresse d'intégration (iframe) Google Maps ou OpenStreetMap, seulement si elle est reconnue et affichée. */
    public static function carteUrl(): ?string
    {
        if (! self::visible('carte')) {
            return null;
        }
        $url = ParametreApplication::get('carte_url');

        return $url && self::carteValide($url) ? $url : null;
    }

    public static function carteValide(string $url): bool
    {
        return (bool) preg_match('~^https://(www\.google\.com/maps/embed|maps\.google\.com/maps|www\.openstreetmap\.org/export/embed\.html)~', $url);
    }
}
