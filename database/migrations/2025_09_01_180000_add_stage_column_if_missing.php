<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('job_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('job_applications','stage')) {
                $table->string('stage')->default('applied')->index()->after('status');
            }
        });
    }
    public function down(): void {
        Schema::table('job_applications', function (Blueprint $table) {
            if (Schema::hasColumn('job_applications','stage')) {
                $table->dropColumn('stage');
            }
        });
    }
};