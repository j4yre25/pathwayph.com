<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificationsTable extends Migration
{
    public function up()
    {
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('graduate_certification_name');
            $table->string('graduate_certification_issuer');
            $table->date('graduate_certification_issue_date');
            $table->date('graduate_certification_expiry_date')->nullable();
            $table->string('graduate_certification_credential_id')->nullable();
            $table->string('graduate_certification_credential_url')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('certifications');
    }
}