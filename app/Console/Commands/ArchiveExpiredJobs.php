<?php

namespace App\Console\Commands;

use App\Models\Job;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ArchiveExpiredJobs extends Command
{
    protected $signature = 'app:archive-expired-jobs';
    protected $description = 'Archive jobs that are expired, full, or have met application limits.';

    public function handle()
    {
        $now = Carbon::now();

        $jobs = Job::all();
        $archivedCount = 0;

        foreach ($jobs as $job) {
            $shouldArchive = false;

            // Check 1: If job has expired based on expiration_date
            if ($job->expiration_date && Carbon::parse($job->expiration_date)->lt($now)) {
                $shouldArchive = true;
            }

            // Check 2: If applicants_limit is set and reached
            if ($job->applicants_limit && $job->applications()->count() >= $job->applicants_limit) {
                $shouldArchive = true;
            }

            // Check 3: If vacancies filled >= vacancy
            if ($job->vacancies_filled >= $job->vacancy) {
                $shouldArchive = true;
            }

            // Optional Check 4: Older than 30 days
            if ($job->created_at->lt($now->copy()->subDays(30))) {
                $shouldArchive = true;
            }

            if ($shouldArchive) {
                $job->delete(); // Soft delete
                $archivedCount++;
            }
        }

        $this->info("Archived $archivedCount job(s).");
    }
}
