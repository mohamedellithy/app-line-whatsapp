<?php
namespace App\Services\SallaServices;

use Log;
use Carbon\Carbon;
use App\Models\Team;
use App\Models\SpPlan;
use App\Models\SpUser;
use Illuminate\Support\Str;
use App\Models\MerchantCredential;
use Illuminate\Support\Facades\Http;
use App\Services\AppSettings\KarzounRequest;

class User{
    protected $merchant;
    protected $store;
    protected static $platform_link  = "https://wh.line.sa/login";
    protected static $descript_our_platform = "https://line.sa/wh/whatsapp/";
    public function check_user_exist($data) {
        // get merchant information
        $this->get_merchant_info($data['data']['access_token']);

        // get store information
        $this->get_store_info($data['data']['access_token']);

        // get user info
        $user = SpUser::where([
            'email' => $this->merchant->data->email  ?: $this->store->data->email
        ])->first();

        Http::post("https://webhook-test.com/bf900a4221bada3c41a4ec0f71f22694",[
            $user
        ]);

        // change update json access token and refresh token
        if($user):
            $user->merchant_info()->updateOrCreate([
                'user_id'     => $user->id,
                'app_name'    => 'salla',
                'merchant_id' => $data['merchant'],
            ],[
                'phone'        => $this->merchant->data->mobile ?: null,
                'store_id'     => $this->store->data->id,
                'access_token' => $data['data']['access_token'],
                'refresh_token'=> $data['data']['refresh_token'],
                'settings'     => '{"abandoned_cart_status":true,"abandoned_cart_message":"\u0633\u0627\u0644\u0629 \u0627\u0644\u0633\u0644\u0629 \u0627\u0644\u0645\u062a\u0631\u0648\u0643\u0629","otp_status":false,"otp_message":"\u0631\u0645\u0632 \u0627\u0644\u062a\u062d\u0642\u0642 \u0627\u0644\u062e\u0627\u0635 \u0628\u0643 \u0644\u062a\u0633\u062c\u064a\u0644 \u0627\u0644\u062f\u062e\u0648\u0644 \u0639\u0644\u0649 \u0645\u062a\u062c\u0631 \" \u0648\u0627\u062a\u0633\u0627\u0628 \u0644\u0627\u064a\u0646\" \u0647\u0648 : {\u0631\u0645\u0632 \u0627\u0644\u062a\u062d\u0642\u0642} .. \u062a\u0633\u0648\u0642 \u0645\u0645\u062a\u0639.","new_customer_status":true,"new_customer_message":"\u0627\u0647\u0644\u0627 \u0648 \u0633\u0647\u0644\u0627 \u0628\u062d\u0636\u0631\u062a\u0643 {\u0627\u0644\u0627\u0633\u0645_\u0643\u0627\u0645\u0644\u0627}","order_status":true,"orders_active_on":["payment_pending","under_review","in_progress","completed","delivering","delivered","shipped","canceled","restored","restoring"],"order_created_message":"\u064a\u0645\u0643\u0646\u0643 \u0627\u0633\u062a\u062e\u062f\u0627\u0645 \u062a\u0644\u0643 \u0627\u0644\u0645\u062a\u063a\u064a\u0631\u0627\u062a \u0641\u0649 \u0631\u0633\u0627\u0644\u062a\u0643","order_default_message":"\u0637\u0644\u0628\u0643  \u062d\u0627\u0644\u0629  \u0627\u0644\u0627\u0646 {\u062d\u0627\u0644\u0629_\u0627\u0644\u0637\u0644\u0628}   -  {\u0631\u0642\u0645_\u0627\u0644\u0637\u0644\u0628}  - {\u0642\u064a\u0645\u0629_\u0627\u0644\u0637\u0644\u0628} - {\u0627\u0633\u0645_\u0627\u0644\u0639\u0645\u064a\u0644} - {\u0627\u0644\u0639\u0645\u0644\u0629} - {\u0631\u0627\u0628\u0637_\u0645\u0639\u0644\u0648\u0645\u0627\u062a_\u0627\u0644\u0637\u0644\u0628} - {\u0634\u0631\u0643\u0629_\u0627\u0644\u0634\u062d\u0646} - {\u0643\u0648\u062f_\u0627\u0644\u0645\u0646\u062a\u062c} - {\u062a\u0641\u0627\u0635\u064a\u0644_\u0645\u0646\u062a\u062c\u0627\u062a_\u0627\u0644\u0637\u0644\u0628\u064a\u0629} - {\u0632\u0631_\u0627\u0644\u062a\u0623\u0643\u064a\u062f}","order_payment_pending_message":null,"order_under_review_message":"\u0637\u0644\u0628\u0643  \u062d\u0627\u0644\u0629  \u0627\u0644\u0627\u0646 {\u062d\u0627\u0644\u0629_\u0627\u0644\u0637\u0644\u0628} #2","order_in_progress_message":null,"order_completed_message":null,"order_delivering_message":null,"order_delivered_message":null,"order_shipped_message":null,"order_canceled_message":null,"order_restored_message":null,"order_restoring_message":null}'
            ]);

            $plan_id        = $user->plan;
            $package = SpPlan::findOrFail($plan_id) ?: null;
            if($package):
                $new_team  = Team::updateOrCreate(
                    [
                        'owner'=> $user->id
                    ],
                    [
                        'pid'  => $plan_id,
                        'permissions' => $package->permissions
                    ]
                );
            endif;
            // message text
            $message = urlencode("
                تهانينا 😀👏
                تم تفعيل التطبيق على حسابك line.sa بنجاح
                تفاصيل الحساب
                👈 البريد الالكترونى : {$user->email}\n
                👈 رابط المنصة : ".self::$platform_link."\n
                بعد تسجيل الدخول قم بالعمل الآتي:\n
                اضغط من القائمة إدارة الحساب\n
                اضغط على زر إضف حساب\n
                سيظهر لك باركود الآن\n
                بعدها افتح واتساب الخاص بك\n
                ثم الاعدادات ثم الاجهزة المرتبطة\n
                قم بتوجيه الكاميرا اتجاه الباركود\n
                وللمزيد من الشروحات الكاملة :\n
                👈 يمكنك الاطلاع على شروحات منصتنا : ".self::$descript_our_platform."\n
            ");

            // send message with all info and it was installed succefully
            send_message($this->merchant->data->mobile,$message);
        endif;

        // return result
        return $user ? true : false;
    }

    public function get_merchant_info($access_token){
        // Getting the store_id and other merchants Info from access token callback salla
        $this->merchant   = KarzounRequest::resolve(
            $end_point    = "https://api.salla.dev/admin/v2/oauth2/user/info",
            $request_type = 'GET',
            $access_token = $access_token
        );
    }

    public function get_store_info($access_token){
        // Getting the store_id and other merchants Info from access token callback salla
        $this->store   = KarzounRequest::resolve(
            $end_point    = "https://api.salla.dev/admin/v2/store/info",
            $request_type = 'GET',
            $access_token = $access_token
        );
    }

    public function create_new_user($data){
        // get merchant information
        $this->get_merchant_info($data['data']['access_token']);

        // get store information
        $this->get_store_info($data['data']['access_token']);

        /*** generate password to send to clinet ***/
        $password       = Str::random(10);
        $user_password  = md5($password);
        $plan_id        = '34';
        $ids            = ($data['merchant'] ?: $this->store->data->id).Str::random(5);
        $new_account                  = new SpUser();
        $new_account->ids             = $ids;
        $new_account->role            = '0';
        $new_account->is_admin        = '0';
        $new_account->language        = 'ar';
        $new_account->fullname        = $this->merchant->data->name   ?: $this->store->data->name;
        $new_account->username        = $this->store->data->name      ?: $this->merchant->data->merchant->username;
        $new_account->email           = $this->merchant->data->email  ?: $this->store->data->email;
        $new_account->password        = $user_password;
        $new_account->avatar          = $this->store->data->avatar    ?: $this->merchant->data->merchant->avatar;
        $new_account->plan            = '34';
        $new_account->expiration_date = self::add_date_plus(90);
        $new_account->timezone        = 'Asia/Riyadh';
        $new_account->login_type      = 'salla';
        $new_account->status          = '2';
        $new_account->created         = strtotime($data['created_at']);
        $new_account->data            = json_encode($data);
        $new_account->save();

        if($new_account):
            $merchant_credentails = new MerchantCredential();
            $merchant_credentails->user_id        = $new_account->id;
            $merchant_credentails->merchant_id    = $data['merchant'];
            $merchant_credentails->phone          = $this->merchant->data->mobile ?: null;
            $merchant_credentails->app_name       = 'salla';
            $merchant_credentails->store_id       = $this->store->data->id;
            $merchant_credentails->access_token   = $data['data']['access_token'];
            $merchant_credentails->refresh_token  = $data['data']['refresh_token'];
            $merchant_credentails->settings       = '{"abandoned_cart_status":true,"abandoned_cart_message":"\u0633\u0627\u0644\u0629 \u0627\u0644\u0633\u0644\u0629 \u0627\u0644\u0645\u062a\u0631\u0648\u0643\u0629","otp_status":false,"otp_message":"\u0631\u0645\u0632 \u0627\u0644\u062a\u062d\u0642\u0642 \u0627\u0644\u062e\u0627\u0635 \u0628\u0643 \u0644\u062a\u0633\u062c\u064a\u0644 \u0627\u0644\u062f\u062e\u0648\u0644 \u0639\u0644\u0649 \u0645\u062a\u062c\u0631 \" \u0648\u0627\u062a\u0633\u0627\u0628 \u0644\u0627\u064a\u0646\" \u0647\u0648 : {\u0631\u0645\u0632 \u0627\u0644\u062a\u062d\u0642\u0642} .. \u062a\u0633\u0648\u0642 \u0645\u0645\u062a\u0639.","new_customer_status":true,"new_customer_message":"\u0627\u0647\u0644\u0627 \u0648 \u0633\u0647\u0644\u0627 \u0628\u062d\u0636\u0631\u062a\u0643 {\u0627\u0644\u0627\u0633\u0645_\u0643\u0627\u0645\u0644\u0627}","order_status":true,"orders_active_on":["payment_pending","under_review","in_progress","completed","delivering","delivered","shipped","canceled","restored","restoring"],"order_created_message":"\u064a\u0645\u0643\u0646\u0643 \u0627\u0633\u062a\u062e\u062f\u0627\u0645 \u062a\u0644\u0643 \u0627\u0644\u0645\u062a\u063a\u064a\u0631\u0627\u062a \u0641\u0649 \u0631\u0633\u0627\u0644\u062a\u0643","order_default_message":"\u0637\u0644\u0628\u0643  \u062d\u0627\u0644\u0629  \u0627\u0644\u0627\u0646 {\u062d\u0627\u0644\u0629_\u0627\u0644\u0637\u0644\u0628}   -  {\u0631\u0642\u0645_\u0627\u0644\u0637\u0644\u0628}  - {\u0642\u064a\u0645\u0629_\u0627\u0644\u0637\u0644\u0628} - {\u0627\u0633\u0645_\u0627\u0644\u0639\u0645\u064a\u0644} - {\u0627\u0644\u0639\u0645\u0644\u0629} - {\u0631\u0627\u0628\u0637_\u0645\u0639\u0644\u0648\u0645\u0627\u062a_\u0627\u0644\u0637\u0644\u0628} - {\u0634\u0631\u0643\u0629_\u0627\u0644\u0634\u062d\u0646} - {\u0643\u0648\u062f_\u0627\u0644\u0645\u0646\u062a\u062c} - {\u062a\u0641\u0627\u0635\u064a\u0644_\u0645\u0646\u062a\u062c\u0627\u062a_\u0627\u0644\u0637\u0644\u0628\u064a\u0629} - {\u0632\u0631_\u0627\u0644\u062a\u0623\u0643\u064a\u062f}","order_payment_pending_message":null,"order_under_review_message":"\u0637\u0644\u0628\u0643  \u062d\u0627\u0644\u0629  \u0627\u0644\u0627\u0646 {\u062d\u0627\u0644\u0629_\u0627\u0644\u0637\u0644\u0628} #2","order_in_progress_message":null,"order_completed_message":null,"order_delivering_message":null,"order_delivered_message":null,"order_shipped_message":null,"order_canceled_message":null,"order_restored_message":null,"order_restoring_message":null}';
            $merchant_credentails->save();

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

                    $phone_number = $merchant_credentails->phone;
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
                        https://line.sa/wh/whatsapp/\n
                        👈 يمكنك الاطلاع على شروحات منصتنا : ".self::$descript_our_platform."\n
                    ");

                    // send message with all info and it was installed succefully
                    return send_message($phone_number,$message);
                endif;
            endif;
        endif;

    }

    public static function upgrade_user_plan($merchant_id,$plan_id,$expiration_date){
        $upgrade_plan = SpUser::where('ids',$merchant_id)->update([
            'package'        => $plan_id,
            'expiration_date'=> $expiration_date
        ]);

        if($upgrade_plan){
           return $plan_id.' --done ';
        }

        return $plan_id.' --failed ';
    }

    public static function reset_password($merchant_id){
        $user           = SpUser::whereHas('merchant_info',function($query) use($merchant_id){
            $query->where('merchant_id',$merchant_id);
        })->first();
        $password       = Str::random(10);
        $user_password  = md5($password);
        $user->password = $user_password;
        $user->save();
        $message = urlencode("
            تهانينا 😀👏
            تم انشاء حسابك على منصة line.sa بنجاح
            تفاصيل الحساب
            👈 البريد الالكترونى : {$user->email}\n
            👈 اسم المستخدم : {$user->username}\n
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
            https://line.sa/wh/whatsapp/\n
            👈 يمكنك الاطلاع على شروحات منصتنا : ".self::$descript_our_platform."\n
        ");

        $merchant = MerchantCredential::where([
            'user_id'     => $user->id,
            'merchant_id' => $merchant_id,
            'app_name'    => 'salla'
        ])->first();

        $settings = $merchant->settings ? json_decode($merchant->settings,true) : [];

        $phone_number = count($settings) > 0 ? ( (isset($settings['custom_merchant_phone']) && $settings['custom_merchant_phone'] != null) ? $settings['custom_merchant_phone'] : $user->merchant_info->phone) : $user->merchant_info->phone;

        return send_message($phone_number,$message);

    }

    public static function add_date_plus($days = 12){
        $date = Carbon::now();
        $date->addDays($days);
        return strtotime($date->toDateString());
    }
}
