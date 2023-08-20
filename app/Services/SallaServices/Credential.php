<?php
namespace App\Services\SallaService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Log;
use App\Services\InterfaceService\Credential as InterfaceCredential;
class Credential implements InterfaceCredential {
    protected static $auth_endPoint   = "https://accounts.salla.sa";
    private $api_endPoint             = "https://api.salla.dev/admin/v2";
    public  $merchant                 = null;

    public function merchant(): void {
        $this->merchant = request()->user()->zid_config ?: null;
    }

    public function resolve_call($path = null,$method = 'get',$body = array(),$credentials = array()) : Object
    {
        $Headers = [
            "Accept-Language" => "ar",
            "Content-Type"    => "application/json",
            "ROLE"            => "Manager",
        ];

        if(isset($credentials['access_token']) || isset($this->merchant->access_token)):
            $Headers["Authorization"]     = ' Bearer '.(isset($credentials['access_token']) ? $credentials['access_token'] : $this->merchant->access_token);
        endif;
        

        $response = Http::withOptions([
            'verify' => false,
        ])->withHeaders($Headers)->$method($this->api_endPoint.$path,$body);

        return $response;
    }
}
