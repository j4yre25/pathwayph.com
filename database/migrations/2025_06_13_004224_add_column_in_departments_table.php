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
        Schema::table('departments', function (Blueprint $table) {
            $table->unsignedBigInteger('human_resources_id')->after('id')->nullable();
            $table->foreign('human_resources_id')
                  ->references('id')
                  ->on('human_resources')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['human_resources_id']);
            $table->dropColumn('human_resources_id');
        });
    }
};
