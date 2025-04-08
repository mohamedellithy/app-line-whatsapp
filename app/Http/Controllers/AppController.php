<?php

namespace App\Http\Controllers;

use App\Models\SallaWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Cache;
use App\Services\SallaServices\AppEvents;

class AppController extends Controller
{
    public function make_event(Request $request){
        set_time_limit(-1);
        ini_set('max_execution_time', 0); //0=NOLIMIT

        $event_content = file_get_contents('php://input');
        $event         = json_decode($event_content,true);
        if(is_string($event)){
            $event = json_decode($event,true);
        }

        dispatch(function() use($event){
            $event_call = new AppEvents();
            $result = $event_call->make_event($event);
            return $result;
        });
    }
}
