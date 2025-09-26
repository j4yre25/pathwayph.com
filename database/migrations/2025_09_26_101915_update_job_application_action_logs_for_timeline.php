<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('job_application_action_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('job_application_action_logs','event')) {
                $table->string('event')->nullable()->after('action_key');
            }
            if (!Schema::hasColumn('job_application_action_logs','payload')) {
                $table->json('payload')->nullable()->after('event');
            }
            $table->index('job_application_id', 'jaal_app_idx');
            $table->index('action_key', 'jaal_key_idx');
            $table->index(['job_application_id','created_at'], 'jaal_app_created_idx');
        });
    }
    public function down()
    {
        Schema::table('job_application_action_logs', function (Blueprint $table) {
            $table->dropIndex('jaal_app_idx');
            $table->dropIndex('jaal_key_idx');
            $table->dropIndex('jaal_app_created_idx');
            // keep columns
        });
    }
};
