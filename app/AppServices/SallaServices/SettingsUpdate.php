<?php
namespace App\AppServices\SallaServices;

use App\Models\Team;
use App\AppServices\AppSettings\AppEvent;
use Log;
class SettingsUpdate implements AppEvent{

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
        Log::channel('settings_update_events')->info($log);
    }

    public function resolve_event(){
        $merchant_id = $this->data->merchant;
        $settings_update = Team::where('ids',$merchant_id)->update([
            'data' => serialize( $this->data )
        ]);

        if ($settings_update) {
            echo 'saved';
        } else {
            echo "Error updating record: ";
        }
    }
}