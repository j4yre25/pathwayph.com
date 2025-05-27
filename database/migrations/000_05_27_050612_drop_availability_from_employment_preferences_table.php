<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employment_preferences', function (Blueprint $table) {
            $table->dropColumn('availability');
        });
    }

    public function down(): void
    {
        Schema::table('employment_preferences', function (Blueprint $table) {
            $table->string('availability')->nullable(); // adjust type if needed
        });
    }
};