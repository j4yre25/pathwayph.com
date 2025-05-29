<?php
// database/migrations/2025_05_30_000002_create_internship_program_career_opportunity_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('internship_program_career_opportunity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_program_id')->constrained()->onDelete('cascade');
            $table->foreignId('career_opportunity_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('internship_program_career_opportunity');
    }
};