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
        Schema::table('graduate_experiences', function (Blueprint $table) {
            if (!Schema::hasColumn('graduate_experiences', 'is_current')) {
            $table->boolean('is_current')->default(false)->after('end_date');
        }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('graduate_experiences', function (Blueprint $table) {
                if (Schema::hasColumn('graduate_experiences', 'is_current')) {
                $table->dropColumn('is_current');
            }
        });
    }
};
