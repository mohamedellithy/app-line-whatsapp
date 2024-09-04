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
        set_time_limit(0);
        ini_set('max_execution_time', 0); //0=NOLIMIT

        $event_content = file_get_contents('php://input');
        $event         = json_decode($event_content,true);
        \Log::info(is_array($event));
        \Log::info($event['event']);
        \Log::info($event);
        
        $event_id = isset($event['data']) ? (isset($event['data']['id']) ? $event['data']['id'] : rand(1,1000)) : rand(1,1000);
        $lock  = Cache::lock("event_no_".$event_id,2);
        if($lock->get()){
            $event_call = new AppEvents();
            $result = $event_call->make_event($event);
            return $result;
        }
        // $salla_webhooks = SallaWebhook::updateOrCreate([
        //     'event' => json_encode($request->all())
        // ]);
        
        // if($salla_webhooks){
        //     return response()->json([
        //         'status' => 200
        //     ]);
        // }
    }
}
