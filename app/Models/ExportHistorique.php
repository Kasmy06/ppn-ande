<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ExportHistorique extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'module', 'format', 'filtres', 'date_generation'];

    protected $casts = [
        'filtres' => 'array',
        'date_generation' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function enregistrer(string $module, string $format, array $filtres = []): void
    {
        static::create([
            'user_id' => Auth::id(),
            'module' => $module,
            'format' => $format,
            'filtres' => array_filter($filtres),
            'date_generation' => now(),
        ]);
    }
}
