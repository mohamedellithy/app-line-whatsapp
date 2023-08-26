<?php

use Illuminate\Support\Facades\Http;

if(!function_exists('formate_order_details')):
    function formate_order_details($order_details){
        $attrs = [];

        $attrs['order_status']   = $order_details['data']['status']['name'];
        $attrs['order_id']       = $order_details['data']['id'];
        $attrs['payment_method'] = $order_details['data']['payment_method'];
        $attrs['currency']       = $order_details['data']['currency'];
        $attrs['order_amount']   = $order_details['data']['amounts']['total']['amount'];
        $attrs['customer']       = $order_details['data']['customer'];
        $attrs['customer_full_name']     = $attrs['customer']['first_name'].' '.$attrs['customer']['last_name'];
        $attrs['customer_phone_number']  = $attrs['customer']['mobile_code'].$attrs['customer']['mobile'];
        $attrs['order_url']              = $order_details['data']['urls']['customer'];
        $attrs['items']                  = $order_details['data']['items'];
        $attrs['bank']                   = $order_details['data']['bank'];
        $attrs['shipping_company']       = $order_details['data']['shipping_company'];
        return $attrs;
    }
endif;


if(!function_exists('send_message')):
    function send_message(
        $phone_number = null,$message      = null,
        $instance_id  = null,$access_token = null){

        $instance_id  = $instance_id  ?: '64AC6D08A99C9';
        $access_token = $access_token ?: '649ba622aa900';

        if($phone_number == null) return 'failed';

        $phone_number = str_replace('+','',$phone_number);

        $result_send_message   = Http::post(
            $end_point    = "https://wh.line.sa/api/send?number=$phone_number&type=text&message=$message&instance_id=$instance_id&access_token=$access_token"
        );

        return $result_send_message['status'];
    }
endif;



function message_order_params($message_to_send = '',$attrs = []){
    preg_match_all("/{(.*?)}/", $message_to_send, $search);
    Http::post('https://webhook.site/19694e58-fa42-41d5-a247-2187b0718cf7',$search);
    foreach($search[1] as $variable):
        $orders_status = [
            'حالة_الطلب'             => $attrs["order_status"],
            'رقم_الطلب'              => $attrs["order_id"],
            'قيمة_الطلب'             => $attrs["order_amount"],
            'اسم_العميل'             => $attrs["customer_full_name"],
            'العملة'                 => $attrs["currency"],
            'رابط_معلومات_الطلب'    => $attrs["order_url"],
            'شركة_الشحن'             => $attrs["shipping_company"],
            'كود_المنتج'             => "",
            'تفاصيل_منتجات_الطلبية' => "",
            'زر_التأكيد'             => 'للتأكيد ارسل كلمة نعم, وللإلغاء ارسل كلمة إلغاء',
        ];

        if($variable == "كود_المنتج"){
            foreach ($attrs["items"] as $item){
                foreach ($item->codes as $code){
                    $code_list[] = $item->name.'  :  '.$code->code;
                }
            }

            $orders_status[$variable] = implode(PHP_EOL, $code_list);
        }

        elseif($variable == "تفاصيل_منتجات_الطلبية"){
            foreach ($attrs["items"] as $item){
                $product_list[] = $item->name.'  :  '.$item->quantity.'  :  '.$item->amounts;
            }

            $orders_status[$variable] = implode(PHP_EOL, $product_list);
        }

        $message_to_send = str_replace("{" . $variable . "}", $orders_status[$variable], $message_to_send);
    endforeach;

    $message_to_send = urlencode($message_to_send);
    return $message_to_send;
}