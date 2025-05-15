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
         Schema::create('institution_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_id')->constrained()->onDelete('cascade'); // Reference to global skills
            $table->foreignId('career_opportunity_id')->constrained()->onDelete('cascade'); // Reference to a career opportunity
            $table->foreignId('institution_id')->constrained('users')->onDelete('cascade'); // User = institution
            $table->foreignId('program_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Prevent duplicates: same skill mapped to same career_opportunity and institution more than once
            $table->unique(['skill_id', 'career_opportunity_id', 'program_id','institution_id'], 'unique_skill_mapping');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institution_skills');
    }
};
