<?php
namespace App\Services\SallaServices;

use Log;
use App\Models\Team;
use App\Models\EventStatus;
use App\Models\SuccessTempModel;
use App\Models\FailedMessagesModel;
use Illuminate\Support\Facades\Http;
use App\Services\AppSettings\AppEvent;
use App\Services\AppSettings\AppMerchant;

class OtpRequest extends AppMerchant implements AppEvent{
    public $data;

    protected $merchant_team = null;
    public function __construct($data){
        // set data
        $this->data = $data;

         // merchant
         $this->merchant_team = Team::with('account')->where([
            'ids' => $this->data['merchant']
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
        $app_event = EventStatus::updateOrCreate([
            'unique_number' => $this->data['merchant'],
            'values'        => json_encode($this->data)
        ],[
            'event_from'    => "salla",
            'type'          => $this->data['event']
        ]);

        $attrs['otp_code'] = $this->data['data']['code'];

        if(filter_var($this->data['data']['contact'],FILTER_VALIDATE_EMAIL)) return;

        if($app_event->status != 'success'):
            $message = "كود التحقق {رمز_التحقق}";
            $filter_message = message_order_params($message, $attrs);
            $result_send_message = send_message(
                "201026051966" ?: $this->data['data']['contact'],
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