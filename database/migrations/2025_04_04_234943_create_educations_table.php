<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEducationsTable extends Migration
{
    public function up()
    {
        Schema::create('educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('graduate_education_institution_id');
            $table->string('graduate_education_program');
            $table->string('graduate_education_field_of_study');
            $table->date('graduate_education_start_date');
            $table->date('graduate_education_end_date')->nullable();
            $table->text('graduate_education_description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('educations');
    }
}