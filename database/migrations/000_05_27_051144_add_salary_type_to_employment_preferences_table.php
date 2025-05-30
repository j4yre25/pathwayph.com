<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employment_preferences', function (Blueprint $table) {
            $table->string('salary_type')->nullable()->after('salary_id'); // You can remove `nullable()` if it's required
        });
    }

    public function down(): void
    {
        Schema::table('employment_preferences', function (Blueprint $table) {
            $table->dropColumn('salary_type');
        });
    }
};
