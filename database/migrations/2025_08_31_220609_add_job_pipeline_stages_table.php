<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::dropIfExists('job_pipeline_stages');

        Schema::create('job_pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->string('owner_type')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('slug');          // applied, screening, etc.
            $table->string('name');          // Display label
            $table->unsignedInteger('position'); // 1..n
            $table->boolean('is_terminal')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['owner_type','owner_id','slug']);
            $table->index(['owner_type','owner_id','position']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('job_pipeline_stages');
    }
};