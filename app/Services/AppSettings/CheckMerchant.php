<?php namespace App\Services\AppSettings;

use App\Models\Team;
use App\Models\SpUser;
use App\Models\SpWhatsAppState;
use App\Models\MerchantCredential;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CheckMerchant {
    public static function Validate($data){
        // get merchant info
        $merchant_info = MerchantCredential::where([
            'merchant_id'    => $data['merchant']
        ])->first();

        // if merchant info is not exist
        if(!$merchant_info) throw new HttpException(200, "Merchant not found");

        // get merchant team
        $merchant_team = Team::with('account')->where([
            'owner' => $merchant_info->user_id
        ])->first();

        // if merchant team is not exist
        if(!$merchant_team) throw new HttpException(200,"Merchant team not found");

        // user info not exist
        $user_info = SpUser::where([
            'id' => $merchant_info->user_id
        ])->whereIn('login_type',['salla'])->first();

        // if user is not exist
        if(!$user_info) throw new HttpException(200,"User not found");

        // is not active 
        if($user_info->status != 2) throw new HttpException(200,"User is not active");

        // permissions is exist
        if(!$user_info->permissions) throw new HttpException(200,"Permissions not found");

        // check user expiration date
        if($user_info->expiration_date != 0){
            // expiration date
            if(strtotime('now') > $user_info->expiration_date){
                throw new HttpException(200,"User is expired");
            }

            // whatsapp state
            $SpWhatsAppState = SpWhatsAppState::where([
                'team_id' => $merchant_team->id
            ])->first();

            // formate permissions
            $permission = json_decode($user_info->permissions,true);

            // if count messages is less than from limit
            if($SpWhatsAppState->wa_total_sent_by_month > $permission['whatsapp_message_per_month']){
                throw new HttpException(200,"Count messages is less than from limit");
            }
        }
    }
}
