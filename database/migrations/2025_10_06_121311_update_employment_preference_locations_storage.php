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
        // 1. Ensure `location` column exists (JSON or TEXT fallback)
        Schema::table('employment_preferences', function (Blueprint $table) {
            if (!Schema::hasColumn('employment_preferences', 'location')) {
                // Use JSON if supported (MySQL 5.7+/MariaDB 10.2+)
                try {
                    $table->json('location')->nullable()->after('job_type');
                } catch (\Throwable $e) {
                    $table->text('location')->nullable()->after('job_type');
                }
            }
        });

        // 2. Backfill from pivot (detect table name)
        $pivotNames = ['employment_preference_location', 'employment_preference_locations'];
        $pivot = null;
        foreach ($pivotNames as $name) {
            if (Schema::hasTable($name)) { $pivot = $name; break; }
        }

        if ($pivot && Schema::hasTable('locations') && Schema::hasColumn('locations','id') && Schema::hasColumn('locations','address')) {
            $pairs = DB::table($pivot)
                ->join('locations','locations.id','=',''.$pivot.'.location_id')
                ->select($pivot.'.employment_preference_id as ep_id','locations.address')
                ->orderBy($pivot.'.id')
                ->get()
                ->groupBy('ep_id');

            foreach ($pairs as $epId => $rows) {
                $addresses = $rows->pluck('address')
                    ->filter()
                    ->map(fn($a)=>trim($a))
                    ->unique()
                    ->values()
                    ->all();

                if (empty($addresses)) continue;

                // Detect column type (json vs text)
                $colType = DB::getSchemaBuilder()
                    ->getColumnType('employment_preferences','location');

                $storeValue = ($colType === 'json')
                    ? json_encode($addresses)
                    : implode(',', $addresses);

                DB::table('employment_preferences')
                    ->where('id', $epId)
                    ->where(function($q){
                        $q->whereNull('location')
                          ->orWhere('location','');
                    })
                    ->update(['location' => $storeValue]);
            }

            // 3. Drop pivot table
            Schema::dropIfExists($pivot);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // (Optional) Recreate empty pivot (cannot restore data)
        if (!Schema::hasTable('employment_preference_location')) {
            Schema::create('employment_preference_location', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employment_preference_id')->constrained()->cascadeOnDelete();
                $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
                $table->timestamps();
            });
        }
        // Leave flattened location column in place.
    }
};
