<?php
namespace App\Services\SallaServices;

use Log;
use App\Models\Team;
use App\Models\Account;
use App\Models\AbandBaskts;
use App\Models\EventStatus;
use App\Models\MerchantCredential;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Services\AppSettings\AppEvent;
use App\Services\AppSettings\AbandonedCart as  AbandonedCartSettings;

class AbandonedCartPurchased implements AppEvent{
    public $data;
    protected $merchant_team = null;

    protected $settings;

    public function __construct($data){
        // set data
        $this->data = $data;

        $merchant_info = MerchantCredential::where([
            'app_name'       => 'salla',
            'merchant_id'    => $this->data['merchant']
        ])->first();

        // merchant
        if(!$merchant_info) return;
        $this->merchant_team = Team::with('account')->where([
            'owner' => $merchant_info->user_id
        ])->first();

        $this->settings      = $merchant_info->settings;

        if($this->settings != null):
            $this->settings = json_decode($this->settings,true);
        endif;

        // track event by using Log
        $this->set_log();

    }

    public function set_log(){
        // encode log
        $log = json_encode($this->data, JSON_UNESCAPED_UNICODE) . PHP_EOL;

        // set log data
        // Log::build([
        //     'driver' => 'single',
        //     'path' => storage_path('logs/salla_events.log'),
        // ])->info($log);
    }

    public function resolve_event(){
        if(!isset($this->settings['abandoned_cart_status'])) return;
        if($this->settings['abandoned_cart_status'] != 1) return;

        // check if account have token or not
        if(!$this->merchant_team) return;
        $account = Account::where([
            'team_id' => $this->merchant_team->id
        ])->first();
        if( (!$account) || ($account->token == null)) return;

        if($this->data['data']['status'] == 'purchased'){
            $app_event = EventStatus::where([
                'unique_number' => $this->data['merchant'].$this->data['data']['id'],
                'event_from'    => "salla",
                'type'          => "abandoned.cart"
            ])->first();
    
            if($app_event){
                $app_event->delete();
            }
        }
    }
}
