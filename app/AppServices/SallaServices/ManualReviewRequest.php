<?php
namespace App\AppServices\SallaServices;
use App\AppServices\AppSettings\KarzounRequest;
use App\AppServices\AppSettings\AppEvent;
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
        $karzoun_send_message   = KarzounRequest::resolve(
            $end_point    = "https://karzoun.app/api/send.php?number=905316836668&type=text&message=123123123&instance_id=123123123&access_token=123123123",
            $request_type = 'POST'
        );
    }

}
