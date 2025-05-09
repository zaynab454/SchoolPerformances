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
        Schema::create('annee_scolaire', function (Blueprint $table) {
            $table->id('id_annee');
            $table->string('annee_scolaire', 20)->unique();
            $table->boolean('est_courante')->default(false);
            $table->unique('est_courante');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annee_scolaire');
    }
};
