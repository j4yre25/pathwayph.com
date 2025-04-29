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
        Schema::table('users', function (Blueprint $table) {
            $table->string('graduate_location')->nullable();
            $table->string('graduate_ethnicity')->nullable();
            $table->string('graduate_address')->nullable();
            $table->text('graduate_about_me')->nullable();
            $table->string('graduate_picture_url')->default('path/to/default/image.jpg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
