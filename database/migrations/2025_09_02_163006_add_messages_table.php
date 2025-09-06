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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            
            // Link to job application
            $table->foreignId('application_id')
                  ->constrained('job_applications')
                  ->onDelete('cascade');

            // Sender (HR / company user)
            $table->foreignId('sender_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Receiver (graduate user)
            $table->foreignId('receiver_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Type of message (e.g., request info, general message)
            $table->enum('message_type', ['request_info', 'general'])
                  ->default('general');

            // Message content
            $table->text('content');

            // Read status
            $table->enum('status', ['unread', 'read'])
                  ->default('unread');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
