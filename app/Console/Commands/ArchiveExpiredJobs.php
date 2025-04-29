<?php

namespace App\Console\Commands;

use App\Models\Job;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ArchiveExpiredJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:archive-expired-jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $expiredJobs = Job::where('created_at', '<', $now->subDays(30))->get();
        foreach ($expiredJobs as $job) {
            $job->delete(); // Soft delete the job
        }
        $fullJobs = Job::whereColumn('vacancies_filled', '>=', 'vacancy')->get();
        foreach ($fullJobs as $job) {
            $job->delete(); // Soft delete the job
        }

        $this->info('Expired and full jobs have been archived.');
    }
}
