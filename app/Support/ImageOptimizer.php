<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Enregistre une image téléversée en la redimensionnant (1600 px max) et en la recompressant,
 * pour que les photos de téléphone (5 à 10 Mo) ne ralentissent pas le site public.
 * Si l'image est déjà légère ou si le format n'est pas géré, elle est stockée telle quelle.
 */
class ImageOptimizer
{
    public const LARGEUR_MAX = 1600;
    public const POIDS_SEUIL = 400 * 1024; // en dessous, une image de taille raisonnable n'est pas retouchée

    public static function stocker(UploadedFile $fichier, string $dossier): string
    {
        $chemin = $dossier.'/'.Str::random(40).'.'.self::extension($fichier);
        $contenu = self::optimiser($fichier);

        Storage::disk('public')->put($chemin, $contenu ?? file_get_contents($fichier->getRealPath()));

        return $chemin;
    }

    private static function extension(UploadedFile $fichier): string
    {
        $ext = strtolower($fichier->guessExtension() ?: $fichier->getClientOriginalExtension() ?: 'jpg');

        return $ext === 'jpeg' ? 'jpg' : $ext;
    }

    /** @return string|null image recompressée, ou null pour conserver l'original */
    private static function optimiser(UploadedFile $fichier): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $infos = @getimagesize($fichier->getRealPath());
        if (! $infos) {
            return null;
        }
        [$largeur, $hauteur, $type] = $infos;

        $trop_grande = $largeur > self::LARGEUR_MAX || $hauteur > self::LARGEUR_MAX;
        if (! $trop_grande && $fichier->getSize() <= self::POIDS_SEUIL) {
            return null;
        }

        $source = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($fichier->getRealPath()),
            IMAGETYPE_PNG => @imagecreatefrompng($fichier->getRealPath()),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($fichier->getRealPath()) : false,
            default => false, // GIF (animé) et autres formats : conservés tels quels
        };
        if (! $source) {
            return null;
        }

        // Les photos de téléphone stockent leur sens dans l'EXIF : on l'applique avant de recompresser.
        if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($fichier->getRealPath());
            $angle = match ($exif['Orientation'] ?? 1) {
                3 => 180, 6 => -90, 8 => 90, default => 0,
            };
            if ($angle !== 0 && ($pivotee = imagerotate($source, $angle, 0))) {
                $source = $pivotee;
            }
        }

        $largeur = imagesx($source);
        $hauteur = imagesy($source);
        $ratio = min(1, self::LARGEUR_MAX / max($largeur, $hauteur));
        $nouvelle = imagecreatetruecolor((int) round($largeur * $ratio), (int) round($hauteur * $ratio));

        if ($type !== IMAGETYPE_JPEG) { // conserver la transparence
            imagealphablending($nouvelle, false);
            imagesavealpha($nouvelle, true);
        }
        imagecopyresampled($nouvelle, $source, 0, 0, 0, 0, imagesx($nouvelle), imagesy($nouvelle), $largeur, $hauteur);

        ob_start();
        match ($type) {
            IMAGETYPE_PNG => imagepng($nouvelle, null, 8),
            IMAGETYPE_WEBP => imagewebp($nouvelle, null, 82),
            default => imagejpeg($nouvelle, null, 82),
        };
        $contenu = ob_get_clean();

        // On ne garde la version recompressée que si elle est réellement plus légère.
        return $contenu !== false && strlen($contenu) < $fichier->getSize() ? $contenu : null;
    }
}
