<?php
namespace App\Services\SallaServices;

use App\Models\FailedMessagesModel;
use App\Models\SuccessTempModel;
use App\Models\ReviewRequest;
use App\Services\AppSettings\AppMerchant;
use App\Services\AppSettings\AppEvent;
use Illuminate\Support\Facades\Http;
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
        $attr = formate_order_details($this->data);
        Http::post('https://webhook.site/19694e58-fa42-41d5-a247-2187b0718cf7',$this->data);

    }

    // public function resolve_event(){
    //     $this->store_id = $this->data->merchant;
    //     $this->order_id = $this->data->data->reference_id;
    //     if (isset($this->data->data->status->customized->name)){
    //         $this->order_status    = $this->data->data->status->customized->name;
    //         $order_status_id       = $this->data->data->status->customized->id;
    //     }
    //     else
    //     {
    //         $this->order_status    = $this->data->data->status->name;
    //         $order_status_id       = $this->data->data->status->id;
    //     }


    //     $this->shipping_company     = $this->data->data->shipping->company ?? 'توصيل خاص';
    //     $this->tracking_number      = $this->data->data->shipping->shipment->id ?? '(لم يتم تحديد رقم التتبع بعد)';
    //     $this->tracking_link        = $this->data->data->shipping->shipment->tracking_link ?? '(لم يتم انشاء رابط التتبع بعد)';
    //     $this->currency             = $this->data->data->currency;
    //     $this->order_url            = $this->data->data->urls->customer;
    //     $order_status_check         = $this->data->data->status->name;
    //     $payment_method             = $this->data->data->payment_method;
    //     $created_at           = $this->data->created_at;
    //     $customer_mobile      = $this->data->data->customer->mobile;
    //     $customer_mobile_code = $this->data->data->customer->mobile_code;
    //     $customer_mobile_code_clean = str_replace("+", "", $customer_mobile_code);
    //     $phone     = $customer_mobile_code_clean . $customer_mobile;
    //     $send_once = $this->order_id . $order_status_id . $phone;

    //     $count_data = SuccessTempModel::where("unique_number", $send_once)->count();

    //     if ($count_data === 0)
    //     {
    //         $this->order_amount  = $this->data->data->amounts->total->amount;
    //         $cart_currency       = $this->data->data->amounts->total->currency;
    //         $customer_first_name = $this->data->data->customer->first_name;
    //         $customer_last_name  = $this->data->data->customer->last_name;
    //         $this->customer_full_name  = $customer_first_name . ' ' . $customer_last_name;
    //         $customer_email      = $this->data->data->customer->email;

    //         /******* start database pull ************/
    //         $array           =  $this->get_merchant_settings();
    //         $get_instances   =  $this->get_instances_data();
    //         list($instance_id,$api_url) = $get_instances;


    //         $cod_msg_check_if_active     = $array->data->settings->karzoun_cod_msg_check ?? 0;
    //         $karzoun_review_check_status = $array->data->settings->karzoun_review_check_status ?? 'delivered';

    //         /**********message type start**********/
    //         $event = $this->data->event;
    //         if ($event == 'order.created' && $cod_msg_check_if_active == 1 && $payment_method == 'cod')
    //         {
    //             $send_or_not = 1;
    //             $message_to_send = $array->data->settings->karzoun_cod_msg;
    //         }else{
    //             $status = [
    //                 'قيد التنفيذ'          => '_order_proccessing_',
    //                 'بإنتظار المراجعة'     => '_order_on_hold_',
    //                 'تم التنفيذ'           => '_order_completed_',
    //                 'بإنتظار الدفع'        => '_order_pending_payment_',
    //                 'تم التوصيل'           => '_order_delivered_',
    //                 'ملغي'                 => '_order_canceled_',
    //                 'جاري التوصيل'        => '_on_delivery_',
    //                 'مسترجع'               => '_order_refunded_',
    //                 'قيد الإسترجاع'        => '_order_refunding_',
    //                 'تم الشحن'            => '_shipped_'
    //             ];
    //             $msg             = 'karzoun'.$status[$order_status_check].'msg';
    //             $msg_check       = 'karzoun'.$status[$order_status_check].'msg_check';
    //             $send_or_not     = $array->data->settings->$msg;
    //             $message_to_send = $array->data->settings->$msg_check;
    //             $review_msg_check_if_active = $array->data->settings->karzoun_review_msg_check ?? 0;

    //             // here add review request on order
    //             $this->review_request($order_status_check,
    //                                   $review_msg_check_if_active,
    //                                   $karzoun_review_check_status);

    //             if(!isset($status[$order_status_check])){
    //                 $send_or_not = 0;
    //                 return;
    //             }
    //         }

    //         $message_to_send = $this->handle_message_to_send($message_to_send);

    //         /**********message type end**********/
    //         if ($send_or_not == 1){
    //             echo 'started to send';
    //             $check_if_sent  =  $this->send_message($phone,$message_to_send,$instance_id,$this->store_id,$api_url);
    //             $response       =  json_encode($check_if_sent);
    //             $log = $send_once . ' ' . $created_at . PHP_EOL;
    //             if (!empty($check_if_sent))
    //             {
    //                 if ($check_if_sent->status == 'success')
    //                 {
    //                     $SuccessTempModel = new SuccessTempModel();
    //                     $SuccessTempModel->unique_number = $send_once;
    //                     $SuccessTempModel->values        = json_encode($this->data);
    //                     $SuccessTempModel->type          = "$event";
    //                     $SuccessTempModel->event_from    = "salla";
    //                     if ($event == 'order.created'):
    //                         $SuccessTempModel->status    = "true";
    //                     endif;
    //                     $SuccessTempModel->save();
    //                     echo ' Sent :)';
    //                 }
    //                 else
    //                 {
    //                     $FailedMessagesModel                = new FailedMessagesModel();
    //                     $FailedMessagesModel->unique_number = $send_once;
    //                     $FailedMessagesModel->value         = json_encode($this->data);
    //                     $FailedMessagesModel->reason        = $response;
    //                     $FailedMessagesModel->type          = "$event";
    //                     $FailedMessagesModel->event_from    = "salla";
    //                     $FailedMessagesModel->save();
    //                     echo 'the response is' . $response;
    //                 }
    //             }
    //             else
    //             {
    //                 $FailedMessagesModel                = new FailedMessagesModel();
    //                 $FailedMessagesModel->unique_number = $send_once;
    //                 $FailedMessagesModel->value         = json_encode($this->data);
    //                 $FailedMessagesModel->reason        = $response;
    //                 $FailedMessagesModel->type          = "$event";
    //                 $FailedMessagesModel->event_from    = "salla";
    //                 $FailedMessagesModel->save();
    //                 echo 'the response is' . $response;
    //             }
    //         }
    //         else
    //         {
    //             echo 'set to not send :)';
    //             // here i am also adding order created webhooks to check if order created or not in database for abandoned carts
    //             if ($event == 'order.created'){
    //                 $SuccessTempModel                = new SuccessTempModel();
    //                 $SuccessTempModel->unique_number = $send_once;
    //                 $SuccessTempModel->values        = json_encode($this->data);
    //                 $SuccessTempModel->type          = "$event";
    //                 $SuccessTempModel->event_from    = "salla";
    //                 $SuccessTempModel->status        = "false";
    //                 $SuccessTempModel->save();
    //             }
    //         }
    //     }
    //     else
    //     {
    //         echo "Sent Before :)";
    //     }
    // }

    public function review_request($order_status_check,
                                   $review_msg_check_if_active,
                                   $karzoun_review_check_status){
        $reviews = [
            'تم التنفيذ' => 'completed',
            'تم التوصيل' => 'delivered',
            'تم الشحن'   => 'shipped'
        ];

        if ($reviews[$order_status_check]){
            if ($review_msg_check_if_active == 1 && $karzoun_review_check_status == $reviews[$order_status_check]){
                $chec_aband = ReviewRequest::where("data", "like", "%$this->order_id%")->first();
                if (empty($chec_aband)){
                    $appand_review       = new ReviewRequest();
                    $appand_review->data = json_encode($this->data);
                    $appand_review->save();
                }
            }
        }
    }

    public function handle_message_to_send($message_to_send = ''){
        preg_match_all("/{(.*?)}/", $message_to_send, $search);
        foreach ($search[1] as $variable):
            $digital_products_codes = '';
            $orders_status = [
                'حالة الطلب'          => $this->order_status,
                'رقم الطلب'           => $this->order_id,
                'قيمة الطلب'          => $this->order_amount,
                'اسم العميل'          => $this->customer_full_name,
                'العملة'              => $this->currency,
                'رابط معلومات الطلب' => $this->order_url,
                'رقم التتبع'          => $this->tracking_number,
                'رابط التتبع'         => $this->tracking_link,
                'شركة الشحن'          => $this->shipping_company,
                'كود المنتج'          => $digital_products_codes,
                'زر التأكيد'          => 'للتأكيد ارسل كلمة نعم, وللإلغاء ارسل كلمة إلغاء',
            ];

            if ($variable == "كود المنتج"){
                foreach ($this->data->data->items as $item){
                    foreach ($item->codes as $code){
                        $code_list[] = $item->name.'  :  '.$code->code;
                    }
                }

                $digital_products_codes = implode(PHP_EOL, $code_list);
            }

            $message_to_send = str_replace("{" . $variable . "}", $orders_status[$variable], $message_to_send);
        endforeach;

        $message_to_send = urlencode($message_to_send);
        return $message_to_send;
    }

}
