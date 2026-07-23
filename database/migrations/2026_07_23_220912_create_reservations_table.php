<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained('etablissements')->cascadeOnDelete();
            $table->date('date');
            $table->enum('creneau', ['matin', 'apres_midi'])->default('matin');
            $table->unsignedInteger('nb_participants_prevu')->default(1);
            $table->unsignedInteger('nb_encadrants_prevu')->default(1);
            $table->enum('statut', ['confirmee', 'en_attente', 'annulee'])->default('en_attente');
            $table->foreignId('cree_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['date', 'creneau']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
