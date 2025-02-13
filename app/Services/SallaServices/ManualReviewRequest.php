<?php
namespace App\Services\SallaServices;
use Log;
use App\Models\Team;
use App\Models\Account;
use App\Models\EventStatus;
use App\Models\MerchantCredential;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\AppSettings\AppEvent;
use App\Services\AppSettings\KarzounRequest;

class ManualReviewRequest implements AppEvent{
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
        if(!isset($this->settings['request_review_status'])) return;
        if($this->settings['request_review_status'] != 1) return;

        // check if account have token or not
        if(!$this->merchant_team) return;
        $account = Account::where([
            'team_id' => $this->merchant_team->id
        ])->first();
        if( (!$account) || ($account->token == null)) return;

        $lock = Cache::lock('event-review-'.$this->data['event'].'-'.$this->data['merchant'].'-'.$this->data['data']['id'], 30);
        if($lock->get()){
            $attrs = formate_order_details($this->data);
            DB::beginTransaction();
            try {
                $app_event = EventStatus::updateOrCreate([
                    'unique_number' => $this->data['merchant'],
                    'values'        => json_encode($this->data)
                ],[
                    'type'          => 'request_review',
                    'event_from'    => "salla"
                ]);
    
                if($app_event->status != 'success'):
                    $message = isset($this->settings['message_request_review']) ? $this->settings['message_request_review'] : '';
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
                DB::commit();
            } catch(\Exception $e){
                DB::rollBack();
            }
        }
    }

}
