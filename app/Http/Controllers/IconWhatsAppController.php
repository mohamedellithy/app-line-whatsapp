<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\SallaServices\AppEvents;
use App\Models\MerchantCredential;
use App\Models\SpUser;
class IconWhatsAppController extends Controller
{
    public function icon_whatsapp(Request $request,$storeId){
        $merchant_info = MerchantCredential::where([
            'app_name'       => 'salla',
            'merchant_id'    => $storeId
        ])->first();
        
        $user = SpUser::where([
            'id' => $merchant_info->user_id
        ])->first();
        
        $setting_merchant = json_decode($merchant_info->settings,true);
        
        if(isset($setting_merchant['from_left_to_right'])){
            if($setting_merchant['from_left_to_right'] == 'right'){
                $setting_merchant['whatsapp_icon_right'] = $setting_merchant['whatsapp_icon_right'] ?: "20px";
                $setting_merchant['whatsapp_icon_left']  = null;
            } elseif($setting_merchant['from_left_to_right'] == 'left'){
                $setting_merchant['whatsapp_icon_left'] = $setting_merchant['whatsapp_icon_left'] ?: "20px";
                $setting_merchant['whatsapp_icon_right']  = null;
            }
        }
        
        if(isset($setting_merchant['from_top_to_bottom'])){
            if($setting_merchant['from_top_to_bottom'] == 'bottom'){
                $setting_merchant['whatsapp_icon_bottom'] = $setting_merchant['whatsapp_icon_bottom'] ?: "20px";
                $setting_merchant['whatsapp_icon_top']  = null;
            } elseif($setting_merchant['from_top_to_bottom'] == 'top'){
                $setting_merchant['whatsapp_icon_top'] = $setting_merchant['whatsapp_icon_top'] ?: "20px";
                $setting_merchant['whatsapp_icon_bottom']  = null;
            }
        }
        
        return response()->json([
            'settings'  => $setting_merchant,
            'plan_free' => ($user ? ($user->plan == 34 ? true : false) : true),
            'allow'    => isset($setting_merchant['wahtsapp_icon_status']) ? $setting_merchant['wahtsapp_icon_status'] : false,
            'styles'   => [
                "whatsapp_button" => [
                    "position"  => "float",
                    "right"     => isset($setting_merchant['whatsapp_icon_right']) ? $setting_merchant['whatsapp_icon_right'] : null,
                    "left"     => isset($setting_merchant['whatsapp_icon_left']) ? $setting_merchant['whatsapp_icon_left'] : null,
                    "bottom"    => isset($setting_merchant['whatsapp_icon_bottom']) ? $setting_merchant['whatsapp_icon_bottom'] : null,
                    "top"    => isset($setting_merchant['whatsapp_icon_top']) ? $setting_merchant['whatsapp_icon_top'] : null,
                    "height"    => isset($setting_merchant['whatsapp_icon_height']) ? $setting_merchant['whatsapp_icon_height'] : null,
                    "width"     => isset($setting_merchant['whatsapp_icon_width']) ? $setting_merchant['whatsapp_icon_width'] : "100px",
                    "position"  => "fixed",
                    "cursor"    => "pointer",
                    "display"   => "flex"
                ],
                "whatsapp_image" => [
                    "width"     => "95%"
                ]
            ],
            'phone'  => isset($setting_merchant['custom_merchant_phone']) ? $setting_merchant['custom_merchant_phone'] : $merchant_info->phone
        ]);
    }
}
