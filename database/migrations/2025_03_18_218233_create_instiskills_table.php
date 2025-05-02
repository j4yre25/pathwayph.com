<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('instiskills', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

        });

        Schema::create('program_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instiskill_id')->constrained()->onDelete('cascade');
            $table->foreignId('program_id')->constrained()->onDelete('cascade');
        });

        Schema::create('career_opportunity_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instiskill_id')->constrained()->onDelete('cascade');
            $table->foreignId('career_opportunity_id')->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_opportunity_skill');
        Schema::dropIfExists('program_skill');
        Schema::dropIfExists('instiskills');
    }
};

