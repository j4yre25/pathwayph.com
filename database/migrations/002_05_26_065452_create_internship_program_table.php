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
        Schema::create('internship_programs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('program_id')->constrained()->onDelete('cascade');
            $table->foreignId('career_opportunity_id')->nullable()->constrained()->onDelete('set null');
            $table->string('skills')->nullable(); // comma-separated skill names or IDs
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('graduate_internship_program', function (Blueprint $table) {
            $table->id();
            $table->foreignId('graduate_id')->constrained()->onDelete('cascade');
            $table->foreignId('internship_program_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduate_internship_program');
        Schema::dropIfExists('internship_programs');
    }
};
