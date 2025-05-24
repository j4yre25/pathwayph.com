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
        Schema::table('jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('peso_id')->nullable()->after('user_id');
            $table->foreign('peso_id')->references('id')->on('peso')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropForeign(['peso_id']);
            $table->dropColumn('peso_id');
        });
    }
};
