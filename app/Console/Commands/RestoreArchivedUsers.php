<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class RestoreArchivedUsers extends Command
{
    protected $signature = 'users:restore-archived {--role=company}';
    protected $description = 'Restore archived users (default: only company users)';

    public function handle()
    {
        $role = $this->option('role');

        $restored = User::whereNotNull('archived_at')
            ->when($role, fn($q) => $q->where('role', $role))
            ->update(['archived_at' => null]);

        $this->info("✅ Restored {$restored} archived {$role} users.");
    }
}
