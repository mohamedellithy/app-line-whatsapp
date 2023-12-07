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
        ->withoutOverlapping()->everyTenMinutes();

        $schedule->call(function () {
            DB::table('event_status')->truncate();
        })->name('empty_event_status')->dailyAt('02:00');

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
        $schedule->call(function () {
            $user = SpUser::with('merchant_info')->doesntHave('team.account')->doesntHave('notifications')->whereHas('merchant_info')->first();
            if($user):
                $platform_link  = "https://wh.line.sa/login";
                $password       = Str::random(10);
                $user_password  = md5($password);
                $user->password = $user_password;
                $user->save();

                $phone_number = $user->merchant_info()->where('app_name','salla')->value('phone');
                // message text
                $message = urlencode("عميلنا العزيز \n
                لاحظنا عدم تنشيط اشتراكك او عدم ربط تطبيق (واتساب لاين) على حسابك لتستفيد من الخدمة التي نقدمها و التي يشترك فيها العديد من التجار على منصة سلة لذلك نرغب توجيهك لبدء الاستفادة من التطبيق و زيادة ارباحك عن طريق استخدام واتساب لاين للعملاء
                سنوضح لك فى الفيديو المرفق كيف تقوم بتثبيت التطبيق و ربط حسابك  \n\n
                https://youtu.be/LdEY0bgCV0k?si=RANUsAlykZbVubSs\n\n
                و هذه بيانات جديدة للوحة التحكم الخاصة بك على منصتنا يمكن استخدامها لمتابعة التثبيت
                تفاصيل الحساب \n
                👈 البريد الالكترونى : {$user->email}\n
                👈 اسم المستخدم : {$user->username}\n
                👈 كلمة المرور  : {$password}\n
                👈 رابط المنصة : {$platform_link}\n
                😀👏 من فضلك لا تبخل علينا فى الاستفسار عن كيفية تفعيل الخدمة على حسابك 😀👏
                ");

                // send message with all info and it was installed succefully
                send_message($phone_number,$message);

                NotificationSubscriber::create([
                    'user_id' => $user->id,
                    'status'  => 'done'
                ]);
            endif;
        })->name('send_notifications_for_not_have_account')->$random_repeate();


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
