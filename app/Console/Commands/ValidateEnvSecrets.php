<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ValidateEnvSecrets extends Command
{
    protected $signature = 'app:validate-env';

    protected $description = 'Validate that required secrets are configured and not left as defaults (REQ.IOTM.2)';

    private const REQUIRED = [
        'APP_KEY' => 'Application encryption key',
        'DB_PASSWORD' => 'Database password',
        'MAIL_PASSWORD' => 'Mail server password',
        'SANCTUM_EXPIRATION' => 'Sanctum token expiry (minutes)',
    ];

    private const FORBIDDEN_VALUES = [
        '', 'null', 'secret', 'password', 'changeme', 'your-key-here',
        'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
    ];

    public function handle(): int
    {
        $failed = false;

        foreach (self::REQUIRED as $key => $label) {
            $value = env($key);

            if ($value === null) {
                $this->error("  MISSING  {$key} — {$label}");
                $failed = true;
                continue;
            }

            if (in_array(strtolower((string) $value), self::FORBIDDEN_VALUES, true)) {
                $this->error("  DEFAULT  {$key} — value looks like a placeholder");
                $failed = true;
                continue;
            }

            $this->line("  <info>OK</info>       {$key}");
        }

        $this->newLine();

        if ($failed) {
            $this->error('Environment validation failed. Set proper values before deploying to production.');

            return self::FAILURE;
        }

        $this->info('All required secrets are configured.');

        return self::SUCCESS;
    }
}
