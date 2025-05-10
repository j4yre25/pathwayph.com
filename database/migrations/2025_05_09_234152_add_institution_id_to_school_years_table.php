<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('school_years', function (Blueprint $table) {
            $table->unsignedBigInteger('institution_id')->nullable()->after('id'); // Add the institution_id column
            $table->foreign('institution_id')->references('id')->on('institutions')->onDelete('cascade'); // Add foreign key constraint if applicable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('school_years', function (Blueprint $table) {
            $table->dropForeign(['institution_id']);
            $table->dropColumn('institution_id');
        });
    }
};
