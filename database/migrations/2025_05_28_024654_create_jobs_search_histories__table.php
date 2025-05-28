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
        Schema::create('job_search_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('graduate_id');
            $table->string('keywords');
            $table->timestamps();

            $table->foreign('graduate_id')->references('id')->on('graduates')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_search_histories');
    }
};
