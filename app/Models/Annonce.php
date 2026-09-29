<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Annonce extends Model
{
    protected $fillable = [
        'titre', 'contenu', 'urgente', 'date_publication', 'date_expiration', 'image_path', 'publie', 'a_valider',
    ];

    protected $casts = [
        'urgente' => 'boolean',
        'date_publication' => 'date',
        'date_expiration' => 'date',
        'publie' => 'boolean',
        'a_valider' => 'boolean',
    ];

    public function scopePublie(Builder $query): Builder
    {
        return $query->where('publie', true);
    }

    /** Publiée et pas encore expirée (une annonce sans date d'expiration reste affichée). */
    public function scopeEnCours(Builder $query): Builder
    {
        return $query->where('date_publication', '<=', today()->toDateString())
            ->where(fn ($q) => $q->whereNull('date_expiration')->orWhere('date_expiration', '>=', today()->toDateString()));
    }

    public function getExpireeAttribute(): bool
    {
        return $this->date_expiration !== null && $this->date_expiration->lt(today());
    }
}
