<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSkillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('skills', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('skills', 'graduate_skills_proficiency_type')) {
                $table->string('graduate_skills_proficiency_type')->after('graduate_skills_name'); // Add proficiency type
            }

            if (!Schema::hasColumn('skills', 'graduate_skills_type')) {
                $table->string('graduate_skills_type')->after('graduate_skills_proficiency_type'); // Add skill type
            }

            if (!Schema::hasColumn('skills', 'graduate_skills_years_experience')) {
                $table->integer('graduate_skills_years_experience')->after('graduate_skills_type'); // Add years of experience
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('skills', function (Blueprint $table) {
            // Drop the columns if they exist
            if (Schema::hasColumn('skills', 'graduate_skills_proficiency_type')) {
                $table->dropColumn('graduate_skills_proficiency_type');
            }

            if (Schema::hasColumn('skills', 'graduate_skills_type')) {
                $table->dropColumn('graduate_skills_type');
            }

            if (Schema::hasColumn('skills', 'graduate_skills_years_experience')) {
                $table->dropColumn('graduate_skills_years_experience');
            }
        });
    }
}
