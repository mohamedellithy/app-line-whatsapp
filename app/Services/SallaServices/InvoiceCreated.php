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
use Illuminate\Support\Facades\Cache;
use App\Services\AppSettings\AppEvent;
use App\Services\AppSettings\AppMerchant;
use App\Services\SallaServices\ManualReviewRequest;

class InvoiceCreated extends AppMerchant implements AppEvent{

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
        if(!isset($this->settings['orders_active_on'])) return;
        
        if($this->data['event'] == 'invoice.created'):
            if(!in_array("invoice_created",$this->settings['orders_active_on'])):
                return;
            endif;
        endif;
        
        // check if account have token or not
        if(!$this->merchant_team) return;
        
        $account = Account::where([
            'team_id' => $this->merchant_team->id
        ])->first();
        if( (!$account) || ($account->token == null)) return 'd';

        $lock = Cache::lock('event-'.$this->data['event'].'-'.$this->data['merchant'].'-'.$this->data['data']['id'], 60);
        if($lock->get()){
            $attrs = formate_invoice_details($this->data);
            $app_event = EventStatus::updateOrCreate([
                'unique_number' => $this->data['merchant'].$this->data['data']['id']
            ],[
                'values'        => json_encode($this->data),
                'event_from'    => "salla",
                'type'          => $this->data['event']
            ]);


            // "" ?: $attrs['customer_phone_number']
            if($app_event->status != 'success'):
                
                $message = isset($this->settings['order_invoice_message']) ? $this->settings['order_invoice_message'] : $this->settings['order_default_message'];
                
                
                $attrs['customer_full_name'] = $attrs['first_name'].' '.$attrs['last_name'];
                $filter_message = message_order_params($message, $attrs);

                $result_send_message = send_message(
                    $attrs['customer_phone_number'],
                    $filter_message,
                    $account->token,
                    $this->merchant_team->ids
                );

                $app_event->update([
                    'status' => $result_send_message
                ]);


                $app_event->increment('count_of_call');

            endif;
        }

    }
}
