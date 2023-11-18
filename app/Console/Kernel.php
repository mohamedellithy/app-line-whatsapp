<?php

namespace App\Console;

use App\Models\SpUser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
        // $schedule->call(function () {
        //     DB::table('event_status')->truncate();
        // })->name('empty_event_status')->dailyAt('02:00');


        // send notifications for all users that not have token account

        $user = SpUser::with('merchant_info')->doesntHave('team.account')->doesntHave('notifications')->whereHas('merchant_info')->first();

        $platform_link  = "https://wh.line.sa/login";
        $descript_our_platform = "https://line.sa/wh/%d8%b4%d8%b1%d9%88%d8%ad%d8%a7%d8%aa-%d9%88%d8%a7%d8%aa%d8%b3%d8%a7%d8%a8-%d9%84%d8%a7%d9%8a%d9%86/";


        $password       = Str::random(10);
        $user_password  = md5($password);
        $user->update([
            'password' => $user_password
        ]);

        $phone_number = "201026051966"; //$user->merchant_info()->where('app_name','salla')->value('phone');
        // message text
        $message = urlencode("
        عميلنا العزيز \n
        لاحظنا عدم تنشيط اشتراكك او عدم ربط تطبيق (واتساب لاين) على حسابك لتستفيد من الخدمة التي نقدمها و التي يشترك فيها العديد من التجار على منصة سلة لذلك نرغب توجيهك لبدء الاستفادة من التطبيق و زيادة ارباحك عن طريق استخدام واتساب لاين للعملاء
        سنوضح لك فى الفيديو المرفق كيف تقوم بتثبيت التطبيق و ربط حسابك  \n\n
        و هذه بيانات جديدة للوحة التحكم الخاصة بك على منصتنا يمكن استخدامها لمتابعة التثبيت
        تفاصيل الحساب \n
        👈 البريد الالكترونى : {$user->email}\n
        👈 اسم المستخدم : {$user->username}\n
        👈 كلمة المرور  : {$password}\n
        👈 رابط المنصة : {$platform_link}\n
        👈 يمكنك الاطلاع على شروحات منصتنا : {$descript_our_platform}\n
        ");

        // send message with all info and it was installed succefully
        return send_message($phone_number,$message);
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
