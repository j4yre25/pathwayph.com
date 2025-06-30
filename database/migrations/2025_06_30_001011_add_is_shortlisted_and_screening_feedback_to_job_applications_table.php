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
        Schema::table('job_applications', function (Blueprint $table) {
                    $table->boolean('is_shortlisted')->default(false)->after('status');
                    $table->string('screening_feedback')->nullable()->after('is_shortlisted');
                });    
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['is_shortlisted', 'screening_feedback']);
        });
    }
};
