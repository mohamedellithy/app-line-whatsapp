<?php namespace App\Services\AppSettings;

use App\Models\Team;
use App\Models\SpUser;
use App\Models\SpWhatsAppState;
use App\Models\MerchantCredential;

class CheckMerchant {
    public static function Validate($data){
        // get merchant info
        $merchant_info = MerchantCredential::where([
            'merchant_id'    => $data['merchant']
        ])->first();

        // if merchant info is not exist
        if(!$merchant_info) return abort(200);

        // get merchant team
        $merchant_team = Team::with('account')->where([
            'owner' => $merchant_info->user_id
        ])->first();

        // if merchant team is not exist
        if(!$merchant_team) return abort(200);

        // user info not exist
        $user_info = SpUser::where([
            'id' => $merchant_info->user_id
        ])->whereIn('login_type',['salla'])->first();

        // if user is not exist
        if(!$user_info) return abort(200);

        // is not active 
        if($user_info->status != 2) return abort(200);

        // permissions is exist
        if(!$user_info->permissions) return abort(200);

        // check user expiration date
        if($user_info->expiration_date != 0){
            // expiration date
            if(strtotime('now') > $user_info->expiration_date){
                return abort(200);
            }

            // whatsapp state
            $SpWhatsAppState = SpWhatsAppState::where([
                'team_id' => $merchant_team->id
            ])->first();

            // formate permissions
            $permission = json_decode($user_info->permissions,true);

            // if count messages is less than from limit
            if($SpWhatsAppState->wa_total_sent_by_month > $permission['whatsapp_message_per_month']){
                return abort(200);
            }
        }
    }
}
