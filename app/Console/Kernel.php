<?php

namespace App\Console;

use App\Models\SpUser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\NotificationSubscriber;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('abandoned:reminder')
        ->withoutOverlapping()->timezone('Asia/Riyadh')->everyTwoHours()->between('7:59', '18:01');

        $schedule->call(function () {
            DB::table('event_status')->truncate();
        })->name('empty_event_status')->weekly();

        $random_minutes = [
            // 'everyFiveMinutes',
            // 'everyTenMinutes',
            // 'everyFifteenMinutes',
            // 'everyThirtyMinutes',
            'hourly'
        ];

        $key_nump = array_rand($random_minutes,1);

        $random_repeate = $random_minutes[$key_nump];


        // send notifications for all users that not have token account
        $schedule->command('merchants:donot-have-a-token')
        ->withoutOverlapping()->$random_repeate()->between('7:59', '18:01');


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
