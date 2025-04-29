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
            Schema::table('users', function (Blueprint $table) {
                $table->string('graduate_first_name')->nullable()->change();
                $table->string('graduate_last_name')->nullable()->change();
                $table->string('graduate_program_completed')->nullable()->change();
                $table->integer('graduate_year_graduated')->nullable()->change();
                $table->text('graduate_skills')->nullable()->change();
                $table->string('company_name')->nullable()->change();
                $table->string('company_address')->nullable()->change();
                $table->string('company_sector')->nullable()->change();
                $table->string('company_category')->nullable()->change();
                $table->string('company_contact_number')->nullable()->change();
                $table->string('company_hr_last_name')->nullable()->change();
                $table->string('company_hr_first_name')->nullable()->change();
                $table->string('company_hr_middle_initial')->nullable()->change();
                $table->string('institution_type')->nullable()->change();
                $table->string('institution_address')->nullable()->change();
                $table->string('institution_contact_number')->nullable()->change();
                $table->string('institution_president_last_name')->nullable()->change();
                $table->string('institution_president_first_name')->nullable()->change();
                $table->string('institution_career_officer_first_name')->nullable()->change();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('graduate_first_name')->nullable(false)->change();
        $table->string('graduate_last_name')->nullable(false)->change();
        });
    }
};
