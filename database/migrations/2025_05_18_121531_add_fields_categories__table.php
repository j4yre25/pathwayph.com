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
       Schema::table('categories', function (Blueprint $table) {
    if (!Schema::hasColumn('categories', 'division_codes')) {
        $table->string('division_codes')->nullable();
    }

    if (!Schema::hasColumn('categories', 'division_code')) {
        $table->string('division_code')->nullable();
    }

    if (!Schema::hasColumn('categories', 'sector_id')) {
        $table->unsignedBigInteger('sector_id')->nullable();
        $table->foreign('sector_id')->references('id')->on('sectors')->onDelete('set null');
    }
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropColumn(['division_codes', 'division_code', 'sector_id']);
        });
    }
};
