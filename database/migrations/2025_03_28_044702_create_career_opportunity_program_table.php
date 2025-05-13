<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('career_opportunity_program', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_opportunity_id')->constrained()->onDelete('cascade');
            $table->foreignId('program_id')->constrained()->onDelete('cascade');
            $table->unique(['career_opportunity_id', 'program_id'], 'unique_career_opportunity_program'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('career_opportunity_program');
    }
};
