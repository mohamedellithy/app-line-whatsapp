<?php
namespace App\Services\SallaServices;

use App\Models\FailedMessagesModel;
use App\Models\SuccessTempModel;
use App\Services\AppSettings\AppEvent;
use Log;
class CustomerCreated implements AppEvent{
    public $data;
    public function __construct($data)
    {
        // set data
        $this->data = $data;

        // track event by using Log
        $this->set_log();
    }

    public function set_log()
    {
        // encode log
        $log = json_encode($this->data, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        // set log data
        Log::channel('auth_events')->info($log);
    }

    public function resolve_event()
    {
        $this->store_id  = $this->data->merchant;
        $created_at      = $this->data->created_at;
        $sotre_url       = $this->data->data->urls->customer;
        $customer_mobile = $this->data->data->mobile;
        $customer_mobile_code       = $this->data->data->mobile_code;
        $customer_id                = $this->data->data->id;
        $customer_mobile_code_clean = str_replace("+", "", $customer_mobile_code);
        $phone                      = $customer_mobile_code_clean . $customer_mobile;
        $send_once                  = $customer_id . $phone;
        $count_data                 = SuccessTempModel::where("unique_number", $send_once)->count();

        if ($count_data === 0) {
            $customer_first_name = $this->data->data->first_name;
            $customer_last_name  = $this->data->data->last_name;
            $this->customer_full_name  = $customer_first_name . ' ' . $customer_last_name;

            $array               =  $this->get_merchant_settings();
            $get_instances       =  $this->get_instances_data();
            list($instance_id, $api_url) = $get_instances;
            /**********message type start**********/
            $send_or_not     = $array->data->settings->karzoun_welcome_msg_check;
            $message_to_send = $array->data->settings->karzoun_welcome_msg;

            $message_to_send = $this->handle_message_to_send($message_to_send);
            /**********message type end**********/
            echo $instance_id.' '.$api_url;
            if ($send_or_not == 1) {
                $check_if_sent =  $this->send_message($phone, $message_to_send, $instance_id, $this->store_id, $api_url);
                $response      = json_encode($check_if_sent);
                $log = $send_once . ' ' . $created_at . PHP_EOL;
                if (!empty($check_if_sent)) {
                    if ($check_if_sent->status == 'success') {
                        $SuccessTempModel                = new SuccessTempModel();
                        $SuccessTempModel->unique_number = $send_once;
                        $SuccessTempModel->values        = json_encode($this->data);
                        $SuccessTempModel->type          = "new-customer";
                        $SuccessTempModel->event_from    = "salla";
                        $SuccessTempModel->save();

                        //   file_put_contents('karzoun_log/success-new-customer.txt', $log, FILE_APPEND);
                        echo ' Sent :)';
                    } else {
                        $FailedMessagesModel = new FailedMessagesModel();
                        $FailedMessagesModel->unique_number = $send_once;
                        $FailedMessagesModel->value         = json_encode($this->data);
                        $FailedMessagesModel->reason        = $response;
                        $FailedMessagesModel->type          = "new-customer";
                        $FailedMessagesModel->event_from    = "salla";
                        $FailedMessagesModel->save();
                        // echo 'the response is' . $response.PHP_EOL;
                    }
                } else {
                    $FailedMessagesModel = new FailedMessagesModel();
                    $FailedMessagesModel->unique_number = $send_once;
                    $FailedMessagesModel->value         = json_encode($this->data);
                    $FailedMessagesModel->reason        = $response;
                    $FailedMessagesModel->type          = "new-customer";
                    $FailedMessagesModel->event_from    = "salla";
                    $FailedMessagesModel->save();

                    //echo 'the response is' . $response.PHP_EOL;
                }
            } else {
                echo 'set to not send';
            }
        } else {
            echo "Sent Before :)";
        }
    }

    public function handle_message_to_send($message_to_send = ''){
        preg_match_all("/{(.*?)}/", $message_to_send, $search);

        foreach ($search[1] as $variable) {
            $client_status = [
                'اسم العميل'          => $this->customer_full_name,
            ];
            $message_to_send = str_replace("{" . $variable . "}", $client_status[$variable], $message_to_send);
        }

        $message_to_send = urlencode($message_to_send);
        return $message_to_send;
    }

}
