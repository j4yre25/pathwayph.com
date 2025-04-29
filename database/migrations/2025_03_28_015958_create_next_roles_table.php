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
        Schema::create('next_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('availability')->nullable();
            $table->string('work_type')->nullable();
            $table->json('locations')->nullable();
            $table->string('right_to_work')->nullable();
            $table->json('salary_expectation')->nullable();
            $table->string('sectors')->nullable();
            $table->json('job_preferences')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('next_roles');
    }
};

