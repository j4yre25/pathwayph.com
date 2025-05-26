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
            $table->dropColumn([
                'posted_by',
                'job_title',
                'description',
                'location',
                'branch_location',
                'vacancy',
                'job_type',
                'experience_level',
                'skills',
                'requirements',
                'is_approved',
                'deleted_at',
                'expiration_date',
                'applicants_limit',
                'min_salary',
                'max_salary',
                'is_salary_negotiable',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('posted_by')->nullable();
            $table->string('job_title');
            $table->text('description');
            $table->string('location')->nullable();
            $table->string('branch_location')->nullable();
            $table->string('vacancy');
            $table->string('job_type');
            $table->string('experience_level');
            $table->string('skills');
            $table->text('requirements')->nullable();
            $table->boolean('is_approved')->nullable();
            $table->softDeletes();
            $table->date('expiration_date')->nullable();
            $table->integer('applicants_limit')->nullable();
            $table->unsignedInteger('min_salary')->nullable();
            $table->unsignedInteger('max_salary')->nullable();
            $table->boolean('is_salary_negotiable')->default(0);
        });
    }
};
