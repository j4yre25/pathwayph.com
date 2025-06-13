<?php
// Run: php artisan make:migration add_company_fields_to_graduates_table

// database/migrations/xxxx_xx_xx_add_company_fields_to_graduates_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('graduates', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('not_company')->nullable()->constrained('not_companies')->nullOnDelete();
            $table->foreignId('sector_id')->nullable()->constrained('sectors')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('graduates', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
            $table->dropForeign(['not_company']);
            $table->dropColumn('not_company');
            $table->dropForeign(['sector_id']);
            $table->dropColumn('sector_id');
        });
    }
};