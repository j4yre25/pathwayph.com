<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('resumes', function (Blueprint $table) {
            $table->unsignedBigInteger('graduate_id')->after('id');
            $table->foreign('graduate_id')->references('id')->on('graduates')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('resumes', function (Blueprint $table) {
            $table->dropForeign(['graduate_id']);
            $table->dropColumn('graduate_id');
        });
    }
};