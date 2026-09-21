<?php

namespace App\Support;

use App\Mail\NotificationEquipe;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class Notifier
{
    /** Prévient les Super Admin par e-mail. Un échec d'envoi ne doit jamais bloquer l'action de l'utilisateur. */
    public static function superAdmins(string $sujet, string $texte): void
    {
        $destinataires = User::where('role', 'super_admin')->where('actif', true)->pluck('email')->filter()->all();
        if (! $destinataires) {
            return;
        }

        try {
            Mail::to($destinataires)->send(new NotificationEquipe($sujet, $texte));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
