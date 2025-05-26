<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->dropForeign(['career_opportunity_id']);
            $table->dropColumn('career_opportunity_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->unsignedBigInteger('career_opportunity_id')->nullable();
            $table->foreign('career_opportunity_id')->references('id')->on('career_opportunities')->onDelete('cascade');
        });
    }
};
