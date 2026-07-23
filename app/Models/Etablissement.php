<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Etablissement extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'type',
        'adresse',
        'ville',
        'code_postal',
        'contact_nom',
        'contact_telephone',
        'contact_email',
    ];

    public function visiteurs(): HasMany
    {
        return $this->hasMany(Visiteur::class);
    }
}
