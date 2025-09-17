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
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'bir_tin')) {
                $table->string('bir_tin', 30)->nullable()->after('sector_id');
            }
            if (!Schema::hasColumn('companies', 'verification_file_path')) {
                $table->string('verification_file_path')->nullable()->after('sector_id');
            }
            if (!Schema::hasColumn('companies', 'company_logo_path')) {
                $table->string('company_logo_path')->nullable()->after('verification_file_path');
            }
            if (!Schema::hasColumn('companies', 'company_profile_photo_path')) {
                $table->string('company_profile_photo_path')->nullable()->after('company_logo_path');
            }
            if (!Schema::hasColumn('companies', 'company_cover_photo_path')) {
                $table->string('company_cover_photo_path')->nullable()->after('company_profile_photo_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'company_cover_photo_path')) {
                $table->dropColumn('company_cover_photo_path');
            }
            if (Schema::hasColumn('companies', 'company_profile_photo_path')) {
                $table->dropColumn('company_profile_photo_path');
            }
            if (Schema::hasColumn('companies', 'company_logo_path')) {
                $table->dropColumn('company_logo_path');
            }
            if (Schema::hasColumn('companies', 'verification_file_path')) {
                $table->dropColumn('verification_file_path');
            }
            if (Schema::hasColumn('companies', 'bir_tin')) {
                $table->dropColumn('bir_tin');
            }
        });
    }
};
