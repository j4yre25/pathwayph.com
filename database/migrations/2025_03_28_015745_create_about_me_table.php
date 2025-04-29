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
        Schema::create('about_me', function (Blueprint $table) {
            $table->id(); // Creates an auto-incrementing primary key
            $table->string('user_first_name'); // First name as a string
            $table->string('user_middle_initial')->nullable(); // Middle initial as a string, nullable
            $table->string('user_last_name'); // Last name as a string
            $table->string('professional_title')->nullable(); // Professional title as a string, nullable
            $table->timestamps(); // Created at and updated at timestamps
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_me');
    }
};

