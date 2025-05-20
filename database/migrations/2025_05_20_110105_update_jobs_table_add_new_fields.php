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
         Schema::table('jobs', function (Blueprint $table) {
            $table->string('job_id')->nullable()->after('id'); 
            $table->string('job_code')->nullable()->after('job_id'); 
            $table->enum('status', ['pending', 'open', 'closed'])->default('pending');
            $table->string('posted_by')->nullable()->after('user_id');
            $table->string('job_title');
            $table->text('job_description');
            $table->text('job_requirements');
            $table->integer('job_vacancies')->default(1);
            $table->enum('job_experience_level',['Entry-level','Intermediate','Mid-level','Senior/Executive'])->default('Entry-level');
            $table->enum('job_employment_type', ['Full-time', 'Part-time', 'Contract', 'Freelance', 'Internship'])->default('Full-time');
            $table->enum('job_work_environment', ['Remote', 'Hybrid', 'On-site'])->default('On-site');
            $table->enum('job_salary_type', ['monthly', 'weekly', 'hourly'])->default('monthly');
            $table->unsignedInteger('job_min_salary')->nullable();
            $table->unsignedInteger('job_max_salary')->nullable();
            $table->string('job_location')->nullable();
            $table->date('job_deadline')->nullable();
            $table->integer('job_application_limit')->nullable();
            $table->string('related_skills');
            $table->boolean('is_negotiable')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->dateTime('deleted_at')->nullable()->after('is_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(columns: [
                'job_id',
                'job_code',
                'status',
                'job_title',
                'job_description',
                'job_requirements',
                'job_vacancies',
                'job_employment_type',
                'job_work_environment',
                'job_salary_type',
                'job_min_salary',
                'job_max_salary',
                'job_location',
                'job_deadline',
                'job_application_limit',
                'related_skills',
                'is_negotiable',
                'is_approved',
                'deleted_at',
            ]);
        });
    }
};
