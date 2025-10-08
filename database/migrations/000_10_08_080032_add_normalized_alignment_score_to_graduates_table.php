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
        Schema::table('graduates', function (Blueprint $table) {
            $table->float('normalized_alignment_score')->nullable()->after('alignment_score');
        });
    }

    public function down(): void
    {
        Schema::table('graduates', function (Blueprint $table) {
            $table->dropColumn('normalized_alignment_score');
        });
    }
};
