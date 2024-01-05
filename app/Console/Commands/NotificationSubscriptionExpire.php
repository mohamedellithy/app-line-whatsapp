<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SpUser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\NotificationSubscriber;
use Carbon\Carbon;
class NotificationUsersPrivate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriber:notification';

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
        $today = Carbon::today();
        $row = SpUser::with('merchant_info','team','team.account')
        ->whereHas('team')
        ->whereHas('team.account')
        ->whereDoesntHave('notifications',function($query){
            $query->where('type','expiration_date');
        })
        ->where('expiration_date','!=',0)
        ->where('expiration_date','<=',$today->timestamp)->first();

        if($row->team->account->pid):
            $formate_phone = explode('@',$row->team->account->pid);
            $phone = $formate_phone[0] ?: '';
            if(count($row->merchant_info) == 0):
                $message = "
                مرحبًا ، ".$row->username."
                نأمل أن تكون بخير وتستمتع بخدمتنا. نود أن نذكرك بأن اشتراكك الحالي انتهى. ونحن نود أن نقدم لك فرصة لتجديد اشتراكك والاستمرار في الاستفادة من جميع المزايا والميزات التي نقدمها.
                لتجديد اشتراكك، يُرجى زيارة الرابط التالي مباشرة:
                https://line.sa/19505
                نشكرك مرة أخرى على ثقتك فينا ونتطلع إلى مواصلة خدمتك. نحن ممتنون لك كعميل ونعدك بأننا سنبذل قصارى جهدنا لتلبية توقعاتك.
                مع خالص الود,
                خدمة عملاء واتساب لاين
                ";
            else:
                $message = "
                مرحبًا ، ".$row->username."
                نأمل أن تكون بخير وتستمتع بخدمتنا. نود أن نذكرك بأن اشتراكك الحالي انتهى. ونحن نود أن نقدم لك فرصة لتجديد اشتراكك والاستمرار في الاستفادة من جميع المزايا والميزات التي نقدمها.
                لتجديد اشتراكك او ترقية الاشتراك ، يُرجى زيارة الرابط التالي مباشرة:
                https://s.salla.sa/apps/install/1662840947?upgrade=1
                نشكرك مرة أخرى على ثقتك فينا ونتطلع إلى مواصلة خدمتك. نحن ممتنون لك كعميل ونعدك بأننا سنبذل قصارى جهدنا لتلبية توقعاتك.
                مع خالص الود,
                خدمة عملاء واتساب لاين
                ";
            endif;
            //$message = trim(preg_replace('/\t/', '', $message));

            // send message with all info and it was installed succefully
            send_message($phone,$message);

            NotificationSubscriber::create([
                'user_id' => $row->id,
                'status'  => 'done',
                'type'    => 'expiration_date'
            ]);
        endif;

        //
        // Http::withOptions([
        //     'verify' => false
        // ])->post("https://webhook-test.com/90a420a1883f090be6c46d8c807e981c",[
        //     'b' => $users,
        //     't' => $today->timestamp
        // ]);
        return Command::SUCCESS;
    }
}
