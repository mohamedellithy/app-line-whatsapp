<?php

namespace App\Console;

use App\Models\SpUser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
// use App\Models\NotificationSubscriber;
// use App\Models\NotificationUsersPrivate;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
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
        //

         ///////////////////////////////////////////////////////////////////////
         $schedule->command('sent:salla_webhooks')
         ->withoutOverlapping()->timezone('Asia/Riyadh')->everyMinute();
 
 
         ///////////////////////////////////////////////////////////////////////
         // $schedule->command('abandoned:reminder')
         // ->withoutOverlapping()->timezone('Asia/Riyadh')->everyTwoHours();
         //->between('8:00', '22:00');
 
         ///////////////////////////////////////////////////////////////////////
         $schedule->call(function () {
             DB::table('event_status')->delete();
         })->name('empty_events_logs')->everyTwoHours();
 
         ///////////////////////////////////////////////////////////////////////
         // $schedule->call(function () {
         //     DB::table('event_status')->where(
         //         'type' ,'=','abandoned.cart'
         //     )->where('status','!=','progress')->delete();
         // })->name('empty_event_abandoned_cart_status')->weekly();
 
         ///////////////////////////////////////////////////////////////////////
         $random_minutes = [
             'everyMinute'
             // 'everyTwoMinutes',
             // 'everyThreeMinutes',
             // 'everyFiveMinutes',
             // 'everyFourMinutes',
             // 'everyTenMinutes'
             // 'everyFifteenMinutes',
             // 'everyThirtyMinutes',
             // 'hourly'
         ];
 
         $key_nump = array_rand($random_minutes,1);
 
         $random_repeate = $random_minutes[$key_nump];
 
 
         // send notifications for expiration date
         // $schedule->command('subscriber:notification')
         // ->withoutOverlapping()->timezone('Asia/Riyadh')->$random_repeate()->between('8:00', '23:00');
 
 
         ///////////////////////////////////////////////////////////////////////
         // send notifications for all users that not have token account
         // $schedule->command('merchants:donot-have-a-token')
         // ->withoutOverlapping()->$random_repeate()->between('8:00', '22:00');
         
         ///////////////////////////////////////////////////////////////////////
         // $schedule->call(function(){
         //     $user = SpUser::with('merchant_info')->where('email',"Modern.sa@outlook.sa")->first();
         //     if($user):
         //         $platform_link  = "https://wh.line.sa/login";
         //         $password       = Str::random(10);
         //         $user_password  = md5($password);
         //         $user->password = $user_password;
         //         $user->save();
 
         //         $phone_number = $user->merchant_info()->where('app_name','salla')->value('phone');
         //         // message text
         //         $message = urlencode("عميلنا العزيز \n
         //         لاحظنا عدم تنشيط اشتراكك او عدم ربط تطبيق (واتساب لاين) على حسابك لتستفيد من الخدمة التي نقدمها و التي يشترك فيها العديد من التجار على منصة سلة لذلك نرغب توجيهك لبدء الاستفادة من التطبيق و زيادة ارباحك عن طريق استخدام واتساب لاين للعملاء
         //         سنوضح لك فى الفيديو المرفق كيف تقوم بتثبيت التطبيق و ربط حسابك  \n\n
         //         https://youtu.be/LdEY0bgCV0k?si=RANUsAlykZbVubSs\n\n
         //         و هذه بيانات جديدة للوحة التحكم الخاصة بك على منصتنا يمكن استخدامها لمتابعة التثبيت
         //         تفاصيل الحساب \n
         //         👈 البريد الالكترونى : {$user->email}\n
         //         👈 اسم المستخدم : {$user->username}\n
         //         👈 كلمة المرور  : {$password}\n
         //         👈 رابط المنصة : {$platform_link}\n
         //         😀👏 من فضلك لا تبخل علينا فى الاستفسار عن كيفية تفعيل الخدمة على حسابك 😀👏
         //         ");
 
         //         // send message with all info and it was installed succefully
         //         send_message($phone_number,$message);
 
         //         // NotificationSubscriber::create([
         //         //     'user_id' => $user->id,
         //         //     'status'  => 'done'
         //         // ]);
         //     endif;
         // })->timezone('Asia/Riyadh');
    }
}
