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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('company_email');
            $table->string('company_tel_phone');
            $table->string('company_mobile_phone');
            $table->string('company_street_address');
            $table->string('company_brgy')->nullable();
            $table->string('company_city');
            $table->string('company_province')->nullable();
            $table->string('company_zip_code');
            $table->string('company_description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
