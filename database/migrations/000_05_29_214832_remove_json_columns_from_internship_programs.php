<?php
// database/migrations/2025_05_30_000004_remove_json_columns_from_internship_programs.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('internship_programs', function (Blueprint $table) {
            // Drop foreign keys first
            if (Schema::hasColumn('internship_programs', 'program_id')) {
                $table->dropForeign(['program_id']);
                $table->dropColumn('program_id');
            }
            if (Schema::hasColumn('internship_programs', 'career_opportunity_id')) {
                $table->dropForeign(['career_opportunity_id']);
                $table->dropColumn('career_opportunity_id');
            }
            if (Schema::hasColumn('internship_programs', 'skill_id')) {
                $table->dropColumn('skill_id');
            }
        });
    }
    public function down(): void
    {
        Schema::table('internship_programs', function (Blueprint $table) {
            $table->json('program_id')->nullable();
            $table->json('career_opportunity_id')->nullable();
            $table->json('skill_id')->nullable();
        });
    }
};