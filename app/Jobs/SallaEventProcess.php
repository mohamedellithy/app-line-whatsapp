<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\SallaServices\AppEvents;
use Illuminate\Contracts\Queue\ShouldQueue;

class SallaEventProcess extends Job implements ShouldQueue
{
    /*
    |--------------------------------------------------------------------------
    | Queueable Jobs
    |--------------------------------------------------------------------------
    |
    | This job base class provides a central location to place any logic that
    | is shared across all of your jobs. The trait included with the class
    | provides access to the "queueOn" and "delay" queue helper methods.
    |
    */

    use InteractsWithQueue, Queueable, SerializesModels;

    protected $event;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    public function __construct($event)
    {
        $this->event = $event;
    }

    public function handle()
    {
        // $event_name  = (isset($this->event['event']) ? $this->event['event'] : '');
        // $merchant_id = (isset($this->event['merchant']) ? $this->event['merchant'] : '');
        // $data_id     = (isset($this->event['data']['id']) ? $this->event['data']['id'] : '');
       /// Cache::lock( 'event-'.$event_name.'-'.$merchant_id.'-'.$data_id,30)->get(function ()  {
            $event_call = new AppEvents();
            $result = $event_call->make_event($this->event);
            return $result;
        //});
        // $lock = Cache::lock(
        // 'event-'.(isset($this->event['event']) ? $this->event['event'] : '').'-'
        // .(isset($this->event['merchant']) ? $this->event['merchant'] : '')
        // .'-'.(isset($this->event['data']['id']) ? $this->event['data']['id'] : ''),30);
        // try{
        //     if($lock->get()){
        //         $event_call = new AppEvents();
        //         $result = $event_call->make_event($this->event);
        //         return $result;
        //     }
        // } catch(\Exception $e){
        //     \Log::info($e->getMessage());
        // } finally{
        //     $lock->release();
        // }
    }
}
