<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('institution_skills', function (Blueprint $table) {
            $table->unsignedBigInteger('program_id')->nullable()->after('institution_id');
            // If you want a foreign key constraint, uncomment the next line:
            // $table->foreign('program_id')->references('id')->on('programs')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('institution_skills', function (Blueprint $table) {
            // If you added a foreign key, drop it first:
            // $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
        });
    }
};
