<?php
namespace App\AppServices\ZidServices;

use App\AppServices\AppSettings\Events;
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

