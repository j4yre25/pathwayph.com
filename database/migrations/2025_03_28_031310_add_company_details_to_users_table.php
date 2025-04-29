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
        Schema::table('users', function (Blueprint $table) {
            $table->string('company_street_address')->after('company_name');
            $table->string('company_brgy')->after('company_street_address');
            $table->string('company_city')->after('company_brgy');
            $table->string('company_province')->after('company_city');
            $table->string('company_zip_code')->after('company_province');
            $table->string('company_email')->after('company_zip_code');
            $table->string('company_telephone_number')->nullable()->after('company_email');
            $table->string('company_hr_gender')->nullable(); 
            $table->string('company_hr_address')->nullable(); 
            $table->date('company_hr_dob')->nullable()->after('company_hr_gender');
            $table->string('company_hr_contact_number')->after('company_hr_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'company_address',
                'company_tellNum',
                'company_hr_birthday',
                'hr_contact_number',
            ]);
        });
    }
};
