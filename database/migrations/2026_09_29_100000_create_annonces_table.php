<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annonces', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 150);
            $table->text('contenu');
            $table->boolean('urgente')->default(false);
            $table->date('date_publication');
            $table->date('date_expiration')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('publie')->default(false);
            $table->boolean('a_valider')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annonces');
    }
};
