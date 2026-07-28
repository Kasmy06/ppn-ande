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
        Schema::table('visiteurs', function (Blueprint $table) {
            $table->string('telephone', 30)->nullable()->after('classe_ou_poste');
            $table->string('email')->nullable()->after('telephone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visiteurs', function (Blueprint $table) {
            $table->dropColumn(['telephone', 'email']);
        });
    }
};
