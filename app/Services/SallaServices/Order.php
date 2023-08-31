<?php
namespace App\Services\SallaServices;

use Log;
use App\Models\Team;
use App\Models\Account;
use App\Models\EventStatu;
use App\Models\EventStatus;
use App\Models\ReviewRequest;
use App\Models\WhatsappSession;
use App\Models\SuccessTempModel;
use App\Models\MerchantCredential;
use App\Models\FailedMessagesModel;
use Illuminate\Support\Facades\Http;
use App\Services\AppSettings\AppEvent;
use App\Services\AppSettings\AppMerchant;

class Order extends AppMerchant implements AppEvent{

    public $data;
    protected $merchant_team = null;

    protected $settings;

    public function __construct($data){
        // set data
        $this->data = $data;

        // merchant
        $this->merchant_team = Team::with('account')->where([
            'ids' => $this->data['merchant']
        ])->first();

        $this->settings      = MerchantCredential::where([
            'merchant_id'    => $this->data['merchant']
        ])->value('settings');

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
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/salla_events.log'),
        ])->info($log);
    }

    public function resolve_event(){
        $attrs = formate_order_details($this->data);
        $app_event = EventStatus::updateOrCreate([
            'unique_number' => $this->data['merchant'].$this->data['data']['id'],
            'values'        => json_encode($this->data)
        ],[
            'event_from'    => "salla",
            'type'          => $this->data['event']
        ]);


        // "" ?: $attrs['customer_phone_number']
        if($app_event->status != 'success'):

            if(!in_array($this->data['data']['status']['slug'],$this->settings['orders_active_on'])) return;

            $index_message = 'orders_'.$this->data['data']['status']['slug'].'_message';
            $message = isset($this->settings[$index_message]) ? $this->settings[$index_message] : $this->settings['orders_default_message'];

            $filter_message = message_order_params($message, $attrs);
            $result_send_message = send_message(
                "201026051966",
                $filter_message,
                $this->merchant_team->account->token,
                $this->merchant_team->ids
            );

            $app_event->update([
                'status' => $result_send_message
            ]);

            $app_event->increment('count_of_call');

            Http::post('https://webhook.site/19694e58-fa42-41d5-a247-2187b0718cf7',$this->data);
        endif;
    }
}
