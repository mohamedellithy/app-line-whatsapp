<?php

use App\Models\Account;
use Illuminate\Support\Facades\Http;

if(!function_exists('formate_order_details')):
    function formate_order_details($order_details){
        $attrs = [];

        if(!isset($order_details['data']['status']['name'])):
            $attrs['order_status']   = $order_details['data']['status'];
            $attrs['order_id']       = isset($order_details['data']['order']) ? $order_details['data']['order']['reference_id'] : $order_details['data']['reference_id'];
            $attrs['payment_method'] = $order_details['data']['order']['payment_method'];
            $attrs['currency']       = $order_details['data']['order']['currency'];
            $attrs['order_amount']   = $order_details['data']['order']['amounts']['total']['amount'];
            $attrs['customer']       = $order_details['data']['order']['customer'];
            $attrs['customer_full_name']     = $attrs['customer']['name'];
            $attrs['customer_phone_number']  = $attrs['customer']['mobile'];
            $attrs['order_url']              = isset($order_details['data']['order']) ? $order_details['data']['order']['urls']['customer'] : "";
            $attrs['items']                  = isset($order_details['data']['order']) ? $order_details['data']['order']['items'] : "";
            $attrs['review_url']             = isset($order_details['data']['order']) ? $order_details['data']['order']['urls']['rating_link'] : "";
            $attrs['tracking_shipment']      = isset($order_details['data']['order']['shipping']['shipment']['tracking_link']) ? $order_details['data']['order']['shipping']['shipment']['tracking_link'] : "";
            $attrs['shipping_company']       = isset($order_details['data']['order']['shipping']['company']) ? $order_details['data']['order']['shipping']['company'] : "";
        else:
            $attrs['order_status']   = $order_details['data']['status']['name'];
            $attrs['order_id']       = isset($order_details['data']['order']) ? $order_details['data']['order']['reference_id'] : $order_details['data']['reference_id'];
            $attrs['payment_method'] = $order_details['data']['payment_method'];
            $attrs['currency']       = $order_details['data']['currency'];
            $attrs['order_amount']   = $order_details['data']['amounts']['total']['amount'];
            $attrs['customer']       = $order_details['data']['customer'];
            $attrs['customer_full_name']     = $attrs['customer']['first_name'].' '.$attrs['customer']['last_name'];
            $attrs['customer_phone_number']  = $attrs['customer']['mobile_code'].$attrs['customer']['mobile'];
            $attrs['order_url']              = $order_details['data']['urls']['customer'];
            $attrs['items']                  = $order_details['data']['items'];
            $attrs['review_url']             = isset($order_details['data']['order']) ? $order_details['data']['order']['urls']['rating_link'] : "";
            $attrs['tracking_shipment']      = isset($order_details['data']['shipping']['shipment']['tracking_link']) ? $order_details['data']['shipping']['shipment']['tracking_link'] : "";
            $attrs['shipping_company']       = isset($order_details['data']['shipping']['company']) ? $order_details['data']['shipping']['company'] : "";
            // $attrs['bank']                   = $order_details['data']['order']['bank'] ?: $order_details['data']['bank'];
        endif;
        return $attrs;
    }
endif;

if(!function_exists('formate_invoice_details')):
    function formate_invoice_details($invoice_details){
        $attrs['order_id']     = isset($invoice_details['data']['order_id']) ? $invoice_details['data']['order_id'] : "";
        $attrs['order_amount'] = isset($invoice_details['data']['total']) ? $invoice_details['data']['total']['amount'] : "";
        $attrs['currency']  = isset($invoice_details['data']['total']) ? $invoice_details['data']['total']['currency'] : "";
        $attrs['first_name']     = $invoice_details['data']['customer']['first_name'] ?: '-';
        $attrs['last_name']      = $invoice_details['data']['customer']['last_name']  ?: '-';
        $attrs['customer_phone_number']  = $invoice_details['data']['customer']['mobile_code'].$invoice_details['data']['customer']['mobile'];
        $attrs['items']          = $invoice_details['data']['items'] ?: null;
        $attrs['payment_method'] = $invoice_details['data']['payment_method'] ?: null;
        return $attrs;
    }
endif;

