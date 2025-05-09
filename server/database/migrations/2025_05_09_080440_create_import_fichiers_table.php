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
        Schema::create('import_fichiers', function (Blueprint $table) {
            $table->id();
            $table->enum('type_fichier', ['Excel', 'XML']);
            $table->string('nom_fichier', 255);
            $table->timestamp('date_import')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->enum('statut', ['Réussi', 'Échoué'])->default('Réussi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_fichiers');
    }
};
