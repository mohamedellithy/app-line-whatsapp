<?php
namespace App\Services\AppSettings;
class KarzounRequest
{
    public static function resolve($end_point = 'https://api.salla.dev/',
        $request_type = 'GET',$access_token   = null,$post_fields  = array()){
        // Getting the store_id from access token callback
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "{$end_point}");
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "{$request_type}");
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$access_token}",
            "Content-Type: application/json"
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        if($post_fields):
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_fields));
        endif;

        $response    = curl_exec($curl);
        $results     = json_decode($response);
        $curel_error =  curl_errno($curl);
        curl_close($curl);
        return $results;
    }
}
