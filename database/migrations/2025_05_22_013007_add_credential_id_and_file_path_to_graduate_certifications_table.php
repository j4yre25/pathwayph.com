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
    Schema::table('graduate_certifications', function ($table) {
        $table->string('credential_id')->nullable()->after('credential_url');
        $table->string('file_path')->nullable()->after('credential_id');
    });
}

public function down()
{
    Schema::table('graduate_certifications', function ($table) {
        $table->dropColumn(['credential_id', 'file_path']);
    });
}
};
