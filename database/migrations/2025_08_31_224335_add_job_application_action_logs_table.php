<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('job_application_action_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_application_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action_key');          
            $table->string('event')->nullable();   
            $table->json('payload')->nullable();   
            $table->timestamps();

            $table->index('job_application_id');
        });
    }
    public function down(): void {
        Schema::dropIfExists('job_application_action_logs');
    }
};