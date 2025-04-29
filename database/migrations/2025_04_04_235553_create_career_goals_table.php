<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCareerGoalsTable extends Migration
{
    public function up()
    {
        Schema::create('career_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('shortTermGoals')->nullable();
            $table->text('longTermGoals')->nullable();
            $table->text('industriesOfInterest')->nullable();
            $table->text('careerPath')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('career_goals');
    }
}