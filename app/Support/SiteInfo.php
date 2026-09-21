<?php

namespace App\Support;

use App\Models\ParametreApplication;

/** Coordonnées et textes du site public, modifiables dans Paramètres > Application. */
class SiteInfo
{
    /** @return string[] */
    public static function telephones(): array
    {
        $brut = (string) ParametreApplication::get('telephones', '');

        return array_values(array_filter(array_map('trim', preg_split('/\R/', $brut))));
    }

    public static function lienTel(string $numero): string
    {
        return 'tel:'.preg_replace('/[^\d+]/', '', $numero);
    }

    public static function adresse(): ?string
    {
        return ParametreApplication::get('adresse') ?: null;
    }

    public static function email(): ?string
    {
        return ParametreApplication::get('email') ?: null;
    }

    public static function horaires(): ?string
    {
        return ParametreApplication::get('horaires') ?: null;
    }

    public static function aPropos(): ?string
    {
        return ParametreApplication::get('a_propos') ?: null;
    }

    /** Adresse d'intégration (iframe) Google Maps ou OpenStreetMap, seulement si elle est reconnue. */
    public static function carteUrl(): ?string
    {
        $url = ParametreApplication::get('carte_url');

        return $url && self::carteValide($url) ? $url : null;
    }

    public static function carteValide(string $url): bool
    {
        return (bool) preg_match('~^https://(www\.google\.com/maps/embed|maps\.google\.com/maps|www\.openstreetmap\.org/export/embed\.html)~', $url);
    }
}
