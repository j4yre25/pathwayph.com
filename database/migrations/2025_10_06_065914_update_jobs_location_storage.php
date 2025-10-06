<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure jobs.location column exists
        Schema::table('jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('jobs','location')) {
                $table->string('location')->nullable()->after('job_title');
            } else {
                $table->string('location')->nullable()->change();
            }
        });

        // 2. Backfill from pivot (first location only)
        if (Schema::hasTable('job_location')
            && Schema::hasColumn('job_location','job_id')
            && Schema::hasColumn('job_location','location_id')
            && Schema::hasTable('locations')
            && Schema::hasColumn('locations','id')
            && Schema::hasColumn('locations','address')) {

            $rows = DB::table('job_location')
                ->join('locations','locations.id','=','job_location.location_id')
                ->select('job_location.job_id','locations.address')
                ->orderBy('job_location.id')
                ->get();

            $seen = [];
            foreach ($rows as $r) {
                if (isset($seen[$r->job_id])) continue;
                DB::table('jobs')->where('id',$r->job_id)->update([
                    'location' => $r->address,
                ]);
                $seen[$r->job_id] = true;
            }
        }

        // 3. Drop pivot table
        if (Schema::hasTable('job_location')) {
            Schema::drop('job_location');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // (Optional) Recreate pivot (empty) if rollback needed
        Schema::create('job_location', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->timestamps();
        });
    }
};
