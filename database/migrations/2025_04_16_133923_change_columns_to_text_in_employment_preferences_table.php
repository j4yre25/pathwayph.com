<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnsToTextInEmploymentPreferencesTable extends Migration
{
    public function up()
    {
        Schema::table('employment_preferences', function (Blueprint $table) {
            $table->text('jobTypes')->change(); // Change to TEXT
            $table->text('salaryExpectations')->change(); // Change to TEXT
            $table->text('preferredLocations')->change(); // Change to TEXT
            $table->text('workEnvironment')->change(); // Change to TEXT
            $table->text('availability')->change(); // Change to TEXT
        });
    }

    public function down()
    {
        Schema::table('employment_preferences', function (Blueprint $table) {
            $table->json('jobTypes')->change(); // Revert to JSON
            $table->json('salaryExpectations')->change(); // Revert to JSON
            $table->json('preferredLocations')->change(); // Revert to JSON
            $table->json('workEnvironment')->change(); // Revert to JSON
            $table->json('availability')->change(); // Revert to JSON
        });
    }
}
