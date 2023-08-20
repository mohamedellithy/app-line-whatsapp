<?php
namespace App\AppServices\AppSettings;

use App\Models\Team;
use App\Models\SpPermession;
use App\Models\Instance;
class AppMerchant{
    protected $store_id;
    protected $order_status;
    protected $order_id;
    protected $order_amount;
    protected $customer_full_name;
    protected $currency;
    protected $order_url;
    protected $tracking_number;
    protected $tracking_link;
    protected $shipping_company;

    public function get_merchant_settings(){
        $merchant_settings = Team::where('ids',$this->store_id)->first();
        $settings          = $merchant_settings->data;
        if ($settings) {
            $array         = unserialize($settings);
        } else {
            echo "please update your app settings";
            return;
        }

        return $array;
    }

    public function get_instances_data(){
        $team            = Team::where('ids',$this->store_id)->first();
        $team_id         = $team->id;
        $instance        = Instance::where('access_token',$this->store_id)->first();
        if ($instance) {
            $instance_id     = $instance->instance_id;
            $api_url         = 'wa.karzoun.app';
        } else {
            $permission  = SpPermession::where('team_id',$team_id)->first();
            if ($permission) {
                $instance_id = $permission->token;
                $api_url     = 'karzoun.app';
            }else{
                echo 'reconnect please Team id : '.$team_id.' Merchant Id : '.$this->store_id.PHP_EOL;
                return;
            }
        }
        $result = array($instance_id, $api_url);
        return $result;
    }

    public function send_message($phone,$message,$instance_id,$access_token,$api_url){
        set_time_limit(0);
        $response_send_message   = KarzounRequest::resolve(
            $end_point    = "https://$api_url/api/send.php?number=$phone&type=text&message=$message&instance_id=$instance_id&access_token=$access_token",
            $request_type = 'POST'
        );
        return $response_send_message;
    }
}
