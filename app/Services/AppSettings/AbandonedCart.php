<?php
namespace App\Services\AppSettings;// اسم مميز للكلاس

use Log;
use App\Models\AbandCart;
use App\Services\AppSettings\AppEvent;

class AbandonedCart implements AppEvent{

    public $data;
    public $source = 'salla';

    public function __construct($data){
        // set data
        $this->data = $data;

        // track event by using Log
        $this->set_log();
    }

    public function set_log(){
        // encode log
        $log = json_encode($this->data, JSON_UNESCAPED_UNICODE) . PHP_EOL;

        // set log data
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/abandoned_cart.log'),
        ])->info($log);
    }

    public function resolve_event(){
        // call events
        $cart_id = $this->data['data']['id'];
        $check_if_aband = AbandCart::where("cart_id", $cart_id)->where('source',$this->source)->first();
        if (empty($check_if_aband)) {
            $appand_baskts              = new AbandCart();
            $appand_baskts->merchant_id = $this->data['merchant'];
            $appand_baskts->cart_id     = $this->data['data']['id'];
            $appand_baskts->source      = $this->source;
            $appand_baskts->data        = json_encode($this->data);
            $appand_baskts->save();
        }
    }
}
