<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobInvitationsTable extends Migration
{
    public function up()
    {
        Schema::create('job_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade'); // foreign key to jobs
            $table->foreignId('graduate_id')->constrained('users')->onDelete('cascade'); // foreign key to graduates (users table)
            $table->foreignId('company_id')->constrained('users')->onDelete('cascade'); // foreign key to company (users table)
            $table->string('status')->default('pending'); // e.g., 'pending', 'accepted', 'declined'
            $table->text('message')->nullable(); // Optional message for the invitation
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_invitations');
    }
}
