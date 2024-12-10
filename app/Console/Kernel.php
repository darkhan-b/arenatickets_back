<?php

namespace App\Console;

use App\Models\API\TicketAgents\AlmatyArenaAPI;
use App\Models\General\FileManager;
use App\Models\Helpers\SitemapHelper;
use App\Models\Reports\DailyStatReport;
use App\Models\Reports\ExcelGenerator;
use App\Models\Specific\Order;
use App\Models\Specific\PromocodeTry;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel {
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//        $schedule->call(function () {
//            Log::error('cron test');
//        })->everyMinute();

        $schedule->call(function () {
            Order::deleteOldOrders();
            Order::cleanTrashedOrders();
            ExcelGenerator::cleanOldExcelFiles();
        })->everyFiveMinutes();

        $schedule->call(function () {
            Order::blockAllSoldTickets();
        })->everyTwoMinutes();

        $schedule->call(function () {
            FileManager::deleteOldExcels();
        })->everyFifteenMinutes();

		$schedule->call(function () {
			AlmatyArenaAPI::synchronizeGeneral();
		})->hourlyAt('2');

        $schedule->call(function () {
            DB::table('personal_access_tokens')
                ->where('expires_at', '<', date('Y-m-d H:i:s'))
                ->delete();
        })->everyTenMinutes();

        $schedule->call(function () {
            PromocodeTry::clean();
        })->dailyAt('02:06');

        $schedule->command('auth:clear-resets')->everyFifteenMinutes();

        $schedule->command('activitylog:clean --force')->dailyAt('01:17');
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
