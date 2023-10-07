<?php
namespace App\Services\SallaServices;

use Log;
use App\Models\Team;
use App\Models\EventStatus;
use App\Models\SuccessTempModel;
use App\Models\MerchantCredential;
use App\Models\FailedMessagesModel;
use App\Services\AppSettings\AppEvent;

class CustomerCreated implements AppEvent{
    public $data;
    protected $merchant_team = null;

    protected $settings;

    public function __construct($data)
    {
        // set data
        $this->data = $data;

        // merchant
        $this->merchant_team = Team::with('account')->where([
            'ids' => $this->data['merchant']
        ])->first();

        $this->settings      = MerchantCredential::where([
            'app_name'       => 'salla',
            'merchant_id'    => $this->data['merchant']
        ])->value('settings');

        if($this->settings != null):
            $this->settings = json_decode($this->settings,true);
        endif;

        // track event by using Log
        $this->set_log();
    }

    public function set_log()
    {
        // encode log
        $log = json_encode($this->data, JSON_UNESCAPED_UNICODE) . PHP_EOL;

        // set log data
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/salla_events.log'),
        ])->info($log);
    }

    public function resolve_event(){
        if($this->settings['new_customer_status'] != 1) return;
        $attrs = formate_customer_details($this->data);
        $app_event = EventStatus::updateOrCreate([
            'unique_number' => $this->data['merchant'],
            'values'        => json_encode($this->data)
        ],[
            'event_from'    => "salla",
            'type'          => $this->data['event']
        ]);

        if($app_event->status != 'success'):
            $message = isset($this->settings['new_customer_message']) ? $this->settings['new_customer_message'] : '';
            $filter_message = message_order_params($message, $attrs);
            $result_send_message = send_message(
                $this->data['data']['mobile_code'].$this->data['data']['mobile'],
                $filter_message,
                $this->merchant_team->account->token,
                $this->merchant_team->ids
            );

            $app_event->update([
                'status' => $result_send_message
            ]);

            $app_event->increment('count_of_call');
        endif;
    }
}
