<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('school_year_range'); // e.g., "2020-2024"
            $table->string('term'); // e.g., "1st Semester"
            $table->foreignId('program_id')->constrained()->onDelete('cascade'); // Foreign key to programs
            $table->text('career_opportunities')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};


