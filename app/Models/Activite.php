<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activite extends Model
{
    public const CATEGORIES = [
        'formation' => 'Formation',
        'atelier' => 'Atelier',
        'evenement' => 'Événement',
        'accompagnement' => 'Accompagnement',
    ];

    protected $fillable = [
        'titre', 'categorie', 'description', 'date_debut', 'date_fin',
        'horaires', 'lieu', 'image_path', 'publie',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'publie' => 'boolean',
    ];

    public function medias(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function scopePublie(Builder $query): Builder
    {
        return $query->where('publie', true);
    }

    /** Activités dont la date de fin (ou de début à défaut) n'est pas passée. */
    public function scopeAVenir(Builder $query): Builder
    {
        return $query->whereRaw('COALESCE(date_fin, date_debut) >= ?', [today()->toDateString()]);
    }

    public function getCategorieLabelAttribute(): string
    {
        return self::CATEGORIES[$this->categorie] ?? $this->categorie;
    }

    public function getEstPasseeAttribute(): bool
    {
        return ($this->date_fin ?? $this->date_debut)->lt(today());
    }
}
