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
        Schema::create('graduates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('current_job_title');
            $table->enum('employment_status', ['Employed', 'Underemployed', 'Unemployed']);
            $table->string('contact_number');
            $table->string('location')->nullable();
            $table->string('ethnicity')->nullable();
            $table->string('address')->nullable();
            $table->text('about_me')->nullable();
            $table->foreignId('institution_id')->constrained('institutions')->onDelete('cascade');
            $table->foreignId('degree_id')->constrained('degrees')->onDelete('cascade');
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('school_year_id')->constrained('school_years')->onDelete('cascade');
            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('personal_website')->nullable();
            $table->text('other_social_links')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduates');
    }
};
