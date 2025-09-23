<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('graduate_experiences', function (Blueprint $table) {
            // Remove foreign keys and id columns if they exist
            if (Schema::hasColumn('graduate_experiences', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }
            if (Schema::hasColumn('graduate_experiences', 'location_id')) {
                $table->dropForeign(['location_id']);
                $table->dropColumn('location_id');
            }
            // Add company_name and address columns if not present
            if (!Schema::hasColumn('graduate_experiences', 'company_name')) {
                $table->string('company_name')->nullable()->after('graduate_id');
            }
            if (!Schema::hasColumn('graduate_experiences', 'address')) {
                $table->string('address')->nullable()->after('company_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('graduate_experiences', function (Blueprint $table) {
            // Restore columns (add as nullable, you may want to adjust types)
            if (!Schema::hasColumn('graduate_experiences', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('graduate_id');
            }
            if (!Schema::hasColumn('graduate_experiences', 'location_id')) {
                $table->unsignedBigInteger('location_id')->nullable()->after('company_id');
            }
            // Remove new columns
            if (Schema::hasColumn('graduate_experiences', 'company_name')) {
                $table->dropColumn('company_name');
            }
            if (Schema::hasColumn('graduate_experiences', 'address')) {
                $table->dropColumn('address');
            }
        });
    }
};
