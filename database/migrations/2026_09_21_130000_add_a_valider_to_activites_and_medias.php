<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // « à valider » = ajouté par un agent, en attente de la décision du Super Admin.
        foreach (['activites', 'medias'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->boolean('a_valider')->default(false);
            });
        }
    }

    public function down(): void
    {
        foreach (['activites', 'medias'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('a_valider');
            });
        }
    }
};
