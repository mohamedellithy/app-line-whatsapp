<?php namespace App\Services\AppSettings;

use App\Models\Team;
use App\Models\SpUser;
use App\Models\SpWhatsAppState;
use App\Models\MerchantCredential;
use App\Exceptions\MerchantValidateException;

class CheckMerchant {
    public static function Validate($data){
        \Log::info($data['merchant']);
        // get merchant info
        $merchant_info = MerchantCredential::where([
            'merchant_id'    => $data['merchant']
        ])->first();

        // if merchant info is not exist
        if(!$merchant_info) throw new MerchantValidateException("Merchant not found",200);

        // get merchant team
        $merchant_team = Team::with('account')->where([
            'owner' => $merchant_info->user_id
        ])->first();

        // if merchant team is not exist
        if(!$merchant_team) throw new MerchantValidateException("Merchant team not found",200);

        // user info not exist
        $user_info = SpUser::where([
            'id' => $merchant_info->user_id
        ])->whereIn('login_type',['salla'])->first();

        // if user is not exist
        if(!$user_info) throw new MerchantValidateException("User not found",200);

        // is not active 
        if($user_info->status != 2) throw new MerchantValidateException("User is not active",200);

        // check user expiration date
        if($user_info->expiration_date != 0){
            // expiration date
            if(strtotime('now') > $user_info->expiration_date){
                throw new MerchantValidateException("User is expired",200);
            }

            // whatsapp state
            $SpWhatsAppState = SpWhatsAppState::where([
                'team_id' => $merchant_team->id
            ])->first();

            // whatsapp state is Not exist
            if(!$SpWhatsAppState) throw new MerchantValidateException("whatsapp state is Not exist",200);

            // permissions is exist
            if(!$merchant_team->permissions) throw new MerchantValidateException("Permissions not found",200);

            // formate permissions
            $permission = json_decode($user_info->permissions,true);

            // if count messages is less than from limit
            if($SpWhatsAppState->wa_total_sent_by_month > $permission['whatsapp_message_per_month']){
                throw new MerchantValidateException("Count messages is less than from limit",200);
            }
        }
    }
}
