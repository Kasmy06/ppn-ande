<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ParametreApplication extends Model
{
    protected $table = 'parametres_applications';

    protected $fillable = ['cle', 'valeur'];

    public static function get(string $cle, mixed $default = null): mixed
    {
        return Cache::rememberForever("parametre.{$cle}", function () use ($cle, $default) {
            return static::where('cle', $cle)->value('valeur') ?? $default;
        });
    }

    public static function set(string $cle, mixed $valeur): void
    {
        static::updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
        Cache::forget("parametre.{$cle}");
    }
}
