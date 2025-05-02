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
        Schema::table('jobs', function (Blueprint $table) {
            $table->text('job_benefits')->nullable()->after('requirements');
            $table->date('expiration_date')->nullable()->after('archived_at');
            $table->integer('applicants_limit')->nullable()->after('expiration_date');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('job_benefits');
            $table->dropColumn('expiration_date');
            $table->dropColumn('applicants_limit');
        });
    }
};
