<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing (WARNING: data loss)
        Schema::dropIfExists('graduate_educations');

        // Recreate with new structure
        Schema::create('graduate_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('graduate_id')->constrained('graduates')->cascadeOnDelete();

            // FK to educations (levels), optional so free-text can be stored in level_of_education
            $table->foreignId('education_id')->nullable()->constrained('educations')->nullOnDelete();

            $table->string('level_of_education')->nullable();
            
            // Program is optional (mainly for higher education)
            $table->string('program')->nullable();

            // School name (free-text)
            $table->string('school_name');

            // Dates
            $table->date('start_date');
            $table->date('end_date')->nullable();

            // Current flag
            $table->boolean('is_current')->default(false);

            // Extra info
            $table->text('description')->nullable();
            $table->text('achievement')->nullable();

            $table->timestamps();

            // Helpful indexes
            $table->index(['graduate_id', 'is_current']);
            $table->index(['education_id']);
            $table->index(['start_date', 'end_date']);
        });

        try {
            DB::statement("
                ALTER TABLE graduate_educations
                ADD COLUMN current_guard BIGINT GENERATED ALWAYS AS (
                    CASE WHEN is_current = 1 THEN graduate_id ELSE NULL END
                ) VIRTUAL
            ");
            Schema::table('graduate_educations', function (Blueprint $table) {
                $table->unique('current_guard', 'ge_current_guard_unique');
            });
        } catch (\Throwable $e) {
            // Ignore on MySQL < 8 or incompatible engines
        }

        // Optional CHECK: end_date >= start_date
        try {
            DB::statement("
                ALTER TABLE graduate_educations
                ADD CONSTRAINT ge_dates_chk CHECK (
                    end_date IS NULL OR end_date >= start_date
                )
            ");
        } catch (\Throwable $e) {
            // Ignore if not supported
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduate_educations');
    }
};
