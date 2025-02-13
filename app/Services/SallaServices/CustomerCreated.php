<?php
namespace App\Services\SallaServices;

use Log;
use App\Models\Team;
use App\Models\Account;
use App\Models\EventStatus;
use App\Models\SuccessTempModel;
use App\Models\MerchantCredential;
use Illuminate\Support\Facades\DB;
use App\Models\FailedMessagesModel;
use Illuminate\Support\Facades\Cache;
use App\Services\AppSettings\AppEvent;

class CustomerCreated implements AppEvent{
    public $data;
    protected $merchant_team = null;

    protected $settings;

    public function __construct($data)
    {
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

    public function set_log()
    {
        // encode log
        $log = json_encode($this->data, JSON_UNESCAPED_UNICODE) . PHP_EOL;

        // set log data
        // Log::build([
        //     'driver' => 'single',
        //     'path' => storage_path('logs/salla_events.log'),
        // ])->info($log);
    }

    public function resolve_event(){
        if(!isset($this->settings['new_customer_status'])) return;
        if($this->settings['new_customer_status'] != 1) return;

        // check if account have token or not
        if(!$this->merchant_team) return;
        $account = Account::where([
            'team_id' => $this->merchant_team->id
        ])->first();
        if( (!$account) || ($account->token == null)) return;

        $lock = Cache::lock('event-'.$this->data['event'].'-'.$this->data['merchant'].'-'.$this->data['data']['mobile'],30);
        if($lock->get()){
            $attrs = formate_customer_details($this->data);
            DB::beginTransaction();
            try{
                $app_event = EventStatus::updateOrCreate([
                    'unique_number' => $this->data['merchant'].$this->data['data']['id']
                ],[
                    'values'        => json_encode($this->data),
                    'event_from'    => "salla",
                    'type'          => $this->data['event']
                ]);

                if($app_event->status != 'success'):
                    $message = isset($this->settings['new_customer_message']) ? $this->settings['new_customer_message'] : '';
                    $filter_message = message_order_params($message, $attrs);
                    $app_event->update([
                        'status' => 'success' //$result_send_message
                    ]);
                    $result_send_message = send_message(
                        $this->data['data']['mobile_code'].$this->data['data']['mobile'],
                        $filter_message,
                        $account->token,
                        $this->merchant_team->ids
                    );
                    $app_event->increment('count_of_call');
                endif;
                DB::commit();
            } catch(\Exception $e){
                DB::rollBack();
            } finally {
                $lock->release();
            }
        }
    }
}


