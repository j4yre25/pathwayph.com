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
    Schema::create('seminar_requests', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('institution_id');
        $table->string('request_for'); // e.g. 'seminar'
        $table->string('event_name');
        $table->dateTime('scheduled_at');
        $table->text('description')->nullable();
        $table->enum('status', ['pending', 'approved', 'disapproved', 'cancelled'])->default('pending');
        $table->timestamps();

        $table->foreign('institution_id')->references('id')->on('institutions')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seminar_requests');
    }
};
