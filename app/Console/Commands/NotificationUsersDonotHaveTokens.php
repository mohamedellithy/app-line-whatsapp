<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SpUser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\NotificationSubscriber;
class NotificationUsersDonotHaveTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'merchants:donot-have-a-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
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
        return Command::SUCCESS;
    }
}
