<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('job_offers', function (Blueprint $table) {
            if (Schema::hasColumn('job_offers','offer_letter_path')) {
                $table->dropColumn('offer_letter_path');
            }
            if (!Schema::hasColumn('job_offers','file_path')) {
                $table->string('file_path')->nullable();
            } else {
                $table->string('file_path')->nullable()->change();
            }
            if (!Schema::hasColumn('job_offers','status')) {
                $table->string('status')->default('sent');
            } else {
                $table->string('status')->default('sent')->change();
            }
            if (!Schema::hasColumn('job_offers','responded_at')) {
                $table->timestamp('responded_at')->nullable();
            }
            // Ensure body is text & nullable
            if (Schema::hasColumn('job_offers','body')) {
                $table->text('body')->nullable()->change();
            } else {
                $table->text('body')->nullable();
            }
        });
    }

    public function down(): void {
        Schema::table('job_offers', function (Blueprint $table) {
            // Cannot reliably restore dropped column; skip.
        });
    }
};