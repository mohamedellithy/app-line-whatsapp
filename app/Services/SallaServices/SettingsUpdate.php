<?php
namespace App\Services\SallaServices;

use Log;
use App\Models\Team;
use App\Models\MerchantCredential;
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
        $this->merchant_team = Team::with('account')->where([
            'ids' => $merchant_info->user->ids
        ])->first();


        // track event by using Log
        $this->set_log();
    }

    public function set_log(){
        // encode log
        $log = json_encode($this->data, JSON_UNESCAPED_UNICODE) . PHP_EOL;

        // set log data
        Log::build([
             'driver' => 'single',
             'path' => storage_path('logs/salla_events.log'),
        ])->info($log);
    }

    public function resolve_event(){
        $merchant_credential = MerchantCredential::where([
            'app_name'       => 'salla',
            'merchant_id' => $this->data['merchant']
        ])->first();

        if($merchant_credential):
            $merchant_credential->update([
                'settings' => json_encode($this->data['data']['settings'])
            ]);
        endif;

    }
}
