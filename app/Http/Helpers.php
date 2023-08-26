<?php


function formate_order_details($order_details){
    $attrs = [];

    $attrs['order_status']   = $order_details['data']['status']['slug'];
    $attrs['payment_method'] = $order_details['data']['payment_method'];
    $attrs['currency']       = $order_details['data']['currency'];
    $attrs['amounts']        = $order_details['data']['amounts']['total']['amount'];

    return $attrs;

}

// function send_message($merchant){
//     //if($merchant == null)
//     $instance_id  = '64AC6D08A99C9';
//     $access_token = '649ba622aa900';
//     $temp = '201026051966' ?: $merchant_credentails->phone;
//     // message text
//     $message = urlencode("
//         تهانينا 😀👏
//         تم انشاء حسابك على منصة line.sa بنجاح
//         تفاصيل الحساب
//         👈 البريد الالكترونى : {$new_account->email} \n
//         👈 اسم المستخدم : {$new_account->username} \n
//         👈 كلمة المرور  : {$password} \n
//         👈 رابط المنصة : {$platform_link} \n
//     ");

//     // send message with all info and it was installed succefully
//     $karzoun_send_message   = Http::post(
//         $end_point    = "https://wh.line.sa/api/send?number=$temp&type=text&message=$message&instance_id=$instance_id&access_token=$access_token"
//     );
// }
