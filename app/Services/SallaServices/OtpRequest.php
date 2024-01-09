<?php
namespace App\Services\SallaServices;

use Log;
use App\Models\Team;
use App\Models\Account;
use App\Models\EventStatus;
use App\Models\SuccessTempModel;
use App\Models\MerchantCredential;
use App\Models\FailedMessagesModel;
use Illuminate\Support\Facades\Http;
use App\Services\AppSettings\AppEvent;
use App\Services\AppSettings\AppMerchant;

class OtpRequest extends AppMerchant implements AppEvent{
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

        if(!$merchant_info) return;

        // merchant
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
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/salla_events.log'),
        ])->info($log);
    }

    public function resolve_event(){
        if(!isset($this->settings['otp_status'])) return;
        if($this->settings['otp_status'] != 1) return;

        // check if account have token or not
        if(!$this->merchant_team) return;
        $account = Account::where([
            'team_id' => $this->merchant_team->id
        ])->first();
        if( (!$account) || ($account->token == null)) return "d";


        $app_event = EventStatus::updateOrCreate([
            'unique_number' => $this->data['merchant'].$this->data['data']['code'],
            'values'        => json_encode($this->data)
        ],[
            'event_from'    => "salla",
            'type'          => $this->data['event']
        ]);

        $attrs['otp_code'] = $this->data['data']['code'];

        if(filter_var($this->data['data']['contact'],FILTER_VALIDATE_EMAIL)) return;

        Http::WithOptions([
            'verify' => false
        ])->post('https://typedwebhook.tools/webhook/2cb07b6c-5499-48e0-8458-73480334f3db',[
            'cart' => $app_event
        ]);

        if($app_event->status != 'success'):
            $message = isset($this->settings['otp_message']) ? $this->settings['otp_message'] : '';
            $filter_message = message_order_params($message, $attrs);

            $result_send_message = send_message(
                $this->data['data']['contact'],
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
