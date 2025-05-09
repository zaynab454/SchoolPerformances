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
        Schema::create('resultat_eleve', function (Blueprint $table) {
            $table->id('id_resultat');
            $table->string('code_eleve', 50);
            $table->foreign('code_eleve')->references('code_eleve')->on('eleve');
            $table->unsignedBigInteger('id_matiere');
            $table->foreign('id_matiere')->references('id_matiere')->on('matiere')->onDelete('cascade');
            $table->string('annee_scolaire', 20);
            $table->string('session', 20);
            
            // Notes détaillées par matière
            $table->decimal('MoyenNoteCC', 5, 2)->comment('Moyenne des notes de CC pour cette matière uniquement');
            $table->decimal('MoyenExamenNote', 5, 2)->comment('Note d\'examen pour cette matière uniquement');
            
            // Moyennes générales
            $table->decimal('MoyenCC', 5, 2)->comment('Moyenne générale de CC toutes matières confondues');
            $table->decimal('MoyenExam', 5, 2)->comment('Moyenne générale des examens toutes matières');
            $table->decimal('MoyenSession', 5, 2)->comment('Résultat global');
            
            $table->unique(['code_eleve', 'id_matiere', 'annee_scolaire', 'session'], 'resultat_eleve_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resultat_eleve');
    }
};
