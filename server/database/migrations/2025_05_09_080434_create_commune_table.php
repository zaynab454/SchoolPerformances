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
        Schema::create('commune', function (Blueprint $table) {
            $table->string('cd_com', 10)->primary();
            $table->string('ll_com', 255);
            $table->string('la_com', 255);
            $table->string('id_province'); 
            $table->foreign('id_province')->references('id_province')->on('province')->onDelete('cascade');
            $table->string('cycle', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commune');
    }
};
