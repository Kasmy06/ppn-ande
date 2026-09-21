<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages_contact', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 120);
            $table->string('email', 150);
            $table->string('telephone', 30)->nullable();
            $table->string('sujet', 150);
            $table->text('message');
            $table->boolean('lu')->default(false);
            $table->timestamps();
        });

        // Numéros de contact initiaux (modifiables ensuite dans Paramètres > Application).
        DB::table('parametres_applications')->insertOrIgnore([
            'cle' => 'telephones',
            'valeur' => "+225 07 79 22 64 60\n+225 07 79 70 64 10\n+225 05 44 76 38 75",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('messages_contact');
    }
};
