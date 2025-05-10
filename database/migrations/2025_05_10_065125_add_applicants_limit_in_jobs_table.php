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
            $table->integer('applicants_limit')->nullable()->after('expiration_date')->comment('Limit the number of applicants for the job');
            $table->dropColumn('application_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('applicants_limit');
            $table->integer('application_limit')->nullable()->after('expiration_date')->comment('Limit the number of applicants for the job');
        });
    }
};
