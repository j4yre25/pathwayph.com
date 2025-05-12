<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('graduates', function (Blueprint $table) {
            $table->id();
            
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_initial')->nullable();

            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('school_year_id')->constrained('school_years')->onDelete('cascade');

            $table->enum('employment_status', ['Employed', 'Underemployed', 'Unemployed'])->default('Unemployed');
            $table->string('current_job_title')->nullable();

            $table->timestamps();
            $table->softDeletes(); // <-- Enable soft deletes for archiving
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('graduates');
    }
};
