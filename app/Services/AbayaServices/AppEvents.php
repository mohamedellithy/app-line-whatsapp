<?php
namespace App\AppServices\AbayaServices;

use App\AppServices\AppSettings\Events;
use Log;
class AppEvents extends Events
{
    public $events = array();
    public $data   = array();
    public function events(){
        return [
          'app.settings.updated'     => 'SettingsUpdate',
          'order.created'            => 'Order',
          'order.updated'            => 'Order'
        ];
    }
}
