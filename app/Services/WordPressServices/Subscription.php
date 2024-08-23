<?php
namespace App\Services\WordPressServices;

use Log;
use Carbon\Carbon;
use App\Models\Team;
use App\Models\SpPlan;
use App\Models\SpUser;
use Illuminate\Support\Str;

class Subscription{

    public $data;
    public $plans;

    public $sort_plans;
    protected $merchant_team = null;
    protected static $platform_link  = "https://wh.line.sa/login";
    protected static $descript_our_platform = "https://doc.line.sa";

    public $package_expire_at; 
    public function __construct($data){
        // set data
        $this->data = $data;

        // set plans
          // set plans
          $this->plans = [
            'الانطلاقة'  => 2,
            'النمو'    => 3,
	        'البلاتيني'  => 35,
            'الاحترفية' => 4
        ];

        $this->package_expire_at = [
            'شهري' => 30,
            'سنوي' => 360
        ];

        $user = SpUser::where([
            'email'    => $this->data['email']
        ])->first();

        if($user):
            $this->renew_subscription($user);
        else:
            $this->create_new_user();
        endif;

        // track event by using Log
        $this->set_log();
    }

    public function set_log(){
        // encode log
        $log = json_encode($this->data, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }

    public function renew_subscription($user){
        $plan_id       = $this->plans[$this->data['package_type']] ?: 34;
        $expiration_date = self::add_date_plus($this->package_expire_at[$this->data['package_expire']] ?: 30);
        $package = SpPlan::findOrFail($plan_id);
        if($package):
            $new_team              = Team::where('owner',$user->id)->first();
            $new_team->pid         = $plan_id;
            $new_team->permissions = $package->permissions;
            $new_team->save();
            $user->expiration_date = $expiration_date;
            $user->plan            = $plan_id;
            $user->save();

            $phone_number = $this->data['phone'];
            // message text
            $message = urlencode("
                تهانينا 😀👏
                تم تجديد اشتراكك على منصة line.sa بنجاح
                👈 رابط المنصة : ".self::$platform_link."\n
                وللمزيد من الشروحات الكاملة :\n
                https://doc.line.sa/\n
                👈 يمكنك الاطلاع على شروحات منصتنا : ".self::$descript_our_platform."\n
            ");

            // send message with all info and it was installed succefully
            return send_message($phone_number,$message);
        endif;
    }

    public function create_new_user(){
        /*** generate password to send to clinet ***/
        $password       = Str::random(10);
        $user_password  = md5($password);
        $plan_id = 34;
        if(isset($this->plans[$this->data['package_type']])){
          $plan_id        = $this->plans[$this->data['package_type']] ?: 34;   
        }
        $ids            = Str::random(8);
        $username       = explode('@', $this->data['email']);
        $new_account                  = new SpUser();
        $new_account->ids             = $ids;
        $new_account->role            = '0';
        $new_account->is_admin        = '0';
        $new_account->language        = 'ar';
        $new_account->fullname        = isset($username[0]) ? $username[0] : 'user_'.rand(1,10000);
        $new_account->username        = isset($username[0]) ? $username[0] : 'user_'.rand(1,10000);
        $new_account->email           = $this->data['email'];
        $new_account->password        = $user_password;
        $new_account->avatar          = "";
        $new_account->plan            = $plan_id;
        $new_account->expiration_date = self::add_date_plus($this->package_expire_at[$this->data['package_expire']] ?: 30);
        $new_account->timezone        = 'Asia/Riyadh';
        $new_account->login_type      = 'wordpress';
        $new_account->status          = '2';
        $new_account->created         = strtotime(date('Y-m-d'));
        $new_account->data            = json_encode($this->data);
        $new_account->save();

        if($new_account):
            $package = SpPlan::findOrFail($plan_id) ?: null;
            if($package):
                $new_team              = new Team();
                $new_team->ids         = $ids;
                $new_team->pid         = $plan_id;
                $new_team->owner       = $new_account->id;
                $new_team->permissions = $package->permissions;
                $new_team->save();

                // check if new team is created
                if($new_team):

                    $phone_number = $this->data['phone'];
                    // message text
                    $message = urlencode("
                        تهانينا 😀👏
                        تم انشاء حسابك على منصة line.sa بنجاح
                        تفاصيل الحساب
                        👈 البريد الالكترونى : {$new_account->email}\n
                        👈 اسم المستخدم : {$new_account->username}\n
                        👈 كلمة المرور  : {$password}\n
                        👈 رابط المنصة : ".self::$platform_link."\n
                        بعد الدخول على الرابط أعلاه وتسجيل الدخول قم بالعمل الآتي:\n
                        اضغط من القائمة إدارة الحساب\n
                        اضغط على زر إضف حساب\n
                        سيظهر لك باركود الآن\n
                        بعدها افتح واتساب الخاص بك\n
                        ثم الاعدادات ثم الاجهزة المرتبطة\n
                        قم بتوجيه الكاميرا اتجاه الباركود\n
                        وللمزيد من الشروحات الكاملة :\n
                        https://doc.line.sa/\n
                        👈 يمكنك الاطلاع على شروحات منصتنا : ".self::$descript_our_platform."\n
                    ");

                    // send message with all info and it was installed succefully
                    return send_message($phone_number,$message);
                endif;
            endif;
        endif;

    }

    public static function add_date_plus($days = 12){
        $date = Carbon::now();
        $date->addDays($days);
        return strtotime($date->toDateString());
    }
}
