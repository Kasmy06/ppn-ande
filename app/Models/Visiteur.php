<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visiteur extends Model
{
    use HasFactory;

    protected $fillable = [
        'prenom',
        'nom',
        'sexe',
        'type',
        'etablissement_id',
        'classe_ou_poste',
        'date_visite',
        'cree_par',
    ];

    protected $casts = [
        'date_visite' => 'date',
    ];

    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    public function getInitialesAttribute(): string
    {
        return mb_strtoupper(mb_substr($this->prenom, 0, 1) . mb_substr($this->nom, 0, 1));
    }
}
