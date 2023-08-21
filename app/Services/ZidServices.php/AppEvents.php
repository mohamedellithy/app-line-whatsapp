<?php
namespace App\Services\ZidServices;

use App\Services\AppSettings\Events;
use Log;
class AppEvents extends Events{
    public $events = array();
    public $data   = array();
    public function events(){
        return [
           // here
        ];
    }
}

