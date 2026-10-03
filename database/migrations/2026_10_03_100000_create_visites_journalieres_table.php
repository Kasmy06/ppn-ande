<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Compteur anonyme : un seul nombre par jour, sans adresse IP ni identifiant de visiteur. */
    public function up(): void
    {
        Schema::create('visites_journalieres', function (Blueprint $table) {
            $table->date('jour')->primary();
            $table->unsignedInteger('nombre')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visites_journalieres');
    }
};
