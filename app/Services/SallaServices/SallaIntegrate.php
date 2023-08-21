<?php namespace App\Services\SallaServices;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\AppSettings\AppIntegrate as AppIntegrate;
class SallaIntegrate extends Credential implements AppIntegrate{
    public function redirect(Request $request) : string{
       return $this->callback($request);
    }

    public function callback(Request $request){
        $response = Http::withOptions([
            'verify' => false,
        ])->asForm()->post(self::$auth_endPoint . '/oauth2/token', [
            'client_id'     => env('SALLA_CLIENT_ID'),
            'client_secret' => env('SALLA_CLIENT_SECRET'),
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => env('APP_URL')."/login/salla",
            'scope'         => 'offline_access',
            'code'          => $request->input('code') // grant code
        ]);

        if($response->successful()):
            $user     = $request->user();
            $data     = $response->json();
            /* authorizer info & store info */
            $authorizer_info           = $this->authorizer_info($data);
            $store                     = $this->authorizer_store($data);
            $data['store_id']          = $store['data']['id'];
            $data['store_url']         = $store['data']['domain'];
            $data['merchant_email']    = $authorizer_info['data']['email']  ?: null;
            $data['merchant_phone']    = $authorizer_info['data']['mobile'] ?: null;
            $data['app_name']          = 'salla';

            /** insert data on merchant account */
            $user->zid_config()->updateOrCreate(
                ['user_id' => $user->id],
                $data
            );
            return 'app-integrated';
        else:
            return 'integrated-failed';
        endif;
    }

    public function authorizer_info(Array $credentials){
        $response = $this->resolve_call('/oauth2/user/info','get',[],$credentials);
        return $response->json();
    }

    public function authorizer_store(Array $credentials){
        $response = $this->resolve_call('/store/info','get',[],$credentials);
        return $response->json();
    }

    public static function refresh_merchant_token($user){

        if(!$user->zid_config) return;

        $response = Http::asForm()->withOptions([
            'verify' => false,
        ])->post(self::$auth_endPoint . '/oauth2/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $user->zid_config->refresh_token, // your merchant refresh token
            'client_id'     => env('SALLA_CLIENT_ID'),
            'client_secret' => env('SALLA_CLIENT_SECRET'),
            'redirect_uri'  => env('APP_URL')."/login/salla"
        ]);

        if($response->successful()):
            $user->zid_config()->updateOrCreate(
                ['user_id' => $user->id],
                $response->json()
            );
            return $user->zid_config;
        else:
            return $response->error();
        endif;
    }
}
