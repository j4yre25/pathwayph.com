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
    Schema::table('graduates', function (Blueprint $table) {
        $table->unsignedBigInteger('institution_id')->nullable()->after('id');
        $table->foreign('institution_id')->references('id')->on('institutions')->onDelete('set null');
    });
}
public function down()
{
    Schema::table('graduates', function (Blueprint $table) {
        $table->dropColumn('institution_id');
    });
}
};
