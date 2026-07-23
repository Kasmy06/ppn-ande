<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class JournalActivite extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'action', 'cible_type', 'cible_id', 'detail', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(string $action, ?Model $cible = null, ?string $detail = null): void
    {
        static::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'cible_type' => $cible ? class_basename($cible) : null,
            'cible_id' => $cible?->getKey(),
            'detail' => $detail,
            'created_at' => now(),
        ]);
    }
}
