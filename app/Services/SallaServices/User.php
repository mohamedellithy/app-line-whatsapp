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
    public static function check_user_exist($data) {
        // get user info
        $user = SpUser::whereHas('merchant_info',function($query) use($data){
            return $query->where('merchant_id',$data['merchant']);
        })->first();

        // change update json access token and refresh token
        if($user):
            $user->merchant_info()->update([
                'access_token' => $data['data']['access_token'],
                'refresh_token'=> $data['data']['refresh_token']
            ]);
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
        $plan_id        = '1';
        $platform_link  = "https://wh.line.sa/login";
        $new_account                  = new SpUser();
        $new_account->ids             = $data['merchant'] ?: $this->store->data->id;
        $new_account->role            = '0';
        $new_account->is_admin        = '0';
        $new_account->language        = 'ar';
        $new_account->fullname        = $this->merchant->data->name   ?: $this->store->data->name;
        $new_account->username        = $this->store->data->name      ?: $this->merchant->data->merchant->username;
        $new_account->email           = $this->merchant->data->email  ?: $this->store->data->email;
        $new_account->password        = $user_password;
        $new_account->avatar          = $this->store->data->avatar    ?: $this->merchant->data->merchant->avatar;
        $new_account->plan            = '1';
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
            $merchant_credentails->store_id       = $this->store->data->id;
            $merchant_credentails->access_token   = $data['data']['access_token'];
            $merchant_credentails->refresh_token  = $data['data']['refresh_token'];
            $merchant_credentails->save();

            $package = SpPlan::first() ?: null;
            if($package):
                $new_team              = new Team();
                $new_team->ids         = $data['merchant'] ?: $this->store->data->id;
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
                        👈 البريد الالكترونى : {$new_account->email} \n
                        👈 اسم المستخدم : {$new_account->username} \n
                        👈 كلمة المرور  : {$password} \n
                        👈 رابط المنصة : {$platform_link} \n
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

    public static function add_date_plus($days = 12){
        $date = Carbon::now();
        $date->addDays($days);
        return strtotime($date->toDateString());
    }
}
