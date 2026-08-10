<?php

namespace App\Console\Commands;

use App\Models\ActionLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeExpiredData extends Command
{
    protected $signature = 'app:purge-expired-data
                            {--days=90 : Purge records older than this many days}
                            {--dry-run : Show counts without deleting}';

    protected $description = 'Purge personal data past its retention period (REQ.PRIV.3)';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $cutoff = now()->subDays($days);

        $this->info("Retention cutoff: {$cutoff->toDateTimeString()} (older than {$days} days)");

        $targets = [
            'action_logs' => ActionLog::where('created_at', '<', $cutoff),
            'personal_access_tokens' => DB::table('personal_access_tokens')->where('created_at', '<', $cutoff),
        ];

        foreach ($targets as $table => $query) {
            $count = $query->count();
            if ($dryRun) {
                $this->line("  [dry-run] {$table}: {$count} rows would be deleted");
            } else {
                $query->delete();
                $this->line("  Deleted {$count} rows from {$table}");
            }
        }

        if (! $dryRun) {
            $this->info('Purge complete.');
        }

        return self::SUCCESS;
    }
}
