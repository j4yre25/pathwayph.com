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
        Schema::table('graduate_educations', function (Blueprint $table) {
            if (!Schema::hasColumn('graduate_educations', 'school_year')) {
                $table->string('school_year')->nullable()->after('end_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('graduate_educations', function (Blueprint $table) {
            if (Schema::hasColumn('graduate_educations', 'school_year')) {
                $table->dropColumn('school_year');
            }
        });
    }
};
