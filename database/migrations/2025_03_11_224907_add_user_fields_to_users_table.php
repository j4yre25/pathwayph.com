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
            // Graduate-specific fields
         
            $table->string('graduate_first_name');
            $table->string('graduate_last_name');
            $table->string('graduate_school_graduated_from')->nullable();
            $table->string('graduate_program_completed')->nullable();
            $table->integer('graduate_year_graduated')->nullable(); // Use date type for year graduated
            $table->string('graduate_skills')->nullable();

            // Company-specific fields
            $table->string('company_name')->nullable();
            $table->string('company_address')->nullable();
            $table->string('company_sector')->nullable();
            $table->string('company_category')->nullable();
            $table->string('company_email')->nullable()->unique();;
            $table->string('company_contact_number')->nullable();
            $table->string('company_hr_last_name')->nullable();
            $table->string('company_hr_first_name')->nullable();
            $table->string('company_hr_middle_initial')->nullable();

            // Institution-specific fields
            $table->string('institution_type')->nullable();
            $table->string('institution_address')->nullable();
            $table->string('institution_email')->nullable()->unique();
            $table->string('institution_contact_number')->nullable();
            $table->string('institution_president_last_name')->nullable();
            $table->string('institution_president_first_name')->nullable();
            $table->string('institution_career_officer_first_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'graduate_school_graduated_from',
                'graduate_program_completed',
                'graduate_year_graduated',
                'graduate_skills',
                'company_name',
                'company_address',
                'company_sector',
                'company_category',
                'company_email',
                'company_contact_number',
                'company_hr_last_name',
                'company_hr_first_name',
                'company_hr_middle_initial',
                'institution_type',
                'institution_address',
                'institution_email',
                'institution_contact_number',
                'institution_president_last_name',
                'institution_president_first_name',
                'institution_career_officer_first_name',
            ]);
        });
    }
};
