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
    protected static $descript_our_platform = "https://doc.line.sa/";
    public function check_user_exist($data) {
        // get merchant information
        $this->get_merchant_info($data['data']['access_token']);

        // get store information
        // $this->get_store_info($data['data']['access_token']);

        // get user info
        $user = SpUser::where([
            'email' => $this->merchant->data->email
        ])->first();


        // change update json access token and refresh token
        if($user):
            $user->merchant_info()->updateOrCreate([
                'user_id'     => $user->id,
                'app_name'    => 'salla',
                'merchant_id' => $data['merchant'],
            ],[
                'phone'        => $this->merchant->data->mobile ?: null,
                'store_id'     => $data['merchant'],
                'access_token' => $data['data']['access_token'],
                'refresh_token'=> $data['data']['refresh_token'],
                'settings'     => '{"custom_merchant_phone":"","abandoned_cart_status":true,"abandoned_cart_message":"\u0645\u0631\u062d\u0628\u0627 {\u0627\u0633\u0645 \u0627\u0644\u0639\u0645\u064a\u0644},\n\n\u0644\u0642\u062f \u0644\u0627\u062d\u0638\u0646\u0627 \u0627\u0646\u0643 \u0646\u0633\u064a\u062a \u0633\u0644\u062a\u0643 \u0641\u064a \u0645\u062a\u062c\u0631\u0646\u0627, \u0648\u0644\u0630\u0644\u0643 \u0627\u0631\u062f\u0646\u0627 \u0627\u0647\u062f\u0627\u0626\u0643 \u0643\u0648\u062f \u062e\u0635\u0645 \u062e\u0627\u0635 \u0644\u0643\u064a \u062a\u0643\u0645\u0644 \u0627\u0644\u0637\u0644\u0628.\n\n\u0642\u064a\u0645\u0629 \u0645\u0634\u062a\u0631\u064a\u0627\u062a\u0643 \u0642\u0628\u0644 \u0627\u0644\u062e\u0635\u0645 \u0647\u064a {\u0627\u062c\u0645\u0627\u0644\u0649_\u0627\u0644\u0633\u0644\u0629}  \u0648\u064a\u0645\u0643\u0646\u0643 \u0627\u0633\u062a\u062e\u062f\u0627\u0645 \u0627\u0644\u0643\u0648\u062f \"LINE\" \u0644\u0644\u062d\u0635\u0648\u0644 \u0639\u0644\u0649 \u062a\u062e\u0641\u064a\u0636 20%.\n\u064a\u0645\u0643\u0646\u0643 \u0627\u0633\u062a\u062e\u062f\u0627\u0645 \u0627\u0644\u0643\u0648\u062f \u0648\u0627\u062a\u0645\u0627\u0645 \u0639\u0645\u0644\u064a\u0629 \u0627\u0644\u0634\u0631\u0627\u0621 \u0628\u0633\u0647\u0648\u0644\u0629 \u0639\u0628\u0631 \u0627\u0644\u0631\u0627\u0628\u0637 \u0627\u0644\u062a\u0627\u0644\u064a\n\n{\u0631\u0627\u0628\u0637_\u0627\u0644\u062f\u0641\u0639}\n\u062e\u062f\u0645\u0629 \u0627\u0644\u0639\u0645\u0644\u0627\u0621","count_abandoned_cart_reminder":2,"request_review_status":true,"message_request_review":"\u0639\u0632\u064a\u0632 \u0627\u0644\u0639\u0645\u064a\u0644 \n\n\u0634\u0643\u0631\u0627 \u0644\u0623\u062e\u062a\u064a\u0627\u0631\u0643 \u0634\u0631\u0627\u0621 \u0645\u0646\u062a\u062c\u0627\u062a\u0646\u0627 \u0648\u0628\u0639\u062f \u0627\u0633\u062a\u0644\u0627\u0645\u0643 \u0627\u0644\u0645\u0646\u062a\u062c \u0646\u062a\u0645\u0646\u0649 \u0633\u0645\u0627\u0639 \u0631\u0623\u064a\u0643\u00a0\u0641\u064a\u00a0\u0645\u0646\u062a\u062c\u0627\u062a\u0646\u0627                                                                                                                                                                                       {\u0631\u0648\u0627\u0628\u0637_\u0627\u0644\u0645\u0646\u062a\u062c\u0627\u062a}","review_added_status":true,"review_added_message":"\u0639\u0632\u064a\u0632\u064a \u0627\u0644\u0639\u0645\u064a\u0644 \n\n\u0646\u0634\u0643\u0631\u0643 \u0644\u062a\u0642\u064a\u064a\u0645 \u0645\u0646\u062a\u062c\u0627\u062a\u0646\u0627 \u0648\u064a\u0633\u0639\u062f\u0646\u0627 \u062a\u0642\u062f\u064a\u0645 \u0643\u0648\u062f \u062e\u0635\u0645 \u062e\u0627\u0635 \u0628\u0643 \u0644\u0634\u0631\u0627\u0621 \u0623\u064a \u0645\u0646\u062a\u062c \u0622\u062e\u0631 \u0623\u0648\u00a0\u0627\u0647\u062f\u0627\u0621\u0647\u00a0\u0644\u0627\u0635\u062f\u0642\u0627\u0626\u0643","otp_status":true,"otp_message":"\u064a\u0627\u0647\u0644\u0627 \u0646\u0648\u0631\u062a\u0646\u0627 \u0631\u0645\u0632 \u0627\u0644\u062a\u062d\u0642\u0642 \u0627\u0644\u062e\u0627\u0635 \u0628\u0643 \u0644\u062a\u0633\u062c\u064a\u0644 \u0627\u0644\u062f\u062e\u0648\u0644 \u0639\u0644\u0649 \u0645\u062a\u062c\u0631 \" \u0648\u0627\u062a\u0633\u0627\u0628 \u0644\u0627\u064a\u0646\" \u0647\u0648 : {\u0631\u0645\u0632_\u0627\u0644\u062a\u062d\u0642\u0642} .. \u062a\u0633\u0648\u0642 \u0645\u0645\u062a\u0639.","new_customer_status":true,"new_customer_message":"\u064a\u0627 \u0647\u0644\u0627 \u0648\u0627\u0644\u0644\u0647, \u0646\u0648\u0631 \u0627\u0644\u0645\u062a\u062c\u0631 \u064a\u0627 {\u0627\u0633\u0645_\u0627\u0644\u0627\u0648\u0644} \n-------\n\u0647\u0644\u0627 \u0648\u063a\u0644\u0627 \u0646\u0648\u0631\u062a\u0646\u0627 \u0648\u0647\u0630\u0627 \u0631\u0642\u0645 \u062e\u062f\u0645\u0629 \u0627\u0644\u0639\u0645\u0644\u0627\u0621 \u0644\u0648 \u0627\u062d\u062a\u062c\u062a \u0627\u064a \u0634\u064a\u0621,\n\u062a\u0648\u0627\u0635\u0644 \u0645\u0639\u0646\u0627 \u0641\u064a \u0627\u064a \u0648\u0642\u062a.. \u062a\u0633\u0648\u0642 \u0645\u0645\u062a\u0639\n\n\u062e\u062f\u0645\u0629 \u0627\u0644\u0639\u0645\u0644\u0627\u0621","order_status":true,"orders_active_on":["payment_pending","under_review","in_progress","completed","delivering","delivered","shipped","canceled","restored","restoring","order_created"],"order_created_message":null,"order_default_message":"\u0639\u0632\u064a\u0632\u064a \u0627\u0644\u0639\u0645\u064a\u0644  {\u0627\u0633\u0645_\u0627\u0644\u0639\u0645\u064a\u0644} \u0637\u0644\u0628\u0643\u0645 \u0631\u0642\u0645  {\u0631\u0642\u0645_\u0627\u0644\u0637\u0644\u0628}  \u062d\u0627\u0644\u062a\u0647 \u0627\u0644\u0627\u0646  {\u062d\u0627\u0644\u0629_\u0627\u0644\u0637\u0644\u0628}","order_payment_pending_message":null,"order_under_review_message":null,"order_in_progress_message":null,"order_completed_message":null,"order_delivering_message":null,"order_delivered_message":null,"order_shipped_message":null,"order_canceled_message":null,"order_restored_message":null,"order_restoring_message":null}'
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
            $message = urlencode("شريكنا العزيز ".$user->username." 👋🏻\n
            اهلا بك في واتساب لاين
            https://wh.line.sa\n
            البريد الالكتروني: ".$user->email."
            رابط شروحات منصتنا : ".self::$descript_our_platform."
            يمكنك الآن الوصول إلى جميع المزايا والخدمات التي نقدمها.\n
            لا تتردد في الاتصال بنا لتقديم الدعم اللازم.\n
            نتمنى لك تجارة رابحة
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
        $password       = Str::random(5);
        $user_password  = md5($password);
        $plan_id        = '34';
        $ids            = $data['merchant'].Str::random(5);
        $store_url      = $this->store->data->domain ?: ''; //(isset($this->store['data']) && isset($this->store['data']['domain'])) ? $this->store['data']['domain'] : "";
        $new_account                  = new SpUser();
        $new_account->ids             = $ids;
        $new_account->role            = '0';
        $new_account->is_admin        = '0';
        $new_account->language        = 'ar';
        $new_account->fullname        = $this->merchant->data->name;
        $new_account->username        = $this->merchant->data->merchant->username;
        $new_account->email           = $this->merchant->data->email;
        $new_account->password        = $user_password;
        $new_account->avatar          = $this->merchant->data->merchant->avatar;
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
            $merchant_credentails->store_id       = $data['merchant'];
            $merchant_credentails->access_token   = $data['data']['access_token'];
            $merchant_credentails->refresh_token  = $data['data']['refresh_token'];
            $merchant_credentails->settings       = '{"custom_merchant_phone":"","abandoned_cart_status":true,"abandoned_cart_message":"\u0645\u0631\u062d\u0628\u0627 {\u0627\u0633\u0645 \u0627\u0644\u0639\u0645\u064a\u0644},\n\n\u0644\u0642\u062f \u0644\u0627\u062d\u0638\u0646\u0627 \u0627\u0646\u0643 \u0646\u0633\u064a\u062a \u0633\u0644\u062a\u0643 \u0641\u064a \u0645\u062a\u062c\u0631\u0646\u0627, \u0648\u0644\u0630\u0644\u0643 \u0627\u0631\u062f\u0646\u0627 \u0627\u0647\u062f\u0627\u0626\u0643 \u0643\u0648\u062f \u062e\u0635\u0645 \u062e\u0627\u0635 \u0644\u0643\u064a \u062a\u0643\u0645\u0644 \u0627\u0644\u0637\u0644\u0628.\n\n\u0642\u064a\u0645\u0629 \u0645\u0634\u062a\u0631\u064a\u0627\u062a\u0643 \u0642\u0628\u0644 \u0627\u0644\u062e\u0635\u0645 \u0647\u064a {\u0627\u062c\u0645\u0627\u0644\u0649_\u0627\u0644\u0633\u0644\u0629}  \u0648\u064a\u0645\u0643\u0646\u0643 \u0627\u0633\u062a\u062e\u062f\u0627\u0645 \u0627\u0644\u0643\u0648\u062f \"LINE\" \u0644\u0644\u062d\u0635\u0648\u0644 \u0639\u0644\u0649 \u062a\u062e\u0641\u064a\u0636 20%.\n\u064a\u0645\u0643\u0646\u0643 \u0627\u0633\u062a\u062e\u062f\u0627\u0645 \u0627\u0644\u0643\u0648\u062f \u0648\u0627\u062a\u0645\u0627\u0645 \u0639\u0645\u0644\u064a\u0629 \u0627\u0644\u0634\u0631\u0627\u0621 \u0628\u0633\u0647\u0648\u0644\u0629 \u0639\u0628\u0631 \u0627\u0644\u0631\u0627\u0628\u0637 \u0627\u0644\u062a\u0627\u0644\u064a\n\n{\u0631\u0627\u0628\u0637_\u0627\u0644\u062f\u0641\u0639}\n\u062e\u062f\u0645\u0629 \u0627\u0644\u0639\u0645\u0644\u0627\u0621","count_abandoned_cart_reminder":2,"request_review_status":true,"message_request_review":"\u0639\u0632\u064a\u0632 \u0627\u0644\u0639\u0645\u064a\u0644 \n\n\u0634\u0643\u0631\u0627 \u0644\u0623\u062e\u062a\u064a\u0627\u0631\u0643 \u0634\u0631\u0627\u0621 \u0645\u0646\u062a\u062c\u0627\u062a\u0646\u0627 \u0648\u0628\u0639\u062f \u0627\u0633\u062a\u0644\u0627\u0645\u0643 \u0627\u0644\u0645\u0646\u062a\u062c \u0646\u062a\u0645\u0646\u0649 \u0633\u0645\u0627\u0639 \u0631\u0623\u064a\u0643\u00a0\u0641\u064a\u00a0\u0645\u0646\u062a\u062c\u0627\u062a\u0646\u0627                                                                                                                                                                                       {\u0631\u0648\u0627\u0628\u0637_\u0627\u0644\u0645\u0646\u062a\u062c\u0627\u062a}","review_added_status":true,"review_added_message":"\u0639\u0632\u064a\u0632\u064a \u0627\u0644\u0639\u0645\u064a\u0644 \n\n\u0646\u0634\u0643\u0631\u0643 \u0644\u062a\u0642\u064a\u064a\u0645 \u0645\u0646\u062a\u062c\u0627\u062a\u0646\u0627 \u0648\u064a\u0633\u0639\u062f\u0646\u0627 \u062a\u0642\u062f\u064a\u0645 \u0643\u0648\u062f \u062e\u0635\u0645 \u062e\u0627\u0635 \u0628\u0643 \u0644\u0634\u0631\u0627\u0621 \u0623\u064a \u0645\u0646\u062a\u062c \u0622\u062e\u0631 \u0623\u0648\u00a0\u0627\u0647\u062f\u0627\u0621\u0647\u00a0\u0644\u0627\u0635\u062f\u0642\u0627\u0626\u0643","otp_status":true,"otp_message":"\u064a\u0627\u0647\u0644\u0627 \u0646\u0648\u0631\u062a\u0646\u0627 \u0631\u0645\u0632 \u0627\u0644\u062a\u062d\u0642\u0642 \u0627\u0644\u062e\u0627\u0635 \u0628\u0643 \u0644\u062a\u0633\u062c\u064a\u0644 \u0627\u0644\u062f\u062e\u0648\u0644 \u0639\u0644\u0649 \u0645\u062a\u062c\u0631 \" \u0648\u0627\u062a\u0633\u0627\u0628 \u0644\u0627\u064a\u0646\" \u0647\u0648 : {\u0631\u0645\u0632_\u0627\u0644\u062a\u062d\u0642\u0642} .. \u062a\u0633\u0648\u0642 \u0645\u0645\u062a\u0639.","new_customer_status":true,"new_customer_message":"\u064a\u0627 \u0647\u0644\u0627 \u0648\u0627\u0644\u0644\u0647, \u0646\u0648\u0631 \u0627\u0644\u0645\u062a\u062c\u0631 \u064a\u0627 {\u0627\u0633\u0645_\u0627\u0644\u0627\u0648\u0644} \n-------\n\u0647\u0644\u0627 \u0648\u063a\u0644\u0627 \u0646\u0648\u0631\u062a\u0646\u0627 \u0648\u0647\u0630\u0627 \u0631\u0642\u0645 \u062e\u062f\u0645\u0629 \u0627\u0644\u0639\u0645\u0644\u0627\u0621 \u0644\u0648 \u0627\u062d\u062a\u062c\u062a \u0627\u064a \u0634\u064a\u0621,\n\u062a\u0648\u0627\u0635\u0644 \u0645\u0639\u0646\u0627 \u0641\u064a \u0627\u064a \u0648\u0642\u062a.. \u062a\u0633\u0648\u0642 \u0645\u0645\u062a\u0639\n\n\u062e\u062f\u0645\u0629 \u0627\u0644\u0639\u0645\u0644\u0627\u0621","order_status":true,"orders_active_on":["payment_pending","under_review","in_progress","completed","delivering","delivered","shipped","canceled","restored","restoring","order_created"],"order_created_message":null,"order_default_message":"\u0639\u0632\u064a\u0632\u064a \u0627\u0644\u0639\u0645\u064a\u0644  {\u0627\u0633\u0645_\u0627\u0644\u0639\u0645\u064a\u0644} \u0637\u0644\u0628\u0643\u0645 \u0631\u0642\u0645  {\u0631\u0642\u0645_\u0627\u0644\u0637\u0644\u0628}  \u062d\u0627\u0644\u062a\u0647 \u0627\u0644\u0627\u0646  {\u062d\u0627\u0644\u0629_\u0627\u0644\u0637\u0644\u0628}","order_payment_pending_message":null,"order_under_review_message":null,"order_in_progress_message":null,"order_completed_message":null,"order_delivering_message":null,"order_delivered_message":null,"order_shipped_message":null,"order_canceled_message":null,"order_restored_message":null,"order_restoring_message":null}';
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
                    $message = urlencode("شريكنا العزيز ".$new_account->username." 👋🏻\n
                    اهلا بك في شركة خط التسويق.
                    نسعد بفتح حساب جديد عبر منصتنا لكم:\n
                    البريد الالكتروني: ".$new_account->email."\n
                    كلمة المرور: ".$password."\n
                    لتفعيل حساب الواتساب الخاص بك نأمل تسجيل الدخول الى حسابك من خلال الرابط التالي: 👇🏻
                    ".self::$platform_link."\n
                    خطوات الربط :\n
                    بعد تسجيل الدخول من الرابط اعلاه اكمل الخطوات التالية:\n
                    اضغط من القائمة إدارة الحساب\n
                    اضغط على زر إضف حساب\n
                    سيظهر لك باركود الآن\n
                    بعدها افتح واتساب الخاص بك\n
                    ثم الاعدادات ثم الاجهزة المرتبطة\n
                    قم بتوجيه الكاميرا اتجاه الباركود\n
                    بعد انجاز الخطوات سيتم ربط الخدمة بمتجرك آليا :
                    ".$store_url."\n
                    وللمزيد من الشروحات الكاملة :\n
                    ".self::$descript_our_platform."\n
                    نرحب بك ونتمنى لك تجارة رابحة\n
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
            return $query->where('merchant_id',$merchant_id);
        })->first();
        $password       = Str::random(5);
        $user_password  = md5($password);
        $user->password = $user_password;
        $user->save();
        $message = urlencode("شريكنا العزيز ".$user->username." 👋🏻\n
        اهلا بك في شركة خط التسويق.
        تم تفعيل حسابك عبر منصتنا:\n
        البريد الالكتروني: ".$user->email."\n
        كلمة المرور: ".$password."\n
        لتفعيل الواتساب الخاص بك نأمل تسجيل الدخول الى حسابك من خلال الرابط التالي: 👇🏻
        ".self::$platform_link."\n
        للربط اكمل الخطوات التالية:
        بعد الدخول على الرابط أعلاه وتسجيل الدخول قم بالعمل الآتي:\n
        اضغط من القائمة إدارة الحساب\n
        اضغط على زر إضف حساب\n
        سيظهر لك باركود الآن\n
        بعدها افتح واتساب الخاص بك\n
        ثم الاعدادات ثم الاجهزة المرتبطة\n
        قم بتوجيه الكاميرا اتجاه الباركود\n
        وللمزيد من الشروحات الكاملة :\n
        ".self::$descript_our_platform."\n

        نرحب بك ونتمنى لك تجارة رابحة\n
        ");

        $merchant = MerchantCredential::where([
            'user_id'     => $user->id,
            'merchant_id' => $merchant_id,
            'app_name'    => 'salla'
        ])->first();

        $settings = $merchant->settings ? json_decode($merchant->settings,true) : [];

        $phone_number = count($settings) > 0 ? ( (isset($settings['custom_merchant_phone']) && $settings['custom_merchant_phone'] != null) ? $settings['custom_merchant_phone'] : $merchant->phone) : $merchant->phone;

        $phone_number = ($phone_number == '966512345678' ? $merchant->phone : $phone_number);
        return send_message($phone_number,$message);
    }

    public static function add_date_plus($days = 12){
        $date = Carbon::now();
        $date->addDays($days);
        return strtotime($date->toDateString());
    }
}
