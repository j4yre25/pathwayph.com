<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateGraduateEducationEndDateColumnType extends Migration
{
    public function up()
    {
        Schema::table('educations', function (Blueprint $table) {
            $table->string('graduate_education_end_date')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('educations', function (Blueprint $table) {
            $table->date('graduate_education_end_date')->nullable()->change();
        });
    }
}
