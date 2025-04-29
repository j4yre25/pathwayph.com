<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateExperiencesTable extends Migration
{
    public function up()
    {
        Schema::table('experiences', function (Blueprint $table) {
            // Add the employment type column
            $table->string('graduate_experience_employment_type')->nullable();
            // Add any other columns you need
        });
    }

    public function down()
    {
        Schema::table('experiences', function (Blueprint $table) {
            // Drop the employment type column
            $table->dropColumn('graduate_experience_employment_type');
            // Drop any other columns you added
        });
    }
}