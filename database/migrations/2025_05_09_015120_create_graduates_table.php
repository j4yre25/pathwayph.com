<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('graduates', function (Blueprint $table) {
            $table->bigIncrements('id'); // Primary key
            $table->unsignedBigInteger('user_id')->index(); // Links to `users` table
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_initial')->nullable();
            $table->unsignedBigInteger('institution_id')->index(); // Links to `institutions` table
            $table->unsignedBigInteger('program_id')->index(); // Links to `programs` table
            $table->unsignedBigInteger('school_year_id')->nullable()->index(); // Links to `school_years` table
            $table->enum('employment_status', ['Employed', 'Underemployed', 'Unemployed']);
            $table->string('current_job_title')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys for integrity
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('program_id')->references('id')->on('programs')->onDelete('cascade');
            $table->foreign('school_year_id')->references('id')->on('school_years')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('graduates');
    }
};
