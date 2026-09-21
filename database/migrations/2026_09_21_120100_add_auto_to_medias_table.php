<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // « auto » = photo de galerie copiée automatiquement depuis l'image de couverture d'une activité.
        Schema::table('medias', function (Blueprint $table) {
            $table->boolean('auto')->default(false)->after('publie');
        });

        $couvertures = DB::table('activites')->whereNotNull('image_path')->pluck('image_path', 'id');
        foreach (DB::table('medias')->where('type', 'photo')->whereNotNull('activite_id')->get() as $media) {
            $cover = $couvertures[$media->activite_id] ?? null;
            if ($cover && basename($cover) === basename((string) $media->fichier_path)) {
                DB::table('medias')->where('id', $media->id)->update(['auto' => true]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('medias', function (Blueprint $table) {
            $table->dropColumn('auto');
        });
    }
};
