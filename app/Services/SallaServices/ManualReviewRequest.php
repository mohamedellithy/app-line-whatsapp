<?php
namespace App\Services\SallaServices;
use App\Services\AppSettings\KarzounRequest;
use App\Services\AppSettings\AppEvent;
use Log;
class ManualReviewRequest implements AppEvent{
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
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/manual_reviews_events.log'),
        ])->info($log);
    }

    public function resolve_event(){
        // send message
        // 
    }

}
