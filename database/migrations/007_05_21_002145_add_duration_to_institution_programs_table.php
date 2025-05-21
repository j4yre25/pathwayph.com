<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('institution_programs', function ($table) {
        $table->string('duration')->nullable()->after('program_code'); // e.g., 'Year' or 'Month'
        $table->integer('duration_time')->nullable()->after('duration'); // e.g., 1-12
    });
}

public function down()
{
    Schema::table('institution_programs', function ($table) {
        $table->dropColumn('duration_time');
        $table->dropColumn('duration');
    });
}
};
