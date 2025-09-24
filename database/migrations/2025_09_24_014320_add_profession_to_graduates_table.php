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
        Schema::table('graduates', function (Blueprint $table) {
            $table->string('profession')->nullable()->after('employment_status');
        });
    }

    public function down()
    {
        Schema::table('graduates', function (Blueprint $table) {
            $table->dropColumn('profession');
        });
    }
};
