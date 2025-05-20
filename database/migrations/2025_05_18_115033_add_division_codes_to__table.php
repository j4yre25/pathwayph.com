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
        Schema::table('sectors', function (Blueprint $table) {
            if (!Schema::hasColumn('sectors', 'division_codes')) {
                $table->string('division_codes')->unique();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sectors', function (Blueprint $table) {
            $table->dropUnique(['division_codes']);
            $table->dropColumn('division_codes');
        });
    }
};
