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
        Schema::create('projects', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('graduate_projects_title'); 
            $table->text('graduate_projects_description');
            $table->string('graduate_projects_role');
            $table->date('graduate_projects_start_date');
            $table->date('graduate_projects_end_date')->nullable();
            $table->string('graduate_projects_url')->nullable();
            $table->json('graduate_projects_tech')->nullable();
            $table->text('graduate_projects_key_accomplishments')->nullable();
            $table->unsignedBigInteger('user_id'); 
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
