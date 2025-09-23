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
        if (!Schema::hasTable('educations')) {
            Schema::create('educations', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->unsignedSmallInteger('order_rank'); // 1 = highest, lower number = higher priority
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('educations');
    }
};
