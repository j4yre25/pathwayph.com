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
        Schema::create('education', function (Blueprint $table) {
            $table->string('graduate_education_institution_id')->nullable();
            $table->string('graduate_education_program')->nullable();
            $table->string('graduate_education_field_of_study')->nullable();
            $table->date('graduate_education_start_date')->nullable();
            $table->date('graduate_education_end_date')->nullable();
            $table->text('graduate_education_description')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('achievements')->nullable();
            $table->boolean('no_achievements')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education', function (Blueprint $table) {
            $table->dropColumn([
                'graduate_education_institution_id',
                'graduate_education_program',
                'graduate_education_field_of_study',
                'graduate_education_start_date',
                'graduate_education_end_date',
                'graduate_education_description',
                'is_current',
                'achievements',
                'no_achievements',
            ]);
        });
    }
};