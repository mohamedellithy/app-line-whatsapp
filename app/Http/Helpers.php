<?php

use Illuminate\Support\Facades\Http;

if(!function_exists('formate_order_details')):
    function formate_order_details($order_details){
        $attrs = [];

        $attrs['order_status']   = $order_details['data']['status']['slug'];
        $attrs['payment_method'] = $order_details['data']['payment_method'];
        $attrs['currency']       = $order_details['data']['currency'];
        $attrs['amounts']        = $order_details['data']['amounts']['total']['amount'];
        $attrs['customer']       = $order_details['data']['customer'];
        $attrs['customer_phone_number']  = $attrs['customer']['mobile_code'].$attrs['customer']['mobile'];
        $attrs['items']          = $order_details['data']['items'];
        $attrs['bank']           = $order_details['data']['bank'];
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

        return $result_send_message;
    }
endif;