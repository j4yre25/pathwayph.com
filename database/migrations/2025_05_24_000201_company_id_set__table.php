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
            $table->unsignedBigInteger('company_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            // If you want to revert, set NOT NULL (only if safe)
            // $table->unsignedBigInteger('company_id')->nullable(false)->change();
        });
    }
};
