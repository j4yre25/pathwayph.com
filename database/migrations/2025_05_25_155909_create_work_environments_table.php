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
        Schema::create('work_environments', function (Blueprint $table) {
            $table->id();
            $table->enum('environment_type', ['Remote', 'Hybrid', 'On-site']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('work_environments');
    }
};
