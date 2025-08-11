<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('graduate_experiences', function (Blueprint $table) {
        $table->unsignedBigInteger('company_id')->nullable()->after('title');
        $table->unsignedBigInteger('not_company')->nullable()->after('company_id');
        $table->unsignedBigInteger('sector_id')->nullable()->after('not_company');
        $table->dropColumn('company');
        // Add foreign keys if you want:
        $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        $table->foreign('sector_id')->references('id')->on('sectors')->onDelete('set null');
    });
}
public function down()
{
    Schema::table('graduate_experiences', function (Blueprint $table) {
        $table->dropForeign(['company_id']);
        $table->dropForeign(['sector_id']);
        $table->dropColumn(['company_id', 'not_company', 'sector_id']);
        $table->string('company')->nullable();
    });
}
};
