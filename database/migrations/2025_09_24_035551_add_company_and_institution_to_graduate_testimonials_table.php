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
        Schema::table('graduate_testimonials', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('graduate_id');
            $table->unsignedBigInteger('institution_id')->nullable()->after('company_id');
            $table->string('company_name')->nullable()->after('company_id');
            $table->string('institution_name')->nullable()->after('institution_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('graduate_testimonials', function (Blueprint $table) {
            //
        });
    }
};
