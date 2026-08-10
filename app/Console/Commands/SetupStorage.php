<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class SetupStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-storage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup storage directories and create symbolic link';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up storage...');

        // Create storage link
        $this->info('Creating storage link...');
        Artisan::call('storage:link');
        $this->info(Artisan::output());

        // Create necessary directories
        $this->info('Creating directories...');
        $directories = [
            'categories',
            'tags',
            'terms',
            'posts',
            'users',
        ];

        foreach ($directories as $dir) {
            Storage::disk('public')->makeDirectory($dir);
            $this->info("Created directory: {$dir}");
        }

        // Harden log directory permissions — REQ.LOG.6
        if (PHP_OS_FAMILY !== 'Windows') {
            $logPath = storage_path('logs');
            chmod($logPath, 0750);
            foreach (glob($logPath.'/*.log') ?: [] as $file) {
                chmod($file, 0640);
            }
            $this->info('Log directory permissions set to 750/640.');
        }

        $this->info('Storage setup completed successfully!');

        return Command::SUCCESS;
    }
}
