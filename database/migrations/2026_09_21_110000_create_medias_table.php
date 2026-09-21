<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medias', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 150);
            $table->enum('type', ['photo', 'video'])->default('photo');
            $table->text('legende')->nullable();
            $table->string('fichier_path')->nullable();
            $table->string('video_url')->nullable();
            $table->foreignId('activite_id')->nullable()->constrained('activites')->nullOnDelete();
            $table->boolean('publie')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medias');
    }
};
