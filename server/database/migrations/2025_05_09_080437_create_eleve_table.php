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
        Schema::create('eleve', function (Blueprint $table) {
            $table->string('code_eleve', 50)->primary();
            $table->string('nom_eleve_ar', 100);
            $table->string('prenom_eleve_ar', 100);
            $table->string('code_etab', 20);
            $table->foreign('code_etab')->references('code_etab')->on('etablissement')->onDelete('cascade');
            $table->string('code_niveau', 10);
            $table->foreign('code_niveau')->references('code_niveau')->on('niveau_scolaire');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eleve');
    }
};
