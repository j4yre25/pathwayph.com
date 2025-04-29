<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAchievementsTable extends Migration
{
    public function up()
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('graduate_achievement_title');
            $table->string('graduate_achievement_issuer');
            $table->date('graduate_achievement_date');
            $table->text('graduate_achievement_description')->nullable();
            $table->string('graduate_achievement_url')->nullable();
            $table->string('graduate_achievement _type')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('achievements');
    }
}