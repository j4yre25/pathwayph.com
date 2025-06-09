<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ArchiveInactiveUsers extends Command
{
    protected $signature = 'users:archive-inactive';

    protected $description = 'Archive company users who have been inactive for 90 days';

    public function handle()
    {
        $cutoffDate = Carbon::now()->subDays(90);

        $usersToArchive = User::where('role', 'company')
            ->whereNull('archived_at')
            ->where(function ($query) use ($cutoffDate) {
                $query->whereNull('last_login_at')
                      ->orWhere('last_login_at', '<', $cutoffDate);
            })
            ->get();

        foreach ($usersToArchive as $user) {
            $user->update(['archived_at' => now()]);
            $this->info("Archived: {$user->email}");
        }

        $this->info("✅ Archived {$usersToArchive->count()} inactive company users.");
        return Command::SUCCESS;
    }
}
