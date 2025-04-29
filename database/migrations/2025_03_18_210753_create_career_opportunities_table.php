<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Title of the career opportunity
            $table->text('description')->nullable(); // Description (optional)
            $table->foreignId('program_id')->constrained()->onDelete('cascade'); // Foreign key to programs
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_opportunities');
    }
};