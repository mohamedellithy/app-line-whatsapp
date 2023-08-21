<?php
namespace App\Services\SallaServices;

use Log;
use Illuminate\Support\Facades\Http;
use App\Services\AppSettings\AppEvent;

class Authorize implements AppEvent{
    public $data;
    public function __construct($data){
        // set data
        $this->data = $data;

        // track event by using Log
        $this->set_log();
    }

    public function set_log(){
        // encode log
        $log = json_encode($this->data, JSON_UNESCAPED_UNICODE) . PHP_EOL;

        // set log data
        Log::channel('auth_events')->info($log);
    }

    public function resolve_event(){
        // if (User::check_user_exist($this->data)) {
        //     echo "user exist before";
        // } else {
        //     User::create_new_user($this->data);
        // }
        Http::post('https://webhook.site/f032ba41-f451-4aba-a8b3-a97fbff114de',[
            'test' => 'hi'
        ]);
    }

}
