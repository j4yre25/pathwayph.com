<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmploymentPreferencesTable extends Migration
{
    public function up()
    {
        Schema::create('employment_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('jobTypes')->nullable();
            $table->decimal('salaryExpectations', 10, 2)->nullable();
            $table->text('preferredLocations')->nullable();
            $table->text('workEnvironment')->nullable();
            $table->string('availability')->nullable();
            $table->text('additionalNotes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employment_preferences');
    }
}