if(!function_exists('formate_cart_details')):
    function formate_cart_details($order_details){
        $attrs = [];

        $attrs['cart_total']   = $order_details['data']['total']['amount'];
        $attrs['cart_currency']       = $order_details['data']['total']['currency'];
        $attrs['cart_total_discount'] = $order_details['data']['total_discount']['amount'];
        $attrs['cart_checkout_url']   = $order_details['data']['checkout_url'];
        $attrs['cart_created_at']     = $order_details['data']['created_at']['date'];
        $attrs['customer_full_name']     = $order_details['data']['customer']['name'];
        $attrs['cart_customer_mobile']   = $order_details['data']['customer']['mobile'];
        $attrs['cart_customer_country']  = $order_details['data']['customer']['country'];
        $attrs['cart_customer_city']     = $order_details['data']['customer']['city'];
        return $attrs;
    }
endif;

if(!function_exists('formate_customer_details')):
    function formate_customer_details($customer_details){
        $attrs = [];

        $attrs['first_name']     = $customer_details['data']['first_name'] ?: '-';
        $attrs['last_name']      = $customer_details['data']['last_name']  ?: '-';
        $attrs['email']          = $customer_details['data']['email'] ?: '-';
        $attrs['front_customer_profile']   = isset($customer_details['data']['urls']) ? $customer_details['data']['urls']['customer'] : '-';
        $attrs['admin_customer_profile']   = isset($customer_details['data']['urls']) ? $customer_details['data']['urls']['admin'] : '-';
        $attrs['gender']         = $customer_details['data']['gender'] ?: '-';
        $attrs['birthday']       = isset($customer_details['data']['birthday']) ? $customer_details['data']['birthday']['date'] : '-';
        $attrs['timezone']       = isset($customer_details['data']['birthday']) ? $customer_details['data']['birthday']['timezone'] : '-';
        $attrs['customer_full_name']     = ($customer_details['data']['first_name'].' '.$customer_details['data']['last_name']) ?: '-';
        $attrs['customer_phone_number']  = ($customer_details['data']['mobile_code'].$customer_details['data']['mobile']) ?: '-';
        $attrs['city']                   = $customer_details['data']['city'] ?: '-';
        $attrs['country']                = $customer_details['data']['country'] ?: '-';
        $attrs['full_address']           = ($customer_details['data']['city'] .'-'. $customer_details['data']['country']) ?: '-';
        return $attrs;
    }
endif;

if(!function_exists('formate_customer_from_reviews_details')):
    function formate_customer_from_reviews_details($customer_details){
        $attrs = [];

        $attrs['rating_review']                 = $customer_details['data']['rating'] ?: null;
        $attrs['content_review']                = $customer_details['data']['content'] ?: null;
        $attrs['order_id']               = isset($customer_details['data']['order']['reference_id']) ? $customer_details['data']['order']['reference_id'] : $customer_details['data']['order']['id'];
        $attrs['order_amount']           = $customer_details['data']['order']['total']['amount'] ?: null;
        $attrs['currency']               = $customer_details['data']['order']['total']['currency'] ?: null;
        $attrs['customer_full_name']     = $customer_details['data']['customer']['name'] ?: null;
        $attrs['customer_phone_number']  = $customer_details['data']['customer']['mobile'] ?: null;
        $attrs['city']                   = $customer_details['data']['customer']['city'] ?: null;
        $attrs['country']                = $customer_details['data']['customer']['country'] ?: null;
        $attrs['full_address']           = ($customer_details['data']['customer']['city'] .'-'. $customer_details['data']['customer']['country']) ?: null;
        return $attrs;
    }
endif;


if(!function_exists('send_message')):
    function send_message(
        $phone_number = null,$message      = null,
        $instance_id  = null,$access_token = null,$media = null){
        set_time_limit(0);
        $instance_id  = $instance_id  ?: '64AC6D08A99C9'; // '64B280D831EC1'
        $access_token = $access_token ?: '649ba622aa900'; // '64b2763270e61'

        if($phone_number == null) return 'failed';

        $phone_number = str_replace('+','',$phone_number);

        if($media != null):
            $end_point    = "https://wh.line.sa/api/send?number=$phone_number&type=type&media_url=$media&message=$message&instance_id=$instance_id&access_token=$access_token";
        elseif($media == null):
            $end_point    = "https://wh.line.sa/api/send?number=$phone_number&type=text&message=$message&instance_id=$instance_id&access_token=$access_token";
        endif;
        try{
            $client   = new \GuzzleHttp\Client();
            $send_result         = $client->post($end_point);
            $body                = $send_result->getBody()->getContents();
            $result_send_message = json_decode($body, true); // Decode as associative array
            \Log::info($body);
        } catch(Exception $e){
            $result_send_message['status'] = 'failed';
            \Log::info($e->getMessage());
        }

        // if($result_send_message['stats'] == false):
        //     send_message_error($result_send_message['type_erro'],$instance_id);
        // endif;
        return isset($result_send_message['status']) ? $result_send_message['status'] : 'failed';
    }
