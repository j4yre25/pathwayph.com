<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JobApplication;
use App\Models\JobPipelineStage;
use Illuminate\Support\Facades\Schema;

class BackfillApplicationStages extends Command
{
    protected $signature = 'ats:backfill-stages';
    protected $description = 'Assign pipeline stage IDs to legacy job applications based on existing stage string';

    public function handle(): int
    {
        if (!Schema::hasTable('job_pipeline_stages') || !Schema::hasTable('job_applications')) {
            $this->error('Required tables missing. Run migrations first.');
            return self::FAILURE;
        }

        $defaults = JobPipelineStage::whereNull('company_id')->get()->keyBy('slug');

        if ($defaults->isEmpty()) {
            $this->error('No default pipeline stages found. Seed them first (DatabaseSeeder).');
            return self::FAILURE;
        }

        $total = JobApplication::whereNull('job_pipeline_stage_id')->count();
        if ($total === 0) {
            $this->info('No applications need backfill.');
            return self::SUCCESS;
        }

        $this->info("Backfilling {$total} applications...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        JobApplication::whereNull('job_pipeline_stage_id')
            ->orderBy('id')
            ->chunkById(500, function ($chunk) use ($defaults, $bar) {
                foreach ($chunk as $app) {
                    $slug = match(strtolower($app->stage ?? '')) {
                        'screening','screened' => 'screening',
                        'testing','tested','assessment','exam' => 'assessment',
                        'interview','final interview' => 'interview',
                        'offer','offer_sent' => 'offer',
                        'hired' => 'hired',
                        'rejected','declined' => 'rejected',
                        default => 'applied'
                    };
                    $stageId = $defaults[$slug]?->id;

                    if ($stageId) {
                        $app->job_pipeline_stage_id = $stageId;
                        // Keep legacy stage string aligned with canonical slug
                        $app->stage = $slug;
                        $app->saveQuietly();
                    }
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);
        $this->info('Backfill complete.');

        return self::SUCCESS;
    }
}