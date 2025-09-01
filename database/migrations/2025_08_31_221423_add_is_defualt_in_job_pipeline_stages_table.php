<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('job_pipeline_stages', function (Blueprint $table) {
            if (!Schema::hasColumn('job_pipeline_stages','is_default')) {
                $table->boolean('is_default')->default(false)->after('active');
            }
        });
    }
    public function down(): void {
        Schema::table('job_pipeline_stages', function (Blueprint $table) {
            if (Schema::hasColumn('job_pipeline_stages','is_default')) {
                $table->dropColumn('is_default');
            }
        });
    }
};