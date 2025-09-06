<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('interview_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('interviewer_id');
            $table->string('interviewer_name')->nullable();
            $table->string('rating')->nullable(); // 1-5 / pass / fail
            $table->text('strengths')->nullable();
            $table->text('weaknesses')->nullable();
            $table->string('recommendation')->nullable(); // move_forward | reject | hold
            $table->timestamps();

            $table->foreign('application_id')->references('id')->on('job_applications')->cascadeOnDelete();
            $table->foreign('interviewer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['application_id','recommendation']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('interview_feedbacks');
    }
};