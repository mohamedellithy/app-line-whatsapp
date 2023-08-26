<?php


function formate_order_details($order_details){
    $attrs = [];

    $attrs['order_status']   = $order_details['data']['status']['slug'];
    $attrs['payment_method'] = $order_details['data']['payment_method'];
    $attrs['currency']       = $order_details['data']['currency'];
    $attrs['amounts']        = $order_details['data']['amounts']['total']['amount'];
    $attrs['customer']       = $order_details['data']['customer'];
    $attrs['items']          = $order_details['data']['items'];
    $attrs['bank']           = $order_details['data']['bank'];


    return $attrs;

}
