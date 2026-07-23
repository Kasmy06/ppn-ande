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
        Schema::create('etablissements', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->enum('type', ['scolaire', 'collectivite', 'association', 'autre'])->default('scolaire');
            $table->string('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('code_postal', 10)->nullable();
            $table->string('contact_nom')->nullable();
            $table->string('contact_telephone')->nullable();
            $table->string('contact_email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etablissements');
    }
};