endif;

if(!function_exists('send_message_error')):
    function send_message_error($type_erro,$instance_owner_id){
        $instance_id  = "64AC6D08A99C9";
        $access_token = "649ba622aa900";
        set_time_limit(0);

        $account = Account::where('token',$instance_owner_id)->first();

        $phone_number_filter = explode('@',$account->pid);
        $phone_number        = $phone_number_filter[0];

        if($type_erro == "expiration_date"):
            $message = "عزيزي العميل\n
                        تم انتهاء باقتك في رسائل واتساب لاين.\n
                        يمكنك تجديد الباقة مباشرة من صفحة واتساب لاين\n
                        https://line.sa/19505\n";

        elseif($type_erro == "count_messages"):
            $message = "عزيزي العميل\n
                        تم استنفاد باقتك في رسائل واتساب لاين.\n
                        يمكنك تجديد الباقة مباشرة من صفحة واتساب لاين\n
                        https://line.sa/19505\n";

        endif;

        if($message):
            $end_point    = "https://wh.line.sa/api/send?number=$phone_number&type=text&message=$message&instance_id=$instance_id&access_token=$access_token";
        endif;
        $client   = new \GuzzleHttp\Client();
        $send_result         = $client->post($end_point);
        $body                = $send_result->getBody()->getContents();
        return [
            'result ' => $body
        ];
    }
endif;

