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
        Schema::create('referral_exports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('graduate_id');
            $table->unsignedBigInteger('job_invitation_id')->nullable();
            $table->string('certificate_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_exports');
    }
};
