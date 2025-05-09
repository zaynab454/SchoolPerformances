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
        Schema::create('secteur_scolaire', function (Blueprint $table) {
            $table->id('id_secteur');
            $table->string('nom_secteur', 255);
            $table->string('code_etab', 10)->nullable();
            $table->foreign('code_etab')->references('code_etab')->on('etablissement')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secteur_scolaire');
    }
};
