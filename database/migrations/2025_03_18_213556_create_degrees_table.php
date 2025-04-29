<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('degrees', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., "Bachelor's Degree"
            $table->enum('type', ['Bachelor', 'Associate', 'Master', 'Doctoral', 'Diploma']);
            $table->timestamps();
            $table->softDeletes(); // Archiving support
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('degrees');
    }
};

