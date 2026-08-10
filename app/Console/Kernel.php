<?php

namespace App\Console;

use App\Jobs\RecalculateProductScores;
use App\Jobs\RecalculateSellerScores;
use App\Jobs\RebuildUserInterests;
use App\Jobs\RefreshOrderTracking;
use App\Jobs\UpdateTrendingProducts;
use App\Models\Order;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\SetupStorage::class,
        Commands\CreatePlaceholderImages::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Schedule the demo database refresh command every 15 minutes in demo mode.
        $schedule->command('demo:refresh-database')->everyFifteenMinutes();

        // Recalculate product ranking scores every hour.
        $schedule->job(new RecalculateProductScores())->hourly()->withoutOverlapping();

        // Rebuild trending badge set in Redis every 30 minutes.
        $schedule->job(new UpdateTrendingProducts())->everyThirtyMinutes()->withoutOverlapping();

        // Rebuild user interest profiles every night at 02:00.
        $schedule->job(new RebuildUserInterests())->dailyAt('02:00')->withoutOverlapping();

        // Recalculate seller reputation scores every night at 03:00.
        $schedule->job(new RecalculateSellerScores())->dailyAt('03:00')->withoutOverlapping();

        // Purge personal data (action logs, expired tokens) older than 90 days — REQ.PRIV.3.
        $schedule->command('app:purge-expired-data --days=90')->dailyAt('04:00')->withoutOverlapping();

        // Refresh tracking data for all active shipped orders every hour.
        $schedule->call(function () {
            Order::where('status', 'shipped')
                ->whereNotNull('carrier')
                ->whereNotNull('tracking_code')
                ->where(function ($q) {
                    $q->whereNull('tracking_checked_at')
                        ->orWhere('tracking_checked_at', '<', now()->subHour());
                })
                ->select('id')
                ->chunk(50, function ($orders) {
                    foreach ($orders as $order) {
                        RefreshOrderTracking::dispatch($order->id);
                    }
                });
        })->hourly()->name('refresh-order-tracking')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
