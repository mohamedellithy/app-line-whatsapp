<?php
namespace App\Services\SallaServices;

use Log;
use App\Models\Team;
use App\Models\MerchantCredential;
use App\Services\SallaServices\User;
use Illuminate\Support\Facades\Http;
use App\Services\AppSettings\AppEvent;

class SettingsUpdate implements AppEvent{
    public $data;
    protected $merchant_team = null;
    public function __construct($data){
        // set data
        $this->data = $data;

        $merchant_info = MerchantCredential::where([
            'app_name'       => 'salla',
            'merchant_id'    => $this->data['merchant']
        ])->first();

        // merchant
        if($merchant_info?->user_id):
            $this->merchant_team = Team::with('account')->where([
                'owner' => $merchant_info->user_id
            ])->first();
        endif;


        // track event by using Log
        $this->set_log();
    }

    public function set_log(){
        // encode log
        $log = json_encode($this->data, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }

    public function resolve_event(){
        $merchant_credential = MerchantCredential::where([
            'app_name'       => 'salla',
            'merchant_id' => $this->data['merchant']
        ])->first();
        
        if(!$merchant_credential) return;

        $filter_settings = json_decode($merchant_credential?->settings,true);

        // if merchant not change number phone
        $this->data['data']['settings']['custom_merchant_phone'] = ($this->data['data']['settings']['custom_merchant_phone'] != '966512345678' ? $this->data['data']['settings']['custom_merchant_phone'] : null);

        if($merchant_credential):
            $merchant_credential->update([
                'settings' => json_encode($this->data['data']['settings'])
            ]);
        endif;

        if(isset($this->data['data']['settings']['custom_merchant_phone']) && ($this->data['data']['settings']['custom_merchant_phone'] != null)):
            if(isset($filter_settings['custom_merchant_phone']) && ($filter_settings['custom_merchant_phone'] != null)):
                if($filter_settings['custom_merchant_phone'] != $this->data['data']['settings']['custom_merchant_phone']):
                    User::reset_password($this->data['merchant']);
                endif;
            else:
                User::reset_password($this->data['merchant']);
            endif;
        endif;
    }
}