function message_order_params($message_to_send = '',$attrs = []){
    preg_match_all("/{(.*?)}/", $message_to_send, $search);
    foreach($search[1] as $variable):
        $orders_status = [
            'حالة_الطلب'             => isset($attrs["order_status"]) ? $attrs["order_status"] : null,
            'رقم_الطلب'              => isset($attrs["order_id"])     ? $attrs["order_id"] : null,
            'قيمة_الطلب'             => isset($attrs["order_amount"]) ? $attrs["order_amount"] : null,
            'اسم_العميل'             => isset($attrs["customer_full_name"]) ? $attrs["customer_full_name"] : null,
            'اسم ـ العميل'           => isset($attrs["customer_full_name"]) ? $attrs["customer_full_name"] : null,
            'اسم العميل'             => isset($attrs["customer_full_name"]) ? $attrs["customer_full_name"] : null,
            'العملة'                 => isset($attrs["currency"])           ?  $attrs["currency"] : null,
            'رابط_معلومات_الطلب'    => isset($attrs["order_url"])          ? $attrs["order_url"]: null,
            'شركة_الشحن'             => isset($attrs["shipping_company"])   ? $attrs["shipping_company"] : null,
            'تفاصيل_منتجات_الطلبية' => "",
            'زر_التأكيد'             => 'للتأكيد ارسل كلمة نعم, وللإلغاء ارسل كلمة إلغاء',
            'رابط_تتبع_الشحنة'       => isset($attrs["tracking_shipment"]) ? $attrs["tracking_shipment"] : null,

            /* OTP */
            'رمز_التحقق'             => isset($attrs['otp_code'])         ? $attrs['otp_code'] : null,
            'رمز التحقق'             => isset($attrs['otp_code'])         ? $attrs['otp_code'] : null,

            /* Customers info */
            'اسم_الاول'             => isset($attrs["first_name"])         ? $attrs["first_name"] : null,
            'اسم_الاخير'            => isset($attrs["last_name"])          ? $attrs["last_name"] : null,
            'الاسم_كاملا'            => isset($attrs["customer_full_name"]) ? $attrs["customer_full_name"] : null,
            'المدينة'              => isset($attrs["city"])               ? $attrs["city"] : null,
            'الدولة'               => isset($attrs["country"])            ? $attrs["country"] : null,
            'تاريخ_الميلاد'         => isset($attrs["birthday"])           ? $attrs["birthday"] : null,
            'البريد_الاكترونى'      => isset($attrs["email"])             ? $attrs["email"] : null,
            'رقم_الجوال'           => isset($attrs["customer_phone_number"])   ? $attrs["customer_phone_number"] : null,
            'النوع'                => isset($attrs["gender"])             ? $attrs["gender"] : null,
            'بروفايل_الزبون'      => isset($attrs["front_customer_profile"])   ? $attrs["front_customer_profile"] : null,
            'بروفايل_الزبون_على_لوحة_التحكم' => isset($attrs["admin_customer_profile"])   ? $attrs["admin_customer_profile"] : null,

            /* Cart details */
            'اجمالى_السلة'         => isset($attrs["cart_total"])           ? $attrs["cart_total"] : null,
            'عملة_السلة'           => isset($attrs["cart_currency"])        ? $attrs["cart_currency"] : null,
            'تخفيض_على_السلة'      => isset($attrs["cart_total_discount"])  ? $attrs["cart_total_discount"] : null,
            'رابط_الدفع'           => isset($attrs["cart_checkout_url"])     ? $attrs["cart_checkout_url"] : null,
            'تاريخ_انشاء_الطلب'   => isset($attrs["cart_created_at"])        ? $attrs["cart_created_at"] : null,
            'اسم_الزبون_السلة'    => isset($attrs["cart_customer_name"])     ? $attrs["cart_customer_name"] : null,
            'رقم_جوال_زبون_السلة' =>  isset($attrs["cart_customer_mobile"])  ? $attrs["cart_customer_mobile"] : null,
            'دولة_زبون_السلة'     =>  isset($attrs["cart_customer_country"])  ? $attrs["cart_customer_country"] : null,
            'مدينة_زبون_السلة'    => isset($attrs["cart_customer_city"])      ? $attrs["cart_customer_city"] : null,

            /* reviews */
            'التقيم'        =>  isset($attrs["rating_review"])  ? $attrs["rating_review"]  : null,
            'التقييم'       =>  isset($attrs["rating_review"])  ? $attrs["rating_review"]  : null,
            // =================================================================
            'نص_التقيم'     =>  isset($attrs["content_review"]) ? $attrs["content_review"] : null,
            'نص_التقييم'    =>  isset($attrs["content_review"]) ? $attrs["content_review"] : null,
            // =================================================================
            'رابط_التقييم'  =>  isset($attrs["review_url"]) ? $attrs["review_url"] : null
        ];

        if($variable == "كود_المنتج"){
            $code_list = [];
            foreach($attrs["items"] as $item){
                if(isset($item['codes'])):
                    foreach($item['codes'] as $code){
                        $code_list[] = $item['name'].'  :  '.$code['code'];
                    }
                endif;
            }

            $orders_status[$variable] = implode(PHP_EOL, $code_list);
        }

        elseif($variable == "تفاصيل_منتجات_الطلبية"){
            foreach ($attrs["items"] as $item){
                $total_amount  = isset($item['amounts']) ? $item['amounts']['total']['amount'] : $item['total']['amount'];
                $total_currency  = isset($item['amounts']) ? $item['amounts']['total']['currency'] : $item['total']['currency'];
                $product_list[] = $item['name'].'  :  '.$item['quantity'].'  :  '.$total_amount.''.$total_currency;
            }

            $orders_status[$variable] = implode(PHP_EOL, $product_list);
        }

        elseif($variable == "روابط_المنتجات"){
            foreach ($attrs["items"] as $item){
                $product_url_list[] = $item['name'].'  :  '.(isset($item['product']) ? $item['product']['url'] : "-");
            }

            $orders_status[$variable] = implode(PHP_EOL, $product_url_list);
        }
        
        elseif($variable == "الملفات"){
            $files_url_list = [];
            foreach ($attrs["items"] as $item){
                if(isset($item["files"])){
                    foreach ($item["files"] as $file){
                        $files_url_list[] = $file['name'].'  :  '.(isset($file['url']) ? $item['url'] : "-");
                    }     
                }
            }

            $orders_status[$variable] = implode(PHP_EOL, $files_url_list);
        }

        $params = isset($orders_status[$variable]) ? $orders_status[$variable] : $variable;
        $message_to_send = str_replace("{" . $variable . "}",$params, $message_to_send);
    endforeach;

    $message_to_send = urlencode($message_to_send);
    return $message_to_send;
}
