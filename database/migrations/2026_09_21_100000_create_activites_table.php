<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activites', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 150);
            $table->enum('categorie', ['formation', 'atelier', 'evenement', 'accompagnement'])->default('atelier');
            $table->text('description');
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->string('horaires', 60)->nullable();
            $table->string('lieu', 150)->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('publie')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activites');
    }
};
