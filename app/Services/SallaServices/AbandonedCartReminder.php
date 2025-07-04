<?php
namespace App\Services\SallaServices;

use Log;
use App\Models\Team;
use App\Models\Account;
use App\Models\MerchantCredential;
use Illuminate\Support\Facades\DB;

class AbandonedCartReminder {
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
    }

    public function resolve_event($app_event){
        if(!isset($this->settings['abandoned_cart_status'])) return;
        if($this->settings['abandoned_cart_status'] != 1) return;

        // check if account have token or not
        if(!$this->merchant_team) return;
        $account = Account::where([
            'team_id' => $this->merchant_team->id
        ])->first();
        if( (!$account) || ($account->token == null)) return;
            $attrs = formate_cart_details($this->data);
            DB::beginTransaction();
            try{
                // "" ?: $attrs['customer_phone_number']
                if($app_event->status != 'success'):
                    $app_event->update([
                        'required_call' => isset($this->settings['count_abandoned_cart_reminder']) ? $this->settings['count_abandoned_cart_reminder'] : 1
                    ]);

                    //if($app_event->required_call > 1):
                        // $app_event->update([
                        //     'status' =>'success'
                        // ]);
                    //endif;

                    $message = $this->settings['abandoned_cart_message'] ?: '';
                    $filter_message = message_order_params($message, $attrs);
                    $result_send_message = send_message(
                        $this->data['data']['customer']['mobile'],
                        $filter_message,
                        $account->token,
                        $this->merchant_team->ids
                    );

                    if($result_send_message == 'success'):
                        $app_event->increment('count_of_call');

                        if($app_event->count_of_call == $app_event->required_call):
                            $app_event->update([
                                'status' => $result_send_message
                            ]);
                        endif;
                    else:
                        $app_event->update([
                            'status' => $result_send_message
                        ]);
                    endif;
                endif;
                DB::commit();
            } catch(\Exception $e){
                DB::rollBack();
            }
    }
}
