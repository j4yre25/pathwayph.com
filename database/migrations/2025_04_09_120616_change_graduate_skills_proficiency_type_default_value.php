<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeGraduateSkillsProficiencyTypeDefaultValue extends Migration
{
    public function up()
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->string('graduate_skills_proficiency_type')->default('NONE')->change();
        });
    }

    public function down()
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->string('graduate_skills_proficiency_type')->nullable()->change();
        });
    }
}