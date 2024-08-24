<?php
namespace App\Services\SallaServices;
use Log;
use App\Models\Team;
use App\Models\Account;
use App\Models\EventStatus;
use App\Models\MerchantCredential;
use App\Services\AppSettings\AppEvent;
use App\Services\AppSettings\KarzounRequest;

class ReviewAdded implements AppEvent{
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
        $log = json_encode($this->data, JSON_UNESCAPED_UNICODE) . PHP_E
    }

    public function resolve_event(){
        if(!isset($this->settings['review_added_status'])) return;
        if($this->settings['review_added_status'] != 1) return;
        $attrs = formate_customer_from_reviews_details($this->data);

        // check if account have token or not
        if(!$this->merchant_team) return;
        $account = Account::where([
            'team_id' => $this->merchant_team->id
        ])->first();
        if( (!$account) || ($account->token == null)) return;

        if($this->data['data']['type'] != 'testimonial') return;

        $app_event = EventStatus::updateOrCreate([
            'unique_number' => $this->data['merchant'],
            'values'        => json_encode($this->data)
        ],[
            'event_from'    => "salla",
            'type'          => $this->data['event']
        ]);

        

        if($app_event->status != 'success'):
            $message = isset($this->settings['review_added_message']) ? $this->settings['review_added_message'] : '';
            $filter_message = message_order_params($message, $attrs);

            $result_send_message = send_message(
                $this->data['data']['customer']['mobile'],
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
