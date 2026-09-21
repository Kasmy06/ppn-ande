<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $table = 'medias';

    protected $fillable = ['titre', 'type', 'legende', 'fichier_path', 'video_url', 'activite_id', 'publie'];

    protected $casts = ['publie' => 'boolean'];

    public function activite(): BelongsTo
    {
        return $this->belongsTo(Activite::class);
    }

    public function scopePublie(Builder $query): Builder
    {
        return $query->where('publie', true);
    }

    public function getFichierUrlAttribute(): ?string
    {
        return $this->fichier_path ? Storage::disk('public')->url($this->fichier_path) : null;
    }

    /** URL d'intégration si la vidéo est un lien YouTube ou Vimeo, sinon null. */
    public function getEmbedUrlAttribute(): ?string
    {
        if (! $this->video_url) {
            return null;
        }
        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([\w-]{11})~', $this->video_url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/'.$m[1];
        }
        if (preg_match('~vimeo\.com/(\d+)~', $this->video_url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return null;
    }

    /** Image d'aperçu : la photo elle‑même ou la miniature YouTube. */
    public function getApercuUrlAttribute(): ?string
    {
        if ($this->type === 'photo') {
            return $this->fichier_url;
        }
        if ($this->video_url && preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([\w-]{11})~', $this->video_url, $m)) {
            return "https://img.youtube.com/vi/{$m[1]}/hqdefault.jpg";
        }

        return null;
    }
}
