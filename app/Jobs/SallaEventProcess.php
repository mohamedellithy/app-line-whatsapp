<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
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

    public function __construct($event)
    {
        $this->event = $event;
    }

    public function handle()
    {
        // try{
            $event_call = new AppEvents();
            $result = $event_call->make_event($this->event);
            return $result;
        // } catch(\Exception $e){

        // };
    }
}
