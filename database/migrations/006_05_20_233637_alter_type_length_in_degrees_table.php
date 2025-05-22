<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('degrees', function ($table) {
        $table->string('type', 100)->change();
    });
}

public function down()
{
    Schema::table('degrees', function ($table) {
        $table->string('type', 50)->change(); // or the original length
    });
}
};
