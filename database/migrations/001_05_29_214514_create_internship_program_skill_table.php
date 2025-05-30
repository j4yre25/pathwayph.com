<?php
// database/migrations/2025_05_30_000003_create_internship_program_skill_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('internship_program_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_program_id')->constrained()->onDelete('cascade');
            $table->foreignId('skill_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('internship_program_skill');
    }
};