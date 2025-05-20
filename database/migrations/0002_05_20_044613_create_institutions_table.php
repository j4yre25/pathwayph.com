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
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('institution_name');
            $table->text('description')->nullable();
            $table->string('contact_number');
            $table->string('email');
            $table->string('address');
            $table->string('website')->nullable();
            $table->string('institution_address');
            $table->string('institution_type');
            $table->string('telephone_number');
            $table->string('institution_president_first_name');
            $table->string('institution_president_last_name');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
