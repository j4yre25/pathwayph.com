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
        Schema::table('graduates', function ($table) {
        $table->float('alignment_score')->nullable()->after('current_job_title');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('graduates', function ($table) {
        $table->dropColumn('alignment_score');
    });
    }
};
