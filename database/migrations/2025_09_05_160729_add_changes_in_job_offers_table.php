<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('job_offers', function (Blueprint $table) {
            if (!Schema::hasColumn('job_offers','job_title')) {
                $table->string('job_title')->nullable()->after('job_application_id');
            }
            if (!Schema::hasColumn('job_offers','message_id')) {
                $table->unsignedBigInteger('message_id')->nullable()->after('job_title');
            }
            if (!Schema::hasColumn('job_offers','body')) {
                $table->text('body')->nullable()->after('message_id');
            }
            if (!Schema::hasColumn('job_offers','file_path')) {
                $table->string('file_path')->nullable()->after('body');
            }
            if (!Schema::hasColumn('job_offers','status')) {
                $table->string('status')->default('sent')->after('start_date');
            }
            if (!Schema::hasColumn('job_offers','responded_at')) {
                $table->timestamp('responded_at')->nullable()->after('status');
            }
            if (Schema::hasColumn('job_offers','offer_letter_path') && !Schema::hasColumn('job_offers','file_path')) {
                // (If you want to rename instead of add new, install doctrine/dbal and rename. Here we just keep both if already added.)
            }
        });

        // Add FK separately to avoid issues if messages table not ready earlier
        Schema::table('job_offers', function (Blueprint $table) {
            if (Schema::hasColumn('job_offers','message_id')) {
                try {
                    $table->foreign('message_id')->references('id')->on('messages')->nullOnDelete();
                } catch (\Throwable $e) {
                    // ignore if already exists
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('job_offers', function (Blueprint $table) {
            // Only safe removals (optional)
            if (Schema::hasColumn('job_offers','job_title'))     $table->dropColumn('job_title');
            if (Schema::hasColumn('job_offers','message_id')) {
                try { $table->dropForeign(['message_id']); } catch (\Throwable $e) {}
                $table->dropColumn('message_id');
            }
            if (Schema::hasColumn('job_offers','body'))          $table->dropColumn('body');
            if (Schema::hasColumn('job_offers','file_path'))     $table->dropColumn('file_path');
            if (Schema::hasColumn('job_offers','offered_salary'))$table->dropColumn('offered_salary');
            if (Schema::hasColumn('job_offers','status'))        $table->dropColumn('status');
            if (Schema::hasColumn('job_offers','responded_at'))  $table->dropColumn('responded_at');
        });
    }
};
