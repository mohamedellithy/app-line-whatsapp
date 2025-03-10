<?php
namespace App\Services\SallaServices;

use Log;
use App\Services\SallaServices\User;
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
        \Log::info($log);
    }

    public function resolve_event(){
        $user = new User();
        if ($user->check_user_exist($this->data)) {
            echo "user exist before";
        } else {
            $user->create_new_user($this->data);
        }

        return response()->json([
            'success' => true
        ],200);
    }

}
