<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'etablissement_id',
        'date',
        'creneau',
        'nb_participants_prevu',
        'nb_encadrants_prevu',
        'statut',
        'cree_par',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cree_par');
    }
}
