<?php

namespace App\Support;

use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StageLogger
{
    public static function log(JobApplication $app, ?string $from, string $to): void
    {
        if (!Schema::hasTable('job_application_stage_logs')) return;
        $cols = Schema::getColumnListing('job_application_stage_logs');
        $row = [
            'job_application_id' => $app->id,
        ];
        if (in_array('from_stage',$cols)) $row['from_stage'] = $from;
        if (in_array('to_stage',$cols))   $row['to_stage'] = $to;
        if (in_array('changed_by',$cols)) $row['changed_by'] = auth()->id();
        if (in_array('created_at',$cols)) $row['created_at'] = now();

        try {
            DB::table('job_application_stage_logs')->insert($row);
        } catch (\Throwable $e) {
            \Log::warning('StageLogger insert failed', ['id'=>$app->id,'err'=>$e->getMessage()]);
        }
    }
}