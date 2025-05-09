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
        Schema::create('etablissement', function (Blueprint $table) {
            $table->string('code_etab', 10)->primary();
            $table->string('nom_etab_fr', 255);
            $table->string('nom_etab_ar', 255);
            $table->string('code_commune', 10);
            $table->foreign('code_commune')->references('cd_com')->on('commune')->onDelete('cascade');
            $table->string('cycle', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etablissement');
    }
};
