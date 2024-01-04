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
        $schedule->command('subscriber:notification');

        // $schedule->command('inspire')->hourly();
        $schedule->command('abandoned:reminder')
        ->withoutOverlapping()->timezone('Asia/Riyadh')->everyTwoHours()->runInBackground();
        //->between('8:00', '22:00');

        $schedule->call(function () {
            DB::table('event_status')->where([
                'type' ,'!=', 'abandoned.cart'
            ])->truncate();
        })->name('empty_event_without_abandoned_cart_status')->everyTwoHours();

        $schedule->call(function () {
            DB::table('event_status')->where([
                'type' ,'=', 'abandoned.cart'
            ])->where('status','!=','progress')->truncate();
        })->name('empty_event_abandoned_cart_status')->weekly();

        // $random_minutes = [
        //     // 'everyFiveMinutes',
        //     // 'everyTenMinutes',
        //     // 'everyFifteenMinutes',
        //     // 'everyThirtyMinutes',
        //     'hourly'
        // ];

        // $key_nump = array_rand($random_minutes,1);

        // $random_repeate = $random_minutes[$key_nump];


        // send notifications for all users that not have token account
        // $schedule->command('merchants:donot-have-a-token')
        // ->withoutOverlapping()->$random_repeate()->between('8:00', '22:00');

        // $schedule->command('merchants:private')
        // ->withoutOverlapping();


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
