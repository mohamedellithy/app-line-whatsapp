<?php
namespace App\Services\AbayaServices;

use App\Models\FailedMessagesModel;
use App\Models\SuccessTempModel;
use App\Services\AppSettings\AppMerchant;
use App\Services\AppSettings\AppEvent;
use App\Services\AppSettings\KarzounRequest;
use Log;
class Order extends AppMerchant implements AppEvent{

    public $data;
    public function __construct($data){
        // set data
        $this->data = $data;

        // track event by using Log
        $this->set_log();
    }

    public function set_log(){
        // encode log
        $log = json_encode($this->data, JSON_UNESCAPED_UNICODE) . PHP_EOL;

        // set log data
        Log::channel('settings_update_events')->info($log);
    }

    public function resolve_event(){
        $this->store_id = $this->data->merchant;
        $this->order_id = $this->data->data->reference_id;
        if (isset($this->data->data->status->customized->name)){
            $this->order_status    = $this->data->data->status->customized->name;
            $order_status_id       = $this->data->data->status->customized->id;
        }
        else
        {
            $this->order_status    = $this->data->data->status->name;
            $order_status_id       = $this->data->data->status->id;
        }


        $this->shipping_company     = $this->data->data->shipping->company ?? 'توصيل خاص';
        $this->tracking_number      = $this->data->data->shipping->shipment->id ?? '(لم يتم تحديد رقم التتبع بعد)';
        $this->tracking_link        = $this->data->data->shipping->shipment->tracking_link ?? '(لم يتم انشاء رابط التتبع بعد)';
        $this->currency             = $this->data->data->currency;
        $this->order_url            = $this->data->data->urls->customer;
        $order_status_check         = $this->data->data->status->name;
        $payment_method             = $this->data->data->payment_method;
        $created_at           = $this->data->created_at;
        $customer_mobile      = $this->data->data->customer->mobile;
        $customer_mobile_code = $this->data->data->customer->mobile_code;
        $customer_mobile_code_clean = str_replace("+", "", $customer_mobile_code);
        $phone     = $customer_mobile_code_clean . $customer_mobile;
        $send_once = $this->order_id . $order_status_id . $phone;

        $count_data = SuccessTempModel::where("unique_number", $send_once)->count();

        if ($count_data === 0)
        {
            $this->order_amount  = $this->data->data->amounts->total->amount;
            $cart_currency       = $this->data->data->amounts->total->currency;
            $customer_first_name = $this->data->data->customer->first_name;
            $customer_last_name  = $this->data->data->customer->last_name;
            $this->customer_full_name  = $customer_first_name . ' ' . $customer_last_name;
            $customer_email      = $this->data->data->customer->email;

            /******* start database pull ************/
            $array           =  $this->get_merchant_settings();

            $cod_msg_check_if_active     = $array->data->settings->karzoun_cod_msg_check ?? 0;
            $karzoun_review_check_status = $array->data->settings->karzoun_review_check_status ?? 'delivered';

            /**********message type start**********/
            $event = $this->data->event;
            if ($event == 'order.created' && $cod_msg_check_if_active == 1 && $payment_method == 'cod')
            {
                $send_or_not = 1;
                $message_to_send = $array->data->settings->karzoun_cod_msg;
            }else{
                $status = [
                    'قيد التنفيذ'          => 'wa_message_in_progress_2',
                    'بإنتظار المراجعة'     => 'wa_awating_review_1',
                    'تم التنفيذ'           => 'wa_done',
                    'تم التوصيل'           => 'wa_delivered',
                    'الغاء الطلب'         => 'wa_cancelling_order',
                    'جاري التوصيل'        => 'wa_delivery_in_progress',
                    'مسترجع'               => 'wa_retrieved',
                    'قيد الإسترجاع'        => 'wa_being_retrieved',
                    'تم الشحن'            => 'wa_charged',
                    'طلبك في معمل الخياطة' =>'wa_your_request_in_the_sewing_lab',
                    'جاري الإستبدال'      =>'wa_replacing',
                    'جاري تجهيز المنتج' =>'wa_the_product_is_being_processed',
                    'ملغي جزئي' =>'wa_partial_canceled',
                    'شحن جزئي' =>'wa_partial_shipping',
                    'طلب قيد التنفيذ في معمل الخياطة' =>'wa_your_request_is_being_processed_in_the_sewing_lab',
                    'قيد التنفيذ للإستبدال' => 'wa_in_progress_for_replacement'
                ];
                $msg             = $status[$order_status_check];
                $send_or_not     = $array->data->settings->$msg;
                $message_to_send = $array->data->settings->$msg;

                if(!isset($status[$order_status_check])){
                    $send_or_not = 0;
                    return;
                }
            }

            /**********message type end**********/
            if ($send_or_not == 1){
                echo 'started to send';
                $check_if_sent  =  $this->send_message($phone,$message_to_send);
                $response       =  json_encode($check_if_sent);
                $log = $send_once . ' ' . $created_at . PHP_EOL;
                if (!empty($check_if_sent))
                {
                    if ($check_if_sent->status == 'success')
                    {
                        $SuccessTempModel = new SuccessTempModel();
                        $SuccessTempModel->unique_number = $send_once;
                        $SuccessTempModel->values        = json_encode($this->data);
                        $SuccessTempModel->type          = "$event";
                        $SuccessTempModel->event_from    = "abaya";
                        $SuccessTempModel->save();
                        echo ' Sent :)';
                    }
                    else
                    {
                        $FailedMessagesModel                = new FailedMessagesModel();
                        $FailedMessagesModel->unique_number = $send_once;
                        $FailedMessagesModel->value         = json_encode($this->data);
                        $FailedMessagesModel->reason        = $response;
                        $FailedMessagesModel->type          = "$event";
                        $FailedMessagesModel->event_from    = "abaya";
                        $FailedMessagesModel->save();
                        echo 'the response is' . $response;
                    }
                }
                else
                {
                    $FailedMessagesModel                = new FailedMessagesModel();
                    $FailedMessagesModel->unique_number = $send_once;
                    $FailedMessagesModel->value         = json_encode($this->data);
                    $FailedMessagesModel->reason        = $response;
                    $FailedMessagesModel->type          = "$event";
                    $FailedMessagesModel->event_from    = "abaya";
                    $FailedMessagesModel->save();
                    echo 'the response is' . $response;
                }
            }
            else
            {
                echo 'set to not send :)';
                // here i am also adding order created webhooks to check if order created or not in database for abandoned carts
                if ($event == 'order.created'){
                    $SuccessTempModel                = new SuccessTempModel();
                    $SuccessTempModel->unique_number = $send_once;
                    $SuccessTempModel->values        = json_encode($this->data);
                    $SuccessTempModel->type          = "$event";
                    $SuccessTempModel->event_from    = "abaya";
                    $SuccessTempModel->status        = "false";
                    $SuccessTempModel->save();
                }
            }
        }
        else
        {
            echo "Sent Before :)";
        }
    }

    public function send_message($phone,$message,$instance_id = null,$access_token = null,$api_url = null){
        set_time_limit(0);
        $response_send_message   = KarzounRequest::resolve(
            $end_point    = 'https://chat-api.keytime.sa/v3/channels/966508760650@whatsapp.eazy.im/messages/'.$phone.'@whatsapp.eazy.im',
            $request_type = 'POST',
            $access_token = "Vf69ifPxjwghuN3nJB9BtgBBWFE5arZyFEf7dU6F",
            $post_fields  = '{
                "message": {
                    "template": {
                        "components": [
                            {
                                "parameters": [
                                    {
                                        "text": " Karzoun",
                                        "type": "text"
                                    },
                                    {
                                        "text": " 123456 ",
                                        "type": "text"
                                    }
                                ],
                                "type": "body"
                            }
                        ],
                        "language": {
                            "code": "ar"
                        },
                        "name": "'.$message.'"
                    },
                    "type": "template"
                }
            }'
        );
        return $response_send_message;
    }

}
